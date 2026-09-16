<?php

namespace App\Http\Controllers;

use App\Models\PangkatGolongan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class DiagramController extends Controller
{
    public function index()
    {
        // 1. Piramida Generasi
        $genZ = Pegawai::where('is_active', true)->where('usia_tahun', '<', 28)->count();
        $milenial = Pegawai::where('is_active', true)->whereBetween('usia_tahun', [28, 43])->count();
        $genX = Pegawai::where('is_active', true)->whereBetween('usia_tahun', [44, 59])->count();
        $boomer = Pegawai::where('is_active', true)->where('usia_tahun', '>=', 60)->count();

        // 2. Gender Composition
        $genderLaki = Pegawai::where('is_active', true)->where('jenis_kelamin', 'L')->count();
        $genderPerempuan = Pegawai::where('is_active', true)->where('jenis_kelamin', 'P')->count();

        // 3. Distribusi Golongan Ruang
        $golonganDist = PangkatGolongan::withCount(['pegawai' => function ($q) {
            $q->where('is_active', true);
        }])->where('golongan_ruang', '!=', '-')
           ->orderByDesc('hirarki_level')
           ->get();

        // 4. Tingkat Pendidikan
        $pendidikanDist = Pegawai::where('is_active', true)
            ->whereNotNull('pendidikan_terakhir')
            ->selectRaw('pendidikan_terakhir, count(*) as total')
            ->groupBy('pendidikan_terakhir')
            ->orderByDesc('total')
            ->get();

        // 5. Almamater / Universitas Terbanyak
        $univDist = Pegawai::where('is_active', true)
            ->whereNotNull('nama_universitas')
            ->where('nama_universitas', '!=', '')
            ->where('nama_universitas', '!=', '-')
            ->selectRaw('nama_universitas, count(*) as total')
            ->groupBy('nama_universitas')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // 6. Masa Tugas di Palembang (Tour of Duty)
        $todUnder1 = Pegawai::where('is_active', true)->where('lama_palembang_tahun', '<', 1)->count();
        $tod1to2 = Pegawai::where('is_active', true)->whereBetween('lama_palembang_tahun', [1, 2])->count();
        $tod2to4 = Pegawai::where('is_active', true)->whereBetween('lama_palembang_tahun', [3, 4])->count();
        $todOver4 = Pegawai::where('is_active', true)->where('lama_palembang_tahun', '>', 4)->count();

        // 7. Formasi Unit Kerja
        $unitDist = UnitKerja::withCount(['pegawai' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('urutan')->get();

        $totalPegawai = Pegawai::where('is_active', true)->count();

        return view('diagram.index', compact(
            'genZ',
            'milenial',
            'genX',
            'boomer',
            'genderLaki',
            'genderPerempuan',
            'golonganDist',
            'pendidikanDist',
            'univDist',
            'todUnder1',
            'tod1to2',
            'tod2to4',
            'todOver4',
            'unitDist',
            'totalPegawai'
        ));
    }
}
