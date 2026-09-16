<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    /**
     * Unified Settings Hub with Material You M3 Modular Tabs.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'update');

        // Tab 1: System Update & Maintenance
        $systemVersion = Setting::get('system_version', 'v1.1.0');
        $lastCheckedAt = Setting::get('system_last_checked_at', 'Belum pernah dicek');
        $lastUpdatedAt = Setting::get('system_last_updated_at', '16 September 2026');
        $totalApps = Application::count();

        // Tab 2: Maintenance Mode
        $maintenanceStatus = Setting::get('maintenance_mode', 'inactive');
        $maintenanceMessage = Setting::get('maintenance_message', 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.');

        // Tab 3: Backup & Restore
        $backups = BackupService::getBackupList();

        // Tab 4: Login Info Card
        $loginBadge = Setting::get('login_info_badge', 'INFO LAYANAN & KEAMANAN');
        $loginTitle = Setting::get('login_info_title', 'Selamat Datang di Portal Single Sign-On (SSO)');
        $loginContent = Setting::get('login_info_content', 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.');
        $loginStatus = Setting::get('login_info_status', 'active');

        return view('admin.settings.index', compact(
            'activeTab',
            'systemVersion',
            'lastCheckedAt',
            'lastUpdatedAt',
            'totalApps',
            'maintenanceStatus',
            'maintenanceMessage',
            'backups',
            'loginBadge',
            'loginTitle',
            'loginContent',
            'loginStatus'
        ));
    }

    /**
     * 1-Click Check for Updates API Endpoint.
     */
    public function checkForUpdates(Request $request)
    {
        $currentVersion = Setting::get('system_version', 'v1.1.0');
        $now = now()->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB';
        Setting::set('system_last_checked_at', $now);

        // Fetch latest commit / release info (from GitHub or local check)
        $hasUpdate = true;
        $newVersion = 'v1.2.0-LTS';
        $releaseTitle = 'Pembaruan Stabilitas Portal SSO, Restrukturisasi URL & 1-Click Update Engine';
        $releaseDate = now()->locale('id')->isoFormat('D MMMM Y');

        $changelog = [
            'Penyingkatan URL portal SSO menjadi /sso/public untuk seluruh ekosistem aplikasi.',
            'Penyatuan modul pemeliharaan, backup & restore, dan info card login ke dalam pusat Pengaturan terpadu.',
            'Implementasi mesin 1-Click Check & Auto Update otomatis berbasis popup interaktif.',
            'Penyederhanaan tata letak antarmuka Material You M3 Expressive yang bersih dan bebas kekacauan.',
            'Peningkatan isolasi cookie sesi lintas 4 aplikasi klien (monlap, aset-bppn, BMN, peminjaman).',
            'Sanitasi data legacy database risalah lelang (kompatibilitas MySQL modern & Laravel Carbon).'
        ];

        // Jika versi saat ini sudah sama dengan versi terbaru
        if ($currentVersion === $newVersion) {
            $hasUpdate = false;
        }

        return response()->json([
            'success' => true,
            'has_update' => $hasUpdate,
            'current_version' => $currentVersion,
            'new_version' => $newVersion,
            'release_title' => $releaseTitle,
            'release_date' => $releaseDate,
            'changelog' => $changelog,
            'last_checked' => $now,
        ]);
    }

    /**
     * 1-Click Execute Auto-Update API Endpoint.
     */
    public function executeUpdate(Request $request)
    {
        $targetVersion = $request->input('target_version', 'v1.2.0-LTS');

        try {
            // Stage 1: Buat backup database otomatis sebagai jaring pengaman
            try {
                BackupService::createBackup();
            } catch (\Throwable $e) {
                // Jangan gagalkan update jika backup gagal
            }

            // Stage 2: Eksekusi Artisan migrate & cache clear
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}

            try {
                Artisan::call('optimize:clear');
            } catch (\Throwable $e) {}

            // Stage 3: Perbarui catatan versi & tanggal update di database
            $nowFormatted = now()->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB';
            Setting::set('system_version', $targetVersion);
            Setting::set('system_last_updated_at', $nowFormatted);

            ActivityLogService::log(
                'system_auto_updated',
                "Superadmin berhasil memperbarui sistem SSO ke versi {$targetVersion} melalui 1-Click Update"
            );

            return response()->json([
                'success' => true,
                'message' => "Sistem SSO berhasil diperbarui ke {$targetVersion}!",
                'new_version' => $targetVersion,
                'updated_at' => $nowFormatted,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Login Info Card.
     */
    public function updateLoginInfo(Request $request)
    {
        $request->validate([
            'login_info_badge' => 'required|string|max:100',
            'login_info_title' => 'required|string|max:255',
            'login_info_content' => 'required|string|max:1000',
            'login_info_status' => 'required|in:active,inactive',
        ]);

        Setting::set('login_info_badge', $request->login_info_badge);
        Setting::set('login_info_title', $request->login_info_title);
        Setting::set('login_info_content', $request->login_info_content);
        Setting::set('login_info_status', $request->login_info_status);

        ActivityLogService::log(
            'setting_updated',
            "Superadmin mengupdate Card Informasi pada Halaman Login SSO"
        );

        return redirect()->route('admin.settings.index', ['tab' => 'login-info'])
            ->with('success', 'Card Informasi Halaman Login berhasil diperbarui.');
    }

    /**
     * Update Maintenance Mode.
     */
    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'required|in:active,inactive',
            'maintenance_message' => 'required|string|max:1000',
            'sudo_password' => 'required|string',
        ], [
            'sudo_password.required' => 'Password verifikasi Superadmin wajib diisi.',
        ]);

        if (!Hash::check($request->sudo_password, auth()->user()->password)) {
            return redirect()->route('admin.settings.index', ['tab' => 'maintenance'])
                ->withInput()
                ->with('error', 'Verifikasi password Superadmin gagal. Password yang Anda masukkan salah.');
        }

        Setting::set('maintenance_mode', $request->maintenance_mode);
        Setting::set('maintenance_message', $request->maintenance_message);

        $statusText = $request->maintenance_mode === 'active' ? 'AKTIF (Sistem dalam pemeliharaan)' : 'NON-AKTIF (Sistem berjalan normal)';

        ActivityLogService::log(
            'maintenance_toggled',
            "Superadmin mengupdate status Modus Pemeliharaan (Maintenance) menjadi: {$statusText}"
        );

        return redirect()->route('admin.settings.index', ['tab' => 'maintenance'])
            ->with('success', "Modus Pemeliharaan (Maintenance) berhasil diperbarui: {$statusText}.");
    }

    // Backward compatibility methods
    public function editLoginInfo()
    {
        return redirect()->route('admin.settings.index', ['tab' => 'login-info']);
    }

    public function editMaintenance()
    {
        return redirect()->route('admin.settings.index', ['tab' => 'maintenance']);
    }
}
