<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        // Group by jenis jabatan
        $struktural = Pegawai::with(['unitKerja', 'pangkatGolongan'])
            ->whereHas('jabatan', function ($q) {
                $q->where('jenis_jabatan', 'struktural');
            })
            ->orWhere('nama_jabatan_raw', 'like', '%Kepala%')
            ->orderBy('no_urut')
            ->get();

        $fungsional = Pegawai::with(['unitKerja', 'pangkatGolongan'])
            ->where('tipe_pegawai', 'pns')
            ->where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('jenis_jabatan', 'fungsional');
                })
                ->orWhere('nama_jabatan_raw', 'like', '%Ahli%')
                ->orWhere('nama_jabatan_raw', 'like', '%Pelelang%')
                ->orWhere('nama_jabatan_raw', 'like', '%Penilai%');
            })
            ->where('nama_jabatan_raw', 'not like', '%Kepala%')
            ->orderBy('no_urut')
            ->get();

        $pelaksana = Pegawai::with(['unitKerja', 'pangkatGolongan'])
            ->where('tipe_pegawai', 'pns')
            ->whereNotIn('id', $struktural->pluck('id'))
            ->whereNotIn('id', $fungsional->pluck('id'))
            ->orderBy('no_urut')
            ->get();

        $ppnpn = Pegawai::with(['unitKerja'])
            ->where('tipe_pegawai', 'ppnpn')
            ->orderBy('no_urut')
            ->get();

        // Job Grade breakdown
        $grades = Pegawai::where('is_active', true)
            ->whereNotNull('job_grade')
            ->selectRaw('job_grade, count(*) as total')
            ->groupBy('job_grade')
            ->orderByDesc('job_grade')
            ->get();

        return view('jabatan.index', compact(
            'struktural',
            'fungsional',
            'pelaksana',
            'ppnpn',
            'grades'
        ));
    }
}
