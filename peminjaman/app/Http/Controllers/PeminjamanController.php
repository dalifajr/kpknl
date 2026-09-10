<?php

namespace App\Http\Controllers;

use App\Http\Requests\BorrowRisalahRequest;
use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    protected PeminjamanService $peminjamanService;

    public function __construct(PeminjamanService $peminjamanService)
    {
        $this->peminjamanService = $peminjamanService;
    }

    /**
     * Catalog of available risalah for borrowers to select.
     */
    public function katalog(Request $request): View
    {
        $type = $request->query('type', 'minuta');

        $query = match ($type) {
            'tap' => RisalahTap::where('status', 'tersedia'),
            'batal' => RisalahBatal::where('status', 'tersedia'),
            default => RisalahMinuta::where('status', 'tersedia'),
        };

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_pelelang', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('box')) {
            $query->where('box', $request->box);
        }

        if ($request->filled('lemari')) {
            $query->where('lemari', $request->lemari);
        }

        $items = $query->orderBy('tgl_risalah', 'desc')->paginate(15)->withQueryString();

        return view('peminjaman.katalog', compact('items', 'type'));
    }

    /**
     * Store loan request from borrower.
     */
    public function store(BorrowRisalahRequest $request): RedirectResponse
    {
        $user = Auth::user();

        try {
            $createdLoans = $this->peminjamanService->borrow(
                $user,
                $request->selected_items,
                $request->alasan_peminjaman
            );

            return redirect()->route('peminjam.pinjaman')
                ->with('success', count($createdLoans) . ' berkas risalah berhasil diajukan untuk dipinjam.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses peminjaman: ' . $e->getMessage());
        }
    }

    /**
     * Borrower's loan history and active list.
     */
    public function peminjamIndex(Request $request): View
    {
        $username = Auth::user()->username;
        $query = Peminjaman::where('nama_peminjam', $username);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjamanList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('peminjam.pinjaman.index', compact('pinjamanList'));
    }

    /**
     * Admin view of all loan requests.
     */
    public function adminIndex(Request $request): View
    {
        $query = Peminjaman::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_peminjam', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjamanList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.peminjaman.index', compact('pinjamanList'));
    }

    /**
     * Admin approves loan request.
     */
    public function adminApprove(int $id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $this->peminjamanService->adminApproveLoan($peminjaman);

        return redirect()->back()
            ->with('success', "Peminjaman Risalah No. {$peminjaman->no_risalah} berhasil divalidasi. Berkas kini berstatus Sedang Dipinjam.");
    }

    /**
     * Borrower confirms receiving the physical document.
     */
    public function konfirmasiTerima(int $id): RedirectResponse
    {
        $username = Auth::user()->username;
        $peminjaman = Peminjaman::where('id', $id)
            ->where('nama_peminjam', $username)
            ->firstOrFail();

        $this->peminjamanService->borrowerConfirmLoan($peminjaman);

        return redirect()->back()
            ->with('success', "Konfirmasi berhasil! Risalah No. {$peminjaman->no_risalah} kini tercatat Sedang Dipinjam.");
    }

    /**
     * Borrower requests return of the borrowed document.
     */
    public function ajukanKembali(int $id): RedirectResponse
    {
        $username = Auth::user()->username;
        $peminjaman = Peminjaman::where('id', $id)
            ->where('nama_peminjam', $username)
            ->firstOrFail();

        $this->peminjamanService->requestReturn($peminjaman);

        return redirect()->back()
            ->with('info', "Pengajuan pengembalian Risalah No. {$peminjaman->no_risalah} terkirim. Silakan serahkan fisik risalah ke Admin.");
    }

    /**
     * Admin view of documents that have been returned (Sudah Dikembalikan).
     */
    public function adminPengembalianIndex(Request $request): View
    {
        $query = Peminjaman::where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_peminjam', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        $pengembalianList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.peminjaman.pengembalian', compact('pengembalianList'));
    }

    /**
     * Admin validates and completes the return process.
     */
    public function adminApproveReturn(int $id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        $this->peminjamanService->adminApproveReturn($peminjaman);

        return redirect()->back()
            ->with('success', "Pengembalian Risalah No. {$peminjaman->no_risalah} berhasil divalidasi. Status risalah telah dipulihkan menjadi Tersedia.");
    }
}
