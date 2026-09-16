@extends('layouts.app')

@section('title', 'Dashboard Eksekutif Kepegawaian — SIMPATIK KPKNL Palembang')
@section('hero-title', 'Dashboard Eksekutif Kepegawaian')
@section('hero-subtitle', 'Sistem Informasi Manajemen Profil & Analitika Terpadu Kepegawaian (SIMPATIK) KPKNL Palembang')

@section('content')

<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #0c306b; --icon-bg: #eff6ff; --icon-color: #0c306b;">
            <div>
                <div class="kpi-title">Total Personil</div>
                <div class="kpi-val">{{ $totalPegawai }}</div>
                <div class="kpi-desc">KPKNL Palembang</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <!-- PNS Definitif -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #1e40af; --icon-bg: #dbeafe; --icon-color: #1e40af;">
            <div>
                <div class="kpi-title">ASN Definitif</div>
                <div class="kpi-val">{{ $totalPns }}</div>
                <div class="kpi-desc">100% Terverifikasi</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>
    </div>

    <!-- Alert KGB -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #f59e0b; --icon-bg: #fef3c7; --icon-color: #d97706;">
            <div>
                <div class="kpi-title">Alert KGB</div>
                <div class="kpi-val text-warning">{{ $totalKgbAlerts }}</div>
                <div class="kpi-desc">&le; 90 Hari / Lewat</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>
    </div>

    <!-- Radar Pensiun -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #ef4444; --icon-bg: #fee2e2; --icon-color: #dc2626;">
            <div>
                <div class="kpi-title">Radar Pensiun</div>
                <div class="kpi-val text-danger">{{ $totalPensiunRadar }}</div>
                <div class="kpi-desc">Usia &ge; 53 Tahun</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-user-clock"></i>
            </div>
        </div>
    </div>

    <!-- Tour of Duty -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #8b5cf6; --icon-bg: #f3e8ff; --icon-color: #7c3aed;">
            <div>
                <div class="kpi-title">Tour of Duty</div>
                <div class="kpi-val text-purple">{{ $totalTourOfDuty }}</div>
                <div class="kpi-desc">&gt; 4 Thn di Palembang</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-arrows-spin"></i>
            </div>
        </div>
    </div>

    <!-- Formasi Unit -->
    <div class="col-sm-6 col-xl-2">
        <div class="kpi-card" style="--card-accent: #10b981; --icon-bg: #d1fae5; --icon-color: #059669;">
            <div>
                <div class="kpi-title">Unit / Seksi</div>
                <div class="kpi-val text-success">{{ $unitStats->count() }}</div>
                <div class="kpi-desc">Subbag &amp; Seksi Aktif</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section Row -->
