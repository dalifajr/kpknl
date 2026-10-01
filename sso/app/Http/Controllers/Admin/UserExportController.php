<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\UserExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserExportController extends Controller
{
    protected UserExportService $exportService;

    public function __construct(UserExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export registered users list with role and assigned applications details.
     */
    public function export(Request $request)
    {
        $currentUser = auth()->user();

        // Fitur eksklusif untuk role Superadmin dan Maintenance
        if (!$currentUser || (!$currentUser->isSuperadmin() && !$currentUser->isMaintenance())) {
            abort(403, 'Hanya Superadmin dan Tim Maintenance yang berwenang mengekspor data user.');
        }

        $filters = $request->only(['search', 'role', 'status']);
        $users = $this->exportService->getFilteredUsersQuery($filters)->get();
        $formattedData = $this->exportService->formatUsersData($users);

        $format = strtolower($request->query('format', 'xlsx'));
        $timestamp = date('Ymd_His');

        $actor = $currentUser->isSuperadmin() ? 'Superadmin' : 'Maintenance';
        $userCount = count($formattedData);

        if ($format === 'csv') {
            $filename = "daftar_user_sso_{$timestamp}.csv";
            $csvContent = $this->exportService->generateCsv($formattedData);

            ActivityLogService::log(
                'users_exported_csv',
                "{$actor} ({$currentUser->username}) mengekspor {$userCount} data user SSO dalam format CSV"
            );

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]);
        }

        // Default: Excel (.xlsx)
        $filename = "daftar_user_sso_{$timestamp}.xlsx";
        $xlsxContent = $this->exportService->generateXlsx($formattedData);

        ActivityLogService::log(
            'users_exported_xlsx',
            "{$actor} ({$currentUser->username}) mengekspor {$userCount} data user SSO dalam format Excel (.xlsx)"
        );

        return response($xlsxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
