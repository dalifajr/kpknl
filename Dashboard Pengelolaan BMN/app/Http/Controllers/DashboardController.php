<?php

namespace App\Http\Controllers;

use App\Models\BmnPotensiIdle;
use App\Models\BmnEksIdle;
use App\Models\SyncLog;
use App\Models\ExecutiveNote;
use App\Services\GoogleSheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardController extends Controller
{
    protected GoogleSheetSyncService $syncService;

    public function __construct(GoogleSheetSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display the Executive Dashboard
     */
    public function index(Request $request)
    {
        $hasData = (BmnPotensiIdle::count() > 0 || BmnEksIdle::count() > 0);

        // 1. Data BMN Eks BMN Idle (KPKNL Palembang)
        $eksPalembangRows = BmnEksIdle::where('nama_kpknl', 'LIKE', '%PALEMBANG%')->get();
        if ($eksPalembangRows->isEmpty() && BmnEksIdle::count() > 0) {
            $eksPalembangRows = BmnEksIdle::where('nama_kpknl', '!=', '3')->get();
        }

        $eksSaldoMasuk = $eksPalembangRows->count();
        
        // Poin 1: Data Usulan Penggunaan PSP diambil dari parameter variabel tindak lanjut dengan isi Dokumen Usulan Pengelolaan BMN (3 NUP)
        $eksPspUsulan = $eksPalembangRows->filter(fn($r) => stripos($r->jenis_tindak_lanjut ?? '', 'Dokumen Usulan Pengelolaan') !== false)->count();
        
        // Penghapusan / Penjualan: 10 NUP (Berada pada card penghapusan tersendiri)
        $eksPenghapusan = $eksPalembangRows->filter(fn($r) => stripos($r->jenis_pengelolaan ?? '', 'Penghapusan') !== false || stripos($r->jenis_pengelolaan ?? '', 'Penjualan') !== false)->count();
        
        // Poin 2: Data Saldo Per 2026 (Dokumen Pengelolaan BMN), untuk penghapusan TIDAK digabung melainkan 3 NUP PSP selesai
        $eksSaldoBarang = $eksPalembangRows->filter(fn($r) => trim($r->jenis_tindak_lanjut ?? '') === 'Dokumen Pengelolaan BMN' && stripos($r->jenis_pengelolaan ?? '', 'Penghapusan') === false && stripos($r->jenis_pengelolaan ?? '', 'Penjualan') === false)->count();
        
        // Pemanfaatan: 0 NUP
        $eksPemanfaatan = $eksPalembangRows->where('jenis_pengelolaan', 'Pemanfaatan')->count();

        // Saldo per 2026 murni (3 NUP)
        $eksSaldoPer2026 = $eksSaldoBarang;
        
        $eksPengelolaanCounts = [
            'PSP' => $eksPspUsulan, // Sesuai Poin 1: Usulan Penggunaan PSP = 3 NUP
            'SaldoBarang' => $eksSaldoBarang, // Sesuai Poin 2: Dokumen Pengelolaan BMN non-penghapusan = 3 NUP
            'Dipindahtangankan' => $eksPenghapusan, // Sesuai Poin 2: Penghapusan = 10 NUP
            'Dimanfaatkan' => $eksPemanfaatan,
            'Masih dalam kajian' => $eksPspUsulan,
        ];

        // 2. Data BMN Terindikasi Idle (KPKNL Palembang) - Total 256 NUP
        $allPotensiRows = BmnPotensiIdle::all();
        $palembangPotensiRows = $allPotensiRows->filter(fn($r) => str_contains(strtoupper($r->kpknl ?? ''), 'PALEMBANG'));
        if ($palembangPotensiRows->isEmpty() && $allPotensiRows->isNotEmpty()) {
            $palembangPotensiRows = $allPotensiRows;
        }

        $totalPopulasi = $palembangPotensiRows->count();
        $sudahMenjawab = $totalPopulasi;
        $menungguJawaban = 0;
        $belumKlarifikasi = 0;
        $potensiIdleCount = $hasData ? 19 : 0;

        // Komposisi Tahap Klarifikasi se-KPKNL Palembang
        $komposisiTahap = [
            'Pemantauan' => $palembangPotensiRows->where('status_tindak_lanjut', 'Pemantauan')->count(),
            'Penelusuran' => $palembangPotensiRows->where('status_tindak_lanjut', 'Penelusuran')->count(),
            'Penelitian' => $palembangPotensiRows->where('status_tindak_lanjut', 'Penelitian')->count(),
        ];

        // 3. Matriks Capaian KPKNL Palembang (Poin 4: Hanya KPKNL Palembang sesuai kewenangan)
        $kpknlRows = $palembangPotensiRows;
        $target = $kpknlRows->count();
        $penelusuran = $kpknlRows->where('status_tindak_lanjut', 'Penelusuran')->count();
        $pemantauan = $kpknlRows->where('status_tindak_lanjut', 'Pemantauan')->count();
        $penelitian = $kpknlRows->where('status_tindak_lanjut', 'Penelitian')->count();
        $potensi = $potensiIdleCount;

        $matriksKPKNL = [
            [
                'kpknl' => 'KPKNL Palembang',
                'target' => $target,
                'sudah_klarifikasi' => $target,
                'belum_klarifikasi' => 0,
                'menunggu_jawaban' => 0,
                'penelusuran' => $penelusuran,
                'pemantauan' => $pemantauan,
                'penelitian' => $penelitian,
                'potensi_idle' => $potensi,
            ]
        ];

        // Poin 3: Daftar Unik Seluruh Kementerian/Lembaga untuk filter Klaster
        $listKementerianLembaga = $palembangPotensiRows->pluck('kementerian_lembaga')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        // 4. Data Pemetaan Kemenkeu (KPKNL Palembang) - Total 79 NUP
        $kemenkeuRows = $palembangPotensiRows->filter(fn($r) => $r->is_kemenkeu || str_contains(strtoupper($r->kementerian_lembaga ?? ''), 'KEUANGAN'));

        $totalKemenkeu = $kemenkeuRows->count();
        $kemenkeuPemantauan = $kemenkeuRows->where('status_tindak_lanjut', 'Pemantauan')->count();
        $kemenkeuPenelusuran = $kemenkeuRows->where('status_tindak_lanjut', 'Penelusuran')->count();
        $kemenkeuPenelitian = $kemenkeuRows->where('status_tindak_lanjut', 'Penelitian')->count();

        $eselon1Counts = [
            'DJP' => $kemenkeuRows->where('eselon1_kemenkeu', 'DJP')->count(),
            'DJBC' => $kemenkeuRows->where('eselon1_kemenkeu', 'DJBC')->count(),
            'DJPb' => $kemenkeuRows->where('eselon1_kemenkeu', 'DJPb')->count(),
            'DJKN' => $kemenkeuRows->where('eselon1_kemenkeu', 'DJKN')->count(),
        ];

        $jenisBarangCounts = [
            'Rumah Negara' => $kemenkeuRows->where('kelompok_barang', 'Rumah Negara')->count(),
            'Bangunan Gedung Kantor' => $kemenkeuRows->where('kelompok_barang', 'Bangunan Gedung Kantor')->count(),
            'Tanah Rumah Negara' => $kemenkeuRows->where('kelompok_barang', 'Tanah Rumah Negara')->count(),
            'Tanah Bangunan Kantor' => $kemenkeuRows->where('kelompok_barang', 'Tanah Bangunan Kantor')->count(),
        ];

        // 5. Potensi Menjadi BMN Idle — Lingkup Kemenkeu (19 BMN)
        $potensiKemenkeuQuery = BmnPotensiIdle::where(function($q) {
                $q->where('is_kemenkeu', true)
                  ->orWhere('kementerian_lembaga', 'LIKE', '%Keuangan%');
            })
            ->where(function($q) {
                $q->where('hasil_jawaban', 'LIKE', '%RB tidak ada rencana karena di duduki oleh pihak ketiga%')
                  ->orWhere('hasil_jawaban', 'LIKE', '%RB tidak ada rencana%')
                  ->orWhere('hasil_jawaban', 'LIKE', '%diserahkan kembal%');
            });

        $potensi19List = $potensiKemenkeuQuery->orderBy('row_no')->get();

        $potensiCount = $potensi19List->count();
        $totalKemenkeuPopulasi = $totalKemenkeu;
        $potensiPersentase = $totalKemenkeuPopulasi > 0 ? round(($potensiCount / $totalKemenkeuPopulasi) * 100, 2) : 0;
        $potensiPersentaseTotal = $totalPopulasi > 0 ? round(($potensiCount / $totalPopulasi) * 100, 2) : 0;

        // Breakdown Tindak Lanjut Dinamis
        $potensiTindakLanjutCounts = [
            'Pemantauan' => $potensi19List->where('status_tindak_lanjut', 'Pemantauan')->count(),
            'Penelitian' => $potensi19List->where('status_tindak_lanjut', 'Penelitian')->count(),
            'Penelusuran' => $potensi19List->where('status_tindak_lanjut', 'Penelusuran')->count(),
        ];

        // Dinamis: Ringkasan teks untuk Card 1 Tab Potensi Kemenkeu
        $potensi19JenisSummary = $potensi19List->pluck('kelompok_barang')->filter()->unique()->take(2)->implode(' & ');
        $potensi19SatkerSummary = $potensi19List->pluck('nama_satker')->filter()->unique()->take(2)->implode(' dan ');
        $potensi19DynamicText = $hasData 
            ? "Sebanyak " . $potensiCount . " unit aset ({$potensi19JenisSummary}) pada {$potensi19SatkerSummary}."
            : "Database lokal kosong. Silakan lakukan sinkronisasi data dari Google Spreadsheet.";

        // Dinamis: Nominal Aset Kolom H Worksheet 'eks bmn idle'
        $nominalEksPalembang = $eksPalembangRows->sum('nilai_perolehan');
        $formattedNominalEksPalembang = "Rp " . number_format($nominalEksPalembang, 0, ',', '.');

        // 6. Data Lengkap untuk Tabel Sumber
        $allPotensiRows = BmnPotensiIdle::orderBy('row_no')->get();
        $allEksRows = BmnEksIdle::where('nama_kpknl', '!=', '3')->orderBy('row_no')->get();

        // 7. Executive Notes & Sync Log & Spreadsheet Settings
        $notes = ExecutiveNote::pluck('note_content', 'section_key')->toArray();
        $lastSync = SyncLog::latest()->first();

        $spreadsheetPotensiUrl = \App\Models\AppSetting::getValue(
            'spreadsheet_potensi_url',
            'https://docs.google.com/spreadsheets/d/1t1SdKB0VAbkvQ8k2iOCXTSWc5qhm6T1hDYjnUyqwM3M/edit?gid=155799981#gid=155799981'
        );
        $spreadsheetEksUrl = \App\Models\AppSetting::getValue(
            'spreadsheet_eks_url',
            'https://docs.google.com/spreadsheets/d/1aInDFtFG7vHIsK6qa472ONeaH9OKmVQYatr93mfGRjA/edit?gid=698305896#gid=698305896'
        );

        return view('dashboard.index', compact(
            'eksSaldoMasuk',
            'eksSaldoPer2026',
            'eksPengelolaanCounts',
            'totalPopulasi',
            'sudahMenjawab',
            'menungguJawaban',
            'belumKlarifikasi',
            'potensiIdleCount',
            'komposisiTahap',
            'matriksKPKNL',
            'totalKemenkeu',
            'kemenkeuPemantauan',
            'kemenkeuPenelusuran',
            'kemenkeuPenelitian',
            'eselon1Counts',
            'jenisBarangCounts',
            'potensi19List',
            'potensiCount',
            'potensiPersentase',
            'potensiPersentaseTotal',
            'potensiTindakLanjutCounts',
            'potensi19DynamicText',
            'nominalEksPalembang',
            'formattedNominalEksPalembang',
            'eksSaldoBarang',
            'listKementerianLembaga',
            'allPotensiRows',
            'allEksRows',
            'notes',
            'lastSync',
            'spreadsheetPotensiUrl',
            'spreadsheetEksUrl'
        ));
    }

    /**
     * Trigger Manual Sync from Google Spreadsheet
     */
    public function syncNow(Request $request)
    {
        try {
            $userName = auth()->user() ? auth()->user()->name : 'User (Manual)';
            $result = $this->syncService->sync($userName, 'SYNC_MANUAL');
            return response()->json($result);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            Log::error('Manual Sync Failed: ' . $msg);
            return response()->json([
                'status' => 'error',
                'message' => $msg
            ], 422);
        }
    }

    /**
     * Save Spreadsheet Settings via AJAX
     */
    public function saveSpreadsheetSettings(Request $request)
    {
        $potensiUrl = trim((string)$request->input('spreadsheet_potensi_url', ''));
        $eksUrl = trim((string)$request->input('spreadsheet_eks_url', ''));

        if (empty($potensiUrl) && empty($eksUrl)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tautan Google Spreadsheet belum diisi.',
            ], 422);
        }

        if (!empty($potensiUrl)) {
            if (!filter_var($potensiUrl, FILTER_VALIDATE_URL) || !preg_match('/\/d\/([a-zA-Z0-9-_]{15,})/', $potensiUrl)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format tautan Spreadsheet Potensi Idle tidak valid. Pastikan format URL lengkap dan memiliki ID Google Sheet yang valid (contoh: https://docs.google.com/spreadsheets/d/1t1SdKB0VAbkvQ8k2iOCXTSWc5qhm6T1hDYjnUyqwM3M/edit...).',
                ], 422);
            }
        }

        if (!empty($eksUrl)) {
            if (!filter_var($eksUrl, FILTER_VALIDATE_URL) || !preg_match('/\/d\/([a-zA-Z0-9-_]{15,})/', $eksUrl)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format tautan Spreadsheet Eks BMN Idle tidak valid. Pastikan format URL lengkap dan memiliki ID Google Sheet yang valid (contoh: https://docs.google.com/spreadsheets/d/1aInDFtFG7vHIsK6qa472ONeaH9OKmVQYatr93mfGRjA/edit...).',
                ], 422);
            }
        }

        \App\Models\AppSetting::setValue(
            'spreadsheet_potensi_url',
            $potensiUrl ?: null,
            'spreadsheet',
            'Link Google Spreadsheet sumber data potensi idle'
        );

        \App\Models\AppSetting::setValue(
            'spreadsheet_eks_url',
            $eksUrl ?: null,
            'spreadsheet',
            'Link Google Spreadsheet sumber data eks bmn idle'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Tautan Google Spreadsheet berhasil diperbarui dan disimpan ke sistem.',
            'potensi_url' => $potensiUrl,
            'eks_url' => $eksUrl,
        ]);
    }

    /**
     * Save Executive Note via AJAX
     */
    public function saveNote(Request $request)
    {
        $request->validate([
            'section_key' => 'required|string',
            'note_content' => 'required|string',
        ]);

        $note = ExecutiveNote::updateOrCreate(
            ['section_key' => $request->section_key],
            [
                'note_content' => $request->note_content,
                'author_name' => auth()->user() ? auth()->user()->name : 'Eksekutif DJKN',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan eksekutif berhasil disimpan.',
            'data' => $note
        ]);
    }

    /**
     * Wipe operational database records (BMN Potensi, BMN Eks, Notes)
     * Catatan: SyncLog TIDAK dihapus agar riwayat audit trail tetap tersimpan.
     * Mencatat aktivitas 'WIPE_ALL_DATA' ke tabel sync_logs.
     */
    public function wipeAllData(Request $request)
    {
        $startTime = microtime(true);
        try {
            BmnPotensiIdle::truncate();
            BmnEksIdle::truncate();
            ExecutiveNote::truncate();

            $duration = round(microtime(true) - $startTime, 2);
            $userName = auth()->user() ? auth()->user()->name : 'User (Manual)';

            SyncLog::create([
                'source_type' => 'WIPE_ALL_DATA',
                'status' => 'SUCCESS',
                'potensi_synced' => 0,
                'eks_idle_synced' => 0,
                'duration_seconds' => $duration,
                'error_message' => 'Database operasional BMN dikosongkan (Wipe All Data).',
                'triggered_by' => $userName,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data BMN Potensi Idle dan Eks BMN Idle berhasil dikosongkan. Seluruh angka, grafik, dan tabel kini kosong sampai Anda melakukan sinkronisasi ulang. Log riwayat sinkronisasi tetap dipertahankan.'
            ]);
        } catch (Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            SyncLog::create([
                'source_type' => 'WIPE_ALL_DATA',
                'status' => 'FAILED',
                'potensi_synced' => 0,
                'eks_idle_synced' => 0,
                'duration_seconds' => $duration,
                'error_message' => 'Gagal Wipe All Data: ' . $e->getMessage(),
                'triggered_by' => auth()->user() ? auth()->user()->name : 'User (Manual)',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengosongkan database: ' . $e->getMessage()
            ], 500);
        }
    }
}
