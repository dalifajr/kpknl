<?php

namespace App\Services;

use App\Models\BmnPotensiIdle;
use App\Models\BmnEksIdle;
use App\Models\SyncLog;
use App\Models\AppSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GoogleSheetSyncService
{
    /**
     * Convert standard Google Sheet URL to xlsx export format
     */
    public function convertToExportUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        if (preg_match('/\/d\/([a-zA-Z0-9-_]{10,})/', $url, $matches)) {
            return "https://docs.google.com/spreadsheets/d/{$matches[1]}/export?format=xlsx";
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return null;
    }

    /**
     * Coordinate mapper for locations in South Sumatra
     */
    protected function getCoordinatesForLocation(string $locationText, string $satkerText = ''): array
    {
        $text = strtolower($locationText . ' ' . $satkerText);

        if (str_contains($text, 'baturaja') || str_contains($text, 'komering ulu') || str_contains($text, 'oku')) {
            return ['lat' => -4.1311, 'lng' => 104.1748];
        }
        if (str_contains($text, 'prabumulih') || str_contains($text, 'cambai') || str_contains($text, 'sindur')) {
            return ['lat' => -3.4328, 'lng' => 104.2372];
        }
        if (str_contains($text, 'ogan ilir') || str_contains($text, 'pemulutan') || str_contains($text, 'seri bandung') || str_contains($text, 'pulau negara') || str_contains($text, 'indralaya')) {
            return ['lat' => -3.2241, 'lng' => 104.6543];
        }
        if (str_contains($text, 'banyuasin') || str_contains($text, 'pangkalan balai')) {
            return ['lat' => -2.8833, 'lng' => 104.3833];
        }
        if (str_contains($text, 'lahat')) {
            return ['lat' => -3.7865, 'lng' => 103.5412];
        }
        if (str_contains($text, 'muara enim')) {
            return ['lat' => -3.6542, 'lng' => 103.7745];
        }
        if (str_contains($text, 'lubuklinggau') || str_contains($text, 'musi rawas')) {
            return ['lat' => -3.2964, 'lng' => 102.8617];
        }
        if (str_contains($text, 'sekayu') || str_contains($text, 'musi banyuasin')) {
            return ['lat' => -2.8906, 'lng' => 103.8118];
        }
        if (str_contains($text, 'kayuagung') || str_contains($text, 'oki')) {
            return ['lat' => -3.3986, 'lng' => 104.8384];
        }
        if (str_contains($text, 'bangka') || str_contains($text, 'pangkalpinang')) {
            return ['lat' => -2.1290, 'lng' => 106.1129];
        }
        if (str_contains($text, 'belitung') || str_contains($text, 'tanjung pandan')) {
            return ['lat' => -2.7411, 'lng' => 107.6329];
        }
        if (str_contains($text, 'jambi') || str_contains($text, 'muaro jambi')) {
            return ['lat' => -1.6101, 'lng' => 103.6131];
        }

        // Default Kota Palembang
        $offsetLat = (rand(-20, 20) / 1000.0);
        $offsetLng = (rand(-20, 20) / 1000.0);
        return ['lat' => -2.9761 + $offsetLat, 'lng' => 104.7754 + $offsetLng];
    }

    /**
     * Parse date safely from Excel cell
     */
    protected function parseDate($cellValue): ?string
    {
        if (empty($cellValue)) {
            return null;
        }

        try {
            if (is_numeric($cellValue)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($cellValue))->format('Y-m-d');
            }

            if ($cellValue instanceof \DateTimeInterface) {
                return Carbon::instance($cellValue)->format('Y-m-d');
            }

            $str = trim((string)$cellValue);
            if (empty($str) || str_contains($str, '1970-01-01')) {
                return null;
            }

            return Carbon::parse($str)->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Download a spreadsheet file by export URL strictly
     * Throws Exception if download fails, HTTP error, or non-Excel payload
     */
    protected function downloadFile(string $exportUrl, string $targetPath, string $sheetLabel = 'Spreadsheet'): void
    {
        try {
            $response = Http::timeout(35)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->get($exportUrl);

            if (!$response->successful()) {
                throw new Exception("HTTP Error {$response->status()} saat mengunduh tautan {$sheetLabel}. Pastikan tautan valid dan Google Sheet diset publik ('Anyone with the link can view').");
            }

            $body = $response->body();

            // Verifikasi bahwa data yang diunduh adalah file XLSX valid (bukan halaman login/HTML Google)
            if (str_starts_with(trim($body), '<!DOCTYPE html') || str_starts_with(trim($body), '<html') || strlen($body) < 1000) {
                throw new Exception("Tautan {$sheetLabel} tidak dapat diekspor sebagai Excel. Pastikan Google Spreadsheet diset publik ('Anyone with the link can view').");
            }

            // File XLSX harus diawali signature ZIP (PK\x03\x04)
            if (!str_starts_with($body, "PK\x03\x04")) {
                throw new Exception("Format berkas yang diterima dari tautan {$sheetLabel} bukan file Excel XLSX yang valid.");
            }

            file_put_contents($targetPath, $body);
        } catch (Exception $e) {
            Log::warning("Gagal mengunduh live Google Sheet {$sheetLabel} dari {$exportUrl}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute full sync strictly based on configured links in AppSetting
     */
    public function sync(string $triggeredBy = 'MANUAL_BUTTON', ?string $activityType = null): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        gc_collect_cycles();

        $startTime = microtime(true);

        if (!$activityType) {
            $activityType = (stripos($triggeredBy, 'Scheduler') !== false || stripos($triggeredBy, 'Cron') !== false || stripos($triggeredBy, 'System') !== false) 
                ? 'SYNC_OTOMATIS' 
                : 'SYNC_MANUAL';
        }

        // 1. Baca tautan dari AppSetting yang diatur pada menu
        $potensiRawUrl = AppSetting::getValue('spreadsheet_potensi_url');
        $eksRawUrl = AppSetting::getValue('spreadsheet_eks_url');

        // Validasi jika kedua tautan kosong
        if (empty($potensiRawUrl) && empty($eksRawUrl)) {
            $errorMsg = "Tautan Google Spreadsheet belum dikonfigurasi. Silakan isi tautan pada menu Pengaturan Link Google Spreadsheet.";
            SyncLog::create([
                'source_type' => $activityType,
                'status' => 'FAILED',
                'potensi_synced' => 0,
                'eks_idle_synced' => 0,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
                'error_message' => $errorMsg,
                'triggered_by' => $triggeredBy,
            ]);
            throw new Exception($errorMsg);
        }

        $potensiExportUrl = $this->convertToExportUrl($potensiRawUrl);
        $eksExportUrl = $this->convertToExportUrl($eksRawUrl);

        if (empty($potensiExportUrl) && empty($eksExportUrl)) {
            $errorMsg = "Format tautan Google Spreadsheet tidak valid. Pastikan format URL berupa 'https://docs.google.com/spreadsheets/d/...'.";
            SyncLog::create([
                'source_type' => $activityType,
                'status' => 'FAILED',
                'potensi_synced' => 0,
                'eks_idle_synced' => 0,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
                'error_message' => $errorMsg,
                'triggered_by' => $triggeredBy,
            ]);
            throw new Exception($errorMsg);
        }

        $tempDir = storage_path('app');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $potensiCount = 0;
        $eksIdleCount = 0;
        $sourceType = 'GOOGLE_SHEET_LIVE';

        DB::beginTransaction();

        try {
            // =========================================================================
            // A. SINKRONISASI SHEET: potensi idle
            // =========================================================================
            $potensiFile = $tempDir . '/temp_potensi_sheet.xlsx';
            if (file_exists($potensiFile)) {
                @unlink($potensiFile);
            }

            if (!$potensiExportUrl) {
                throw new Exception("Tautan Google Spreadsheet Potensi Idle belum diatur atau formatnya tidak valid.");
            }

            // Unduh file langsung dari tautan Google Spreadsheet
            $this->downloadFile($potensiExportUrl, $potensiFile, 'Potensi Idle');

            if (!file_exists($potensiFile)) {
                throw new Exception("Gagal menyimpan file temporary Spreadsheet Potensi Idle.");
            }

            $reader = IOFactory::createReaderForFile($potensiFile);
            $reader->setReadDataOnly(true);
            $spreadsheetPotensi = $reader->load($potensiFile);

            if (!$spreadsheetPotensi->sheetNameExists('potensi idle')) {
                throw new Exception("Sheet 'potensi idle' tidak ditemukan pada file Google Spreadsheet yang ditautkan. Pastikan nama tab sheet tepat 'potensi idle'.");
            }

            $sheet = $spreadsheetPotensi->getSheetByName('potensi idle');
            $rows = $sheet->toArray(null, true, true, false);

            if (!empty($rows)) {
                BmnPotensiIdle::query()->delete();

                foreach (array_slice($rows, 1) as $idx => $row) {
                    $kodeSatker = trim((string)($row[4] ?? ''));
                    $namaSatker = trim((string)($row[6] ?? ''));
                    $nup = (int)($row[8] ?? 0);
                    $namaBarang = trim((string)($row[10] ?? ''));

                    if (empty($kodeSatker) && empty($namaSatker) && empty($namaBarang)) {
                        continue;
                    }

                    $kl = trim((string)($row[5] ?? ''));
                    $isKemenkeu = (
                        str_contains(strtoupper($kl), 'KEUANGAN') ||
                        str_contains(strtoupper($namaSatker), 'KANWIL DJP') ||
                        str_contains(strtoupper($namaSatker), 'KANWIL DJBC') ||
                        str_contains(strtoupper($namaSatker), 'KANWIL DJPB') ||
                        str_contains(strtoupper($namaSatker), 'KPKNL') ||
                        str_contains(strtoupper($namaSatker), 'KPP') ||
                        str_contains(strtoupper($namaSatker), 'KPPBC') ||
                        str_contains(strtoupper($namaSatker), 'KPPN') ||
                        str_contains(strtoupper($namaSatker), 'BALAI DIKLAT KEUANGAN')
                    );

                    $eselon1 = null;
                    if ($isKemenkeu) {
                        $uSatker = strtoupper($namaSatker);
                        if (str_contains($uSatker, 'DJPB') || str_contains($uSatker, 'PERBENDAHARAAN') || str_contains($uSatker, 'KPPN')) {
                            $eselon1 = 'DJPb';
                        } elseif (str_contains($uSatker, 'DJBC') || str_contains($uSatker, 'BEA') || str_contains($uSatker, 'CUKAI') || str_contains($uSatker, 'KPPBC')) {
                            $eselon1 = 'DJBC';
                        } elseif (str_contains($uSatker, 'DJP') || str_contains($uSatker, 'PAJAK') || str_contains($uSatker, 'KPP PRATAMA') || str_contains($uSatker, 'KP2KP')) {
                            $eselon1 = 'DJP';
                        } elseif (str_contains($uSatker, 'DJKN') || str_contains($uSatker, 'KEKAYAAN NEGARA') || str_contains($uSatker, 'KPKNL')) {
                            $eselon1 = 'DJKN';
                        } else {
                            $eselon1 = 'DJP';
                        }
                    }

                    $statusIdle = trim((string)($row[24] ?? ''));
                    if (empty($statusIdle)) {
                        $statusIdle = 'Bukan BMN Idle';
                    }

                    $alamat = trim((string)($row[3] ?? ''));
                    $coords = $this->getCoordinatesForLocation($alamat, $namaSatker);

                    BmnPotensiIdle::create([
                        'row_no' => (int)($row[0] ?? ($idx + 1)),
                        'hasil_skor' => trim((string)($row[1] ?? '')),
                        'kanwil' => trim((string)($row[2] ?? '')),
                        'kpknl' => trim((string)($row[3] ?? 'KPKNL Palembang')),
                        'kode_satker' => $kodeSatker,
                        'kementerian_lembaga' => $kl ?: 'KEMENTERIAN KEUANGAN',
                        'nama_satker' => $namaSatker,
                        'kode_barang' => trim((string)($row[7] ?? '')),
                        'nup' => $nup,
                        'kelompok_barang' => trim((string)($row[9] ?? 'Rumah Negara')),
                        'nama_barang' => $namaBarang,
                        'hasil_pengukuran_sbsk' => trim((string)($row[11] ?? '')),
                        'surat_klarifikasi' => trim((string)($row[12] ?? '')),
                        'tanggal_klarifikasi' => $this->parseDate($row[13] ?? null),
                        'surat_jawaban' => trim((string)($row[14] ?? '')),
                        'tanggal_jawaban' => $this->parseDate($row[15] ?? null),
                        'hasil_jawaban' => trim((string)($row[16] ?? '')),
                        'tujuan_surat' => trim((string)($row[17] ?? '')),
                        'validasi_kanwil' => trim((string)($row[18] ?? '')),
                        'bobot_nilai' => is_numeric($row[19] ?? null) ? (float)$row[19] : 0,
                        'status_klarifikasi' => trim((string)($row[20] ?? '')),
                        'pemetaan_jawaban' => trim((string)($row[21] ?? '')),
                        'status_tindak_lanjut' => trim((string)($row[22] ?? 'Penelusuran')),
                        'hasil_penelitian' => trim((string)($row[23] ?? '')),
                        'status_bmn_idle' => $statusIdle,
                        'klasterisasi' => trim((string)($row[25] ?? 'K1')),
                        'cek_tanggal' => trim((string)($row[26] ?? '')),
                        'cek_kpknl' => trim((string)($row[27] ?? '')),
                        'is_kemenkeu' => $isKemenkeu,
                        'eselon1_kemenkeu' => $eselon1,
                        'latitude' => $coords['lat'],
                        'longitude' => $coords['lng'],
                    ]);

                    $potensiCount++;
                }
            }

            // Bersihkan memory objek PhpSpreadsheet
            $spreadsheetPotensi->disconnectWorksheets();
            unset($spreadsheetPotensi, $reader);
            gc_collect_cycles();

            // =========================================================================
            // B. SINKRONISASI SHEET: eks bmn idle
            // =========================================================================
            $eksFile = $tempDir . '/temp_eks_sheet.xlsx';
            if (file_exists($eksFile)) {
                @unlink($eksFile);
            }

            if (!$eksExportUrl) {
                throw new Exception("Tautan Google Spreadsheet Eks BMN Idle belum diatur atau formatnya tidak valid.");
            }

            // Jika URL Eks sama persis dengan URL Potensi, copy file yang sudah diunduh
            if ($eksExportUrl === $potensiExportUrl && file_exists($potensiFile)) {
                copy($potensiFile, $eksFile);
            } else {
                $this->downloadFile($eksExportUrl, $eksFile, 'Eks BMN Idle');
            }

            if (!file_exists($eksFile)) {
                throw new Exception("Gagal menyimpan file temporary Spreadsheet Eks BMN Idle.");
            }

            $readerEks = IOFactory::createReaderForFile($eksFile);
            $readerEks->setReadDataOnly(true);
            $spreadsheetEks = $readerEks->load($eksFile);

            if (!$spreadsheetEks->sheetNameExists('eks bmn idle')) {
                throw new Exception("Sheet 'eks bmn idle' tidak ditemukan pada file Google Spreadsheet yang ditautkan. Pastikan nama tab sheet tepat 'eks bmn idle'.");
            }

            $sheetEks = $spreadsheetEks->getSheetByName('eks bmn idle');
            $rowsEks = $sheetEks->toArray(null, true, true, false);

            if (!empty($rowsEks)) {
                BmnEksIdle::query()->delete();

                foreach (array_slice($rowsEks, 2) as $idx => $row) {
                    $namaKpknl = trim((string)($row[2] ?? ''));
                    $kodeBarang = trim((string)($row[3] ?? ''));
                    $uraianBarang = trim((string)($row[4] ?? ''));
                    $nup = (int)($row[5] ?? 0);

                    if (empty($namaKpknl) && empty($kodeBarang) && empty($uraianBarang)) {
                        continue;
                    }

                    if ($namaKpknl === '3' || $kodeBarang === '4') {
                        continue;
                    }

                    $rawLuas = (string)($row[6] ?? '0');
                    $luas = (float)str_replace([',', '.'], '', $rawLuas);

                    $rawNilai = (string)($row[7] ?? '0');
                    $cleanNilai = preg_replace('/[^\d]/', '', $rawNilai);
                    $nilaiPerolehan = !empty($cleanNilai) ? (float)$cleanNilai : 0;

                    $alamat = trim((string)($row[8] ?? ''));
                    $coords = $this->getCoordinatesForLocation($alamat, $uraianBarang);

                    BmnEksIdle::create([
                        'row_no' => (int)($row[0] ?? ($idx + 1)),
                        'nama_kanwil' => trim((string)($row[1] ?? 'Kanwil DJKN SJB')),
                        'nama_kpknl' => $namaKpknl ?: 'KPKNL PALEMBANG',
                        'kode_barang' => $kodeBarang,
                        'uraian_barang' => $uraianBarang,
                        'nup' => $nup,
                        'luas' => $luas,
                        'nilai_perolehan' => $nilaiPerolehan,
                        'alamat' => $alamat,
                        'jenis_tindak_lanjut' => trim((string)($row[9] ?? '')),
                        'jenis_pengelolaan' => trim((string)($row[10] ?? 'PSP')),
                        'no_surat' => trim((string)($row[11] ?? '')),
                        'tanggal_surat' => $this->parseDate($row[12] ?? null),
                        'verifikasi_kanwil' => trim((string)($row[13] ?? 'SESUAI')),
                        'link_bukti_dokumen' => trim((string)($row[14] ?? '')),
                        'tipe' => trim((string)($row[15] ?? '')),
                        'latitude' => $coords['lat'],
                        'longitude' => $coords['lng'],
                    ]);

                    $eksIdleCount++;
                }
            }

            $spreadsheetEks->disconnectWorksheets();
            unset($spreadsheetEks, $readerEks);
            gc_collect_cycles();

            DB::commit();

            $duration = round(microtime(true) - $startTime, 2);

            // Simpan audit log sinkronisasi
            SyncLog::create([
                'source_type' => $activityType ?: $sourceType,
                'status' => 'SUCCESS',
                'potensi_synced' => $potensiCount,
                'eks_idle_synced' => $eksIdleCount,
                'duration_seconds' => $duration,
                'triggered_by' => $triggeredBy,
            ]);

            return [
                'status' => 'success',
                'message' => "Sinkronisasi berhasil! {$potensiCount} data Potensi Idle dan {$eksIdleCount} data Eks BMN Idle berhasil disinkronkan dari Google Spreadsheet dalam {$duration} detik.",
                'potensi_synced' => $potensiCount,
                'eks_idle_synced' => $eksIdleCount,
                'duration_seconds' => $duration,
                'source_type' => $activityType ?: $sourceType,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            $duration = round(microtime(true) - $startTime, 2);

            SyncLog::create([
                'source_type' => $activityType ?: $sourceType,
                'status' => 'FAILED',
                'potensi_synced' => 0,
                'eks_idle_synced' => 0,
                'duration_seconds' => $duration,
                'error_message' => $e->getMessage(),
                'triggered_by' => $triggeredBy,
            ]);

            throw $e;
        }
    }
}
