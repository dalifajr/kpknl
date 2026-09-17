<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Jabatan;
use App\Models\PangkatGolongan;
use App\Models\Pegawai;
use App\Models\SyncLog;
use App\Models\UnitKerja;
use App\Services\GoogleSheetSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = Pegawai::where('is_active', true)->count();
        $totalPns = Pegawai::where('is_active', true)->where('tipe_pegawai', 'pns')->count();
        $totalPpnpn = Pegawai::where('is_active', true)->where('tipe_pegawai', 'ppnpn')->count();

        // Status Akurasi Data & Gelar HRIS ASN Definitif
        $pnsClearHris = Pegawai::where('is_active', true)
            ->where('tipe_pegawai', 'pns')
            ->where(function ($q) {
                $q->where('status_gelar', 'like', '%Clear%')
                  ->orWhere('status_gelar', 'like', '%sesuai%');
            })->count();

        $pnsMismatchHris = Pegawai::where('is_active', true)
            ->where('tipe_pegawai', 'pns')
            ->where('status_gelar', 'like', '%tidak sesuai%')
            ->count();

        // KGB Alerts: 2 tahun dari TMT KGB terakhir, alert jika overdue (<0) atau jatuh tempo dalam 90 hari
        $now = Carbon::now()->startOfDay();
        $kgbPegawai = Pegawai::where('is_active', true)
            ->whereNotNull('tmt_kgb')
            ->get()
            ->filter(function ($pegawai) use ($now) {
                if (!$pegawai->tmt_kgb) return false;
                $nextKgb = $pegawai->tmt_kgb->copy()->addYears(2)->startOfDay();
                $diff = $now->diffInDays($nextKgb, false);
                return $diff <= 90; // Overdue (<0) or coming up within 90 days
            })
            ->sortBy(function ($p) {
                return $p->tmt_kgb ? $p->tmt_kgb->copy()->addYears(2)->timestamp : PHP_INT_MAX;
            });

        $totalKgbAlerts = $kgbPegawai->count();

        // Pensiun Radar: Pegawai usia >= 55 tahun atau sisa dinas <= 36 bulan
        $radarPensiun = Pegawai::where('is_active', true)
            ->where('tipe_pegawai', 'pns')
            ->where('usia_tahun', '>=', 53)
            ->orderByDesc('usia_tahun')
            ->get();

        $totalPensiunRadar = $radarPensiun->count();

        // Tour of Duty: > 4 tahun di Palembang
        $tourOfDutyPegawai = Pegawai::where('is_active', true)
            ->where('tipe_pegawai', 'pns')
            ->where('lama_palembang_tahun', '>=', 4)
            ->orderByDesc('lama_palembang_tahun')
            ->get();

        $totalTourOfDuty = $tourOfDutyPegawai->count();

        // Generasi breakdown
        $genZ = Pegawai::where('is_active', true)->where('usia_tahun', '<', 28)->count();
        $milenial = Pegawai::where('is_active', true)->whereBetween('usia_tahun', [28, 43])->count();
        $genX = Pegawai::where('is_active', true)->whereBetween('usia_tahun', [44, 59])->count();
        $boomer = Pegawai::where('is_active', true)->where('usia_tahun', '>=', 60)->count();

        // Gender breakdown
        $totalLaki = Pegawai::where('is_active', true)->where('jenis_kelamin', 'L')->count();
        $totalPerempuan = Pegawai::where('is_active', true)->where('jenis_kelamin', 'P')->count();

        // Unit Kerja Breakdown
        $unitStats = UnitKerja::withCount(['pegawai as total_pns' => function ($q) {
            $q->where('tipe_pegawai', 'pns')->where('is_active', true);
        }, 'pegawai as total_ppnpn' => function ($q) {
            $q->where('tipe_pegawai', 'ppnpn')->where('is_active', true);
        }])->orderBy('urutan')->get();

        // Last sync info
        $lastSync = SyncLog::latest('synced_at')->first();
        $sheetUrl = AppSetting::get('google_sheet_url', GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL);

        return view('dashboard.index', compact(
            'totalPegawai',
            'totalPns',
            'totalPpnpn',
            'totalKgbAlerts',
            'kgbPegawai',
            'totalPensiunRadar',
            'radarPensiun',
            'totalTourOfDuty',
            'tourOfDutyPegawai',
            'genZ',
            'milenial',
            'genX',
            'boomer',
            'totalLaki',
            'totalPerempuan',
            'pnsClearHris',
            'pnsMismatchHris',
            'unitStats',
            'lastSync',
            'sheetUrl'
        ));
    }

    /**
     * Trigger Google Sheet Sync
     */
    public function sync(Request $request, GoogleSheetSyncService $syncService)
    {
        $user = auth()->user();
        if ($user && $user->role === 'user') {
            $msg = 'Akses ditolak. Sinkronisasi hanya dapat dijalankan oleh Admin atau Tim Kepegawaian.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        $url = $request->input('sheet_url');
        $result = $syncService->sync($url);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Save Spreadsheet Settings (Maintenance only)
     */
    public function saveSettings(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'maintenance') {
            abort(403, 'Akses ditolak. Pengaturan hanya dapat diubah oleh akun Maintenance.');
        }

        $request->validate([
            'sheet_url' => 'required|url',
            'webhook_url' => 'nullable|url',
        ]);

        $url = $request->input('sheet_url');
        AppSetting::set('google_sheet_url', $url, 'Tautan Google Spreadsheet');

        if ($request->filled('webhook_url')) {
            AppSetting::set('google_sheet_webhook_url', $request->input('webhook_url'), 'Webhook Google Apps Script');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Google Spreadsheet berhasil disimpan.',
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan Google Spreadsheet berhasil disimpan.');
    }

    /**
     * Check spreadsheet permissions and connection status (Maintenance / Superadmin)
     */
    public function checkSpreadsheetPermission(Request $request, GoogleSheetSyncService $syncService)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat melakukan pengecekan izin.',
            ], 403);
        }

        $sheetUrl = $request->input('sheet_url');
        $webhookUrl = $request->input('webhook_url');

        // SSRF Protection: Validate allowed Google domains
        if ($sheetUrl) {
            $parsed = parse_url($sheetUrl);
            $scheme = strtolower($parsed['scheme'] ?? '');
            $host = strtolower($parsed['host'] ?? '');
            if ($scheme !== 'https' || !in_array($host, ['docs.google.com', 'drive.google.com'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SSRF Alert: Tautan spreadsheet harus menggunakan protokol HTTPS dan domain resmi Google Docs (docs.google.com).',
                ], 422);
            }
        }

        if ($webhookUrl) {
            $parsed = parse_url($webhookUrl);
            $scheme = strtolower($parsed['scheme'] ?? '');
            $host = strtolower($parsed['host'] ?? '');
            if ($scheme !== 'https' || !in_array($host, ['script.google.com', 'script.googleusercontent.com'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SSRF Alert: Tautan webhook harus menggunakan protokol HTTPS dan domain resmi Google Apps Script (script.google.com).',
                ], 422);
            }
        }

        $result = $syncService->checkPermissions($sheetUrl, $webhookUrl);

        return response()->json($result);
    }

    /**
     * Wipe all Pegawai and synchronization data (Maintenance only)
     */
    public function wipeData(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'maintenance') {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya akun role Maintenance yang dapat melakukan Wipe Data.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $totalPegawai = Pegawai::count();

            // Record Wipe in ChangeLog
            \App\Models\ChangeLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'nama_pegawai' => 'ALL_DATA',
                'action' => 'wipe',
                'description' => "Wipe Data: {$totalPegawai} data pegawai dihapus oleh akun Maintenance ({$user->name}).",
                'sync_status' => 'synced',
            ]);

            // Truncate or Delete Pegawai
            Pegawai::query()->delete();
            SyncLog::query()->delete();

            AppSetting::set('last_synced_at', null);

            DB::commit();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Wipe Data Berhasil! Seluruh {$totalPegawai} data pegawai dan log telah dibersihkan.",
                ]);
            }

            return redirect()->back()->with('success', "Wipe Data Berhasil! {$totalPegawai} data pegawai telah dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan Wipe Data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View Data Mentah Spreadsheet (Khusus Superadmin, Admin, Maintenance)
     */
    public function spreadsheetRaw(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = ['superadmin', 'admin', 'maintenance'];

        if (!$user || !in_array($user->role, $allowedRoles)) {
            abort(403, 'Akses Terbatas: Halaman Data Mentah Spreadsheet hanya dapat diakses oleh Superadmin, Admin, dan Maintenance.');
        }

        $query = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])->orderBy('no_urut');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nama_jabatan_raw', 'like', "%{$search}%")
                  ->orWhere('pangkat_golongan_raw', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe_pegawai', $request->input('tipe'));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_kerja_id', $request->input('unit_id'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) $perPage = 10;
        $totalRows = (clone $query)->count();
        $pegawais = $query->paginate($perPage)->withQueryString();
        $unitKerjas = UnitKerja::orderBy('urutan')->get();
        $sheetUrl = AppSetting::get('google_sheet_url', GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL);
        $lastSync = SyncLog::latest('synced_at')->first();

        return view('spreadsheet.raw', compact('pegawais', 'totalRows', 'unitKerjas', 'sheetUrl', 'lastSync'));
    }
}
