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
     * Sync data from Google Spreadsheet
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
                throw new Exception("Gagal mengunduh spreadsheet dari Google (HTTP status: {$response->status()}). Pastikan link dapat diakses publik.");
            }

            $csvContent = $response->body();
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

            // Clear old records
            Pegawai::query()->delete();

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

                $pegawai = Pegawai::create([
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
                ]);

                // If Kepala Kantor, set as head of Pimpinan unit
                if (str_contains(strtolower($jabatanRaw), 'kepala kpknl')) {
                    UnitKerja::where('kode_unit', 'PIMPINAN')->update(['kepala_pegawai_id' => $pegawai->id]);
                }

                $importedCount++;
            }

            // Save Sync Audit Log
            $syncLog = SyncLog::create([
                'source_url' => $url,
                'total_rows_imported' => $importedCount,
                'total_pns' => $pnsCount,
                'total_ppnpn' => $ppnpnCount,
                'status' => 'success',
                'message' => "Berhasil menyinkronkan {$importedCount} data personil ({$pnsCount} ASN dan {$ppnpnCount} PPNPN).",
                'synced_at' => Carbon::now(),
            ]);

            AppSetting::set('last_synced_at', Carbon::now()->toIso8601String(), 'Waktu sinkronisasi terakhir');
            AppSetting::set('google_sheet_url', $url, 'Tautan Google Spreadsheet aktif');

            DB::commit();

            return [
                'success' => true,
                'message' => "Sinkronisasi berhasil! {$importedCount} data pegawai ({$pnsCount} PNS, {$ppnpnCount} PPNPN) berhasil diperbarui.",
                'total' => $importedCount,
                'pns' => $pnsCount,
                'ppnpn' => $ppnpnCount,
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
}
