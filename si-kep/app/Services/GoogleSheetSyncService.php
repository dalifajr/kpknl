<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Jabatan;
use App\Models\PangkatGolongan;
use App\Models\Pegawai;
use App\Models\SyncLog;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetSyncService
{
    public const DEFAULT_SPREADSHEET_URL = 'https://docs.google.com/spreadsheets/d/1-pfEY_56CvIBaaCyxrfjv5nitEMHhPQCXKzuwdxX6hA/edit?gid=1668021245#gid=1668021245';

    /**
     * Convert standard Google Sheet URL to CSV export format
     */
    public function convertToCsvUrl(?string $url): ?string
    {
        if (empty($url)) {
            $url = self::DEFAULT_SPREADSHEET_URL;
        }

        $url = trim($url);

        $sheetId = null;
        $gid = '1668021245';

        if (preg_match('/\/d\/([a-zA-Z0-9-_]{15,})/', $url, $matches)) {
            $sheetId = $matches[1];
        }

        if (preg_match('/[#&?]gid=([0-9]+)/', $url, $gidMatches)) {
            $gid = $gidMatches[1];
        }

        if ($sheetId) {
            return "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
        }

        return $url;
    }

    /**
     * Check spreadsheet access permissions (Read Only vs Read & Write) and format validity
     */
    public function checkPermissions(?string $sheetUrl = null, ?string $webhookUrl = null): array
    {
        $url = $sheetUrl ?: AppSetting::get('google_sheet_url', self::DEFAULT_SPREADSHEET_URL);
        $csvUrl = $this->convertToCsvUrl($url);
        $webhook = $webhookUrl !== null ? $webhookUrl : AppSetting::get('google_sheet_webhook_url', '');

        $canRead = false;
        $readStatus = 'unknown';
        $readMessage = '';
        $advice = null;

        // 1. Test Read Access via CSV
        try {
            $response = Http::timeout(10)->retry(1, 300)->get($csvUrl);

            if (!$response->successful()) {
                $status = $response->status();
                if ($status === 401 || $status === 403) {
                    $readStatus = 'no_access';
                    $readMessage = "Akses Ditolak (HTTP {$status}). File Google Spreadsheet bersifat privat atau dibatasi.";
                    $advice = "Buka file di Google Spreadsheet > Klik 'Share' (Bagikan) di pojok kanan atas > Ubah General Access menjadi 'Anyone with the link' (Siapa saja yang memiliki tautan) > Pilih peran 'Viewer' atau 'Editor' > Simpan.";
                } elseif ($status === 404) {
                    $readStatus = 'not_found';
                    $readMessage = "File spreadsheet tidak ditemukan (HTTP 404). Pastikan tautan atau ID Spreadsheet sudah benar.";
                    $advice = "Periksa kembali tautan Google Spreadsheet Anda.";
                } else {
                    $readStatus = 'http_error';
                    $readMessage = "Gagal mengakses spreadsheet dari Google (HTTP status: {$status}).";
                    $advice = "Pastikan server terkoneksi dengan internet dan tautan Google Spreadsheet valid.";
                }
            } else {
                $body = $response->body();
                $contentType = $response->header('Content-Type') ?: '';

                // Check if Google redirected to a HTML sign-in page
                if (str_contains($contentType, 'text/html') && (str_contains($body, 'accounts.google.com') || str_contains($body, 'ServiceLogin') || str_contains($body, 'need permission'))) {
                    $readStatus = 'no_access';
                    $readMessage = "Akses Spreadsheet Ditolak. Google mengalihkan ke halaman login akun karena file ini dibatasi (bukan publik).";
                    $advice = "Buka file di Google Spreadsheet > Klik 'Share' (Bagikan) > Ubah ke 'Anyone with the link' (Siapa saja yang memiliki tautan) > Selesai.";
                } elseif (empty(trim($body))) {
                    $readStatus = 'empty';
                    $readMessage = "Konten spreadsheet kosong.";
                    $advice = "Pastikan lembar kerja memiliki baris data kepegawaian.";
                } else {
                    // Check required headers
                    $upper = strtoupper(substr($body, 0, 4096));
                    if (!str_contains($upper, 'NAMA') || !str_contains($upper, 'NIP') || !str_contains($upper, 'JABATAN')) {
                        $readStatus = 'invalid_format';
                        $readMessage = "Format kolom spreadsheet tidak sesuai. Kolom 'NAMA', 'NIP', dan 'JABATAN' tidak ditemukan.";
                        $advice = "Pastikan tautan menyertakan parameter gid yang mengarah ke lembar kerja 'Daftar Pegawai'.";
                    } else {
                        $canRead = true;
                        $readStatus = 'authorized';
                        $readMessage = "Izin baca valid. Berhasil mengunduh dan membaca struktur kolom master kepegawaian.";
                    }
                }
            }
        } catch (\Exception $e) {
            $readStatus = 'network_error';
            $readMessage = "Koneksi ke Google Spreadsheet gagal: " . $e->getMessage();
            $advice = "Periksa koneksi internet server dan pastikan domain docs.google.com tidak diblokir firewall.";
        }

        // 2. Test Write Access via Webhook
        $canWrite = false;
        $writeStatus = 'none';
        $writeMessage = '';

        if (empty(trim($webhook))) {
            $writeStatus = 'not_configured';
            $writeMessage = 'Webhook Google Apps Script belum diatur (Kanal Tulis belum aktif).';
        } else {
            try {
                $probe = Http::timeout(8)->post($webhook, [
                    'action' => 'check_permission',
                    'ping' => true,
                    'timestamp' => now()->toIso8601String(),
                ]);

                if ($probe->successful()) {
                    $canWrite = true;
                    $writeStatus = 'authorized';
                    $writeMessage = 'Webhook Google Apps Script terhubung aktif dan siap menerima data pembaruan.';
                } else {
                    $writeStatus = 'unauthorized';
                    $writeMessage = "Webhook Apps Script mengembalikan HTTP {$probe->status()} (Akses tulis gagal/dibatasi).";
                }
            } catch (\Exception $e) {
                $writeStatus = 'connection_failed';
                $writeMessage = "Gagal menghubungi webhook Apps Script: " . $e->getMessage();
            }
        }

        // 3. Overall Permission Classification
        if (!$canRead) {
            $permission = $readStatus === 'invalid_format' ? 'invalid_format' : 'no_access';
            $permissionLabel = $readStatus === 'invalid_format' ? 'Format Kolom Tidak Sesuai' : 'Akses Ditolak (Privat)';
            $summary = $readMessage;
        } elseif ($canRead && $canWrite) {
            $permission = 'read_and_write';
            $permissionLabel = 'Read & Write (Akses Penuh)';
            $summary = 'Spreadsheet dapat dibaca publik dan webhook siap menerima pembaruan data secara dua arah.';
        } else {
            $permission = 'read_only';
            $permissionLabel = 'Read Only (Hanya Baca)';
            $summary = 'Spreadsheet dapat diimpor ke SI-KEP, namun perubahan data dari aplikasi web hanya akan disimpan di database lokal karena webhook tulis belum diatur atau tidak merespons.';
            if (empty($advice)) {
                $advice = "Jika Anda ingin perubahan dari SI-KEP otomatis tertulis balik ke spreadsheet, atur URL Webhook Google Apps Script di formulir Pengaturan.";
            }
        }

        return [
            'success' => $canRead,
            'permission' => $permission,
            'permission_label' => $permissionLabel,
            'can_read' => $canRead,
            'can_write' => $canWrite,
            'read_status' => $readStatus,
            'read_message' => $readMessage,
            'write_status' => $writeStatus,
            'write_message' => $writeMessage,
            'message' => $summary,
            'advice' => $advice,
        ];
    }

    /**
     * Sync data from Google Spreadsheet with Smart Upsert (Safe Preservation of Local Data)
     */
    public function sync(?string $sheetUrl = null): array
    {
        $startTime = microtime(true);
        $url = $sheetUrl ?: AppSetting::get('google_sheet_url', self::DEFAULT_SPREADSHEET_URL);
        $csvUrl = $this->convertToCsvUrl($url);

        try {
            // Fetch CSV content with retry and timeout
            $response = Http::timeout(30)->retry(2, 500)->get($csvUrl);

            if (!$response->successful()) {
                $st = $response->status();
                if ($st === 401 || $st === 403) {
                    throw new Exception("Akses Ditolak (HTTP {$st}). Pastikan spreadsheet memiliki izin 'Anyone with the link can view'.");
                }
                throw new Exception("Gagal mengunduh spreadsheet dari Google (HTTP status: {$st}). Pastikan link dapat diakses publik.");
            }

            $csvContent = $response->body();
            $contentType = $response->header('Content-Type') ?: '';

            if (str_contains($contentType, 'text/html') && (str_contains($csvContent, 'accounts.google.com') || str_contains($csvContent, 'ServiceLogin') || str_contains($csvContent, 'need permission'))) {
                throw new Exception("Akses Spreadsheet Ditolak. File bersifat privat dan memerlukan izin login Google. Ubah pengaturan sharing ke 'Anyone with the link'.");
            }

            if (empty(trim($csvContent))) {
                throw new Exception("Konten spreadsheet kosong.");
            }

            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $csvContent);
            rewind($stream);

            $rows = [];
            while (($row = fgetcsv($stream)) !== false) {
                $rows[] = $row;
            }
            fclose($stream);

            // Find Header Row (row containing 'NAMA', 'NIP', 'JABATAN')
            $headerRowIndex = -1;
            foreach ($rows as $index => $row) {
                $joined = strtoupper(implode(';', $row));
                if (str_contains($joined, 'NAMA') && str_contains($joined, 'NIP') && str_contains($joined, 'JABATAN')) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex === -1) {
                throw new Exception("Format kolom spreadsheet tidak dikenali. Kolom 'NAMA', 'NIP', dan 'JABATAN' tidak ditemukan.");
            }

            $header = array_map(fn($h) => trim(strtoupper($h)), $rows[$headerRowIndex]);
            $colMap = [];
            foreach ($header as $colIdx => $colName) {
                $colMap[$colName] = $colIdx;
            }

            // Data begins after header / number index row
            $dataStartIndex = $headerRowIndex + 1;
            if (isset($rows[$dataStartIndex]) && isset($rows[$dataStartIndex][0]) && is_numeric(trim($rows[$dataStartIndex][0])) && isset($rows[$dataStartIndex][1]) && is_numeric(trim($rows[$dataStartIndex][1]))) {
                $dataStartIndex++;
            }

            DB::beginTransaction();

            $pnsCount = 0;
            $ppnpnCount = 0;
            $importedCount = 0;
            $createdCount = 0;
            $updatedCount = 0;
            $importedNips = [];
            $importedNiks = [];

            for ($i = $dataStartIndex; $i < count($rows); $i++) {
                // Rule: Row 41 and below or Gorontalo data must NOT be included
                if ($i >= 40) { // 0-indexed row 40 is line 41 in spreadsheet
                    break;
                }

                $row = $rows[$i];
                if (count($row) < 3) {
                    continue;
                }

                $rowText = implode(';', $row);
                if (str_contains(strtolower($rowText), 'gorontalo') || str_contains(strtolower($rowText), 'komitmen menjaga integritas')) {
                    break;
                }

                $noVal = trim($this->getVal($row, $colMap, ['NO', 'NO.']));
                // Must be valid number between 1 and 33
                if (!is_numeric($noVal) || intval($noVal) < 1 || intval($noVal) > 33) {
                    continue;
                }

                $nama = trim($this->getVal($row, $colMap, ['NAMA']));
                if (empty($nama) || strtolower($nama) === 'nama') {
                    continue;
                }

                $nipRaw = trim($this->getVal($row, $colMap, ['NIP']));
                $nip = preg_replace('/[^0-9]/', '', $nipRaw);
                $nikRaw = trim($this->getVal($row, $colMap, ['NIK']));
                $nik = preg_replace('/[^0-9]/', '', $nikRaw);

                $namaGelar = trim($this->getVal($row, $colMap, ['NAMA DI DATA POKOK HRIS', 'NAMA LENGKAP']));
                $jabatanRaw = trim($this->getVal($row, $colMap, ['JABATAN']));
                $perJabatan = trim($this->getVal($row, $colMap, ['PER.JABATAN']));
                $pangkatRaw = trim($this->getVal($row, $colMap, ['PANGKAT / GOLONGAN', 'PANGKAT']));
                $gradingRaw = trim($this->getVal($row, $colMap, ['GRADING', 'JOB GRADE']));

                // Tipe Pegawai Classification
                $isPpnpn = empty($nip) || strlen($nip) < 10 || str_contains(strtolower($jabatanRaw), 'ppnpn') ||
                           str_contains(strtolower($jabatanRaw), 'pramubakti') || str_contains(strtolower($jabatanRaw), 'pengemudi') ||
                           str_contains(strtolower($jabatanRaw), 'keamanan');
                $tipePegawai = $isPpnpn ? 'ppnpn' : 'pns';

                if ($tipePegawai === 'pns') {
                    $pnsCount++;
                } else {
                    $ppnpnCount++;
                }

                // Resolve Unit Kerja
                $unitKerjaId = $this->resolveUnitKerja($jabatanRaw, $perJabatan);

                // Resolve Jabatan
                $jabatanId = $this->resolveJabatan($jabatanRaw, $tipePegawai, $gradingRaw);

                // Resolve Pangkat Golongan
                $pangkatId = $this->resolvePangkatGolongan($pangkatRaw, $tipePegawai);

                // Masa kerja & lama palembang parsing
                $masaKerjaRaw = trim($this->getVal($row, $colMap, ['MASA KERJA']));
                [$mkTahun, $mkBulan] = $this->parseDuration($masaKerjaRaw);

                $lamaPalembangRaw = trim($this->getVal($row, $colMap, ['LAMA DI PALEMBANG']));
                [$lpTahun, $lpBulan] = $this->parseDuration($lamaPalembangRaw);

                $usiaRaw = trim($this->getVal($row, $colMap, ['USIA']));
                [$usiaTahun, $usiaBulan] = $this->parseDuration($usiaRaw);

                $noUrut = intval(trim($this->getVal($row, $colMap, ['NO', 'NO.']))) ?: ($importedCount + 1);

                $pegawaiData = [
                    'no_urut' => $noUrut,
                    'nip' => !empty($nip) ? $nip : null,
                    'nik' => !empty($nik) ? $nik : null,
                    'nama' => $nama,
                    'nama_lengkap_gelar' => !empty($namaGelar) ? $namaGelar : $nama,
                    'tipe_pegawai' => $tipePegawai,
                    'unit_kerja_id' => $unitKerjaId,
                    'jabatan_id' => $jabatanId,
                    'pangkat_golongan_id' => $pangkatId,
                    'nama_jabatan_raw' => $jabatanRaw,
                    'per_jabatan' => $perJabatan,
                    'job_grade' => is_numeric($gradingRaw) ? intval($gradingRaw) : null,
                    'tmt_nip' => $this->parseDate($this->getVal($row, $colMap, ['TMT NIP'])),
                    'tmt_eselon' => $this->parseDate($this->getVal($row, $colMap, ['TMT ESELON'])),
                    'tmt_palembang' => $this->parseDate($this->getVal($row, $colMap, ['TMT PALEMBANG'])),
                    'masa_kerja_raw' => $masaKerjaRaw,
                    'masa_kerja_tahun' => $mkTahun,
                    'masa_kerja_bulan' => $mkBulan,
                    'lama_palembang_raw' => $lamaPalembangRaw,
                    'lama_palembang_tahun' => $lpTahun,
                    'lama_palembang_bulan' => $lpBulan,
                    'pangkat_golongan_raw' => $pangkatRaw,
                    'tmt_golongan' => $this->parseDate($this->getVal($row, $colMap, ['TMT GOLONGAN'])),
                    'tmt_kgb' => $this->parseDate($this->getVal($row, $colMap, ['TMT KGB'])),
                    'tmt_grading' => $this->parseDate($this->getVal($row, $colMap, ['TMT GRADING'])),
                    'tempat_lahir' => trim($this->getVal($row, $colMap, ['TEMPAT LAHIR'])),
                    'tanggal_lahir' => $this->parseDate($this->getVal($row, $colMap, ['TGL LAHIR', 'TANGGAL LAHIR'])),
                    'usia_raw' => $usiaRaw,
                    'usia_tahun' => $usiaTahun,
                    'usia_bulan' => $usiaBulan,
                    'jenis_kelamin' => strtoupper(trim($this->getVal($row, $colMap, ['JENIS KELAMIN']))) === 'P' ? 'P' : 'L',
                    'pendidikan_terakhir' => trim($this->getVal($row, $colMap, ['PENDIDIKAN (HRIS)', 'PENDIDIKAN'])),
                    'fakultas' => trim($this->getVal($row, $colMap, ['FAKULTAS'])),
                    'jurusan' => trim($this->getVal($row, $colMap, ['JURUSAN'])),
                    'tahun_lulus' => is_numeric(trim($this->getVal($row, $colMap, ['TAHUN LULUS']))) ? intval(trim($this->getVal($row, $colMap, ['TAHUN LULUS']))) : null,
                    'nama_universitas' => trim($this->getVal($row, $colMap, ['NAMA UNIVERSITAS'])),
                    'tmt_ue_iv' => trim($this->getVal($row, $colMap, ['TMT UE IV'])),
                    'lama_bertugas_ue_iv' => trim($this->getVal($row, $colMap, ['LAMA BERTUGAS DI UE IV'])),
                    'status_gelar' => trim($this->getVal($row, $colMap, ['STATUS PENDIDIKAN DAN PENCANTUMAN GELAR AKADEMIK', 'STATUS GELAR'])),
                    'validasi_jabatan' => $this->parseBool($this->getVal($row, $colMap, ['VALIDASI DATA JABATAN TERAKHIR'])),
                    'validasi_pangkat' => $this->parseBool($this->getVal($row, $colMap, ['VALIDASI DATA PANGKAT TERAKHIR'])),
                    'validasi_pendidikan' => $this->parseBool($this->getVal($row, $colMap, ['VALIDASI DATA PENDIDIKAN TERAKHIR'])),
                    'is_active' => true,
                ];

                // Smart Upsert: Find existing pegawai by NIP or NIK to preserve avatar and local IDs
                $existing = null;
                if (!empty($nip)) {
                    $existing = Pegawai::where('nip', $nip)->first();
                    $importedNips[] = $nip;
                } elseif (!empty($nik)) {
                    $existing = Pegawai::where('nik', $nik)->first();
                    $importedNiks[] = $nik;
                }

                if ($existing) {
                    // Update attributes while keeping existing avatar_url intact!
                    $existing->fill($pegawaiData);
                    $existing->save();
                    $pegawai = $existing;
                    $updatedCount++;
                } else {
                    $pegawai = Pegawai::create($pegawaiData);
                    $createdCount++;
                }

                // If Kepala Kantor, set as head of Pimpinan unit
                if (str_contains(strtolower($jabatanRaw), 'kepala kpknl')) {
                    UnitKerja::where('kode_unit', 'PIMPINAN')->update(['kepala_pegawai_id' => $pegawai->id]);
                }

                $importedCount++;
            }

            // Deactivate any records no longer in spreadsheet instead of deleting them
            $deactivateQuery = Pegawai::where('is_active', true);
            $hasFilter = false;
            $cleanNips = array_filter($importedNips);
            $cleanNiks = array_filter($importedNiks);
            if (!empty($cleanNips)) {
                $deactivateQuery->whereNotIn('nip', $cleanNips);
                $hasFilter = true;
            }
            if (!empty($cleanNiks)) {
                $deactivateQuery->where(function($q) use ($cleanNiks) {
                    $q->whereNull('nik')->orWhereNotIn('nik', $cleanNiks);
                });
                $hasFilter = true;
            }
            if ($hasFilter) {
                $deactivateQuery->update(['is_active' => false]);
            }

            // Save Sync Audit Log
            $syncLog = SyncLog::create([
                'source_url' => $url,
                'total_rows_imported' => $importedCount,
                'total_pns' => $pnsCount,
                'total_ppnpn' => $ppnpnCount,
                'status' => 'success',
                'message' => "Berhasil menyinkronkan {$importedCount} data personil ({$createdCount} baru, {$updatedCount} diperbarui: {$pnsCount} ASN dan {$ppnpnCount} PPNPN).",
                'synced_at' => Carbon::now(),
            ]);

            AppSetting::set('last_synced_at', Carbon::now()->toIso8601String(), 'Waktu sinkronisasi terakhir');
            AppSetting::set('google_sheet_url', $url, 'Tautan Google Spreadsheet aktif');

            DB::commit();

            // Push pending local changes to spreadsheet
            $pendingPushResult = $this->pushPendingChanges();
            $pushInfo = '';
            if ($pendingPushResult['total'] > 0) {
                $pushInfo = " ({$pendingPushResult['synced']} perubahan lokal disinkronkan ke spreadsheet)";
            }

            return [
                'success' => true,
                'message' => "Sinkronisasi berhasil! {$importedCount} data pegawai diproses ({$createdCount} baru, {$updatedCount} diperbarui: {$pnsCount} PNS, {$ppnpnCount} PPNPN){$pushInfo}.",
                'total' => $importedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'pns' => $pnsCount,
                'ppnpn' => $ppnpnCount,
                'pending_pushed' => $pendingPushResult,
                'duration' => round(microtime(true) - $startTime, 2) . 's',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Google Sheet Sync Error: ' . $e->getMessage());

            SyncLog::create([
                'source_url' => $url,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'synced_at' => Carbon::now(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal melakukan sinkronisasi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve Unit Kerja from Jabatan Text
     */
    protected function resolveUnitKerja(string $jabatanText, string $perJabatan = ''): ?int
    {
        $text = strtolower($jabatanText . ' ' . $perJabatan);

        if (str_contains($text, 'kepala kpknl')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'PIMPINAN'],
                ['nama_unit' => 'Pimpinan KPKNL Palembang', 'singkatan' => 'Pimpinan', 'urutan' => 1]
            );
            return $unit->id;
        }

        if (str_contains($text, 'subbagian umum') || str_contains($text, 'umum') || str_contains($text, 'tata usaha')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SUBBAG_UMUM'],
                ['nama_unit' => 'Subbagian Umum', 'singkatan' => 'Umum', 'urutan' => 2]
            );
            return $unit->id;
        }

        if (str_contains($text, 'pengelolaan kekayaan negara') || str_contains($text, 'pkn')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_PKN'],
                ['nama_unit' => 'Seksi Pengelolaan Kekayaan Negara', 'singkatan' => 'PKN', 'urutan' => 3]
            );
            return $unit->id;
        }

        if (str_contains($text, 'penilaian') || str_contains($text, 'penilai')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_PENILAIAN'],
                ['nama_unit' => 'Seksi Penilaian', 'singkatan' => 'Penilaian', 'urutan' => 4]
            );
            return $unit->id;
        }

        if (str_contains($text, 'piutang negara') || str_contains($text, 'pn')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_PN'],
                ['nama_unit' => 'Seksi Piutang Negara', 'singkatan' => 'Piutang Negara', 'urutan' => 5]
            );
            return $unit->id;
        }

        if (str_contains($text, 'hukum dan informasi') || str_contains($text, 'hukum & informasi') || str_contains($text, 'seksi hi')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_HI'],
                ['nama_unit' => 'Seksi Hukum dan Informasi', 'singkatan' => 'Hukum & Informasi', 'urutan' => 6]
            );
            return $unit->id;
        }

        if (str_contains($text, 'kepatuhan internal') || str_contains($text, 'seksi ki')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_KI'],
                ['nama_unit' => 'Seksi Kepatuhan Internal', 'singkatan' => 'Kepatuhan Internal', 'urutan' => 7]
            );
            return $unit->id;
        }

        if (str_contains($text, 'lelang') || str_contains($text, 'pelelang')) {
            $unit = UnitKerja::firstOrCreate(
                ['kode_unit' => 'SEKSI_LELANG'],
                ['nama_unit' => 'Seksi Pelayanan Lelang', 'singkatan' => 'Lelang', 'urutan' => 8]
            );
            return $unit->id;
        }

        // Fallback Subbagian Umum
        $unit = UnitKerja::firstOrCreate(
            ['kode_unit' => 'SUBBAG_UMUM'],
            ['nama_unit' => 'Subbagian Umum', 'singkatan' => 'Umum', 'urutan' => 2]
        );
        return $unit->id;
    }

    /**
     * Resolve Jabatan Entity & Category
     */
    protected function resolveJabatan(string $jabatanText, string $tipePegawai, ?string $grading): ?int
    {
        $text = trim($jabatanText);
        if (empty($text)) {
            $text = 'Pelaksana';
        }

        $lower = strtolower($text);
        $jenis = 'pelaksana';
        $eselon = null;

        if ($tipePegawai === 'ppnpn') {
            $jenis = 'ppnpn';
        } elseif (str_contains($lower, 'kepala kpknl')) {
            $jenis = 'struktural';
            $eselon = 'Eselon III.a';
        } elseif (str_contains($lower, 'kepala seksi') || str_contains($lower, 'kepala subbagian')) {
            $jenis = 'struktural';
            $eselon = 'Eselon IV.a';
        } elseif (str_contains($lower, 'pelelang') || str_contains($lower, 'penilai') || str_contains($lower, 'pranata') || str_contains($lower, 'pengelola pengadaan') || str_contains($lower, 'arsiparis') || str_contains($lower, 'fungsional')) {
            $jenis = 'fungsional';
        }

        $grade = is_numeric($grading) ? intval($grading) : null;

        $jabatan = Jabatan::firstOrCreate(
            ['nama_jabatan' => $text],
            [
                'jenis_jabatan' => $jenis,
                'level_eselon' => $eselon,
                'standar_grade' => $grade,
            ]
        );

        return $jabatan->id;
    }

    /**
     * Resolve Pangkat Golongan
     */
    protected function resolvePangkatGolongan(string $pangkatText, string $tipePegawai): ?int
    {
        $raw = trim($pangkatText);
        if (empty($raw) || $tipePegawai === 'ppnpn' || $raw === '-') {
            $pg = PangkatGolongan::firstOrCreate(
                ['golongan_ruang' => '-'],
                ['nama_pangkat' => 'Non-PNS (PPNPN)', 'hirarki_level' => 0]
            );
            return $pg->id;
        }

        // e.g. "Pembina Tk.I / IV.b", "Penata / III.c"
        $parts = explode('/', $raw);
        $pangkatName = trim($parts[0]);
        $golRuang = isset($parts[1]) ? trim($parts[1]) : '-';

        // Normalize Roman numeral format e.g. IV.b -> IV/b
        $golRuang = str_replace('.', '/', $golRuang);

        $hirarkiLevels = [
            'IV/e' => 17, 'IV/d' => 16, 'IV/c' => 15, 'IV/b' => 14, 'IV/a' => 13,
            'III/d' => 12, 'III/c' => 11, 'III/b' => 10, 'III/a' => 9,
            'II/d' => 8, 'II/c' => 7, 'II/b' => 6, 'II/a' => 5,
            'I/d' => 4, 'I/c' => 3, 'I/b' => 2, 'I/a' => 1,
        ];

        $level = $hirarkiLevels[$golRuang] ?? 0;

        $pg = PangkatGolongan::firstOrCreate(
            ['golongan_ruang' => $golRuang],
            [
                'nama_pangkat' => $pangkatName ?: 'PNS',
                'hirarki_level' => $level,
            ]
        );

        return $pg->id;
    }

    /**
     * Safe Parse Duration Text (e.g., "30 thn 6 bln" => [30, 6])
     */
    protected function parseDuration(string $durationText): array
    {
        $tahun = 0;
        $bulan = 0;

        if (preg_match('/([0-9]+)\s*(?:thn|tahun)/i', $durationText, $tMatches)) {
            $tahun = intval($tMatches[1]);
        }

        if (preg_match('/([0-9]+)\s*(?:bln|bulan)/i', $durationText, $bMatches)) {
            $bulan = intval($bMatches[1]);
        }

        return [$tahun, $bulan];
    }

    /**
     * Parse Date safely supporting Indonesian and standard formats
     */
    protected function parseDate(?string $value): ?string
    {
        if (empty($value) || trim($value) === '-' || trim($value) === '') {
            return null;
        }

        $str = trim($value);

        // Remove newlines or invisible characters
        $str = preg_replace('/[\r\n\t]+/', ' ', $str);

        // Try standard format patterns: d/m/Y, d-m-Y, Y-m-d, j/n/Y
        $formats = [
            'd/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y',
            'd-m-Y', 'j-n-Y', 'Y-m-d',
            'd M Y', 'd F Y', 'Y/m/d'
        ];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $str);
                if ($d && $d->year > 1940 && $d->year < 2100) {
                    return $d->format('Y-m-d');
                }
            } catch (Exception $e) {
                // continue to next format
            }
        }

        try {
            $d = Carbon::parse($str);
            if ($d && $d->year > 1940 && $d->year < 2100) {
                return $d->format('Y-m-d');
            }
        } catch (Exception $e) {
            // Ignore parse fail
        }

        return null;
    }

    /**
     * Parse Boolean
     */
    protected function parseBool(?string $val): bool
    {
        if (empty($val)) {
            return true;
        }
        $val = strtoupper(trim($val));
        return in_array($val, ['TRUE', '1', 'YA', 'SESUAI', 'SUDAH CLEAR (SESUAI DENGAN HRIS)', 'VALID']);
    }

    /**
     * Safe column getter
     */
    protected function getVal(array $row, array $colMap, array $possibleKeys): string
    {
        foreach ($possibleKeys as $key) {
            $keyUpper = strtoupper($key);
            if (isset($colMap[$keyUpper])) {
                $idx = $colMap[$keyUpper];
                if (isset($row[$idx])) {
                    return trim($row[$idx]);
                }
            }
        }
        return '';
    }

    /**
     * Push a change log entry to Google Spreadsheet via Apps Script Webhook
     */
    public function pushRowUpdate(\App\Models\ChangeLog $log): array
    {
        $webhookUrl = AppSetting::get('google_sheet_webhook_url');

        if (empty($webhookUrl)) {
            // Webhook belum diatur, tandai pending
            $log->update([
                'sync_status' => 'pending',
                'sync_error' => 'Webhook Google Spreadsheet belum diatur di Pengaturan Sistem. Data tersimpan aman di database lokal.',
            ]);
            return [
                'success' => false,
                'message' => 'Tersimpan lokal (Webhook spreadsheet belum dikonfigurasi).',
                'pending' => true,
            ];
        }

        try {
            $response = Http::timeout(15)->post($webhookUrl, [
                'action' => $log->action,
                'nip' => $log->nip,
                'nama' => $log->nama_pegawai,
                'data' => $log->payload_after,
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                $log->update([
                    'sync_status' => 'synced',
                    'sync_error' => null,
                    'synced_at' => now(),
                ]);
                return [
                    'success' => true,
                    'message' => 'Berhasil disinkronkan ke Google Spreadsheet.',
                ];
            } else {
                $errMsg = "HTTP " . $response->status() . ": " . substr($response->body(), 0, 200);
                $log->update([
                    'sync_status' => 'pending',
                    'sync_error' => $errMsg,
                ]);
                return [
                    'success' => false,
                    'message' => "Gagal sinkron ke spreadsheet: {$errMsg}",
                    'pending' => true,
                ];
            }
        } catch (Exception $e) {
            $log->update([
                'sync_status' => 'pending',
                'sync_error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Koneksi ke Google Spreadsheet terputus. Data disimpan di database lokal.',
                'pending' => true,
            ];
        }
    }

    /**
     * Push all pending change logs to Google Spreadsheet
     */
    public function pushPendingChanges(): array
    {
        $pending = \App\Models\ChangeLog::where('sync_status', 'pending')->get();
        $successCount = 0;
        $failCount = 0;

        foreach ($pending as $log) {
            $res = $this->pushRowUpdate($log);
            if ($res['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'total' => $pending->count(),
            'synced' => $successCount,
            'failed' => $failCount,
        ];
    }
}
