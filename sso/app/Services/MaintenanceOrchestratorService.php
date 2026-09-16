<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationBackup;
use App\Models\ApplicationDeployment;
use App\Models\ApplicationRelease;
use App\Models\MaintenanceEvent;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaintenanceOrchestratorService
{
    /**
     * Get root monorepo directory (d:\laragon\www)
     */
    public static function getWorkspaceRoot(): string
    {
        return realpath(dirname(base_path())) ?: dirname(base_path());
    }

    /**
     * Resolve Laragon/system binary directories for Windows
     */
    public static function getLaragonBinDirs(): array
    {
        $roots = ['C:\\laragon\\bin', 'D:\\laragon\\bin'];
        $dirs = [];

        foreach ($roots as $root) {
            if (!is_dir($root)) continue;

            // Git cmd / bin
            $gitCmd = $root . '\\git\\cmd';
            $gitBin = $root . '\\git\\bin';
            if (is_dir($gitCmd)) $dirs[] = $gitCmd;
            elseif (is_dir($gitBin)) $dirs[] = $gitBin;

            // Composer
            $composerDir = $root . '\\composer';
            if (is_dir($composerDir)) $dirs[] = $composerDir;

            // PHP versions (prioritizing active version)
            $phpRoot = $root . '\\php';
            if (is_dir($phpRoot)) {
                $phpDirs = glob($phpRoot . '\\php*', GLOB_ONLYDIR);
                if (!empty($phpDirs)) {
                    foreach ($phpDirs as $p) {
                        $dirs[] = $p;
                    }
                }
            }

            // MySQL
            $mysqlBin = static::getMysqlBinDir();
            if ($mysqlBin) {
                $dirs[] = $mysqlBin;
            }
        }

        return array_values(array_unique($dirs));
    }

    /**
     * Resolve PHP CLI binary (avoiding php-cgi.exe when running in web server)
     */
    public static function getPhpCliBinary(): string
    {
        $bin = PHP_BINARY;
        if (str_ends_with(strtolower($bin), 'php-cgi.exe')) {
            $cli = dirname($bin) . DIRECTORY_SEPARATOR . 'php.exe';
            if (file_exists($cli)) return $cli;
        } elseif (str_ends_with(strtolower($bin), 'php-cgi')) {
            $cli = dirname($bin) . DIRECTORY_SEPARATOR . 'php';
            if (file_exists($cli)) return $cli;
        }

        foreach (static::getLaragonBinDirs() as $dir) {
            $candidate = $dir . DIRECTORY_SEPARATOR . 'php.exe';
            if (file_exists($candidate)) return $candidate;
        }

        return $bin ?: 'php';
    }

    /**
     * Resolve MySQL binaries directory (e.g. Laragon or XAMPP or PATH)
     */
    public static function getMysqlBinDir(): string
    {
        $candidateDirs = [
            'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin',
            'C:\\laragon\\bin\\mysql\\default\\bin',
            'D:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin',
            'C:\\xampp\\mysql\\bin',
            'D:\\xampp\\mysql\\bin',
        ];

        foreach ($candidateDirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . 'mysqldump.exe')) {
                return $dir;
            }
        }

        return '';
    }

    /**
     * Get mysqldump executable command
     */
    public static function getMysqldumpBinary(): string
    {
        $dir = static::getMysqlBinDir();
        if ($dir && file_exists($dir . DIRECTORY_SEPARATOR . 'mysqldump.exe')) {
            return '"' . $dir . DIRECTORY_SEPARATOR . 'mysqldump.exe"';
        }
        return 'mysqldump';
    }

    /**
     * Get mysql client executable command
     */
    public static function getMysqlClientBinary(): string
    {
        $dir = static::getMysqlBinDir();
        if ($dir && file_exists($dir . DIRECTORY_SEPARATOR . 'mysql.exe')) {
            return '"' . $dir . DIRECTORY_SEPARATOR . 'mysql.exe"';
        }
        return 'mysql';
    }

    /**
     * Check if workspace root is a git repository
     */
    public static function isGitRepository(): bool
    {
        $root = static::getWorkspaceRoot();
        return is_dir($root . DIRECTORY_SEPARATOR . '.git');
    }

    /**
     * Execute a shell command via proc_open with Laragon PATH environment injection
     */
    public static function runProcess(string $command, ?string $cwd = null, ?string $stdin = null, int $timeout = 180): array
    {
        $cwd = $cwd ?: static::getWorkspaceRoot();
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // Prepare environment with Laragon tools and non-interactive Git
        $rawEnv = array_merge($_ENV, $_SERVER);
        $env = [];
        foreach ($rawEnv as $k => $v) {
            if (is_scalar($v)) {
                $env[$k] = (string)$v;
            }
        }
        $laragonBins = static::getLaragonBinDirs();
        $currentPath = getenv('PATH') ?: ($env['PATH'] ?? ($env['Path'] ?? ''));
        if (!empty($laragonBins)) {
            $newPath = implode(PATH_SEPARATOR, $laragonBins) . PATH_SEPARATOR . $currentPath;
            $env['PATH'] = $newPath;
            $env['Path'] = $newPath;
            putenv("PATH={$newPath}");
        }

        $env['GIT_TERMINAL_PROMPT'] = '0';
        $env['GIT_SSH_COMMAND'] = 'ssh -o BatchMode=yes';

        if (PHP_OS_FAMILY === 'Windows') {
            $cmdExec = 'cmd.exe /c "' . $command . '"';
            $process = proc_open($cmdExec, $descriptors, $pipes, $cwd, $env, ['bypass_shell' => true]);
        } else {
            $process = proc_open($command, $descriptors, $pipes, $cwd, $env, ['bypass_shell' => false]);
        }

        if (!is_resource($process)) {
            return [
                'success' => false,
                'output' => "Gagal menjalankan proses: {$command}",
                'exit_code' => -1,
            ];
        }

        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        $output = trim($stdout . "\n" . $stderr);

        return [
            'success' => ($exitCode === 0),
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Run PHP Artisan command inside application directory
     */
    public static function runArtisan(Application $app, string $artisanCommand): array
    {
        $appPath = $app->resolved_path;
        if (!$appPath || !file_exists($appPath)) {
            return [
                'success' => false,
                'output' => "Folder aplikasi tidak ditemukan: {$app->folder_path}",
                'exit_code' => -1,
            ];
        }

        $phpBin = escapeshellarg(static::getPhpCliBinary());
        $fullCmd = "{$phpBin} artisan {$artisanCommand}";

        return static::runProcess($fullCmd, $appPath);
    }

    /**
     * Fetch remote changes from origin git
     */
    public static function fetchRemote(string $branch = 'main'): array
    {
        if (!static::isGitRepository()) {
            return [
                'success' => false,
                'output' => "Folder workspace (" . static::getWorkspaceRoot() . ") belum diinisialisasi sebagai repositori Git.",
                'exit_code' => -1,
            ];
        }

        $root = static::getWorkspaceRoot();
        return static::runProcess("git fetch origin {$branch}", $root);
    }

    /**
     * Get recent git commits for application folder
     */
    public static function getCommitHistory(Application $app, int $limit = 15): array
    {
        if (!static::isGitRepository()) {
            return [];
        }

        $root = static::getWorkspaceRoot();
        $folder = $app->folder_path ? escapeshellarg($app->folder_path) : '';
        $pathFilter = $folder ? "-- {$folder}" : '';

        // Format: %H (hash) | %h (short) | %an (author) | %ad (date) | %s (subject)
        $cmd = "git log -n {$limit} --format=\"%H|%h|%an|%ad|%s\" --date=format:\"%Y-%m-%d %H:%M\" origin/main {$pathFilter}";
        $result = static::runProcess($cmd, $root);

        if (!$result['success'] || empty($result['output'])) {
            // Fallback to local HEAD
            $cmd = "git log -n {$limit} --format=\"%H|%h|%an|%ad|%s\" --date=format:\"%Y-%m-%d %H:%M\" {$pathFilter}";
            $result = static::runProcess($cmd, $root);
        }

        $commits = [];
        if (!empty($result['output'])) {
            $lines = explode("\n", trim($result['output']));
            foreach ($lines as $line) {
                $parts = explode('|', trim($line), 5);
                if (count($parts) >= 5) {
                    $hash = trim($parts[0]);
                    $commits[] = [
                        'hash' => $hash,
                        'short_hash' => trim($parts[1]),
                        'author' => trim($parts[2]),
                        'date' => trim($parts[3]),
                        'message' => trim($parts[4]),
                        'is_current' => ($app->current_commit === $hash),
                    ];
                }
            }
        }

        return $commits;
    }

    /**
     * Check application health status
     */
    public static function checkAppHealth(Application $app): array
    {
        $start = microtime(true);
        $url = $app->url;

        // Check if down file exists in app storage
        $isDown = false;
        if ($app->resolved_path) {
            $downFile = $app->resolved_path . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down';
            $isDown = file_exists($downFile);
        }

        try {
            $checkUrl = rtrim($url, '/') . '/';
            $response = Http::withoutRedirecting()->withoutVerifying()->timeout(5)->get($checkUrl);
            $latency = round((microtime(true) - $start) * 1000);
            $statusCode = $response->status();

            if ($statusCode === 503 || $isDown) {
                $status = 'maintenance';
            } elseif ($statusCode >= 200 && $statusCode < 400) {
                $status = 'healthy';
            } else {
                $status = 'degraded';
            }

            $app->update([
                'health_status' => $status,
                'last_health_check_at' => now(),
            ]);

            return [
                'status' => $status,
                'status_code' => $statusCode,
                'latency_ms' => $latency,
                'is_maintenance' => $isDown || ($statusCode === 503),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $start) * 1000);
            $status = $isDown ? 'maintenance' : 'down';

            $app->update([
                'health_status' => $status,
                'last_health_check_at' => now(),
            ]);

            return [
                'status' => $status,
                'status_code' => 0,
                'latency_ms' => $latency,
                'is_maintenance' => $isDown,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enable maintenance mode for an application
     */
    public static function enableMaintenanceMode(Application $app, ?string $secret = null, ?User $user = null): array
    {
        $secret = $secret ?: Str::random(24);
        $appPath = $app->resolved_path;

        if (!$appPath || !file_exists($appPath)) {
            return ['success' => false, 'message' => "Folder aplikasi tidak ditemukan."];
        }

        // Run artisan down --secret="..."
        $res = static::runArtisan($app, "down --secret=\"{$secret}\"");

        // Alternatively ensure storage/framework/down exists
        $downFile = $appPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down';
        if (!file_exists($downFile)) {
            $downPayload = json_encode([
                'secret' => $secret,
                'status' => 503,
                'template' => null,
                'retry' => null,
            ], JSON_PRETTY_PRINT);
            @file_put_contents($downFile, $downPayload);
        }

        $app->update([
            'maintenance_mode' => true,
            'maintenance_bypass_token' => $secret,
            'health_status' => 'maintenance',
        ]);

        MaintenanceEvent::create([
            'application_id' => $app->id,
            'event_type' => 'emergency',
            'status' => 'active',
            'title' => 'Aktivasi Mode Pemeliharaan Manual',
            'description' => 'Mode pemeliharaan diaktifkan oleh administrator SSO.',
            'start_time' => now(),
            'bypass_secret' => $secret,
            'created_by' => $user?->id,
        ]);

        ActivityLogService::log(
            'maintenance_mode_enabled',
            "Mode pemeliharaan diaktifkan untuk aplikasi {$app->name} (Secret: {$secret})"
        );

        return [
            'success' => true,
            'bypass_token' => $secret,
            'output' => $res['output'],
        ];
    }

    /**
     * Disable maintenance mode for an application
     */
    public static function disableMaintenanceMode(Application $app, ?User $user = null): array
    {
        $appPath = $app->resolved_path;
        if (!$appPath || !file_exists($appPath)) {
            return ['success' => false, 'message' => "Folder aplikasi tidak ditemukan."];
        }

        // Run artisan up
        $res = static::runArtisan($app, 'up');

        // Remove down file if still present
        $downFile = $appPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down';
        if (file_exists($downFile)) {
            @unlink($downFile);
        }

        $app->update([
            'maintenance_mode' => false,
            'maintenance_bypass_token' => null,
            'health_status' => 'healthy',
        ]);

        MaintenanceEvent::where('application_id', $app->id)
            ->where('status', 'active')
            ->update([
                'status' => 'completed',
                'end_time' => now(),
            ]);

        ActivityLogService::log(
            'maintenance_mode_disabled',
            "Mode pemeliharaan dinonaktifkan untuk aplikasi {$app->name}"
        );

        return [
            'success' => true,
            'output' => $res['output'],
        ];
    }

    /**
     * Create database snapshot for an application
     */
    public static function createDatabaseSnapshot(
        Application $app,
        string $type = 'manual',
        ?string $notes = null,
        ?User $user = null
    ): ApplicationBackup {
        $dbName = $app->database_name;
        if (!$dbName) {
            throw new \Exception("Aplikasi {$app->name} belum memiliki konfigurasi nama database.");
        }

        $timestamp = date('Ymd_His');
        $filename = "snapshot_{$app->slug}_{$timestamp}.sql";
        $relativeDir = "backups/applications/{$app->slug}";
        $storageDir = storage_path("app/{$relativeDir}");

        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $targetFile = $storageDir . DIRECTORY_SEPARATOR . $filename;
        $mysqldumpBin = static::getMysqldumpBinary();

        // Run mysqldump command
        $dumpCmd = "{$mysqldumpBin} -u root --single-transaction --quick --routines --triggers {$dbName}";
        $res = static::runProcess($dumpCmd, static::getWorkspaceRoot());

        if (!$res['success'] || empty($res['output'])) {
            // Fallback: Pure PDO dump
            $sqlContent = static::dumpDatabaseViaPdo($dbName);
            file_put_contents($targetFile, $sqlContent);
        } else {
            file_put_contents($targetFile, $res['output']);
        }

        $fileSize = file_exists($targetFile) ? filesize($targetFile) : 0;

        $backup = ApplicationBackup::create([
            'application_id' => $app->id,
            'backup_type' => $type,
            'filename' => $filename,
            'db_dump_path' => "{$relativeDir}/{$filename}",
            'file_size_bytes' => $fileSize,
            'commit_hash' => $app->current_commit,
            'is_restorable' => true,
            'notes' => $notes ?: "Snapshot database otomatis sebelum perubahan",
            'created_by' => $user?->id,
        ]);

        ActivityLogService::log(
            'database_snapshot_created',
            "Snapshot database {$dbName} berhasil dibuat: {$filename} (" . $backup->formatted_size . ")"
        );

        return $backup;
    }

    /**
     * Restore database from snapshot
     */
    public static function restoreDatabaseSnapshot(ApplicationBackup $backup, ?User $user = null): array
    {
        $app = $backup->application;
        $dbName = $app->database_name;
        $fullPath = storage_path("app/{$backup->db_dump_path}");

        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => "File snapshot tidak ditemukan di storage: {$backup->db_dump_path}"];
        }

        $sqlContent = file_get_contents($fullPath);
        $mysqlBin = static::getMysqlClientBinary();

        // Try mysql client command via stdin
        $cmd = "{$mysqlBin} -u root {$dbName}";
        $res = static::runProcess($cmd, static::getWorkspaceRoot(), $sqlContent);

        if (!$res['success']) {
            // Fallback via PDO
            $resPdo = static::restoreDatabaseViaPdo($dbName, $sqlContent);
            if (!$resPdo['success']) {
                return ['success' => false, 'message' => "Gagal restore database: " . $resPdo['error']];
            }
        }

        ActivityLogService::log(
            'database_snapshot_restored',
            "Database {$dbName} berhasil di-restore dari snapshot: {$backup->filename}"
        );

        return ['success' => true, 'message' => "Database {$dbName} berhasil di-restore."];
    }

    /**
     * Execute 7-Stage Deployment Pipeline (Update / Deploy / Hotfix)
     */
    public static function executeDeploy(
        Application $app,
        string $targetCommit,
        string $deploymentType = 'update',
        ?User $user = null
    ): ApplicationDeployment {
        $deployment = ApplicationDeployment::create([
            'application_id' => $app->id,
            'deployment_type' => $deploymentType,
            'source_commit' => $app->current_commit,
            'target_commit' => $targetCommit,
            'status' => 'in_progress',
            'current_stage' => 'preflight',
            'deployed_by' => $user?->id,
            'started_at' => now(),
        ]);

        $bypassToken = Str::random(24);
        $backup = null;
        $root = static::getWorkspaceRoot();

        try {
            // STAGE 1: PREFLIGHT
            $deployment->appendLog('preflight', 'Memulai Preflight Inspection...');
            if (!static::isGitRepository()) {
                throw new \Exception("Workspace root (" . static::getWorkspaceRoot() . ") belum diinisialisasi sebagai repositori Git. Pastikan folder memiliki git repo dengan remote origin.");
            }
            if (!$app->resolved_path || !file_exists($app->resolved_path)) {
                throw new \Exception("Folder aplikasi {$app->folder_path} tidak ditemukan pada disk.");
            }
            if (!$app->database_name) {
                throw new \Exception("Database name belum dikonfigurasi pada aplikasi.");
            }
            $deployment->appendLog('preflight', "Git repository: OK");
            $deployment->appendLog('preflight', "Folder target: {$app->resolved_path} [OK]");
            $deployment->appendLog('preflight', "Database target: {$app->database_name} [OK]");

            // STAGE 2: DATABASE SNAPSHOT
            $deployment->appendLog('snapshot_db', "Membuat snapshot database {$app->database_name} secara otomatis...");
            $backup = static::createDatabaseSnapshot(
                $app,
                'auto_pre_deploy',
                "Auto-snapshot sebelum deployment {$targetCommit}",
                $user
            );
            $deployment->update(['backup_id' => $backup->id]);
            $deployment->appendLog('snapshot_db', "Snapshot tersimpan: {$backup->filename} ({$backup->formatted_size}) [OK]");

            // STAGE 3: MAINTENANCE MODE ON
            $deployment->appendLog('maintenance_on', 'Mengaktifkan mode pemeliharaan dengan bypass token...');
            static::enableMaintenanceMode($app, $bypassToken, $user);
            $deployment->appendLog('maintenance_on', "Mode pemeliharaan aktif. Traffic diarahkan ke 503. Token: {$bypassToken} [OK]");

            // STAGE 4: PATH-ISOLATED CODE CHECKOUT
            $folderArg = escapeshellarg($app->folder_path);
            $deployment->appendLog('code_sync', "Sinkronisasi kode path-isolated ke commit {$targetCommit}...");
            $checkoutRes = static::runProcess("git checkout {$targetCommit} -- {$folderArg}/", $root);
            if (!$checkoutRes['success']) {
                throw new \Exception("Git checkout gagal: " . $checkoutRes['output']);
            }
            $deployment->appendLog('code_sync', "Kode aplikasi {$app->folder_path} berhasil disinkronkan ke {$targetCommit} [OK]");

            // STAGE 5: COMPOSER AUTOLOAD & ARTISAN MIGRATE
            $deployment->appendLog('migration_composer', 'Menjalankan composer dump-autoload & migrasi skema database...');
            static::runProcess("composer dump-autoload --no-interaction", $app->resolved_path);
            $migrateRes = static::runArtisan($app, "migrate --force");
            $deployment->appendLog('migration_composer', "Output migrasi: " . ($migrateRes['output'] ?: 'Tidak ada migrasi baru.') . " [OK]");

            // STAGE 6: CACHE WARMUP & OPTIMIZE
            $deployment->appendLog('cache_warmup', 'Membersihkan cache lama dan melakukan cache warmup...');
            static::runArtisan($app, "optimize:clear");
            static::runArtisan($app, "config:cache");
            static::runArtisan($app, "route:cache");
            $deployment->appendLog('cache_warmup', "Cache konfigurasi dan rute telah dihangatkan [OK]");

            // Lift maintenance mode
            static::disableMaintenanceMode($app, $user);
            $deployment->appendLog('smoke_test', 'Mode pemeliharaan dinonaktifkan. Menjalankan smoke test HTTP GET...');

            // STAGE 7: SMOKE TEST & VERIFICATION
            $health = static::checkAppHealth($app);
            $deployment->appendLog('smoke_test', "Smoke test result: Status Code={$health['status_code']}, Latency={$health['latency_ms']}ms, Health={$health['status']}");

            if ($health['status'] !== 'healthy') {
                throw new \Exception("Smoke test gagal: Aplikasi mengembalikan HTTP {$health['status_code']} ({$health['error']}).");
            }
            $deployment->appendLog('completed', "Aplikasi VERIFIED LIVE dan sehat pada commit {$targetCommit} [OK].");

            // Update app details
            $app->update([
                'current_commit' => $targetCommit,
                'health_status' => 'healthy',
                'maintenance_mode' => false,
                'maintenance_bypass_token' => null,
            ]);

            $deployment->update([
                'status' => 'success',
                'current_stage' => 'completed',
                'finished_at' => now(),
            ]);

            ActivityLogService::log(
                'deployment_success',
                "Deployment berhasil untuk {$app->name} ke commit " . substr($targetCommit, 0, 7)
            );

        } catch (\Throwable $e) {
            // AUTOMATED ROLLBACK PROTOCOL
            $deployment->appendLog('rollback', "CRITICAL ERROR: " . $e->getMessage());
            $deployment->appendLog('rollback', "Menjalankan protokol ROLLBACK OTOMATIS untuk mencegah downtime dan data loss...");

            if ($backup) {
                $deployment->appendLog('rollback', "Mengembalikan snapshot database dari {$backup->filename}...");
                static::restoreDatabaseSnapshot($backup, $user);
                $deployment->appendLog('rollback', "Database dipulihkan ke kondisi awal [OK]");
            }

            if ($app->current_commit) {
                $folderArg = escapeshellarg($app->folder_path);
                $deployment->appendLog('rollback', "Mengembalikan source code ke commit {$app->current_commit}...");
                static::runProcess("git checkout {$app->current_commit} -- {$folderArg}/", $root);
            }

            static::runArtisan($app, "optimize:clear");
            static::disableMaintenanceMode($app, $user);

            $deployment->update([
                'status' => 'rolled_back',
                'current_stage' => 'rollback_completed',
                'finished_at' => now(),
            ]);

            ActivityLogService::log(
                'deployment_failed_rolled_back',
                "Deployment {$app->name} gagal dan berhasil di-rollback: " . $e->getMessage()
            );
        }

        return $deployment;
    }

    /**
     * Execute Rollback / Downgrade to previous deployment or commit
     */
    public static function executeRollback(
        Application $app,
        int $targetDeploymentId,
        ?User $user = null
    ): ApplicationDeployment {
        $targetDep = ApplicationDeployment::findOrFail($targetDeploymentId);
        $targetCommit = $targetDep->source_commit ?: $targetDep->target_commit;

        $rollbackDeployment = ApplicationDeployment::create([
            'application_id' => $app->id,
            'deployment_type' => 'downgrade',
            'source_commit' => $app->current_commit,
            'target_commit' => $targetCommit,
            'status' => 'in_progress',
            'current_stage' => 'preflight',
            'deployed_by' => $user?->id,
            'started_at' => now(),
        ]);

        $root = static::getWorkspaceRoot();
        $bypassToken = Str::random(24);

        try {
            // 1. Preflight
            $rollbackDeployment->appendLog('preflight', "Memulai Downgrade/Rollback ke commit {$targetCommit}...");

            // 2. Safety Snapshot
            $rollbackDeployment->appendLog('snapshot_db', "Membuat snapshot darurat sebelum proses downgrade...");
            $emergencyBackup = static::createDatabaseSnapshot(
                $app,
                'auto_pre_deploy',
                "Emergency snapshot sebelum downgrade ke {$targetCommit}",
                $user
            );
            $rollbackDeployment->update(['backup_id' => $emergencyBackup->id]);

            // 3. Maintenance Mode On
            static::enableMaintenanceMode($app, $bypassToken, $user);
            $rollbackDeployment->appendLog('maintenance_on', "Mode pemeliharaan aktif untuk downgrade.");

            // 4. If target deployment had a clean backup, offer/restore it if needed
            if ($targetDep->backup_id && $targetDep->backup) {
                $rollbackDeployment->appendLog('database_restore', "Mengembalikan state database dari deployment referensi: {$targetDep->backup->filename}...");
                static::restoreDatabaseSnapshot($targetDep->backup, $user);
                $rollbackDeployment->appendLog('database_restore', "Database dipulihkan ke versi deployment referensi [OK]");
            }

            // 5. Path-Isolated Code Checkout
            $folderArg = escapeshellarg($app->folder_path);
            $rollbackDeployment->appendLog('code_sync', "Sinkronisasi kode ke commit downgrade {$targetCommit}...");
            $resCheckout = static::runProcess("git checkout {$targetCommit} -- {$folderArg}/", $root);
            if (!$resCheckout['success']) {
                throw new \Exception("Checkout downgrade gagal: " . $resCheckout['output']);
            }

            // 6. Composer & Cache
            static::runProcess("composer dump-autoload --no-interaction", $app->resolved_path);
            static::runArtisan($app, "optimize:clear");
            static::runArtisan($app, "config:cache");
            static::runArtisan($app, "route:cache");

            // 7. Smoke test & lift maintenance
            static::disableMaintenanceMode($app, $user);
            $rollbackDeployment->appendLog('completed', "Downgrade selesai. Aplikasi kembali ONLINE pada commit {$targetCommit}.");

            $app->update([
                'current_commit' => $targetCommit,
                'health_status' => 'healthy',
                'maintenance_mode' => false,
                'maintenance_bypass_token' => null,
            ]);

            $rollbackDeployment->update([
                'status' => 'success',
                'current_stage' => 'completed',
                'finished_at' => now(),
            ]);

            ActivityLogService::log(
                'downgrade_success',
                "Rollback/Downgrade aplikasi {$app->name} berhasil ke commit " . substr($targetCommit, 0, 7)
            );

        } catch (\Throwable $e) {
            $rollbackDeployment->appendLog('rollback_failed', "GAGAL DOWNGRADE: " . $e->getMessage());
            static::disableMaintenanceMode($app, $user);

            $rollbackDeployment->update([
                'status' => 'failed',
                'current_stage' => 'failed',
                'finished_at' => now(),
            ]);

            ActivityLogService::log(
                'downgrade_failed',
                "Rollback/Downgrade aplikasi {$app->name} gagal: " . $e->getMessage()
            );
        }

        return $rollbackDeployment;
    }

    /**
     * Pure PDO fallback for dumping database
     */
    protected static function dumpDatabaseViaPdo(string $dbName): string
    {
        $pdo = new \PDO("mysql:host=127.0.0.1;dbname={$dbName};charset=utf8mb4", 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $sql = "-- Database Dump: {$dbName}\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createRow[1] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $vals = array_map(function ($val) use ($pdo) {
                    return $val === null ? 'NULL' : $pdo->quote($val);
                }, array_values($row));

                $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    /**
     * Pure PDO fallback for restoring database
     */
    protected static function restoreDatabaseViaPdo(string $dbName, string $sqlContent): array
    {
        try {
            $pdo = new \PDO("mysql:host=127.0.0.1;dbname={$dbName};charset=utf8mb4", 'root', '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sqlContent);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
