<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function (\Illuminate\Http\Request $request) {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    if (session()->has('sso_error') || session()->has('errors') || $request->has('login')) {
        return view('auth.login', [
            'ssoError' => session('sso_error') ?? (session('errors') ? session('errors')->first() : null),
        ]);
    }
    return redirect()->route('auth.redirect');
})->name('login');

Route::get('/auth/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $leasedAssets = \App\Models\Asset::where('kondisi_aset', 'DISEWAKAN')->get();
        $expiringLeases = $leasedAssets->filter(function($a) { return $a->is_sewa_expiring; });
        $totalSewaNilai = $leasedAssets->sum('sewa_lelang_nilai');
        
        $yearSql = config('database.default') === 'sqlite' ? "strftime('%Y', tgl_mulai)" : "YEAR(tgl_mulai)";
        $leaseTrend = \App\Models\AssetLease::selectRaw("{$yearSql} as tahun, SUM(nilai_sewa) as total_nilai, COUNT(*) as total_kontrak")
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->get();

        return view('dashboard', compact('leasedAssets', 'expiringLeases', 'totalSewaNilai', 'leaseTrend'));
    })->name('dashboard');

    // Mass Asset Import
    Route::get('/assets/import', [\App\Http\Controllers\AssetImportController::class, 'index'])->name('assets.import.index');
    Route::get('/assets/import/template', [\App\Http\Controllers\AssetImportController::class, 'downloadTemplate'])->name('assets.import.template');
    Route::post('/assets/import/preview', [\App\Http\Controllers\AssetImportController::class, 'preview'])->name('assets.import.preview');
    Route::post('/assets/import/execute', [\App\Http\Controllers\AssetImportController::class, 'execute'])->name('assets.import.execute');
    Route::get('/assets/import/{id}', [\App\Http\Controllers\AssetImportController::class, 'show'])->name('assets.import.show');
    Route::get('/assets/import/{id}/failed-excel', [\App\Http\Controllers\AssetImportController::class, 'downloadFailed'])->name('assets.import.downloadFailed');

    // Assets CRUD & Reports
    Route::get('/assets/export/excel', [\App\Http\Controllers\AssetController::class, 'exportExcel'])->name('assets.exportExcel');
    Route::get('/assets/export/pdf', [\App\Http\Controllers\AssetController::class, 'exportPdf'])->name('assets.exportPdf');
    Route::get('/assets/export/buku-profil', [\App\Http\Controllers\AssetController::class, 'exportBukuProfilPdf'])->name('assets.exportBukuProfilPdf');
    Route::get('/assets/export/resume-profil', [\App\Http\Controllers\AssetController::class, 'exportResumePdf'])->name('assets.exportResumePdf');
    Route::get('/assets/{asset}/buku-profil', [\App\Http\Controllers\AssetController::class, 'exportBukuProfilPdf'])->name('assets.singleBukuProfilPdf');
    Route::post('/assets/{id}/restore', [\App\Http\Controllers\AssetController::class, 'restore'])->name('assets.restore');
    Route::post('/assets/{id}/perpanjang-sewa', [\App\Http\Controllers\AssetController::class, 'perpanjangSewa'])->name('assets.perpanjangSewa');
    Route::post('/assets/{asset}/upload-attachment', [\App\Http\Controllers\AssetController::class, 'uploadAttachment'])->name('assets.uploadAttachment');
    Route::delete('/assets/bulk', [\App\Http\Controllers\AssetController::class, 'bulkDestroy'])->name('assets.bulkDestroy');
    Route::resource('assets', \App\Http\Controllers\AssetController::class);

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Region API
    Route::get('/api/provinces', [\App\Http\Controllers\Api\RegionController::class, 'provinces']);
    Route::get('/api/regencies/{province}', [\App\Http\Controllers\Api\RegionController::class, 'regencies']);
    Route::get('/api/districts/{regency}', [\App\Http\Controllers\Api\RegionController::class, 'districts']);
    Route::get('/api/villages/{district}', [\App\Http\Controllers\Api\RegionController::class, 'villages']);
});
