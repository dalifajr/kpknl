@extends('layouts.app')

@section('title', 'Dashboard Eksekutif Kepegawaian — SI-KEP KPKNL Palembang')
@section('hero-title', 'Dashboard Eksekutif Kepegawaian')
@section('hero-subtitle', 'Sistem Informasi Manajemen Profil & Analitika Terpadu Kepegawaian (SIMPATIK) KPKNL Palembang')

@section('content')

<!-- KPI Accent Summary Cards (From referensi_desain/desain2.html) -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Personil</small>
                    <i class="fas fa-users text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $totalPegawai }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">KPKNL Palembang</small>
            </div>
        </div>
    </div>

    <!-- ASN Definitif -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">ASN Definitif</small>
                    <i class="fas fa-user-shield text-info opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $totalPns }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">100% Terverifikasi</small>
            </div>
        </div>
    </div>

    <!-- Alert KGB -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Alert KGB</small>
                    <i class="fas fa-bell text-warning opacity-50"></i>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $totalKgbAlerts }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">&le; 90 Hari / Lewat</small>
            </div>
        </div>
    </div>

    <!-- Radar Pensiun -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-danger">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Radar Pensiun</small>
                    <i class="fas fa-user-clock text-danger opacity-50"></i>
                </div>
                <h3 class="fw-bold text-danger mb-0">{{ $totalPensiunRadar }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Usia &ge; 53 Tahun</small>
            </div>
        </div>
    </div>

    <!-- Tour of Duty -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-secondary" style="border-left-color: #7c3aed !important;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Tour of Duty</small>
                    <i class="fas fa-arrows-spin text-purple opacity-50" style="color: #7c3aed;"></i>
                </div>
                <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ $totalTourOfDuty }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">&gt; 4 Thn di Palembang</small>
            </div>
        </div>
    </div>

    <!-- Formasi Unit -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Seksi / Subbag</small>
                    <i class="fas fa-building-columns text-success opacity-50"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $unitStats->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Unit Kerja Aktif</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section Row (From referensi_desain) -->
<div class="row g-4 mb-4">
    <!-- Chart: Piramida Usia & Generasi -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-chart-column text-primary me-2"></i>Komposisi Generasi &amp; Kelompok Usia
                </h6>
                <span class="badge bg-light text-secondary border">33 Personil</span>
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
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-venus-mars text-primary me-2"></i>Rasio Gender Pegawai
                </h6>
                <span class="badge bg-light text-secondary border">L / P</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div style="height: 220px; position: relative;">
                    <canvas id="chartGender"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end">
                        <div class="small text-muted"><i class="fas fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $totalLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted"><i class="fas fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $totalPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Tables Row: Early Warning KGB & Tour of Duty (From referensi_desain) -->
<div class="row g-4 mb-4">
    <!-- Tabel EWS KGB -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-triangle-exclamation text-warning me-2"></i>Early Warning System — KGB
                </h6>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $totalKgbAlerts }} Pegawai</span>
            </div>
            <div class="card-body p-0">
                @if($kgbPegawai->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-circle-check fs-2 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada jadwal jatuh tempo KGB dalam 90 hari ke depan.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Pegawai</th>
                                    <th>Gol.</th>
                                    <th>TMT KGB</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kgbPegawai as $p)
                                    @php $st = $p->kgb_status; @endphp
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->tmt_kgb ? $p->tmt_kgb->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td class="text-end pe-4">
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
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-arrows-split-up-and-left text-primary me-2"></i>Indeks Tour of Duty (&gt; 4 Thn)
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $totalTourOfDuty }} Pegawai</span>
            </div>
            <div class="card-body p-0">
                @if($tourOfDutyPegawai->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-circle-check fs-2 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada pegawai definitif dengan penugasan lebih dari 4 tahun.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Pegawai</th>
                                    <th>Unit / Seksi</th>
                                    <th>TMT Palembang</th>
                                    <th class="text-end pe-4">Durasi Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tourOfDutyPegawai as $p)
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border">{{ $p->unitKerja?->singkatan ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $p->tmt_palembang ? $p->tmt_palembang->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td class="text-end pe-4">
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

<!-- Formasi Personil Unit Kerja (From referensi_desain) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-sitemap text-primary me-2"></i>Distribusi Formasi Personil per Seksi &amp; Subbagian
        </h6>
        <a href="{{ route('unit_kerja.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            Lihat Detail Formasi <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($unitStats as $unit)
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-4 border bg-body-tertiary h-100 transition-all">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary px-2 py-1" style="font-size: 0.7rem;">{{ $unit->singkatan ?: 'SEKSI' }}</span>
                            <span class="fw-bold text-dark fs-5">{{ $unit->total_pns }} <span class="small text-muted fs-6">ASN</span></span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $unit->nama_unit }}</h6>
                        <div class="text-muted small" style="font-size: 0.75rem;">KPKNL Palembang</div>
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
                        '#0c306b',
                        '#e11d48'
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
                        labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } }
                    }
                },
                cutout: '68%'
            }
        });
    });
</script>
@endpush
