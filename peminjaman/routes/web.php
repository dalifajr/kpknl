<?php

use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\DashboardWebController;
use App\Http\Controllers\KatalogWebController;
use App\Http\Controllers\PeminjamanWebController;
use App\Http\Controllers\StatistikWebController;
use App\Http\Controllers\ValidasiWebController;
use App\Http\Controllers\Api\PeminjamanApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Peminjaman Risalah Lelang KPKNL Palembang
|--------------------------------------------------------------------------
| Arsitektur Fullstack Laravel Blade terintegrasi Single Sign-On (SSO).
*/

// SSO Authentication Routes (Single Sign-On KPKNL Palembang)
Route::get('/auth/sso', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
Route::match(['get', 'post'], '/logout', [SsoController::class, 'logout'])->name('logout');

// Eliminasi Login & Register Manual: dialihkan langsung ke SSO terpusat
Route::get('/login', function () {
    return redirect()->route('sso.redirect');
})->name('login');

Route::get('/register', function () {
    return redirect()->route('sso.redirect');
})->name('register');

// Fullstack Laravel Blade Web Application Routes (Stateful Auth Session)
Route::middleware(['auth'])->group(function () {
    // 1. Monitoring Risalah Lelang (Dashboard Utama)
    Route::get('/', [DashboardWebController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

    // 2. Statistik & Tren Analitik Grafik
    Route::get('/statistik', [StatistikWebController::class, 'index'])->name('statistik.index');
    Route::get('/statistik/data', [StatistikWebController::class, 'getData'])->name('statistik.data');

    // 3. Katalog Risalah Lelang Fisik
    Route::get('/katalog', [KatalogWebController::class, 'index'])->name('katalog.index');

    // 4. Tata Kelola Peminjaman Berkas (5 Tahap Alur)
    Route::get('/peminjaman', [PeminjamanWebController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/store', [PeminjamanWebController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/{id}/approve', [PeminjamanWebController::class, 'approve'])->name('peminjaman.approve');
    Route::post('/peminjaman/{id}/confirm-receive', [PeminjamanWebController::class, 'confirmReceive'])->name('peminjaman.confirm-receive');
    Route::post('/peminjaman/{id}/return-request', [PeminjamanWebController::class, 'returnRequest'])->name('peminjaman.return-request');
    Route::post('/peminjaman/{id}/verify-return', [PeminjamanWebController::class, 'verifyReturn'])->name('peminjaman.verify-return');

    // 4. Validasi Risalah Pending (Admin Seksi HI)
    Route::get('/validasi', [ValidasiWebController::class, 'index'])->name('validasi.index');
    Route::post('/validasi/{id}/approve', [ValidasiWebController::class, 'approve'])->name('validasi.approve');
    Route::post('/validasi/{id}/reject', [ValidasiWebController::class, 'reject'])->name('validasi.reject');

    // 5. Pendaftaran Risalah Baru (Pelelang & Admin)
    Route::get('/pendaftaran', [ValidasiWebController::class, 'pendaftaran'])->name('pendaftaran.index');
    Route::post('/pendaftaran/store', [ValidasiWebController::class, 'storePendaftaran'])->name('pendaftaran.store');

    // 6. Revisi Risalah (Pelelang & Admin)
    Route::get('/revisi', [ValidasiWebController::class, 'revisi'])->name('revisi.index');
    Route::post('/revisi/{id}/resubmit', [ValidasiWebController::class, 'resubmitRevisi'])->name('revisi.resubmit');
});

// RESTful API Endpoints (Stateful Auth Session for AJAX / Client Fetch)
Route::prefix('api')->middleware(['web'])->group(function () {
    Route::get('/auth/me', [PeminjamanApiController::class, 'me']);

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard/stats', [PeminjamanApiController::class, 'dashboardStats']);
        Route::get('/risalah', [PeminjamanApiController::class, 'getRisalah']);
        Route::get('/risalah/pending', [PeminjamanApiController::class, 'getPendingRisalah']);
        Route::get('/risalah/revisi', [PeminjamanApiController::class, 'getRevisiRisalah']);
        Route::post('/risalah/store', [PeminjamanApiController::class, 'storeRisalah']);
        Route::post('/risalah/{id}/validate', [PeminjamanApiController::class, 'approveRisalah']);
        Route::post('/risalah/{id}/reject-revision', [PeminjamanApiController::class, 'rejectRisalah']);
        Route::post('/risalah/revisi/{id}/resubmit', [PeminjamanApiController::class, 'resubmitRevisi']);
        Route::get('/peminjaman', [PeminjamanApiController::class, 'getPeminjaman']);
        Route::post('/peminjaman/store', [PeminjamanApiController::class, 'storePeminjaman']);
        Route::post('/peminjaman/{id}/approve', [PeminjamanApiController::class, 'approvePeminjaman']);
        Route::post('/peminjaman/{id}/confirm-receive', [PeminjamanApiController::class, 'confirmReceivePeminjaman']);
        Route::post('/peminjaman/{id}/return-request', [PeminjamanApiController::class, 'requestReturnPeminjaman']);
        Route::post('/peminjaman/{id}/verify-return', [PeminjamanApiController::class, 'verifyReturnPeminjaman']);
    });
});
