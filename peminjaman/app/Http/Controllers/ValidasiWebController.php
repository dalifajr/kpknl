<?php

namespace App\Http\Controllers;

use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Models\RisalahTap;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ValidasiWebController extends Controller
{
    /**
     * Display pending risalah queue for Admin Seksi HI.
     */
    public function index(): View
    {
        $pendingList = RisalahPending::where('status', 'belum_validasi')
            ->orderByDesc('id')
            ->paginate(15);

        return view('validasi.index', compact('pendingList'));
    }

    /**
     * Approve and assign physical Lemari and Box.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'lemari' => 'required|string|max:100',
            'box' => 'required|string|max:100',
        ]);

        $pending = RisalahPending::findOrFail($id);

        DB::transaction(function () use ($pending, $request) {
            $data = [
                'no_risalah' => $pending->no_risalah,
                'tgl_risalah' => $pending->tgl_risalah,
                'tgl_validasi' => Carbon::now()->toDateString(),
                'nama_pelelang' => $pending->nama_pelelang,
                'pemohon_lelang' => $pending->pemohon_lelang,
                'link_erisalah' => $pending->link_erisalah,
                'box' => $request->input('box'),
                'lemari' => $request->input('lemari'),
                'status' => 'tersedia',
            ];

            if ($pending->jenis === 'minuta') {
                RisalahMinuta::create($data);
            } elseif ($pending->jenis === 'tap') {
                RisalahTap::create($data);
            } elseif ($pending->jenis === 'batal') {
                RisalahBatal::create($data);
            }

            $pending->update(['status' => 'validasi']);
        });

        return redirect()->route('validasi.index')
            ->with('success', 'Risalah ' . $pending->no_risalah . ' berhasil divalidasi dan ditempatkan pada ' . $request->input('lemari') . ' / ' . $request->input('box') . '.');
    }

    /**
     * Reject and send revision notes back to Pejabat Lelang.
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        $pending = RisalahPending::findOrFail($id);

        DB::transaction(function () use ($pending, $request) {
            RisalahRevisi::create([
                'no_risalah' => $pending->no_risalah,
                'jenis' => $pending->jenis,
                'tgl_risalah' => $pending->tgl_risalah,
                'tgl_revisi' => Carbon::now()->toDateString(),
                'nama_pelelang' => $pending->nama_pelelang,
                'pemohon_lelang' => $pending->pemohon_lelang,
                'catatan' => $request->input('catatan'),
                'status' => 'revisi',
            ]);

            $pending->delete();
        });

        return redirect()->route('validasi.index')
            ->with('success', 'Risalah dikembalikan ke Pejabat Lelang dengan catatan revisi.');
    }

    /**
     * Display new auction minute registration form (Pelelang & Admin).
     */
    public function pendaftaran(): View
    {
        return view('pendaftaran.index');
    }

    /**
     * Store new auction minute into pending queue.
     */
    public function storePendaftaran(Request $request): RedirectResponse
    {
        $request->validate([
            'no_risalah' => 'required|string|max:100',
            'jenis' => 'required|in:minuta,tap,batal',
            'tgl_risalah' => 'required|date',
            'nama_pelelang' => 'required|string|max:255',
            'pemohon_lelang' => 'required|string|max:255',
        ]);

        RisalahPending::create([
            'no_risalah' => $request->input('no_risalah'),
            'jenis' => $request->input('jenis'),
            'tgl_risalah' => $request->input('tgl_risalah'),
            'nama_pelelang' => $request->input('nama_pelelang'),
            'pemohon_lelang' => $request->input('pemohon_lelang'),
            'status' => 'belum_validasi',
        ]);

        return redirect()->route('katalog.index')
            ->with('success', 'Pendaftaran risalah berhasil diajukan ke Seksi HI untuk divalidasi!');
    }

    /**
     * Display revision list (Pelelang & Admin).
     */
    public function revisi(): View
    {
        $revisiList = RisalahRevisi::where('status', 'revisi')
            ->orderByDesc('id')
            ->paginate(15);

        return view('revisi.index', compact('revisiList'));
    }

    /**
     * Resubmit corrected risalah back to pending queue.
     */
    public function resubmitRevisi(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'no_risalah' => 'required|string|max:100',
            'tgl_risalah' => 'required|date',
            'pemohon_lelang' => 'required|string|max:255',
        ]);

        $revisi = RisalahRevisi::findOrFail($id);

        DB::transaction(function () use ($revisi, $request) {
            RisalahPending::create([
                'no_risalah' => $request->input('no_risalah'),
                'jenis' => $revisi->jenis,
                'tgl_risalah' => $request->input('tgl_risalah'),
                'nama_pelelang' => $revisi->nama_pelelang,
                'pemohon_lelang' => $request->input('pemohon_lelang'),
                'status' => 'belum_validasi',
            ]);

            $revisi->update(['status' => 'dikirim']);
        });

        return redirect()->route('revisi.index')
            ->with('success', 'Risalah revisi berhasil dikirim ulang ke Seksi HI untuk divalidasi!');
    }
}
