<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public static function createBackup(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_" . $dbName;

        $sql = "-- SSO KPKNL Palembang Database Backup\n";
        $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$keyName ?? array_values((array)$tableObj)[0];

            // Get Create Table query
            $createTable = DB::select("SHOW CREATE TABLE `$table`")[0];
            $createSql = $createTable->{'Create Table'} ?? array_values((array)$createTable)[1];

            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createSql . ";\n\n";

            // Get Table Data
            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $rowArr = (array) $row;
                $cols = array_keys($rowArr);
                $vals = array_map(function ($val) {
                    if ($val === null) {
                        return 'NULL';
                    }
                    return "'" . addslashes($val) . "'";
                }, array_values($rowArr));

                $sql .= "INSERT INTO `$table` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_sso_kpknl_' . date('Y-m-d_H-i-s') . '.sql';
        Storage::disk('local')->put('backups/' . $filename, $sql);

        ActivityLogService::log(
            'backup_created',
            "Membuat backup database manual: {$filename}"
        );

        return $filename;
    }

    public static function restoreBackup(string $sqlContent): bool
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Split queries safely
        $queries = array_filter(
            array_map('trim', explode(";\n", $sqlContent)),
            fn($q) => !empty($q) && !str_starts_with($q, '--')
        );

        foreach ($queries as $query) {
            if (!empty($query)) {
                DB::unprepared($query);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        ActivityLogService::log(
            'backup_restored',
            "Melakukan restore database dari file SQL."
        );

        return true;
    }

    public static function getBackupList(): array
    {
        $files = Storage::disk('local')->files('backups');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => Storage::disk('local')->size($file),
                'date' => date('Y-m-d H:i:s', Storage::disk('local')->lastModified($file)),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $backups;
    }
}
