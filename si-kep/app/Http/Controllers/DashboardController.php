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

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = Pegawai::where('is_active', true)->count();
        $totalPns = Pegawai::where('is_active', true)->where('tipe_pegawai', 'pns')->count();
        $totalPpnpn = Pegawai::where('is_active', true)->where('tipe_pegawai', 'ppnpn')->count();

        // KGB Alerts: within 90 days or overdue
        $now = Carbon::now()->startOfDay();
        $kgbPegawai = Pegawai::where('is_active', true)
            ->whereNotNull('tmt_kgb')
            ->get()
            ->filter(function ($pegawai) use ($now) {
                if (!$pegawai->tmt_kgb) return false;
                $diff = $now->diffInDays($pegawai->tmt_kgb, false);
                return $diff <= 90; // Overdue (<0) or coming up within 90 days
            })
            ->sortBy('tmt_kgb');

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
     * Save Spreadsheet URL Setting
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'sheet_url' => 'required|url',
        ]);

        $url = $request->input('sheet_url');
        AppSetting::set('google_sheet_url', $url, 'Tautan Google Spreadsheet');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tautan Google Spreadsheet berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Tautan Google Spreadsheet berhasil diperbarui.');
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

        $pegawais = $query->get();
        $unitKerjas = UnitKerja::orderBy('urutan')->get();
        $sheetUrl = AppSetting::get('google_sheet_url', GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL);
        $lastSync = SyncLog::latest('synced_at')->first();

        return view('spreadsheet.raw', compact('pegawais', 'unitKerjas', 'sheetUrl', 'lastSync'));
    }
}
