<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRisalahRequest;
use App\Http\Requests\UpdateRisalahRequest;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahPending;
use App\Models\RisalahTap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RisalahController extends Controller
{
    /**
     * Display listing of Risalah Minuta with filters.
     */
    public function minuta(Request $request): View
    {
        $query = RisalahMinuta::query();
        $this->applyFilters($query, $request);

        $data = $query->orderBy('tgl_risalah', 'desc')->paginate(15)->withQueryString();
        $type = 'minuta';
        $title = 'Data Risalah Minuta';

        return view('risalah.index', compact('data', 'type', 'title'));
    }

    /**
     * Display listing of Risalah TAP with filters.
     */
    public function tap(Request $request): View
    {
        $query = RisalahTap::query();
        $this->applyFilters($query, $request);

        $data = $query->orderBy('tgl_risalah', 'desc')->paginate(15)->withQueryString();
        $type = 'tap';
        $title = 'Data Risalah TAP (Tidak Ada Penawaran)';

        return view('risalah.index', compact('data', 'type', 'title'));
    }

    /**
     * Display listing of Risalah Batal with filters.
     */
    public function batal(Request $request): View
    {
        $query = RisalahBatal::query();
        $this->applyFilters($query, $request);

        $data = $query->orderBy('tgl_risalah', 'desc')->paginate(15)->withQueryString();
        $type = 'batal';
        $title = 'Data Risalah Batal';

        return view('risalah.index', compact('data', 'type', 'title'));
    }

    /**
     * Show form for creating a new risalah.
     */
    public function create(): View
    {
        return view('risalah.create');
    }

    /**
     * Store newly created risalah to risalah_pending.
     */
    public function store(StoreRisalahRequest $request): RedirectResponse
    {
        // Enforce authenticated username as nama_pelelang for integrity
        $pelelangName = Auth::user()->username;

        RisalahPending::create([
            'no_risalah' => $request->no_risalah,
            'jenis' => $request->jenis,
            'tgl_risalah' => $request->tgl_risalah,
            'tgl_validasi' => null,
            'nama_pelelang' => $pelelangName,
            'pemohon_lelang' => $request->pemohon_lelang,
            'link_erisalah' => $request->link_erisalah,
            'box' => $request->box,
            'lemari' => $request->lemari,
            'keterangan' => $request->keterangan,
            'catatan' => $request->catatan,
            'status' => 'belum_validasi',
        ]);

        $redirectRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'pelelang.pending';

        return redirect()->route($redirectRoute)
            ->with('success', 'Risalah berhasil diajukan dan sedang menunggu validasi oleh Admin.');
    }

    /**
     * Show form for editing an existing validated risalah.
     */
    public function edit(string $type, int $id): View
    {
        $model = $this->getModelByTypeAndId($type, $id);

        return view('risalah.edit', compact('model', 'type'));
    }

    /**
     * Update existing validated risalah.
     */
    public function update(UpdateRisalahRequest $request, string $type, int $id): RedirectResponse
    {
        $model = $this->getModelByTypeAndId($type, $id);

        $model->update([
            'no_risalah' => $request->no_risalah,
            'tgl_risalah' => $request->tgl_risalah,
            'tgl_validasi' => $request->tgl_validasi,
            'nama_pelelang' => $request->nama_pelelang,
            'pemohon_lelang' => $request->pemohon_lelang,
            'link_erisalah' => $request->link_erisalah,
            'box' => $request->box,
            'lemari' => $request->lemari,
            'status' => $request->status,
        ]);

        $routeName = match ($type) {
            'minuta' => 'risalah.minuta',
            'tap' => 'risalah.tap',
            'batal' => 'risalah.batal',
            default => 'admin.dashboard',
        };

        return redirect()->route($routeName)
            ->with('success', "Data Risalah {$model->no_risalah} berhasil diperbarui.");
    }

    /**
     * Display pending risalah for pelelang.
     */
    public function pending(Request $request): View
    {
        $username = Auth::user()->username;
        $query = RisalahPending::where('nama_pelelang', $username);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        $pendingList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('pelelang.pending', compact('pendingList'));
    }

    /**
     * Display history of all risalah created by the authenticated pelelang.
     */
    public function history(Request $request): View
    {
        $username = Auth::user()->username;

        $minuta = RisalahMinuta::where('nama_pelelang', $username)->get()->map(fn($item) => $item->setAttribute('jenis', 'Minuta'));
        $tap = RisalahTap::where('nama_pelelang', $username)->get()->map(fn($item) => $item->setAttribute('jenis', 'TAP'));
        $batal = RisalahBatal::where('nama_pelelang', $username)->get()->map(fn($item) => $item->setAttribute('jenis', 'Batal'));

        $combined = $minuta->concat($tap)->concat($batal)->sortByDesc('tgl_risalah');

        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $combined = $combined->filter(function ($item) use ($s) {
                return str_contains(strtolower($item->no_risalah ?? ''), $s) ||
                    str_contains(strtolower($item->pemohon_lelang ?? ''), $s);
            });
        }

        return view('pelelang.history', ['historyList' => $combined]);
    }

    /**
     * Helper to apply common search and date filters to risalah queries.
     */
    protected function applyFilters($query, Request $request): void
    {
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tgl_risalah', $request->tahun);
        }

        if ($request->filled('bulan_awal') && $request->filled('bulan_akhir')) {
            $query->whereMonth('tgl_risalah', '>=', $request->bulan_awal)
                ->whereMonth('tgl_risalah', '<=', $request->bulan_akhir);
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tgl_risalah', $request->bulan);
        }
    }

    /**
     * Helper to find model by type and ID.
     */
    protected function getModelByTypeAndId(string $type, int $id)
    {
        return match (strtolower($type)) {
            'minuta' => RisalahMinuta::findOrFail($id),
            'tap' => RisalahTap::findOrFail($id),
            'batal' => RisalahBatal::findOrFail($id),
            default => abort(404, 'Tipe risalah tidak valid.'),
        };
    }
}
