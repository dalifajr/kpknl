<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Models\RisalahTap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardWebController extends Controller
{
    /**
     * Display the main monitoring dashboard.
     */
    public function index(): View
    {
        $minutaCount = RisalahMinuta::count();
        $tapCount = RisalahTap::count();
        $batalCount = RisalahBatal::count();
        $totalAll = $minutaCount + $tapCount + $batalCount;

        // Ketersediaan Fisik
        $minutaDipinjam = RisalahMinuta::where('status', 'sedang_dipinjam')->count();
        $tapDipinjam = RisalahTap::where('status', 'sedang_dipinjam')->count();
        $batalDipinjam = RisalahBatal::where('status', 'sedang_dipinjam')->count();
        $totalDipinjam = $minutaDipinjam + $tapDipinjam + $batalDipinjam;
        $totalTersedia = max(0, $totalAll - $totalDipinjam);

        // Antrean Validasi & Revisi
        $pendingCount = RisalahPending::where('status', 'belum_validasi')->count();
        $revisiCount = RisalahRevisi::where('status', 'revisi')->count();

        // Peminjaman Status Counts
        $loanActiveCount = Peminjaman::whereIn('status', [
            Peminjaman::STATUS_PROSES,
            Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
            Peminjaman::STATUS_SEDANG_DIPINJAM,
            Peminjaman::STATUS_PROSES_PENGEMBALIAN,
        ])->count();
        $loanCompletedCount = Peminjaman::where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN)->count();
        $loanTotalCount = Peminjaman::count();

        // Top Pejabat Lelang Teraktif
        $topPelelang = RisalahMinuta::select('nama_pelelang', DB::raw('count(*) as total'))
            ->whereNotNull('nama_pelelang')
            ->where('nama_pelelang', '!=', '')
            ->groupBy('nama_pelelang')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Top Peminjam Berkas
        $topPeminjam = Peminjaman::select('nama_peminjam', DB::raw('count(*) as total'))
            ->whereNotNull('nama_peminjam')
            ->groupBy('nama_peminjam')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Transaksi Peminjaman Terkini
        $recentLoans = Peminjaman::orderByDesc('id')
            ->take(6)
            ->get();

        // Chart Data (JSON Ready)
        $chartData = [
            'komposisi' => [
                'labels' => ['Minuta (Laku)', 'TAP (Tidak Ada Penawaran)', 'Batal Lelang'],
                'values' => [$minutaCount, $tapCount, $batalCount],
                'colors' => ['#0c306b', '#f59e0b', '#ef4444'],
            ],
            'ketersediaan' => [
                'labels' => ['Tersedia di Lemari', 'Sedang Dipinjam'],
                'values' => [$totalTersedia, $totalDipinjam],
                'colors' => ['#10b981', '#f59e0b'],
            ],
        ];

        return view('dashboard.index', compact(
            'minutaCount',
            'tapCount',
            'batalCount',
            'totalAll',
            'totalTersedia',
            'totalDipinjam',
            'pendingCount',
            'revisiCount',
            'loanActiveCount',
            'loanCompletedCount',
            'loanTotalCount',
            'topPelelang',
            'topPeminjam',
            'recentLoans',
            'chartData'
        ));
    }
}
