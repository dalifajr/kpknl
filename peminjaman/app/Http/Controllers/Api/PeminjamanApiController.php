<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Models\RisalahTap;
use App\Models\User;
use App\Services\PeminjamanService;
use App\Services\RisalahValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PeminjamanApiController extends Controller
{
    protected PeminjamanService $peminjamanService;
    protected RisalahValidationService $validationService;

    public function __construct(PeminjamanService $peminjamanService, RisalahValidationService $validationService)
    {
        $this->peminjamanService = $peminjamanService;
        $this->validationService = $validationService;
    }

    /**
     * Get Current Authenticated User & Role
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->getRoleLabel(),
                'role_badge_class' => $user->getRoleBadgeClass(),
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Executive Dashboard Statistics & Analytical Charts
     */
    public function dashboardStats(): JsonResponse
    {
        $user = Auth::user();

        // 1. Basic counts across categories
        $minutaCount = RisalahMinuta::count();
        $tapCount = RisalahTap::count();
        $batalCount = RisalahBatal::count();
        $totalAll = $minutaCount + $tapCount + $batalCount;

        $minutaTersedia = RisalahMinuta::where('status', 'tersedia')->count();
        $tapTersedia = RisalahTap::where('status', 'tersedia')->count();
        $batalTersedia = RisalahBatal::where('status', 'tersedia')->count();
        $totalTersedia = $minutaTersedia + $tapTersedia + $batalTersedia;
        $totalDipinjam = $totalAll - $totalTersedia;

        $pendingCount = RisalahPending::count();
        $revisiCount = RisalahRevisi::count();

        // 2. Loans overview
        $pinjamanQuery = Peminjaman::query();
        $totalPeminjamanRecords = $pinjamanQuery->count();
        $peminjamanAktif = (clone $pinjamanQuery)->whereIn('status', [
            Peminjaman::STATUS_PROSES,
            Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
            Peminjaman::STATUS_SEDANG_DIPINJAM,
            Peminjaman::STATUS_PROSES_PENGEMBALIAN,
        ])->count();
        $peminjamanSelesai = (clone $pinjamanQuery)->where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN)->count();

        // User personal stats if pelelang or peminjam
        $userStats = [];
        if ($user) {
            if ($user->isPelelang()) {
                $userStats['my_pending'] = RisalahPending::where('nama_pelelang', 'like', "%{$user->name}%")
                    ->orWhere('nama_pelelang', 'like', "%{$user->username}%")->count();
                $userStats['my_revisi'] = RisalahRevisi::where('nama_pelelang', 'like', "%{$user->name}%")
                    ->orWhere('nama_pelelang', 'like', "%{$user->username}%")->count();
                $userStats['my_completed'] = RisalahMinuta::where('nama_pelelang', 'like', "%{$user->name}%")
                    ->orWhere('nama_pelelang', 'like', "%{$user->username}%")->count();
            } elseif ($user->isPeminjam()) {
                $userStats['my_active_loans'] = Peminjaman::where('nama_peminjam', $user->username)
                    ->whereIn('status', [
                        Peminjaman::STATUS_PROSES,
                        Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
                        Peminjaman::STATUS_SEDANG_DIPINJAM,
                        Peminjaman::STATUS_PROSES_PENGEMBALIAN,
                    ])->count();
                $userStats['my_completed_loans'] = Peminjaman::where('nama_peminjam', $user->username)
                    ->where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN)->count();
            }
        }

        // 3. Top 5 Pejabat Lelang
        $topPelelangMinuta = DB::table('risalah_minuta')
            ->select('nama_pelelang', DB::raw('count(*) as total'))
            ->whereNotNull('nama_pelelang')
            ->where('nama_pelelang', '!=', '')
            ->groupBy('nama_pelelang')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 4. Top 5 Peminjam
        $topPeminjam = DB::table('peminjaman')
            ->select('nama_peminjam', DB::raw('count(*) as total'))
            ->whereNotNull('nama_peminjam')
            ->where('nama_peminjam', '!=', '')
            ->groupBy('nama_peminjam')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 5. Recent Activity / Overdue Loans
        $activeLoans = Peminjaman::whereIn('status', [
            Peminjaman::STATUS_PROSES,
            Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
            Peminjaman::STATUS_SEDANG_DIPINJAM,
            Peminjaman::STATUS_PROSES_PENGEMBALIAN,
        ])->orderBy('id', 'desc')->limit(6)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'counts' => [
                    'minuta' => $minutaCount,
                    'tap' => $tapCount,
                    'batal' => $batalCount,
                    'total_all' => $totalAll,
                    'tersedia' => $totalTersedia,
                    'sedang_dipinjam' => $totalDipinjam,
                    'pending' => $pendingCount,
                    'revisi' => $revisiCount,
                    'peminjaman_aktif' => $peminjamanAktif,
                    'peminjaman_selesai' => $peminjamanSelesai,
                    'peminjaman_total' => $totalPeminjamanRecords,
                ],
                'user_stats' => $userStats,
                'charts' => [
                    'komposisi' => [
                        'labels' => ['Minuta (Lelang Laku)', 'TAP (Tidak Ada Penawaran)', 'Batal'],
                        'values' => [$minutaCount, $tapCount, $batalCount],
                        'colors' => ['#0c306b', '#f59e0b', '#ef4444'],
                    ],
                    'ketersediaan' => [
                        'labels' => ['Tersedia di Lemari/Box', 'Sedang Dipinjam'],
                        'values' => [$totalTersedia, $totalDipinjam],
                        'colors' => ['#10b981', '#f59e0b'],
                    ],
                ],
                'top_pelelang' => $topPelelangMinuta,
                'top_peminjam' => $topPeminjam,
                'active_loans' => $activeLoans,
            ],
        ]);
    }

    /**
     * Unified Catalog of Risalah (Minuta, TAP, Batal)
     */
    public function getRisalah(Request $request): JsonResponse
    {
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $page = max(1, (int)$request->query('page', 1));
        $perPage = max(1, min(100, (int)$request->query('per_page', 15)));

        $buildQuery = function ($table, $jenisLabel) use ($status, $search) {
            $q = DB::table($table)->select(
                'id',
                'no_risalah',
                'tgl_risalah',
                'nama_pelelang',
                'pemohon_lelang',
                'box',
                'lemari',
                'status',
                'link_erisalah',
                DB::raw("'{$jenisLabel}' as jenis")
            );

            if ($status !== 'all') {
                $q->where('status', $status);
            }

            if (!empty($search)) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('no_risalah', 'like', "%{$search}%")
                        ->orWhere('nama_pelelang', 'like', "%{$search}%")
                        ->orWhere('pemohon_lelang', 'like', "%{$search}%")
                        ->orWhere('box', 'like', "%{$search}%")
                        ->orWhere('lemari', 'like', "%{$search}%");
                });
            }

            return $q;
        };

        if ($type === 'minuta') {
            $query = $buildQuery('risalah_minuta', 'minuta');
        } elseif ($type === 'tap') {
            $query = $buildQuery('risalah_tap', 'tap');
        } elseif ($type === 'batal') {
            $query = $buildQuery('risalah_batal', 'batal');
        } else {
            $qMinuta = $buildQuery('risalah_minuta', 'minuta');
            $qTap = $buildQuery('risalah_tap', 'tap');
            $qBatal = $buildQuery('risalah_batal', 'batal');
            $query = $qMinuta->unionAll($qTap)->unionAll($qBatal);
        }

        // Count total items
        $total = $query->count();

        // Fetch paginated slice
        $items = $query->orderBy('tgl_risalah', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get Pending Risalah list
     */
    public function getPendingRisalah(): JsonResponse
    {
        $items = RisalahPending::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Get Revisi Risalah list
     */
    public function getRevisiRisalah(): JsonResponse
    {
        $items = RisalahRevisi::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Submit New Risalah (Pelelang)
     */
    public function storeRisalah(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isPelelang()) {
            return response()->json(['error' => 'Hanya Pejabat Lelang dan Administrator yang berwenang mendaftarkan risalah.'], 403);
        }

        $validated = $request->validate([
            'no_risalah' => 'required|string|max:50',
            'jenis' => 'required|in:minuta,tap,batal',
            'tgl_risalah' => 'required|date',
            'nama_pelelang' => 'required|string|max:100',
            'pemohon_lelang' => 'required|string|max:100',
            'link_erisalah' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $created = RisalahPending::create([
            'no_risalah' => $validated['no_risalah'],
            'jenis' => $validated['jenis'],
            'tgl_risalah' => $validated['tgl_risalah'],
            'nama_pelelang' => $validated['nama_pelelang'],
            'pemohon_lelang' => $validated['pemohon_lelang'],
            'link_erisalah' => $validated['link_erisalah'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'belum_validasi',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Risalah No. {$created->no_risalah} berhasil diajukan dan masuk ke antrian verifikasi Admin.",
            'data' => $created,
        ]);
    }

    /**
     * Approve Pending Risalah & Assign Storage (Admin)
     */
    public function approveRisalah(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            return response()->json(['error' => 'Hanya Administrator Arsip yang dapat memvalidasi risalah.'], 403);
        }

        $validated = $request->validate([
            'lemari' => 'required|string|max:50',
            'box' => 'required|string|max:50',
            'tgl_validasi' => 'nullable|date',
            'link_erisalah' => 'nullable|string',
        ]);

        $pending = RisalahPending::findOrFail($id);

        try {
            $created = $this->validationService->approve($pending, [
                'lemari' => $validated['lemari'],
                'box' => $validated['box'],
                'tgl_validasi' => $validated['tgl_validasi'] ?? now()->toDateString(),
                'link_erisalah' => $validated['link_erisalah'] ?? $pending->link_erisalah,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Risalah No. {$created->no_risalah} berhasil divalidasi dan ditempatkan pada Lemari {$validated['lemari']} Box {$validated['box']}.",
                'data' => $created,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal memvalidasi risalah: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Request Revision for Pending Risalah (Admin)
     */
    public function rejectRisalah(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            return response()->json(['error' => 'Hanya Administrator Arsip yang dapat meminta revisi risalah.'], 403);
        }

        $validated = $request->validate([
            'catatan' => 'required|string',
            'catatan_no' => 'nullable|string',
            'catatan_jenis' => 'nullable|string',
            'catatan_tgl' => 'nullable|string',
            'catatan_pelelang' => 'nullable|string',
            'catatan_pemohon' => 'nullable|string',
        ]);

        $pending = RisalahPending::findOrFail($id);

        try {
            $revisi = $this->validationService->requestRevision($pending, $validated);

            return response()->json([
                'status' => 'success',
                'message' => "Risalah No. {$revisi->no_risalah} dikembalikan ke Pejabat Lelang untuk revisi.",
                'data' => $revisi,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal mengembalikan risalah: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Resubmit Revised Risalah (Pelelang)
     */
    public function resubmitRevisi(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'no_risalah' => 'required|string|max:50',
            'jenis' => 'required|in:minuta,tap,batal',
            'tgl_risalah' => 'required|date',
            'pemohon_lelang' => 'required|string|max:100',
            'link_erisalah' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $revisi = RisalahRevisi::findOrFail($id);

        try {
            $pending = $this->validationService->resubmitRevision($revisi, $validated);

            return response()->json([
                'status' => 'success',
                'message' => "Risalah No. {$pending->no_risalah} telah diperbaiki dan dikirimkan ulang ke antrian validasi Admin.",
                'data' => $pending,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal mengirim ulang revisi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get List of Loan Records
     */
    public function getPeminjaman(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Peminjaman::query();

        // If peminjam role, limit to their own loans unless searching/admin
        if ($user && $user->isPeminjam()) {
            $query->where(function ($q) use ($user) {
                $q->where('nama_peminjam', $user->username)
                    ->orWhere('nama_peminjam', 'like', "%{$user->name}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_peminjam', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        $page = max(1, (int)$request->query('page', 1));
        $perPage = max(1, min(100, (int)$request->query('per_page', 15)));

        $total = $query->count();
        $items = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Submit Loan Request (Multi-Item Cart Supported)
     */
    public function storePeminjaman(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Sesi login tidak valid.'], 401);
        }

        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'string',
            'alasan_peminjaman' => 'required|string|max:500',
        ]);

        try {
            $createdLoans = $this->peminjamanService->borrow(
                $user,
                $request->selected_items,
                $request->alasan_peminjaman
            );

            return response()->json([
                'status' => 'success',
                'message' => count($createdLoans) . ' berkas risalah berhasil diajukan untuk dipinjam.',
                'data' => $createdLoans,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Admin Approves Loan Request
     */
    public function approvePeminjaman(int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            return response()->json(['error' => 'Hanya Administrator Arsip yang dapat menyetujui peminjaman.'], 403);
        }

        $loan = Peminjaman::findOrFail($id);
        $loan->update([
            'status' => Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Permohonan Peminjaman Risalah No. {$loan->no_risalah} telah disetujui. Berkas siap diambil oleh peminjam.",
            'data' => $loan,
        ]);
    }

    /**
     * Borrower Confirms Physical Receipt
     */
    public function confirmReceivePeminjaman(int $id): JsonResponse
    {
        $loan = Peminjaman::findOrFail($id);
        $this->peminjamanService->borrowerConfirmLoan($loan);

        return response()->json([
            'status' => 'success',
            'message' => "Penerimaan fisik Risalah No. {$loan->no_risalah} telah dikonfirmasi. Status berkas kini resmi Sedang Dipinjam.",
            'data' => $loan,
        ]);
    }

    /**
     * Borrower Requests Return
     */
    public function requestReturnPeminjaman(int $id): JsonResponse
    {
        $loan = Peminjaman::findOrFail($id);
        $this->peminjamanService->requestReturn($loan);

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan pengembalian Risalah No. {$loan->no_risalah} berhasil dicatat. Silakan serahkan berkas fisik ke Administrator Arsip.",
            'data' => $loan,
        ]);
    }

    /**
     * Admin Verifies Physical Return & Restores Availability
     */
    public function verifyReturnPeminjaman(int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            return response()->json(['error' => 'Hanya Administrator Arsip yang dapat memverifikasi pengembalian fisik.'], 403);
        }

        $loan = Peminjaman::findOrFail($id);
        $this->peminjamanService->adminApproveReturn($loan);

        return response()->json([
            'status' => 'success',
            'message' => "Pengembalian fisik Risalah No. {$loan->no_risalah} berhasil diverifikasi. Berkas kembali berstatus 'Tersedia' di lemari/box.",
            'data' => $loan,
        ]);
    }
}
