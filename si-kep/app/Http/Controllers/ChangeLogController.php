<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Models\Pegawai;
use App\Services\GoogleSheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Rollback a specific change log entry (Superadmin & Maintenance only)
     */
    public function rollback(Request $request, $id, GoogleSheetSyncService $syncService)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            abort(403, 'Akses Terbatas: Fitur Rollback hanya dapat dilakukan oleh Superadmin dan Maintenance.');
        }

        $log = ChangeLog::findOrFail($id);

        if ($log->action === 'rollback') {
            return response()->json([
                'success' => false,
                'message' => 'Log ini adalah entri rollback dan tidak dapat di-rollback kembali secara langsung.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pegawai = null;
            $actionDesc = '';
            $currentPayload = [];
            $restoredPayload = [];

            if ($log->action === 'update') {
                if (empty($log->payload_before)) {
                    throw new \Exception('Data awal (payload_before) tidak ditemukan pada log ini.');
                }

                $pegawai = Pegawai::find($log->pegawai_id) ?? Pegawai::where('nip', $log->nip)->first();
                if (!$pegawai) {
                    throw new \Exception("Pegawai {$log->nama_pegawai} tidak ditemukan di database.");
                }

                $currentPayload = $pegawai->toArray();
                $restoredPayload = $log->payload_before;

                // Protect immutable fields
                unset($restoredPayload['id'], $restoredPayload['created_at'], $restoredPayload['updated_at']);

                // Fill and save
                $pegawai->fill($restoredPayload);
                $pegawai->save();

                $actionDesc = "Rollback pembaruan data pegawai {$pegawai->nama} (Log #{$log->id}) ke kondisi sebelum pembaruan.";
            } elseif ($log->action === 'create') {
                // If the log was creation of a new pegawai, rollback means deleting the pegawai
                $pegawai = Pegawai::find($log->pegawai_id) ?? Pegawai::where('nip', $log->nip)->first();
                if ($pegawai) {
                    $currentPayload = $pegawai->toArray();
                    $pegawai->delete();
                    $actionDesc = "Rollback penambahan personil: Menghapus data {$log->nama_pegawai} (Log #{$log->id}).";
                } else {
                    $actionDesc = "Rollback penambahan personil: Pegawai {$log->nama_pegawai} sudah tidak ada di database.";
                }
            } elseif ($log->action === 'delete') {
                // If the log was deletion, rollback means recreating from payload_before
                if (empty($log->payload_before)) {
                    throw new \Exception('Data sebelum penghapusan tidak ditemukan pada log ini.');
                }
                $restoredPayload = $log->payload_before;
                unset($restoredPayload['id']);
                $pegawai = Pegawai::create($restoredPayload);
                $actionDesc = "Rollback penghapusan: Memulihkan personil {$pegawai->nama} (Log #{$log->id}).";
            } else {
                throw new \Exception("Aksi {$log->action} tidak didukung untuk rollback otomatis.");
            }

            // Record new audit log for this rollback action
            $rollbackLog = ChangeLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'pegawai_id' => $log->action === 'create' ? null : ($pegawai?->id ?? $log->pegawai_id),
                'nama_pegawai' => $log->nama_pegawai,
                'nip' => $log->nip,
                'action' => 'rollback',
                'description' => $actionDesc,
                'changes' => [
                    'original_log_id' => $log->id,
                    'original_action' => $log->action,
                    'reverted_fields' => array_keys($log->changes ?? []),
                ],
                'payload_before' => $currentPayload,
                'payload_after' => $pegawai ? $pegawai->toArray() : [],
                'sync_status' => 'pending',
            ]);

            DB::commit();

            // Try Dual-Write to Google Sheet if pegawai still exists
            $syncResult = ['success' => false, 'message' => 'Tersimpan lokal'];
            if ($pegawai) {
                $syncResult = $syncService->pushRowUpdate($rollbackLog);
            }

            $msg = "Perubahan pada log #{$log->id} berhasil dibatalkan (Rollback)! " . ($syncResult['success'] ? 'Tersinkron ke Google Sheet.' : 'Tersimpan di database lokal.');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'log' => $rollbackLog,
                    'sync' => $syncResult,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan rollback: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal melakukan rollback: ' . $e->getMessage());
        }
    }
}
