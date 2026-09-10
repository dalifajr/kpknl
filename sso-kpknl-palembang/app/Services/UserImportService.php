<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserImportService
{
    /**
     * Parse uploaded file (XLSX, XML, CSV, TXT, Excel CSV) into array of rows
     */
    public function parseFile(string $filePath): array
    {
        if (empty($filePath) || !file_exists($filePath)) {
            return [];
        }

        $contentHeader = @file_get_contents($filePath, false, null, 0, 500);

        // Detect XLSX format via magic bytes (PK\x03\x04) or extension
        if ($contentHeader && (str_starts_with($contentHeader, "PK\x03\x04") || str_ends_with(strtolower($filePath), '.xlsx'))) {
            $sharedStrings = [];
            $sheetXmlContent = null;

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) === true) {
                    // Extract shared strings if present
                    $sstContent = $zip->getFromName('xl/sharedStrings.xml');
                    if ($sstContent) {
                        $sharedStrings = $this->parseSharedStringsXml($sstContent);
                    }

                    // Extract sheet1.xml (or first worksheet)
                    $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
                    if (!$sheetXmlContent) {
                        // Fallback to searching first worksheet entry
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $name = $zip->getNameIndex($i);
                            if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                                $sheetXmlContent = $zip->getFromName($name);
                                break;
                            }
                        }
                    }
                    $zip->close();
                }
            }

            // Fallback for pure PHP zip string extraction
            if (!$sheetXmlContent) {
                $fullContent = @file_get_contents($filePath);
                if ($fullContent) {
                    // Extract shared strings snippet
                    $sstPos = strpos($fullContent, '<sst');
                    if ($sstPos !== false) {
                        $sstEndPos = strpos($fullContent, '</sst>', $sstPos);
                        if ($sstEndPos !== false) {
                            $sstSnippet = substr($fullContent, $sstPos, ($sstEndPos + 6) - $sstPos);
                            $sharedStrings = $this->parseSharedStringsXml('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $sstSnippet);
                        }
                    }

                    // Extract worksheet snippet
                    $pos = strpos($fullContent, '<worksheet');
                    if ($pos !== false) {
                        $endPos = strpos($fullContent, '</worksheet>', $pos);
                        if ($endPos !== false) {
                            $xmlSnippet = substr($fullContent, $pos, ($endPos + 12) - $pos);
                            $sheetXmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $xmlSnippet;
                        }
                    }
                }
            }

            if ($sheetXmlContent) {
                return $this->parseXlsxXml($sheetXmlContent, $sharedStrings);
            }
        }

        if ($contentHeader && (str_contains($contentHeader, 'urn:schemas-microsoft-com:office:spreadsheet') || str_contains($contentHeader, 'ProgId="Excel.Sheet"'))) {
            return $this->parseXmlSpreadsheet($filePath);
        }



        $rows = [];
        $handle = @fopen($filePath, 'r');

        if (!$handle) {
            return [];
        }

        // Auto-detect delimiter (, or ; or \t)
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if (str_contains($firstLine, ';')) {
            $delimiter = ';';
        } elseif (str_contains($firstLine, "\t")) {
            $delimiter = "\t";
        }

        $header = null;
        $rowNum = 1;

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            // Remove UTF-8 BOM if present on first column
            if ($rowNum === 1 && isset($data[0])) {
                $data[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $data[0]);
            }

            // Skip empty lines
            if (empty(array_filter($data))) {
                $rowNum++;
                continue;
            }

            if (!$header) {
                // Normalize header names
                $header = array_map(function ($col) {
                    $c = strtolower(trim($col));
                    if (in_array($c, ['nama', 'nama lengkap', 'name', 'full_name'])) return 'name';
                    if (in_array($c, ['username', 'user_name', 'user'])) return 'username';
                    if (in_array($c, ['email', 'e-mail'])) return 'email';
                    if (in_array($c, ['role', 'peran', 'jabatan'])) return 'role';
                    if (in_array($c, ['status', 'state'])) return 'status';
                    if (in_array($c, ['password', 'pass', 'sandi'])) return 'password';
                    return $c;
                }, $data);
            } else {
                $row = [];
                foreach ($header as $index => $colName) {
                    $row[$colName] = isset($data[$index]) ? trim($data[$index]) : '';
                }
                $row['row_num'] = $rowNum;
                $rows[] = $row;
            }
            $rowNum++;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse Excel XML SpreadsheetML format
     */
    protected function parseXmlSpreadsheet(string $filePath): array
    {
        libxml_use_internal_errors(true);
        $content = file_get_contents($filePath);
        if (!$content) {
            libxml_clear_errors();
            return [];
        }

        $xml = @simplexml_load_string($content);
        if (!$xml) {
            libxml_clear_errors();
            return [];
        }

        $rows = [];
        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $xmlRows = $xml->xpath('//ss:Worksheet/ss:Table/ss:Row');
        if (!is_array($xmlRows)) {
            libxml_clear_errors();
            return [];
        }

        $header = null;
        $rowNum = 1;

        foreach ($xmlRows as $rowNode) {
            $rowNode->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
            $cells = $rowNode->xpath('ss:Cell');
            if (!is_array($cells)) continue;

            $cellValues = [];
            foreach ($cells as $cell) {
                $cell->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
                $dataNodes = $cell->xpath('ss:Data');
                $val = '';
                if (is_array($dataNodes) && count($dataNodes) > 0) {
                    $val = (string) $dataNodes[0];
                } else {
                    $val = (string) $cell;
                }
                $cellValues[] = trim($val);
            }

            if (empty(array_filter($cellValues))) {
                $rowNum++;
                continue;
            }

            if (!$header) {
                $header = array_map(function ($col) {
                    $c = strtolower(trim($col));
                    if (in_array($c, ['nama', 'nama lengkap', 'name', 'full_name'])) return 'name';
                    if (in_array($c, ['username', 'user_name', 'user'])) return 'username';
                    if (in_array($c, ['email', 'e-mail'])) return 'email';
                    if (in_array($c, ['role', 'peran', 'jabatan'])) return 'role';
                    if (in_array($c, ['status', 'state'])) return 'status';
                    if (in_array($c, ['password', 'pass', 'sandi'])) return 'password';
                    return $c;
                }, $cellValues);
            } else {
                $row = [];
                foreach ($header as $index => $colName) {
                    $row[$colName] = isset($cellValues[$index]) ? trim($cellValues[$index]) : '';
                }
                $row['row_num'] = $rowNum;
                $rows[] = $row;
            }
            $rowNum++;
        }

        libxml_clear_errors();
        return $rows;
    }

    /**
     * Parse Shared Strings XML (xl/sharedStrings.xml)
     */
    protected function parseSharedStringsXml(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $strings = [];
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml || !isset($xml->si)) {
            libxml_clear_errors();
            return [];
        }

        foreach ($xml->si as $siNode) {
            $val = '';
            if (isset($siNode->t)) {
                $val = (string) $siNode->t;
            } elseif (isset($siNode->r)) {
                foreach ($siNode->r as $rNode) {
                    if (isset($rNode->t)) {
                        $val .= (string) $rNode->t;
                    }
                }
            }
            $strings[] = trim($val);
        }

        libxml_clear_errors();
        return $strings;
    }

    /**
     * Parse XLSX OpenXML Worksheet XML
     */
    protected function parseXlsxXml(string $xmlContent, array $sharedStrings = []): array
    {
        libxml_use_internal_errors(true);
        $rows = [];
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml || !isset($xml->sheetData->row)) {
            libxml_clear_errors();
            return [];
        }

        $header = null;
        $rowNum = 1;

        foreach ($xml->sheetData->row as $rowNode) {
            $cellValues = [];
            if (isset($rowNode->c)) {
                foreach ($rowNode->c as $cNode) {
                    $val = '';
                    $type = (string) ($cNode['t'] ?? '');

                    if ($type === 's' && isset($cNode->v)) {
                        $idx = (int) $cNode->v;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif (isset($cNode->is->t)) {
                        $val = (string) $cNode->is->t;
                    } elseif (isset($cNode->v)) {
                        $val = (string) $cNode->v;
                    }
                    $cellValues[] = trim($val);
                }
            }

            if (empty(array_filter($cellValues))) {
                $rowNum++;
                continue;
            }

            if (!$header) {
                $header = array_map(function ($col) {
                    $c = strtolower(trim($col));
                    if (in_array($c, ['nama', 'nama lengkap', 'name', 'full_name'])) return 'name';
                    if (in_array($c, ['username', 'user_name', 'user'])) return 'username';
                    if (in_array($c, ['email', 'e-mail'])) return 'email';
                    if (in_array($c, ['role', 'peran', 'jabatan'])) return 'role';
                    if (in_array($c, ['status', 'state'])) return 'status';
                    if (in_array($c, ['password', 'pass', 'sandi'])) return 'password';
                    return $c;
                }, $cellValues);
            } else {
                $row = [];
                foreach ($header as $index => $colName) {
                    $row[$colName] = isset($cellValues[$index]) ? trim($cellValues[$index]) : '';
                }
                $row['row_num'] = $rowNum;
                $rows[] = $row;
            }
            $rowNum++;
        }

        libxml_clear_errors();
        return $rows;
    }




    /**
     * Validate rows and check for duplicate usernames
     */
    public function validateRows(array $rows): array
    {
        $validRows = [];
        $errors = [];
        $duplicates = [];
        $seenUsernamesInFile = [];

        foreach ($rows as $row) {
            $rowNum = $row['row_num'] ?? 0;
            $rowErrors = [];

            // Mandatory Name
            if (empty($row['name'])) {
                $rowErrors[] = "Baris #{$rowNum}: Nama wajib diisi.";
            }

            // Mandatory Username
            if (empty($row['username'])) {
                $rowErrors[] = "Baris #{$rowNum}: Username wajib diisi.";
            } else {
                $username = strtolower(trim($row['username']));
                
                // Duplicate check inside file
                if (in_array($username, $seenUsernamesInFile)) {
                    $duplicates[] = [
                        'row_num' => $rowNum,
                        'username' => $username,
                        'reason' => "Duplikat di dalam file import (Baris #{$rowNum})"
                    ];
                } else {
                    $seenUsernamesInFile[] = $username;
                }

                // Duplicate check against DB (only active users)
                $usernameExists = User::where('username', $username)->exists();
                $emailExists = !empty($row['email']) && User::where('email', trim($row['email']))->exists();

                if ($usernameExists || $emailExists) {
                    $reasons = [];
                    if ($usernameExists) $reasons[] = "Username '{$username}' sudah terdaftar";
                    if ($emailExists) $reasons[] = "Email '" . trim($row['email']) . "' sudah terdaftar";

                    $duplicates[] = [
                        'row_num' => $rowNum,
                        'username' => $username,
                        'reason' => implode(' & ', $reasons)
                    ];
                }
            }

            // Mandatory Email
            if (empty($row['email'])) {
                $rowErrors[] = "Baris #{$rowNum}: Email wajib diisi.";
            } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Baris #{$rowNum}: Format email '{$row['email']}' tidak valid.";
            }

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
            } else {
                $validRows[] = $row;
            }
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
            'duplicates' => $duplicates,
        ];
    }

    /**
     * Initialize import job in Cache
     */
    public function createJob(array $rows, array $duplicates): array
    {
        $jobId = 'imp_' . Str::random(12);

        $jobData = [
            'job_id' => $jobId,
            'status' => !empty($duplicates) ? 'paused_duplicate' : 'processing', // processing, paused_duplicate, completed, cancelled
            'duplicate_resolution' => null, // null, 'suffix', 'skip'
            'total_rows' => count($rows),
            'processed_rows' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'rows' => $rows,
            'duplicates' => $duplicates,
            'logs' => [
                [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => "Job import #{$jobId} dibuat dengan " . count($rows) . " baris data."
                ]
            ],
            'created_at' => now()->toDateTimeString(),
            'start_time' => microtime(true),
        ];

        if (!empty($duplicates)) {
            $jobData['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'warning',
                'message' => "PERHATIAN: Terdeteksi " . count($duplicates) . " username duplikat. Menunggu konfirmasi Superadmin..."
            ];
        }

        Cache::put("import_job_{$jobId}", $jobData, now()->addHours(6));

        return $jobData;
    }

    /**
     * Get current job state from Cache
     */
    public function getJob(string $jobId): ?array
    {
        return Cache::get("import_job_{$jobId}");
    }

    /**
     * Update job state in Cache
     */
    public function updateJob(string $jobId, array $jobData): void
    {
        Cache::put("import_job_{$jobId}", $jobData, now()->addHours(6));
    }

    /**
     * Process next batch of rows
     */
    public function processBatch(string $jobId, int $batchSize = 5): array
    {
        $job = $this->getJob($jobId);

        if (!$job) {
            return ['error' => 'Job import tidak ditemukan.'];
        }

        if (in_array($job['status'], ['completed', 'cancelled', 'paused_duplicate'])) {
            return $this->formatJobResponse($job);
        }

        $rolesMap = Role::all()->pluck('id', 'name')->toArray();
        $userRoleId = $rolesMap['user'] ?? 3;

        $startIndex = $job['processed_rows'];
        $rowsToProcess = array_slice($job['rows'], $startIndex, $batchSize);

        foreach ($rowsToProcess as $row) {
            if ($job['status'] === 'cancelled') {
                break;
            }

            $rowNum = $row['row_num'] ?? ($job['processed_rows'] + 1);
            $username = strtolower(trim($row['username']));
            $name = trim($row['name']);
            $email = trim($row['email']);
            $roleName = strtolower(trim($row['role'] ?? 'user'));
            $status = strtolower(trim($row['status'] ?? 'active'));
            $password = !empty($row['password']) ? $row['password'] : 'Password123!';

            if (!in_array($status, ['active', 'inactive'])) {
                $status = 'active';
            }

            // Check DB duplicate for active username or email
            $userExists = User::where('username', $username)->exists();
            $emailExists = User::where('email', $email)->exists();

            if ($userExists || $emailExists) {
                if ($job['duplicate_resolution'] === 'skip') {
                    $job['skipped_count']++;
                    $job['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'warning',
                        'message' => "Baris #{$rowNum}: User '{$name}' dilewati (duplikat username/email)."
                    ];
                    $job['processed_rows']++;
                    continue;
                } elseif ($job['duplicate_resolution'] === 'suffix') {
                    // Auto-suffix username if duplicate: username_1, username_2...
                    if ($userExists) {
                        $counter = 1;
                        $newUsername = "{$username}_{$counter}";
                        while (User::withTrashed()->where('username', $newUsername)->exists()) {
                            $counter++;
                            $newUsername = "{$username}_{$counter}";
                        }
                        $username = $newUsername;
                        $job['logs'][] = [
                            'time' => now()->format('H:i:s'),
                            'type' => 'info',
                            'message' => "Baris #{$rowNum}: Username diubah menjadi '{$username}' (resolusi duplikat)."
                        ];
                    }

                    // Auto-suffix email if duplicate: email_1@domain, email_2@domain...
                    if ($emailExists) {
                        $parts = explode('@', $email, 2);
                        $counter = 1;
                        $domain = $parts[1] ?? 'kpknl.go.id';
                        $newEmail = "{$parts[0]}_{$counter}@{$domain}";
                        while (User::withTrashed()->where('email', $newEmail)->exists()) {
                            $counter++;
                            $newEmail = "{$parts[0]}_{$counter}@{$domain}";
                        }
                        $email = $newEmail;
                        $job['logs'][] = [
                            'time' => now()->format('H:i:s'),
                            'type' => 'info',
                            'message' => "Baris #{$rowNum}: Email diubah menjadi '{$email}' (resolusi duplikat)."
                        ];
                    }
                } else {
                    // Pause job for duplicate confirmation
                    $job['status'] = 'paused_duplicate';
                    $job['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'warning',
                        'message' => "Baris #{$rowNum}: Terhenti karena username/email terindikasi duplikat."
                    ];
                    $this->updateJob($jobId, $job);
                    return $this->formatJobResponse($job);
                }
            }

            try {
                // Check if a soft-deleted user exists with same username or email; if so, restore and update
                $trashedUser = User::onlyTrashed()
                    ->where(function ($q) use ($username, $email) {
                        $q->where('username', $username)
                          ->orWhere('email', $email);
                    })
                    ->first();

                if ($trashedUser) {
                    $trashedUser->restore();
                    $trashedUser->update([
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'status' => $status,
                    ]);
                    $user = $trashedUser;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'status' => $status,
                    ]);
                }

                // Attach role
                $targetRoleId = $rolesMap[$roleName] ?? $userRoleId;
                $user->roles()->sync([$targetRoleId]);

                $job['success_count']++;
                $job['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'success',
                    'message' => "Baris #{$rowNum}: Berhasil mengimpor user '{$name}' (@{$username})."
                ];

                ActivityLogService::log(
                    'user_imported',
                    "Superadmin mengimpor user '{$name}' (@{$username}) via Excel Import"
                );
            } catch (\Exception $e) {
                $job['failed_count']++;
                $job['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'error',
                    'message' => "Baris #{$rowNum}: Gagal mengimpor '{$name}' - " . $e->getMessage()
                ];
            }

            $job['processed_rows']++;
        }

        // Check if finished
        if ($job['processed_rows'] >= $job['total_rows']) {
            $job['status'] = 'completed';
            $job['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'success',
                'message' => "IMPORT SELESAI: {$job['success_count']} berhasil, {$job['skipped_count']} dilewati, {$job['failed_count']} gagal dari total {$job['total_rows']} baris."
            ];
        }

        $this->updateJob($jobId, $job);

        return $this->formatJobResponse($job);
    }

    /**
     * Format job response with calculated ETA and percentage
     */
    public function formatJobResponse(array $job): array
    {
        $total = max(1, $job['total_rows']);
        $processed = $job['processed_rows'];
        $percentage = min(100, round(($processed / $total) * 100));

        $elapsed = microtime(true) - ($job['start_time'] ?? microtime(true));
        $avgTimePerRow = $processed > 0 ? $elapsed / $processed : 0;
        $remainingRows = max(0, $total - $processed);
        $etaSeconds = round($avgTimePerRow * $remainingRows);

        $etaFormatted = '0 detik';
        if ($etaSeconds > 60) {
            $etaFormatted = floor($etaSeconds / 60) . 'm ' . ($etaSeconds % 60) . 's';
        } else {
            $etaFormatted = "{$etaSeconds} detik";
        }

        if ($job['status'] === 'completed') {
            $etaFormatted = 'Selesai';
        } elseif ($job['status'] === 'cancelled') {
            $etaFormatted = 'Dibatalkan';
        }

        return [
            'job_id' => $job['job_id'],
            'status' => $job['status'],
            'percentage' => $percentage,
            'processed_rows' => $processed,
            'total_rows' => $total,
            'success_count' => $job['success_count'],
            'skipped_count' => $job['skipped_count'],
            'failed_count' => $job['failed_count'],
            'eta' => $etaFormatted,
            'logs' => array_slice($job['logs'], -30), // Return last 30 log items
            'duplicates' => $job['duplicates'] ?? [],
            'duplicate_resolution' => $job['duplicate_resolution'] ?? null,
        ];
    }
}
