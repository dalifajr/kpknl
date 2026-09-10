<?php

namespace App\Http\Controllers;

use App\Http\Requests\RevisionRequest;
use App\Http\Requests\ValidateRisalahRequest;
use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Services\RisalahValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ValidasiRisalahController extends Controller
{
    protected RisalahValidationService $validationService;

    public function __construct(RisalahValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Admin view of all pending risalah.
     */
    public function index(Request $request): View
    {
        $query = RisalahPending::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_pelelang', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $pendingList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.validasi.index', compact('pendingList'));
    }

    /**
     * Admin form to validate / approve single pending risalah.
     */
    public function show(int $id): View
    {
        $pending = RisalahPending::findOrFail($id);

        return view('admin.validasi.show', compact('pending'));
    }

    /**
     * Admin approves pending risalah and moves to minuta/tap/batal.
     */
    public function approve(ValidateRisalahRequest $request, int $id): RedirectResponse
    {
        $pending = RisalahPending::findOrFail($id);

        $this->validationService->approve($pending, $request->validated());

        return redirect()->route('admin.validasi.index')
            ->with('success', "Risalah No. {$pending->no_risalah} berhasil divalidasi dan dipindahkan ke tabel " . strtoupper($pending->jenis) . ".");
    }

    /**
     * Admin requests revision and moves to risalah_revisi.
     */
    public function requestRevision(RevisionRequest $request, int $id): RedirectResponse
    {
        $pending = RisalahPending::findOrFail($id);

        $this->validationService->requestRevision($pending, $request->validated());

        return redirect()->route('admin.validasi.index')
            ->with('warning', "Risalah No. {$pending->no_risalah} telah dikembalikan ke pelelang untuk perbaikan/revisi.");
    }

    /**
     * Pelelang views their revisions.
     */
    public function pelelangRevisi(Request $request): View
    {
        $username = Auth::user()->username;
        $query = RisalahRevisi::where('nama_pelelang', $username);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        $revisiList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('pelelang.revisi.index', compact('revisiList'));
    }

    /**
     * Pelelang edit revision form.
     */
    public function editRevisi(int $id): View
    {
        $username = Auth::user()->username;
        $revisi = RisalahRevisi::where('id', $id)
            ->where('nama_pelelang', $username)
            ->firstOrFail();

        return view('pelelang.revisi.edit', compact('revisi'));
    }

    /**
     * Pelelang resubmits revision back to pending.
     */
    public function resubmitRevision(Request $request, int $id): RedirectResponse
    {
        $username = Auth::user()->username;
        $revisi = RisalahRevisi::where('id', $id)
            ->where('nama_pelelang', $username)
            ->firstOrFail();

        $request->validate([
            'no_risalah' => ['required', 'string', 'max:50'],
            'jenis' => ['required', 'string', 'in:minuta,tap,batal'],
            'tgl_risalah' => ['required', 'date'],
            'pemohon_lelang' => ['required', 'string', 'max:100'],
        ]);

        $this->validationService->resubmitRevision($revisi, $request->all());

        return redirect()->route('pelelang.revisi.index')
            ->with('success', "Risalah No. {$revisi->no_risalah} berhasil diperbaiki dan diajukan ulang ke antrian validasi Admin.");
    }
}
