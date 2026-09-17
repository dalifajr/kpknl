<?php

use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagramController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\UnitKerjaController;
use Illuminate\Support\Facades\Route;

// Authentication via SSO KPKNL Palembang
Route::get('/login', [SsoController::class, 'showLogin'])->name('login');
Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
Route::post('/logout', [SsoController::class, 'logout'])->name('logout');

// Protected Routes (Accessible by Authenticated Users)
Route::middleware('auth')->group(function () {
    // 1. Dashboard Eksekutif
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync', [DashboardController::class, 'sync'])->name('sync');
    Route::post('/settings/sheet-url', [DashboardController::class, 'saveSettings'])->name('settings.sheet_url');

    // 2. Data Kepegawaian & Detail Modal
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('/pegawai/filter-modal', [PegawaiController::class, 'filterModal'])->name('pegawai.filter_modal');
    Route::get('/pegawai/form-data/{id?}', [PegawaiController::class, 'getFormData'])->name('pegawai.form_data');
    Route::post('/pegawai/store', [PegawaiController::class, 'store'])->name('pegawai.store');
    Route::post('/pegawai/{id}/update', [PegawaiController::class, 'update'])->name('pegawai.update');
    Route::get('/pegawai/{id}/detail', [PegawaiController::class, 'detail'])->name('pegawai.detail');

    // 3. Struktur & Jabatan
    Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');

    // 4. Formasi Unit Kerja
    Route::get('/unit-kerja', [UnitKerjaController::class, 'index'])->name('unit_kerja.index');

    // 5. Diagram & Analitika
    Route::get('/diagram', [DiagramController::class, 'index'])->name('diagram.index');

    // 6. Data Mentah Spreadsheet (Khusus Superadmin, Admin, Maintenance)
    Route::get('/spreadsheet-raw', [DashboardController::class, 'spreadsheetRaw'])->name('spreadsheet.raw');

    // 7. Log Perubahan & Audit Sync (Superadmin & Maintenance)
    Route::get('/log-perubahan', [\App\Http\Controllers\ChangeLogController::class, 'index'])->name('change_log.index');
    Route::post('/log-perubahan/sync-pending', [\App\Http\Controllers\ChangeLogController::class, 'syncPending'])->name('change_log.sync_pending');
    Route::post('/log-perubahan/{id}/rollback', [\App\Http\Controllers\ChangeLogController::class, 'rollback'])->name('change_log.rollback');

    // 8. Wipe Data & Deteksi Izin Spreadsheet (Maintenance / Superadmin)
    Route::post('/settings/check-spreadsheet-permission', [DashboardController::class, 'checkSpreadsheetPermission'])->name('settings.check_permission');
    Route::post('/settings/wipe-data', [DashboardController::class, 'wipeData'])->name('settings.wipe_data');

    // 9. Informasi Sistem & Tentang Aplikasi
    Route::get('/about', function () {
        $totalPegawai = \App\Models\Pegawai::where('is_active', true)->count();
        $lastSynced = \App\Models\AppSetting::get('last_synced_at');
        return view('about.index', compact('totalPegawai', 'lastSynced'));
    })->name('about');
});
