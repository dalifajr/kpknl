@extends('layouts.bmn_master')

@section('title', 'Statistik & Tren Grafik Risalah Lelang — KPKNL Palembang')
@section('page_title', 'Statistik & Tren Grafik Risalah Lelang')
@section('page_subtitle', 'Analisis komparasi bulanan, tahunan, statistik peminjam, dan produktivitas pejabat lelang — KPKNL Palembang')

@section('page_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-chart-pie"></i> Monitoring Operasional
    </a>
    <a href="{{ route('katalog.index') }}" class="btn btn-warning btn-sm d-flex align-items-center gap-2 fw-semibold text-dark shadow-sm">
        <i class="fas fa-boxes-stacked"></i> Jelajah Katalog
    </a>
@endsection

@section('content')

<!-- ========================================================================= -->
<!-- 1. BILAH KONTROL FILTER (TAHUN & BULAN)                                   -->
<!-- ========================================================================= -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fw-semibold">
                <i class="fas fa-filter me-1.5"></i> Parameter Analisis
            </span>
            <small class="text-muted d-none d-md-inline">
                Pilih rentang waktu untuk menyinkronkan seluruh agregasi visualisasi secara interaktif
            </small>
        </div>

        <form id="filterForm" method="GET" action="{{ route('statistik.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Filter Tahun -->
            <div class="d-flex align-items-center gap-1.5">
                <label for="filterYear" class="small fw-semibold text-muted text-nowrap">Tahun:</label>
                <select name="year" id="filterYear" class="form-select form-select-sm" style="width: 110px; font-weight: 600;" onchange="onFilterChange()">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Bulan -->
            <div class="d-flex align-items-center gap-1.5">
                <label for="filterMonth" class="small fw-semibold text-muted text-nowrap">Bulan:</label>
                <select name="month" id="filterMonth" class="form-select form-select-sm" style="width: 135px; font-weight: 600;" onchange="onFilterChange()">
                    <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                    @foreach([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ] as $num => $namaBulan)
                        <option value="{{ $num }}" {{ (string)$selectedMonth === (string)$num ? 'selected' : '' }}>{{ $namaBulan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <a href="{{ route('statistik.index', ['year' => 2026, 'month' => 'all']) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Kembalikan Filter Default">
                <i class="fas fa-rotate-left"></i> Reset
            </a>
        </form>
    </div>

    <!-- Mini KPI Ribbon -->
    <div class="card-body py-2 px-3 bg-light border-top">
        <div class="row g-2 text-center text-md-start align-items-center">
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-gavel fa-sm"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Pelelangan Periode Ini</small>
                        <div class="fw-bold text-dark fs-6" id="summaryLelang">
                            {{ number_format($summary['totalLelangPeriod'], 0, ',', '.') }} Berkas
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
                    <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-handshake-angle fa-sm"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Peminjaman Periode Ini</small>
                        <div class="fw-bold text-dark fs-6" id="summaryPinjam">
                            {{ number_format($summary['totalPinjamPeriod'], 0, ',', '.') }} Transaksi
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
                    <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-users fa-sm"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Peminjam Aktif</small>
                        <div class="fw-bold text-dark fs-6" id="summaryPeminjam">
                            {{ number_format($summary['totalPeminjamAktif'], 0, ',', '.') }} Pegawai
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. GRID 4 GRAFIK UTAMA                                                    -->
<!-- ========================================================================= -->
<div class="row g-4">
    <!-- --------------------------------------------------------------------- -->
    <!-- GRAFIK 1: Tren Bulanan (Pelelangan vs Peminjaman)                     -->
    <!-- --------------------------------------------------------------------- -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-chart-line text-primary"></i> 1. Grafik Bulanan Pelelangan & Peminjaman
                    </h2>
                    <small class="text-muted" id="monthlyTitleSubtitle">
                        Komparasi akta lelang terbit vs sirkulasi peminjaman per bulan tahun <span class="fw-bold text-primary">{{ $selectedYear }}</span>
                    </small>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    Combo Bar-Line
                </span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="monthlyComboChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span><i class="fas fa-info-circle me-1 text-primary"></i> Batang: Akta Lelang | Garis Emas: Peminjaman</span>
                <span class="fw-semibold">Tahun {{ $selectedYear }}</span>
            </div>
        </div>
    </div>

    <!-- --------------------------------------------------------------------- -->
    <!-- GRAFIK 2: Tren Pertumbuhan Tahunan (2019 - 2026)                       -->
    <!-- --------------------------------------------------------------------- -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-chart-simple text-info"></i> 2. Grafik Tahunan Pelelangan & Peminjaman
                    </h2>
                    <small class="text-muted">
                        Tren makro pertumbuhan volume lelang dan peminjaman fisik (2019 - 2026)
                    </small>
                </div>
                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    Multi-Year
                </span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="yearlyGroupedChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span><i class="fas fa-circle-check me-1 text-info"></i> Agregasi 10.483 berkas fisik pada basis data KPKNL</span>
                <span class="fw-semibold">2019 - 2026</span>
            </div>
        </div>
    </div>

    <!-- --------------------------------------------------------------------- -->
    <!-- GRAFIK 3: Statistik Peminjam (Tahunan & Bulanan)                       -->
    <!-- --------------------------------------------------------------------- -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-user-check text-warning"></i> 3. Grafik Peminjam Berkas Teraktif
                    </h2>
                    <small class="text-muted" id="peminjamTitleSubtitle">
                        Statistik pegawai peminjam berkas teraktif periode <span class="fw-bold text-warning">{{ $selectedYear }}</span>
                    </small>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    Status Breakdown
                </span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="peminjamHorizontalChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span><i class="fas fa-square me-1 text-success"></i> Hijau: Sudah Kembali | <i class="fas fa-square me-1 text-warning"></i> Kuning: Sedang Dipinjam</span>
                <span>Top Pegawai</span>
            </div>
        </div>
    </div>

    <!-- --------------------------------------------------------------------- -->
    <!-- GRAFIK 4: Statistik Pejabat Lelang (Tahunan & Bulanan)                 -->
    <!-- --------------------------------------------------------------------- -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-gavel text-danger"></i> 4. Grafik Pejabat Lelang (Produktivitas Akta)
                    </h2>
                    <small class="text-muted" id="pelelangTitleSubtitle">
                        Distribusi produk akta lelang (Minuta, TAP, Batal) per pelelang periode <span class="fw-bold text-danger">{{ $selectedYear }}</span>
                    </small>
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    Stacked Bar
                </span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="pelelangStackedChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span><i class="fas fa-square me-1 text-primary"></i> Minuta (Laku) | <i class="fas fa-square me-1 text-info"></i> TAP | <i class="fas fa-square me-1 text-danger"></i> Batal</span>
                <span>Top Pejabat Lelang</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initial Datasets from Controller
    let currentPayload = {
        yearlyChart: @json($yearlyChart),
        monthlyChart: @json($monthlyChart),
        peminjamChart: @json($peminjamChart),
        pelelangChart: @json($pelelangChart),
        summary: @json($summary)
    };

    let monthlyComboChartInstance = null;
    let yearlyGroupedChartInstance = null;
    let peminjamHorizontalChartInstance = null;
    let pelelangStackedChartInstance = null;

    // Common Chart Defaults (Google Sans & High Contrast DJKN Navy)
    Chart.defaults.font.family = "'Google Sans', 'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Inisialisasi Grafik 1: Bulanan Combo (Bar + Line)
    function initMonthlyComboChart(data) {
        const ctx = document.getElementById('monthlyComboChart').getContext('2d');
        if (monthlyComboChartInstance) monthlyComboChartInstance.destroy();

        monthlyComboChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Peminjaman Berkas',
                        data: data.peminjaman,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.15)',
                        borderWidth: 3,
                        pointBackgroundColor: '#d97706',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.35,
                        fill: false,
                        yAxisID: 'y1',
                        order: 1
                    },
                    {
                        type: 'bar',
                        label: 'Akta Risalah Terbit',
                        data: data.pelelangan,
                        backgroundColor: 'rgba(12, 48, 107, 0.85)',
                        hoverBackgroundColor: '#0c306b',
                        borderRadius: 6,
                        borderSkipped: false,
                        yAxisID: 'y',
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15,
                            font: { weight: 600, size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleFont: { weight: 'bold', size: 13 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Akta Lelang',
                            font: { weight: 600 }
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: {
                            display: true,
                            text: 'Peminjaman',
                            font: { weight: 600 }
                        }
                    }
                }
            }
        });
    }

    // 2. Inisialisasi Grafik 2: Tahunan Grouped Bar
    function initYearlyGroupedChart(data) {
        const ctx = document.getElementById('yearlyGroupedChart').getContext('2d');
        if (yearlyGroupedChartInstance) yearlyGroupedChartInstance.destroy();

        yearlyGroupedChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Total Pelelangan (Minuta+TAP+Batal)',
                        data: data.pelelangan,
                        backgroundColor: '#1e40af',
                        hoverBackgroundColor: '#0c306b',
                        borderRadius: 6
                    },
                    {
                        label: 'Total Peminjaman Berkas',
                        data: data.peminjaman,
                        backgroundColor: '#f59e0b',
                        hoverBackgroundColor: '#d97706',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15,
                            font: { weight: 600, size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        title: {
                            display: true,
                            text: 'Volume Dokumen / Transaksi',
                            font: { weight: 600 }
                        }
                    }
                }
            }
        });
    }

    // 3. Inisialisasi Grafik 3: Horizontal Stacked Bar Peminjam
    function initPeminjamHorizontalChart(data) {
        const ctx = document.getElementById('peminjamHorizontalChart').getContext('2d');
        if (peminjamHorizontalChartInstance) peminjamHorizontalChartInstance.destroy();

        peminjamHorizontalChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Sudah Dikembalikan',
                        data: data.kembali,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Sedang Dipinjam / Proses',
                        data: data.dipinjam,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 12,
                            font: { weight: 600 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        title: {
                            display: true,
                            text: 'Jumlah Berkas Dipinjam',
                            font: { weight: 600 }
                        }
                    },
                    y: {
                        stacked: true,
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 4. Inisialisasi Grafik 4: Stacked Bar Pejabat Lelang
    function initPelelangStackedChart(data) {
        const ctx = document.getElementById('pelelangStackedChart').getContext('2d');
        if (pelelangStackedChartInstance) pelelangStackedChartInstance.destroy();

        pelelangStackedChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Minuta (Laku)',
                        data: data.minuta,
                        backgroundColor: '#0c306b',
                        borderRadius: 4
                    },
                    {
                        label: 'TAP (Nol Penawaran)',
                        data: data.tap,
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    },
                    {
                        label: 'Batal Lelang',
                        data: data.batal,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 12,
                            font: { weight: 600 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: {
                            maxRotation: 25,
                            minRotation: 15,
                            font: { size: 11 }
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        title: {
                            display: true,
                            text: 'Akta Risalah Diterbitkan',
                            font: { weight: 600 }
                        }
                    }
                }
            }
        });
    }

    // Dynamic Filter Handler via AJAX
    function onFilterChange() {
        const year = document.getElementById('filterYear').value;
        const month = document.getElementById('filterMonth').value;

        // Fetch JSON Data from endpoint
        fetch(`{{ route('statistik.data') }}?year=${year}&month=${month}`)
            .then(res => res.json())
            .then(data => {
                // Update Subtitle Texts
                document.getElementById('monthlyTitleSubtitle').innerHTML = `Komparasi akta lelang terbit vs sirkulasi peminjaman per bulan tahun <span class="fw-bold text-primary">${year}</span>`;
                document.getElementById('peminjamTitleSubtitle').innerHTML = `Statistik pegawai peminjam berkas teraktif periode <span class="fw-bold text-warning">${year}</span>`;
                document.getElementById('pelelangTitleSubtitle').innerHTML = `Distribusi produk akta lelang (Minuta, TAP, Batal) per pelelang periode <span class="fw-bold text-danger">${year}</span>`;

                // Update Summary Counters
                document.getElementById('summaryLelang').textContent = Number(data.summary.totalLelangPeriod).toLocaleString('id-ID') + ' Berkas';
                document.getElementById('summaryPinjam').textContent = Number(data.summary.totalPinjamPeriod).toLocaleString('id-ID') + ' Transaksi';
                document.getElementById('summaryPeminjam').textContent = Number(data.summary.totalPeminjamAktif).toLocaleString('id-ID') + ' Pegawai';

                // Re-render Charts with updated datasets
                initMonthlyComboChart(data.monthlyChart);
                initYearlyGroupedChart(data.yearlyChart);
                initPeminjamHorizontalChart(data.peminjamChart);
                initPelelangStackedChart(data.pelelangChart);
            })
            .catch(err => {
                console.error('Gagal memperbarui grafik:', err);
            });
    }

    // Render all on initial page load
    document.addEventListener('DOMContentLoaded', function () {
        initMonthlyComboChart(currentPayload.monthlyChart);
        initYearlyGroupedChart(currentPayload.yearlyChart);
        initPeminjamHorizontalChart(currentPayload.peminjamChart);
        initPelelangStackedChart(currentPayload.pelelangChart);
    });
</script>
@endpush
