<?php

/**
 * KPKNL Palembang - Script Sinkronisasi Metadata Pembaruan Sistem SSO
 * Dipanggil oleh update.bat setelah git pull & migrasi selesai dieksekusi.
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$commit = $argv[1] ?? 'latest';
$now = now()->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB';

try {
    \App\Models\Setting::set('system_version', 'v1.2.0 (' . $commit . ')');
    \App\Models\Setting::set('system_last_updated_at', $now);
    
    \App\Services\ActivityLogService::log(
        'system_updated_bat',
        "Pembaruan sistem repositori (Commit {$commit}) berhasil dieksekusi via update.bat"
    );

    echo " [OK] Metadata berhasil dicatat ke Portal SSO: {$now} | Versi: v1.2.0 ({$commit})" . PHP_EOL;
} catch (\Throwable $e) {
    echo " [WARNING] Tidak dapat memperbarui metadata SSO: " . $e->getMessage() . PHP_EOL;
}
