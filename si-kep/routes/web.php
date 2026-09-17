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
    Route::get('/pegawai/{id}/detail', [PegawaiController::class, 'detail'])->name('pegawai.detail');

    // 3. Struktur & Jabatan
    Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');

    // 4. Formasi Unit Kerja
    Route::get('/unit-kerja', [UnitKerjaController::class, 'index'])->name('unit_kerja.index');

    // 5. Diagram & Analitika
    Route::get('/diagram', [DiagramController::class, 'index'])->name('diagram.index');

    // 6. Data Mentah Spreadsheet (Khusus Superadmin, Admin, Maintenance)
    Route::get('/spreadsheet-raw', [DashboardController::class, 'spreadsheetRaw'])->name('spreadsheet.raw');
});