<div class="row g-4 mb-4">
    <!-- Chart: Piramida Usia & Generasi -->
    <div class="col-lg-8">
        <div class="card card-custom h-100">
            <div class="card-header-clean">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary"></i>
                    <span>Komposisi Generasi &amp; Kelompok Usia</span>
                </div>
                <span class="badge bg-light text-muted border">33 Personil</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px;">
                    <canvas id="chartGenerasi"></canvas>
                </div>
                <div class="row text-center mt-3 pt-3 border-top g-2">
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN Z (&lt;28 Thn)</div>
                        <div class="fw-bold fs-5 text-info">{{ $genZ }}</div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">MILENIAL (28-43 Thn)</div>
                        <div class="fw-bold fs-5 text-primary">{{ $milenial }}</div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN X (44-59 Thn)</div>
                        <div class="fw-bold fs-5 text-warning">{{ $genX }}</div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">BOOMER (&ge;60 Thn)</div>
                        <div class="fw-bold fs-5 text-danger">{{ $boomer }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Gender Ratio -->
    <div class="col-lg-4">
        <div class="card card-custom h-100">
            <div class="card-header-clean">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-venus-mars text-primary"></i>
                    <span>Rasio Gender Pegawai</span>
                </div>
                <span class="badge bg-light text-muted border">L / P</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div style="height: 220px; position: relative;">
                    <canvas id="chartGender"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end">
                        <div class="small text-muted"><i class="fa-solid fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $totalLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted"><i class="fa-solid fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $totalPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Tables Row: Early Warning KGB & Tour of Duty -->
<div class="row g-4 mb-4">
    <!-- Tabel EWS KGB -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header-clean">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                    <span>Early Warning System — Kenaikan Gaji Berkala (KGB)</span>
                </div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $totalKgbAlerts }} Pegawai</span>
            </div>
            <div class="card-body p-0">
                @if($kgbPegawai->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-circle-check fs-2 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada jadwal jatuh tempo KGB dalam 90 hari ke depan.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Pegawai</th>
                                    <th>Gol.</th>
                                    <th>TMT KGB</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kgbPegawai as $p)
                                    @php $st = $p->kgb_status; @endphp
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-subtle badge-navy">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->tmt_kgb ? $p->tmt_kgb->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $st['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                                                {{ $st['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Tour of Duty -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header-clean">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-arrows-split-up-and-left text-primary"></i>
                    <span>Indeks Tour of Duty (&gt; 4 Tahun di Palembang)</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $totalTourOfDuty }} Pegawai</span>
            </div>
            <div class="card-body p-0">
                @if($tourOfDutyPegawai->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-circle-check fs-2 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada pegawai definitif dengan penugasan lebih dari 4 tahun.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Pegawai</th>
                                    <th>Unit / Seksi</th>
                                    <th>TMT Palembang</th>
                                    <th>Durasi Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tourOfDutyPegawai as $p)
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-subtle badge-slate">{{ $p->unitKerja?->singkatan ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $p->tmt_palembang ? $p->tmt_palembang->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                                                {{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Formasi Personil Unit Kerja -->
<div class="card card-custom">
    <div class="card-header-clean">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-sitemap text-primary"></i>
            <span>Distribusi Formasi Personil per Seksi &amp; Subbagian</span>
        </div>
        <a href="{{ route('unit_kerja.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            Lihat Detail Formasi <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($unitStats as $unit)
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 border bg-light h-100 hover-lift">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary px-2 py-1" style="font-size: 0.7rem;">{{ $unit->singkatan ?: 'SEKSI' }}</span>
                            <span class="fw-bold text-dark fs-5">{{ $unit->total_pns }} <span class="small text-muted fs-6">ASN</span></span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $unit->nama_unit }}</h6>
                        <div class="text-muted small" style="font-size: 0.78rem;">KPKNL Palembang</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Chart Generasi
        const ctxGenerasi = document.getElementById('chartGenerasi').getContext('2d');
        new Chart(ctxGenerasi, {
            type: 'bar',
            data: {
                labels: ['Gen Z (<28 th)', 'Milenial (28-43 th)', 'Gen X (44-59 th)', 'Boomer (≥60 th)'],
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: [{{ $genZ }}, {{ $milenial }}, {{ $genX }}, {{ $boomer }}],
                    backgroundColor: [
                        'rgba(56, 189, 248, 0.85)',
                        'rgba(14, 165, 233, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(239, 68, 68, 0.85)'
                    ],
                    borderColor: [
                        '#0284c7',
                        '#0369a1',
                        '#d97706',
                        '#b91c1c'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 2 }
                    }
                }
            }
        });

        // Chart Gender
        const ctxGender = document.getElementById('chartGender').getContext('2d');
        new Chart(ctxGender, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [{{ $totalLaki }}, {{ $totalPerempuan }}],
                    backgroundColor: [
                        '#1e40af',
                        '#f43f5e'
                    ],
                    hoverOffset: 6,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 14, font: { weight: '600' } }
                    }
                },
                cutout: '68%'
            }
        });
    });
</script>
@endpush
