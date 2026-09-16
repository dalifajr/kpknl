<?php

namespace App\Http\Controllers;

use App\Models\PangkatGolongan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])
            ->where('is_active', true);

        // Search Keyword
        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nama_lengkap_gelar', 'like', "%{$keyword}%")
                  ->orWhere('nip', 'like', "%{$keyword}%")
                  ->orWhere('nik', 'like', "%{$keyword}%")
                  ->orWhere('nama_jabatan_raw', 'like', "%{$keyword}%")
                  ->orWhere('nama_universitas', 'like', "%{$keyword}%");
            });
        }

        // Filter Unit Kerja
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Filter Tipe Pegawai (PNS / PPNPN)
        if ($request->filled('tipe_pegawai')) {
            $query->where('tipe_pegawai', $request->tipe_pegawai);
        }

        // Filter Pangkat Golongan
        if ($request->filled('pangkat_id')) {
            $query->where('pangkat_golongan_id', $request->pangkat_id);
        }

        // Filter Gender
        if ($request->filled('gender')) {
            $query->where('jenis_kelamin', $request->gender);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'no_urut');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['no_urut', 'nama', 'nip', 'job_grade', 'usia_tahun', 'lama_palembang_tahun'])) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('no_urut', 'asc');
        }

        $perPage = $request->input('per_page', 15);
        $pegawais = $query->paginate($perPage)->withQueryString();

        $unitKerjas = UnitKerja::orderBy('urutan')->get();
        $pangkats = PangkatGolongan::where('golongan_ruang', '!=', '-')->orderByDesc('hirarki_level')->get();

        $totalCount = Pegawai::where('is_active', true)->count();
        $pnsCount = Pegawai::where('is_active', true)->where('tipe_pegawai', 'pns')->count();
        $ppnpnCount = Pegawai::where('is_active', true)->where('tipe_pegawai', 'ppnpn')->count();

        return view('pegawai.index', compact(
            'pegawais',
            'unitKerjas',
            'pangkats',
            'totalCount',
            'pnsCount',
            'ppnpnCount'
        ));
    }

    /**
     * Return employee profile detail for Modal popup
     */
    public function detail($id)
    {
        $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return view('pegawai.detail_modal', compact('pegawai'));
        }

        return view('pegawai.show', compact('pegawai'));
    }
}
