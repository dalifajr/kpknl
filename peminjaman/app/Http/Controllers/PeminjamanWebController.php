<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\User;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class PeminjamanWebController extends Controller
{
    protected PeminjamanService $peminjamanService;

    public function __construct(PeminjamanService $peminjamanService)
    {
        $this->peminjamanService = $peminjamanService;
    }

    /**
     * Display the loan workflow table.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');
        $search = trim($request->input('search', ''));

        $query = Peminjaman::orderByDesc('id');

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', "%{$search}%")
                  ->orWhere('alasan_peminjaman', 'LIKE', "%{$search}%")
                  ->orWhere('no_risalah', 'LIKE', "%{$search}%");
            });
        }

        $peminjamanList = $query->paginate(15)->appends($request->query());

        // Status counts for tabs
        $counts = [
            'all' => Peminjaman::count(),
            'Proses Peminjaman' => Peminjaman::where('status', Peminjaman::STATUS_PROSES)->count(),
            'Menunggu Konfirmasi' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU_KONFIRMASI)->count(),
            'Sedang Dipinjam' => Peminjaman::where('status', Peminjaman::STATUS_SEDANG_DIPINJAM)->count(),
            'Proses Pengembalian' => Peminjaman::where('status', Peminjaman::STATUS_PROSES_PENGEMBALIAN)->count(),
            'Sudah Dikembalikan' => Peminjaman::where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN)->count(),
        ];

        return view('peminjaman.index', compact('peminjamanList', 'status', 'search', 'counts'));
    }

    /**
     * Store a new loan application (Multi-Item Cart Supported).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('katalog.index')->with('error', 'Sesi login tidak valid.');
        }

        // Support both selected_items array (e.g. ['minuta:123', 'tap:45']) and items array ([['id' => 123, 'jenis' => 'minuta']])
        $selectedItems = $request->input('selected_items', []);
        if (empty($selectedItems) && $request->has('items')) {
            $rawItems = $request->input('items');
            if (is_array($rawItems)) {
                foreach ($rawItems as $it) {
                    if (isset($it['jenis']) && isset($it['id'])) {
                        $selectedItems[] = $it['jenis'] . ':' . $it['id'];
                    }
                }
            }
        }

        $request->merge(['selected_items' => $selectedItems]);

        $request->validate([
            'selected_items' => 'required|array|min:1|max:5',
            'selected_items.*' => 'string',
            'alasan_peminjaman' => 'required|string|max:500',
        ], [
            'selected_items.required' => 'Pilih minimal satu berkas risalah untuk dipinjam.',
            'alasan_peminjaman.required' => 'Alasan atau keperluan peminjaman kedinasan wajib diisi.',
        ]);

        try {
            $createdLoans = $this->peminjamanService->borrow(
                $user,
                $request->selected_items,
                $request->alasan_peminjaman
            );

            return redirect()->route('peminjaman.index')
                ->with('success', count($createdLoans) . ' berkas risalah berhasil diajukan untuk dipinjam! Menunggu persetujuan Admin Seksi HI.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    /**
     * Stage 1 -> 2: Admin approves loan request (Menunggu Konfirmasi Ambil Fisik).
     */
    public function approve($id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $peminjaman->update([
            'status' => Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
        ]);

        return redirect()->back()
            ->with('success', 'Permohonan peminjaman Risalah No. ' . $peminjaman->no_risalah . ' telah disetujui! Berkas fisik siap diambil.');
    }

    /**
     * Stage 2 -> 3: Borrower/Admin confirms physical receipt (Sedang Dipinjam).
     */
    public function confirmReceive($id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $this->peminjamanService->borrowerConfirmLoan($peminjaman);

        return redirect()->back()
            ->with('success', 'Pengambilan fisik Risalah No. ' . $peminjaman->no_risalah . ' telah dikonfirmasi! Status kini Sedang Dipinjam.');
    }

    /**
     * Stage 3 -> 4: Borrower requests return (Proses Pengembalian).
     */
    public function returnRequest($id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $this->peminjamanService->requestReturn($peminjaman);

        return redirect()->back()
            ->with('success', 'Pengembalian fisik Risalah No. ' . $peminjaman->no_risalah . ' telah diajukan! Silakan serahkan berkas ke Seksi HI.');
    }

    /**
     * Stage 4 -> 5: Admin verifies physical file return and restores availability status to 'tersedia'.
     */
    public function verifyReturn($id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $this->peminjamanService->adminApproveReturn($peminjaman);

        return redirect()->back()
            ->with('success', 'Verifikasi pengembalian fisik Risalah No. ' . $peminjaman->no_risalah . ' selesai! Berkas kembali berstatus Tersedia.');
    }
}
