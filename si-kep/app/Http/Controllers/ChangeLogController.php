<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Services\GoogleSheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeLogController extends Controller
{
    /**
     * Display Audit & Sync Change Logs (Superadmin & Maintenance only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            abort(403, 'Akses Terbatas: Menu Log Perubahan hanya dapat diakses oleh Superadmin dan Maintenance.');
        }

        $query = ChangeLog::with(['user', 'pegawai'])->latest();

        // Filter Status Sync
        if ($request->filled('status') && in_array($request->status, ['pending', 'synced', 'failed'])) {
            $query->where('sync_status', $request->status);
        }

        // Search Keyword
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_pegawai', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('user_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(10)->withQueryString();

        // Statistics
        $totalLogs = ChangeLog::count();
        $pendingLogs = ChangeLog::where('sync_status', 'pending')->count();
        $syncedLogs = ChangeLog::where('sync_status', 'synced')->count();
        $failedLogs = ChangeLog::where('sync_status', 'failed')->count();

        return view('change_log.index', compact(
            'logs',
            'totalLogs',
            'pendingLogs',
            'syncedLogs',
            'failedLogs'
        ));
    }

    /**
     * Manually Push Pending Change Logs to Google Spreadsheet
     */
    public function syncPending(GoogleSheetSyncService $syncService)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            abort(403, 'Akses Terbatas.');
        }

        $result = $syncService->pushPendingChanges();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Proses sinkronisasi antrian selesai! {$result['synced']} berhasil, {$result['failed']} gagal dari {$result['total']} antrian.",
                'data' => $result,
            ]);
        }

        return redirect()->back()->with('success', "Proses sinkronisasi antrian selesai! {$result['synced']} berhasil, {$result['failed']} gagal dari total {$result['total']} antrian.");
    }
}
