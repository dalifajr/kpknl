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
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard with system stats.
     */
    public function admin(): View
    {
        $pendingCount = RisalahPending::count();
        $minutaCount = RisalahMinuta::count();
        $tapCount = RisalahTap::count();
        $batalCount = RisalahBatal::count();
        $loanActiveCount = Peminjaman::whereIn('status', [
            Peminjaman::STATUS_PROSES,
            Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
            Peminjaman::STATUS_SEDANG_DIPINJAM,
            Peminjaman::STATUS_PROSES_PENGEMBALIAN,
        ])->count();
        $revisiCount = RisalahRevisi::count();

        $recentPending = RisalahPending::orderBy('id', 'desc')->take(5)->get();
        $recentLoans = Peminjaman::orderBy('id', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'minutaCount',
            'tapCount',
            'batalCount',
            'loanActiveCount',
            'revisiCount',
            'recentPending',
            'recentLoans'
        ));
    }

    /**
     * Pelelang Dashboard with auctioneer stats.
     */
    public function pelelang(): View
    {
        $username = Auth::user()->username;

        $myPendingCount = RisalahPending::where('nama_pelelang', $username)->count();
        $myRevisiCount = RisalahRevisi::where('nama_pelelang', $username)->count();
        $myMinutaCount = RisalahMinuta::where('nama_pelelang', $username)->count();
        $myTapCount = RisalahTap::where('nama_pelelang', $username)->count();
        $myBatalCount = RisalahBatal::where('nama_pelelang', $username)->count();

        $recentRevisi = RisalahRevisi::where('nama_pelelang', $username)->orderBy('id', 'desc')->take(5)->get();
        $recentPending = RisalahPending::where('nama_pelelang', $username)->orderBy('id', 'desc')->take(5)->get();

        return view('pelelang.dashboard', compact(
            'myPendingCount',
            'myRevisiCount',
            'myMinutaCount',
            'myTapCount',
            'myBatalCount',
            'recentRevisi',
            'recentPending'
        ));
    }

    /**
     * Peminjam Dashboard with borrower stats.
     */
    public function peminjam(): View
    {
        $username = Auth::user()->username;

        $activeLoans = Peminjaman::where('nama_peminjam', $username)
            ->whereIn('status', [
                Peminjaman::STATUS_PROSES,
                Peminjaman::STATUS_MENUNGGU_KONFIRMASI,
                Peminjaman::STATUS_SEDANG_DIPINJAM,
                Peminjaman::STATUS_PROSES_PENGEMBALIAN,
            ])
            ->get();

        $waitingConfirmation = $activeLoans->where('status', Peminjaman::STATUS_MENUNGGU_KONFIRMASI);
        $totalBorrowed = Peminjaman::where('nama_peminjam', $username)->count();

        return view('peminjam.dashboard', compact(
            'activeLoans',
            'waitingConfirmation',
            'totalBorrowed'
        ));
    }
}
