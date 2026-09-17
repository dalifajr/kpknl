<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        $unitKerjas = UnitKerja::with(['pegawai' => function ($q) {
            $q->with(['jabatan', 'pangkatGolongan'])->orderBy('no_urut');
        }])->orderBy('urutan')->get();

        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])
            ->where('is_active', true)
            ->orderBy('no_urut')
            ->get();

        $totalPegawai = Pegawai::where('is_active', true)->count();
        $totalPns = Pegawai::where('is_active', true)->where('tipe_pegawai', 'pns')->count();
        $totalPpnpn = Pegawai::where('is_active', true)->where('tipe_pegawai', 'ppnpn')->count();

        return view('unit_kerja.index', compact('unitKerjas', 'allPegawai', 'totalPegawai', 'totalPns', 'totalPpnpn'));
    }
}
