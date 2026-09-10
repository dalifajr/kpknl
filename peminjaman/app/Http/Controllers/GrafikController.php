<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GrafikController extends Controller
{
    /**
     * Helper to construct YEAR() SQL depending on database driver.
     */
    private function yearSql(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }

    /**
     * Helper to construct MONTH() SQL depending on database driver.
     */
    private function monthSql(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    /**
     * Main Grafik Index (INFORMASI GRAFIK).
     * Has exactly 4 main menus: Bulanan, Tahunan, Pelelang, Peminjam.
     */
    public function index(): View
    {
        return view('grafik.index');
    }

    /**
     * Submenu GRAFIK BULANAN.
     * Has 2 choices: Pelelangan and Peminjaman.
     */
    public function bulananMenu(): View
    {
        return view('grafik.bulanan');
    }

    /**
     * Submenu GRAFIK TAHUNAN.
     * Has 2 choices: Pelelangan and Peminjaman.
     */
    public function tahunanMenu(): View
    {
        return view('grafik.tahunan');
    }

    /**
     * Grafik Bulanan Pelelangan per Tahun.
     */
    public function bulananPelelangan(Request $request): View|JsonResponse
    {
        // If AJAX request or action=get_data
        if ($request->ajax() || $request->query('action') === 'get_data') {
            return $this->bulananPelelanganData($request);
        }

        $yearSql = $this->yearSql('tgl_risalah');

        // Fetch all unique valid years across minuta, tap, and batal
        $minutaYears = RisalahMinuta::selectRaw("{$yearSql} as yr")->whereNotNull('tgl_risalah')->whereRaw("{$yearSql} > 0")->distinct()->pluck('yr');
        $tapYears = RisalahTap::selectRaw("{$yearSql} as yr")->whereNotNull('tgl_risalah')->whereRaw("{$yearSql} > 0")->distinct()->pluck('yr');
        $batalYears = RisalahBatal::selectRaw("{$yearSql} as yr")->whereNotNull('tgl_risalah')->whereRaw("{$yearSql} > 0")->distinct()->pluck('yr');

        $years = $minutaYears->merge($tapYears)->merge($batalYears)->unique()->filter()->sort()->values();

        return view('grafik.bulanan_pelelangan', compact('years'));
    }

    /**
     * AJAX data endpoint for Grafik Bulanan Pelelangan.
     */
    public function bulananPelelanganData(Request $request): JsonResponse
    {
        $year = (int)$request->query('year');
        $status = strtolower($request->query('status', ''));

        $labels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];

        if ($status === 'minuta') {
            $data = $this->getMonthlyCountByTable('risalah_minuta', $year);
            return response()->json(['labels' => $labels, 'values' => $data]);
        } elseif ($status === 'tap') {
            $data = $this->getMonthlyCountByTable('risalah_tap', $year);
            return response()->json(['labels' => $labels, 'values' => $data]);
        } elseif ($status === 'batal') {
            $data = $this->getMonthlyCountByTable('risalah_batal', $year);
            return response()->json(['labels' => $labels, 'values' => $data]);
        } else {
            // "Semua" -> Returns 3 separate datasets
            $datasets = [
                [
                    'label' => 'Risalah Minuta',
                    'data' => $this->getMonthlyCountByTable('risalah_minuta', $year),
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => false,
                ],
                [
                    'label' => 'Risalah Tap',
                    'data' => $this->getMonthlyCountByTable('risalah_tap', $year),
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false,
                ],
                [
                    'label' => 'Risalah Batal',
                    'data' => $this->getMonthlyCountByTable('risalah_batal', $year),
                    'borderColor' => 'rgba(255, 206, 86, 1)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'fill' => false,
                ],
            ];

            return response()->json(['labels' => $labels, 'datasets' => $datasets]);
        }
    }

    private function getMonthlyCountByTable(string $table, int $year): array
    {
        $yearSql = $this->yearSql('tgl_risalah');
        $monthSql = $this->monthSql('tgl_risalah');

        $raw = DB::table($table)
            ->selectRaw("{$monthSql} as bulan, COUNT(*) as total")
            ->whereRaw("{$yearSql} = ?", [$year])
            ->groupBy(DB::raw($monthSql))
            ->pluck('total', 'bulan')
            ->toArray();

        $values = array_fill(0, 12, 0);
        foreach ($raw as $bulan => $total) {
            $values[(int)$bulan - 1] = (int)$total;
        }

        return $values;
    }

    /**
     * Grafik Bulanan Peminjaman (Total Semua Peminjam).
     */
    public function bulananPeminjaman(): View
    {
        $yearSql = $this->yearSql('tgl_peminjaman');
        $monthSql = $this->monthSql('tgl_peminjaman');

        $records = DB::table('peminjaman')
            ->selectRaw("{$yearSql} AS tahun, {$monthSql} AS bulan, COUNT(*) AS total")
            ->whereNotNull('tgl_peminjaman')
            ->whereRaw("{$yearSql} > 0")
            ->groupBy(DB::raw($yearSql), DB::raw($monthSql))
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $dataBulanan = [];
        foreach ($records as $row) {
            $dataBulanan[$row->tahun][$row->bulan] = (int)$row->total;
        }

        return view('grafik.bulanan_peminjaman', compact('dataBulanan'));
    }

    /**
     * Grafik Tahunan Pelelangan.
     */
    public function tahunanPelelangan(Request $request): View
    {
        $filter = $request->query('jenis', 'minuta');
        if (!in_array($filter, ['minuta', 'tap', 'batal'])) {
            $filter = 'minuta';
        }

        $table = "risalah_{$filter}";
        $yearSql = $this->yearSql('tgl_risalah');

        $records = DB::table($table)
            ->selectRaw("{$yearSql} AS tahun, COUNT(*) AS jumlah")
            ->whereNotNull('tgl_risalah')
            ->whereRaw("{$yearSql} > 0")
            ->groupBy(DB::raw($yearSql))
            ->orderBy('tahun', 'ASC')
            ->get();

        $tahun = $records->pluck('tahun')->toArray();
        $jumlah = $records->pluck('jumlah')->toArray();

        return view('grafik.tahunan_pelelangan', compact('filter', 'tahun', 'jumlah'));
    }

    /**
     * Grafik Tahunan Peminjaman (Total).
     */
    public function tahunanPeminjaman(): View
    {
        $yearSql = $this->yearSql('tgl_peminjaman');

        $records = DB::table('peminjaman')
            ->selectRaw("{$yearSql} AS tahun, COUNT(*) AS total")
            ->whereNotNull('tgl_peminjaman')
            ->whereRaw("{$yearSql} > 0")
            ->groupBy(DB::raw($yearSql))
            ->orderBy('tahun', 'ASC')
            ->get();

        $tahun = $records->pluck('tahun')->toArray();
        $total = $records->pluck('total')->toArray();

        return view('grafik.tahunan_peminjaman', compact('tahun', 'total'));
    }

    /**
     * Grafik Pelelang (Bulanan / Tahunan).
     */
    public function pelelang(Request $request): View
    {
        $pelelangList = DB::table('risalah_minuta')
            ->select('nama_pelelang')
            ->whereNotNull('nama_pelelang')
            ->where('nama_pelelang', '!=', '')
            ->distinct()
            ->orderBy('nama_pelelang', 'ASC')
            ->pluck('nama_pelelang');

        $pelelang = $request->query('pelelang', '');
        $jenis = $request->query('jenis', '');

        $dataBulanan = [];
        $dataTahunan = [];

        if ($pelelang && $jenis) {
            $yearSql = $this->yearSql('tgl_risalah');
            $monthSql = $this->monthSql('tgl_risalah');

            if ($jenis === 'bulanan') {
                $rows = DB::table('risalah_minuta')
                    ->selectRaw("{$yearSql} AS tahun, {$monthSql} AS bulan, COUNT(*) AS total")
                    ->where('nama_pelelang', $pelelang)
                    ->whereNotNull('tgl_risalah')
                    ->whereRaw("{$yearSql} > 0")
                    ->groupBy(DB::raw($yearSql), DB::raw($monthSql))
                    ->orderBy('tahun')
                    ->orderBy('bulan')
                    ->get();

                foreach ($rows as $r) {
                    $dataBulanan[$r->tahun][$r->bulan] = (int)$r->total;
                }
            } elseif ($jenis === 'tahunan') {
                $rows = DB::table('risalah_minuta')
                    ->selectRaw("{$yearSql} AS tahun, COUNT(*) AS total")
                    ->where('nama_pelelang', $pelelang)
                    ->whereNotNull('tgl_risalah')
                    ->whereRaw("{$yearSql} > 0")
                    ->groupBy(DB::raw($yearSql))
                    ->orderBy('tahun')
                    ->get();

                foreach ($rows as $r) {
                    $dataTahunan[] = ['tahun' => $r->tahun, 'total' => (int)$r->total];
                }
            }
        }

        return view('grafik.pelelang', compact('pelelangList', 'pelelang', 'jenis', 'dataBulanan', 'dataTahunan'));
    }

    /**
     * Grafik Peminjam (Bulanan / Tahunan).
     */
    public function peminjam(Request $request): View
    {
        $peminjamList = DB::table('peminjaman')
            ->select('nama_peminjam')
            ->whereNotNull('nama_peminjam')
            ->where('nama_peminjam', '!=', '')
            ->distinct()
            ->orderBy('nama_peminjam', 'ASC')
            ->pluck('nama_peminjam');

        $peminjam = $request->query('peminjam', '');
        $jenis = $request->query('jenis', '');

        $dataBulanan = [];
        $dataTahunan = [];

        if ($peminjam && $jenis) {
            $yearSql = $this->yearSql('tgl_peminjaman');
            $monthSql = $this->monthSql('tgl_peminjaman');

            if ($jenis === 'bulanan') {
                $rows = DB::table('peminjaman')
                    ->selectRaw("{$yearSql} AS tahun, {$monthSql} AS bulan, COUNT(*) AS total")
                    ->where('nama_peminjam', $peminjam)
                    ->whereNotNull('tgl_peminjaman')
                    ->whereRaw("{$yearSql} > 0")
                    ->groupBy(DB::raw($yearSql), DB::raw($monthSql))
                    ->orderBy('tahun')
                    ->orderBy('bulan')
                    ->get();

                foreach ($rows as $r) {
                    $dataBulanan[$r->tahun][$r->bulan] = (int)$r->total;
                }
            } elseif ($jenis === 'tahunan') {
                $rows = DB::table('peminjaman')
                    ->selectRaw("{$yearSql} AS tahun, COUNT(*) AS total")
                    ->where('nama_peminjam', $peminjam)
                    ->whereNotNull('tgl_peminjaman')
                    ->whereRaw("{$yearSql} > 0")
                    ->groupBy(DB::raw($yearSql))
                    ->orderBy('tahun')
                    ->get();

                foreach ($rows as $r) {
                    $dataTahunan[] = ['tahun' => $r->tahun, 'total' => (int)$r->total];
                }
            }
        }

        return view('grafik.peminjam', compact('peminjamList', 'peminjam', 'jenis', 'dataBulanan', 'dataTahunan'));
    }
}
