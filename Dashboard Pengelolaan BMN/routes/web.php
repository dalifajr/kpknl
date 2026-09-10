<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\SsoController;

// Authentication Routes (Strict SSO Login)
Route::get('/login', [SsoController::class, 'showLogin'])->name('login');
Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('auth.sso.redirect');
Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('auth.sso.callback');
Route::post('/auth/logout', [SsoController::class, 'logout'])->name('auth.logout');

// Protected Executive Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/api/sync', [DashboardController::class, 'syncNow'])->name('api.sync');
    Route::post('/api/notes', [DashboardController::class, 'saveNote'])->name('api.notes.save');
    Route::post('/api/settings/spreadsheet', [DashboardController::class, 'saveSpreadsheetSettings'])->name('api.settings.spreadsheet');
    Route::post('/api/wipe-all-data', [DashboardController::class, 'wipeAllData'])->name('api.wipeAllData');
});

// Helper for local browser testing
if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'kepala.kantor@kemenkeu.go.id'],
            [
                'name' => 'Kepala KPKNL Palembang',
                'username' => 'kepala-kantor',
                'role' => 'superadmin',
                'password' => bcrypt('password123')
            ]
        );
        \Illuminate\Support\Facades\Auth::login($user, true);
        return redirect()->route('dashboard');
    });
}
