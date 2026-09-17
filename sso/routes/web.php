<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserImportController;
use App\Http\Controllers\Admin\ApplicationController;

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\MaintenanceOrchestratorController;
use App\Http\Controllers\User\LoginSessionController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OAuth\AuthorizationController;
use App\Http\Controllers\OAuth\TokenController;

// Auth Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Application Icon Direct Asset / Fallback Route (guarantees icon availability on all environments safely)
Route::get('/app-icon/{path}', function ($path) {
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'ico', 'gif'];
    $filename = basename($path);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions, true)) {
        abort(404);
    }

    $candidatePaths = [
        storage_path('app/public/' . $path),
        storage_path('app/public/applications/' . $filename),
        public_path('images/apps/' . $filename),
    ];

    $allowedRoots = [
        realpath(storage_path('app/public')),
        realpath(public_path('images/apps')),
    ];

    $targetFile = null;
    foreach ($candidatePaths as $candidate) {
        $real = realpath($candidate);
        if ($real && file_exists($real)) {
            foreach ($allowedRoots as $root) {
                if ($root && str_starts_with($real, $root)) {
                    $targetFile = $real;
                    break 2;
                }
            }
        }
    }

    if (!$targetFile) {
        abort(404);
    }

    $mime = mime_content_type($targetFile) ?: 'image/png';
    if ($ext === 'svg') {
        $mime = 'image/svg+xml';
    }

    return response()->file($targetFile, ['Content-Type' => $mime]);
})->where('path', '.*')->name('application.icon');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notification Center Page
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Profile & Password
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // User Login Sessions (Multi-session tracking)
    Route::get('/login-sessions', [LoginSessionController::class, 'index'])->name('login-sessions.index');
    Route::delete('/login-sessions/{session}', [LoginSessionController::class, 'destroy'])->name('login-sessions.destroy');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');


    // OAuth Authorization Endpoint (user consent / launch app)
    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('oauth.authorize');

    // User Management Routes (Superadmin & Maintenance)
    Route::middleware(['role:superadmin,maintenance'])->prefix('admin')->name('admin.')->group(function () {
        // User Import Routes
        Route::get('users/import-template', [UserImportController::class, 'downloadTemplate'])->name('users.import-template');
        Route::post('users/import-upload', [UserImportController::class, 'upload'])->name('users.import-upload');
        Route::get('users/import-progress/{jobId}', [UserImportController::class, 'progress'])->name('users.import-progress');
        Route::get('users/import-status/{jobId}', [UserImportController::class, 'status'])->name('users.import-status');
        Route::post('users/import-step/{jobId}', [UserImportController::class, 'step'])->name('users.import-step');
        Route::post('users/import-resolve/{jobId}', [UserImportController::class, 'resolveDuplicates'])->name('users.import-resolve');
        Route::post('users/import-cancel/{jobId}', [UserImportController::class, 'cancel'])->name('users.import-cancel');

        // User Management
        Route::resource('users', UserController::class);
    });

    // Maintenance & Technical Admin Routes (Eksklusif Role Maintenance: Kelola Aplikasi, Pengaturan, Backup, Orchestrator)
    Route::middleware(['role:maintenance'])->prefix('admin')->name('admin.')->group(function () {
        // Application Management
        Route::resource('applications', ApplicationController::class);
        Route::post('applications/{application}/regenerate-secret', [ApplicationController::class, 'regenerateSecret'])->name('applications.regenerate-secret');
        Route::post('applications/{application}/assign-users', [ApplicationController::class, 'assignUsers'])->name('applications.assign-users');
        Route::put('applications/{application}/users/{user}/role', [ApplicationController::class, 'updateUserRole'])->name('applications.update-user-role');
        Route::delete('applications/{application}/users/{user}', [ApplicationController::class, 'revokeUser'])->name('applications.revoke-user');

        // Backup & Restore Management
        Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('backup/create', [BackupController::class, 'create'])->name('backup.create');
        Route::get('backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
        Route::post('backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
        Route::delete('backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');

        // Unified Settings Hub (Pusat Pengaturan Terpadu)
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings/check-update', [SettingController::class, 'checkForUpdates'])->name('settings.check-update');
        Route::post('settings/execute-update', [SettingController::class, 'executeUpdate'])->name('settings.execute-update');

        // Login Info Card Settings
        Route::get('settings/login-info', [SettingController::class, 'editLoginInfo'])->name('settings.login-info.edit');
        Route::post('settings/login-info', [SettingController::class, 'updateLoginInfo'])->name('settings.login-info.update');

        // Maintenance Mode Settings
        Route::get('settings/maintenance', [SettingController::class, 'editMaintenance'])->name('settings.maintenance.edit');
        Route::post('settings/maintenance', [SettingController::class, 'updateMaintenance'])->name('settings.maintenance.update');

        // Centralized Maintenance & Lifecycle Orchestrator
        Route::prefix('maintenance-orchestrator')->name('maintenance.')->group(function () {
            Route::get('/', [MaintenanceOrchestratorController::class, 'index'])->name('index');
            Route::get('/apps/{application}', [MaintenanceOrchestratorController::class, 'appConsole'])->name('app-console');
            Route::get('/deployments', [MaintenanceOrchestratorController::class, 'deployments'])->name('deployments');
            Route::get('/backups', [MaintenanceOrchestratorController::class, 'backups'])->name('backups');
            Route::post('/fetch-git', [MaintenanceOrchestratorController::class, 'fetchGit'])->name('fetch-git');
            Route::get('/commits/{application}', [MaintenanceOrchestratorController::class, 'getGitCommits'])->name('commits');
            Route::post('/deploy/{application}', [MaintenanceOrchestratorController::class, 'deploy'])->name('deploy');
            Route::post('/rollback/{application}', [MaintenanceOrchestratorController::class, 'rollback'])->name('rollback');
            Route::post('/toggle-maintenance/{application}', [MaintenanceOrchestratorController::class, 'toggleMaintenance'])->name('toggle');
            Route::post('/backup/{application}', [MaintenanceOrchestratorController::class, 'createBackup'])->name('backup');
            Route::post('/restore-backup/{backup}', [MaintenanceOrchestratorController::class, 'restoreBackup'])->name('restore-backup');
            Route::get('/download-backup/{backup}', [MaintenanceOrchestratorController::class, 'downloadBackup'])->name('download-backup');
            Route::get('/logs/{deployment}', [MaintenanceOrchestratorController::class, 'getDeploymentLog'])->name('logs');
            Route::get('/health/{application}', [MaintenanceOrchestratorController::class, 'checkHealth'])->name('health');
        });
    });
});



// OAuth Server Public Endpoints (Protected by Rate Limiter)
Route::post('/oauth/token', [TokenController::class, 'token'])
    ->middleware('throttle:60,1')
    ->name('oauth.token');
