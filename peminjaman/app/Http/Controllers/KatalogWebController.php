<?php

namespace App\Http\Controllers;

use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KatalogWebController extends Controller
{
    /**
     * Display the catalog of physical auction minutes.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'all');
        $status = $request->input('status', 'all');
        $search = trim($request->input('search', ''));
        $perPage = 15;
        $page = (int) $request->input('page', 1);

        $tables = [];
        if ($type === 'all' || $type === 'minuta') {
            $tables[] = ['table' => 'risalah_minuta', 'jenis' => 'minuta', 'jenis_label' => 'Minuta (Laku)'];
        }
        if ($type === 'all' || $type === 'tap') {
            $tables[] = ['table' => 'risalah_tap', 'jenis' => 'tap', 'jenis_label' => 'TAP'];
        }
        if ($type === 'all' || $type === 'batal') {
            $tables[] = ['table' => 'risalah_batal', 'jenis' => 'batal', 'jenis_label' => 'Batal'];
        }

        $unionQuery = null;
        foreach ($tables as $t) {
            $q = DB::table($t['table'])->select([
                'id',
                'no_risalah',
                'tgl_risalah',
                'nama_pelelang',
                'pemohon_lelang',
                'lemari',
                'box',
                DB::raw("COALESCE(status, 'tersedia') as status"),
                DB::raw("'{$t['jenis']}' as jenis"),
                DB::raw("'{$t['jenis_label']}' as jenis_label"),
            ]);

            if ($status === 'tersedia') {
                $q->where(function ($sub) {
                    $sub->where('status', 'tersedia')
                        ->orWhereNull('status');
                });
            } elseif ($status === 'dipinjam') {
                $q->where('status', 'sedang_dipinjam');
            }

            if (!empty($search)) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('no_risalah', 'LIKE', "%{$search}%")
                        ->orWhere('nama_pelelang', 'LIKE', "%{$search}%")
                        ->orWhere('pemohon_lelang', 'LIKE', "%{$search}%");
                });
            }

            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        // Count total results
        $total = $unionQuery ? DB::query()->fromSub($unionQuery, 'katalog_union')->count() : 0;

        // Fetch paginated results
        $items = collect();
        if ($unionQuery) {
            $items = DB::query()
                ->fromSub($unionQuery, 'katalog_union')
                ->orderBy('tgl_risalah', 'desc')
                ->orderBy('id', 'desc')
                ->forPage($page, $perPage)
                ->get();
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('katalog.index', [
            'risalahList' => $paginator,
            'type' => $type,
            'status' => $status,
            'search' => $search,
            'totalItems' => $total,
        ]);
    }
}
