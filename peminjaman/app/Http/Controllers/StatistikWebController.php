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

class StatistikWebController extends Controller
{
    /**
     * Display the main statistics & trends analytics page.
     */
    public function index(Request $request): View
    {
        $selectedYear = (int) $request->input('year', 2026);
        $selectedMonth = $request->input('month', 'all');

        $availableYears = [2026, 2025, 2024, 2023, 2022, 2021, 2020, 2019];

        $payload = $this->buildAnalyticsPayload($selectedYear, $selectedMonth);

        return view('statistik.index', array_merge($payload, [
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'availableYears' => $availableYears,
        ]));
    }

    /**
     * API endpoint returning JSON data for reactive client-side filter changes.
     */
    public function getData(Request $request): JsonResponse
    {
        $selectedYear = (int) $request->input('year', 2026);
        $selectedMonth = $request->input('month', 'all');

        $payload = $this->buildAnalyticsPayload($selectedYear, $selectedMonth);

        return response()->json($payload);
    }

    /**
     * Build the entire dataset payload for the 4 sets of charts.
     */
    private function buildAnalyticsPayload(int $year, string $month): array
    {
        // 1. DATASET GRAFIK TAHUNAN (2019 - 2026)
        $yearsRange = [2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
        $yearlyPelelangan = [];
        $yearlyPeminjaman = [];

        $minutaByYear = DB::table('risalah_minuta')
            ->selectRaw('YEAR(tgl_risalah) as y, count(*) as total')
            ->whereNotNull('tgl_risalah')
            ->where('tgl_risalah', '!=', '0000-00-00')
            ->groupBy('y')
            ->pluck('total', 'y')
            ->toArray();

        $tapByYear = DB::table('risalah_tap')
            ->selectRaw('YEAR(tgl_risalah) as y, count(*) as total')
            ->whereNotNull('tgl_risalah')
            ->where('tgl_risalah', '!=', '0000-00-00')
            ->groupBy('y')
            ->pluck('total', 'y')
            ->toArray();

        $batalByYear = DB::table('risalah_batal')
            ->selectRaw('YEAR(tgl_risalah) as y, count(*) as total')
            ->whereNotNull('tgl_risalah')
            ->where('tgl_risalah', '!=', '0000-00-00')
            ->groupBy('y')
            ->pluck('total', 'y')
            ->toArray();

        $pinjamByYear = DB::table('peminjaman')
            ->selectRaw('YEAR(tgl_peminjaman) as y, count(*) as total')
            ->whereNotNull('tgl_peminjaman')
            ->groupBy('y')
            ->pluck('total', 'y')
            ->toArray();

        foreach ($yearsRange as $y) {
            $totalLelang = ($minutaByYear[$y] ?? 0) + ($tapByYear[$y] ?? 0) + ($batalByYear[$y] ?? 0);
            $yearlyPelelangan[] = $totalLelang;
            $yearlyPeminjaman[] = $pinjamByYear[$y] ?? 0;
        }

        // 2. DATASET GRAFIK BULANAN (Januari - Desember) untuk Tahun Terpilih
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $monthlyMinuta = DB::table('risalah_minuta')
            ->selectRaw('MONTH(tgl_risalah) as m, count(*) as total')
            ->whereYear('tgl_risalah', $year)
            ->groupBy('m')
            ->pluck('total', 'm')
            ->toArray();

        $monthlyTap = DB::table('risalah_tap')
            ->selectRaw('MONTH(tgl_risalah) as m, count(*) as total')
            ->whereYear('tgl_risalah', $year)
            ->groupBy('m')
            ->pluck('total', 'm')
            ->toArray();

        $monthlyBatal = DB::table('risalah_batal')
            ->selectRaw('MONTH(tgl_risalah) as m, count(*) as total')
            ->whereYear('tgl_risalah', $year)
            ->groupBy('m')
            ->pluck('total', 'm')
            ->toArray();

        $monthlyPinjam = DB::table('peminjaman')
            ->selectRaw('MONTH(tgl_peminjaman) as m, count(*) as total')
            ->whereYear('tgl_peminjaman', $year)
            ->groupBy('m')
            ->pluck('total', 'm')
            ->toArray();

        $monthlyLelangCombined = [];
        $monthlyPinjamData = [];

        foreach (range(1, 12) as $m) {
            $totL = ($monthlyMinuta[$m] ?? 0) + ($monthlyTap[$m] ?? 0) + ($monthlyBatal[$m] ?? 0);
            $monthlyLelangCombined[] = $totL;
            $monthlyPinjamData[] = $monthlyPinjam[$m] ?? 0;
        }

        // 3. DATASET GRAFIK PEMINJAM (Top Peminjam dengan breakdown status)
        $qPeminjam = DB::table('peminjaman')
            ->select('nama_peminjam')
            ->whereNotNull('nama_peminjam')
            ->where('nama_peminjam', '!=', '');

        if ($year > 0) {
            $qPeminjam->whereYear('tgl_peminjaman', $year);
        }
        if ($month !== 'all' && is_numeric($month)) {
            $qPeminjam->whereMonth('tgl_peminjaman', (int) $month);
        }

        $topPeminjamNames = (clone $qPeminjam)
            ->select('nama_peminjam', DB::raw('count(*) as total'))
            ->groupBy('nama_peminjam')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('nama_peminjam')
            ->toArray();

        // If no records in selected period, fallback to all-time top 5
        if (empty($topPeminjamNames)) {
            $topPeminjamNames = DB::table('peminjaman')
                ->select('nama_peminjam', DB::raw('count(*) as total'))
                ->whereNotNull('nama_peminjam')
                ->where('nama_peminjam', '!=', '')
                ->groupBy('nama_peminjam')
                ->orderByDesc('total')
                ->take(5)
                ->pluck('nama_peminjam')
                ->toArray();
        }

        $peminjamLabels = [];
        $peminjamKembali = [];
        $peminjamDipinjam = [];

        foreach ($topPeminjamNames as $pName) {
            $peminjamLabels[] = $pName;

            $qKembali = DB::table('peminjaman')
                ->where('nama_peminjam', $pName)
                ->where('status', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN);

            $qDipinjam = DB::table('peminjaman')
                ->where('nama_peminjam', $pName)
                ->where('status', '!=', Peminjaman::STATUS_SUDAH_DIKEMBALIKAN);

            if ($year > 0) {
                $qKembali->whereYear('tgl_peminjaman', $year);
                $qDipinjam->whereYear('tgl_peminjaman', $year);
            }
            if ($month !== 'all' && is_numeric($month)) {
                $qKembali->whereMonth('tgl_peminjaman', (int) $month);
                $qDipinjam->whereMonth('tgl_peminjaman', (int) $month);
            }

            $peminjamKembali[] = $qKembali->count();
            $peminjamDipinjam[] = $qDipinjam->count();
        }

        // 4. DATASET GRAFIK PELELANG (Top Pejabat Lelang dengan breakdown Minuta, TAP, Batal)
        $qPelelangMinuta = DB::table('risalah_minuta')
            ->select('nama_pelelang')
            ->whereNotNull('nama_pelelang')
            ->where('nama_pelelang', '!=', '');

        if ($year > 0) {
            $qPelelangMinuta->whereYear('tgl_risalah', $year);
        }
        if ($month !== 'all' && is_numeric($month)) {
            $qPelelangMinuta->whereMonth('tgl_risalah', (int) $month);
        }

        $topPelelangNames = (clone $qPelelangMinuta)
            ->select('nama_pelelang', DB::raw('count(*) as total'))
            ->groupBy('nama_pelelang')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('nama_pelelang')
            ->toArray();

        // Fallback to all-time top pelelang if period has few entries
        if (count($topPelelangNames) < 3) {
            $topPelelangNames = DB::table('risalah_minuta')
                ->select('nama_pelelang', DB::raw('count(*) as total'))
                ->whereNotNull('nama_pelelang')
                ->where('nama_pelelang', '!=', '')
                ->groupBy('nama_pelelang')
                ->orderByDesc('total')
                ->take(6)
                ->pluck('nama_pelelang')
                ->toArray();
        }

        $pelelangLabels = [];
        $pelelangMinutaData = [];
        $pelelangTapData = [];
        $pelelangBatalData = [];

        foreach ($topPelelangNames as $plName) {
            // Format shortened name for clean chart labels
            $cleanPl = preg_replace('/,.*$/', '', $plName);
            $pelelangLabels[] = $cleanPl;

            $qM = DB::table('risalah_minuta')->where('nama_pelelang', $plName);
            $qT = DB::table('risalah_tap')->where('nama_pelelang', $plName);
            $qB = DB::table('risalah_batal')->where('nama_pelelang', $plName);

            if ($year > 0) {
                $qM->whereYear('tgl_risalah', $year);
                $qT->whereYear('tgl_risalah', $year);
                $qB->whereYear('tgl_risalah', $year);
            }
            if ($month !== 'all' && is_numeric($month)) {
                $qM->whereMonth('tgl_risalah', (int) $month);
                $qT->whereMonth('tgl_risalah', (int) $month);
                $qB->whereMonth('tgl_risalah', (int) $month);
            }

            $pelelangMinutaData[] = $qM->count();
            $pelelangTapData[] = $qT->count();
            $pelelangBatalData[] = $qB->count();
        }

        // Summary Metric Cards
        $totalLelangPeriod = array_sum($monthlyLelangCombined);
        $totalPinjamPeriod = array_sum($monthlyPinjamData);
        $totalPeminjamAktif = DB::table('peminjaman')
            ->when($year > 0, fn($q) => $q->whereYear('tgl_peminjaman', $year))
            ->distinct('nama_peminjam')
            ->count('nama_peminjam');

        return [
            'yearlyChart' => [
                'labels' => $yearsRange,
                'pelelangan' => $yearlyPelelangan,
                'peminjaman' => $yearlyPeminjaman,
            ],
            'monthlyChart' => [
                'labels' => array_values($monthNames),
                'pelelangan' => $monthlyLelangCombined,
                'peminjaman' => $monthlyPinjamData,
            ],
            'peminjamChart' => [
                'labels' => $peminjamLabels,
                'kembali' => $peminjamKembali,
                'dipinjam' => $peminjamDipinjam,
            ],
            'pelelangChart' => [
                'labels' => $pelelangLabels,
                'minuta' => $pelelangMinutaData,
                'tap' => $pelelangTapData,
                'batal' => $pelelangBatalData,
            ],
            'summary' => [
                'totalLelangPeriod' => $totalLelangPeriod,
                'totalPinjamPeriod' => $totalPinjamPeriod,
                'totalPeminjamAktif' => $totalPeminjamAktif,
                'year' => $year,
                'month' => $month,
            ],
        ];
    }
}
