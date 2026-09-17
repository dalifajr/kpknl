@extends('layouts.bmn_master')

@section('title', 'Monitoring Risalah Lelang')
@section('page_title', 'Monitoring Risalah Lelang')
@section('page_subtitle', 'Monitoring ketersediaan fisik, perputaran, dan tata kelola arsip Risalah Lelang — KPKNL Palembang')



@section('content')

<!-- ========================================================================= -->
<!-- 1. KPI SUMMARY CARDS ROW (Exact BMN Interactive Card Pattern)             -->
<!-- ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- Total Risalah -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('katalog.index', ['type' => 'all']) }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">TOTAL ARSIP</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($totalAll, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-landmark ms-auto text-primary opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Minuta (Laku) -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('katalog.index', ['type' => 'minuta']) }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">MINUTA (LAKU)</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($minutaCount, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-gavel ms-auto text-info opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- TAP -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('katalog.index', ['type' => 'tap']) }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">TAP</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($tapCount, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-file-excel ms-auto text-warning opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Batal -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('katalog.index', ['type' => 'batal']) }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">BATAL LELANG</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($batalCount, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-ban ms-auto text-danger opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tersedia Fisik -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('katalog.index', ['status' => 'tersedia']) }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">TERSEDIA FISIK</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-success mb-0">{{ number_format($totalTersedia, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-vault ms-auto text-success opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Peminjaman Aktif -->
    <div class="col-12 col-sm-6 col-xl-2">
        <a href="{{ route('peminjaman.index') }}" class="text-decoration-none">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-secondary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">PEMINJAMAN AKTIF</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-warning mb-0">{{ number_format($loanActiveCount, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Berkas</span></h3>
                        <i class="fas fa-hand-holding-hand ms-auto text-warning opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. CHARTS & LEADERBOARDS ROW                                              -->
<!-- ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Chart Komposisi -->
    <div class="col-12 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fas fa-chart-pie text-primary me-2"></i> Komposisi Risalah Lelang
                </div>
                <span class="badge bg-light text-muted border">{{ number_format($totalAll, 0, ',', '.') }} Total</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-3" style="min-height: 260px;">
                <div style="width: 100%; height: 220px;">
                    <canvas id="chartKomposisi"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Ketersediaan -->
    <div class="col-12 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fas fa-chart-pie text-success me-2"></i> Status Ketersediaan Fisik
                </div>
                <span class="badge bg-success-subtle text-success border">Realtime Lemari</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-3" style="min-height: 260px;">
                <div style="width: 100%; height: 220px;">
                    <canvas id="chartKetersediaan"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Pejabat Lelang -->
    <div class="col-12 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark">
                    <i class="fas fa-trophy text-warning me-2"></i> Top Pejabat Lelang Teraktif
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border">Minuta Terbanyak</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topPelelang as $idx => $pelelang)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $idx === 0 ? 'bg-warning text-dark' : ($idx === 1 ? 'bg-secondary' : 'bg-light text-muted border') }} rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem;">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="fw-medium text-dark text-truncate" style="max-width: 180px; font-size: 0.82rem;">
                                    {{ $pelelang->nama_pelelang }}
                                </span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.75rem;">
                                {{ number_format($pelelang->total, 0, ',', '.') }} Risalah
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">Belum ada data pejabat lelang.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. RECENT LOANS TABLE CARD                                                -->
<!-- ========================================================================= -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="fw-bold text-dark">
            <i class="fas fa-clock-rotate-left text-primary me-2"></i> Permohonan & Peminjaman Berkas Terkini
        </div>
        <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-primary">
            Buka Seluruh Peminjaman <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
            <thead class="table-light">
                <tr>
                    <th>No. Pinjam</th>
                    <th>Peminjam</th>
                    <th>Tgl Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Status Alur</th>
                    <th className="text-end" style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLoans as $loan)
                    <tr>
                        <td class="fw-bold text-muted">#{{ $loan->id }}</td>
                        <td class="fw-semibold text-dark">{{ $loan->nama_peminjam }}</td>
                        <td class="text-muted">{{ $loan->tgl_peminjaman ?? '-' }}</td>
                        <td class="text-muted">{{ $loan->tgl_pengembalian ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $loan->getStatusBadgeClass() }} px-2 py-1">
                                {{ $loan->status }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('peminjaman.index', ['search' => $loan->id]) }}" class="btn btn-sm btn-light border py-1 px-2.5">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data peminjaman terkini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);

        // Chart 1: Komposisi
        const ctx1 = document.getElementById('chartKomposisi');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: chartData.komposisi.labels,
                    datasets: [{
                        data: chartData.komposisi.values,
                        backgroundColor: chartData.komposisi.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { family: "'Google Sans', 'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                                padding: 14
                            }
                        }
                    }
                }
            });
        }

        // Chart 2: Ketersediaan
        const ctx2 = document.getElementById('chartKetersediaan');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: chartData.ketersediaan.labels,
                    datasets: [{
                        data: chartData.ketersediaan.values,
                        backgroundColor: chartData.ketersediaan.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { family: "'Google Sans', 'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                                padding: 14
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
