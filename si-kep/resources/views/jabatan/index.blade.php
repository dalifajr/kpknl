@extends('layouts.app')

@section('title', 'Struktur & Jabatan — SIMPATIK KPKNL Palembang')
@section('hero-title', 'Struktur & Formasi Jabatan')
@section('hero-subtitle', 'Pemetaan aparatur berdasarkan jenjang struktural, fungsional tertentu (Pelelang & Penilai), pelaksana, serta hierarki kelas jabatan (grading).')

@section('content')

<!-- KPI Jabatan Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card" style="--card-accent: #0c306b; --icon-bg: #eff6ff; --icon-color: #0c306b;">
            <div>
                <div class="kpi-title">Pejabat Struktural</div>
                <div class="kpi-val">{{ $struktural->count() }}</div>
                <div class="kpi-desc">Eselon III.a &amp; IV.a</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card" style="--card-accent: #1e40af; --icon-bg: #dbeafe; --icon-color: #1e40af;">
            <div>
                <div class="kpi-title">Fungsional Tertentu</div>
                <div class="kpi-val text-primary">{{ $fungsional->count() }}</div>
                <div class="kpi-desc">Pelelang &amp; Penilai</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-gavel"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card" style="--card-accent: #10b981; --icon-bg: #d1fae5; --icon-color: #059669;">
            <div>
                <div class="kpi-title">Staf Pelaksana</div>
                <div class="kpi-val text-success">{{ $pelaksana->count() }}</div>
                <div class="kpi-desc">Pengolah Data &amp; Operasional</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-users-gear"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card" style="--card-accent: #f59e0b; --icon-bg: #fef3c7; --icon-color: #d97706;">
            <div>
                <div class="kpi-title">Sebaran Grading</div>
                <div class="kpi-val text-warning">{{ $grades->count() }}</div>
                <div class="kpi-desc">Grade 7 s.d. Grade 18</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
    </div>
</div>

<!-- Sebaran Job Grading -->
<div class="card card-custom mb-4">
    <div class="card-header-clean">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-simple text-primary"></i>
            <span>Distribusi Kelas Jabatan (Job Grading Kemenkeu)</span>
        </div>
        <span class="badge bg-light text-muted border">Tingkat Remunerasi</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($grades as $g)
                <div class="col-6 col-md-3 col-xl-1-5">
                    <div class="p-3 rounded-3 border bg-light text-center">
                        <span class="badge bg-navy mb-1" style="font-size: 0.72rem;">Kelas Jabatan</span>
                        <div class="fw-bold fs-4 text-primary">Grade {{ $g->job_grade }}</div>
                        <div class="text-muted small fw-semibold">{{ $g->total }} Pegawai</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Tabbed Personnel List by Category -->
<div class="card card-custom">
    <div class="card-header-clean p-0 border-bottom">
        <ul class="nav nav-tabs border-0 px-3 pt-3" id="jabatanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small px-4 py-3" id="tab-struktural-tab" data-bs-toggle="tab" data-bs-target="#tab-struktural" type="button" role="tab">
                    <i class="fa-solid fa-user-tie me-1 text-primary"></i> Struktural ({{ $struktural->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small px-4 py-3" id="tab-fungsional-tab" data-bs-toggle="tab" data-bs-target="#tab-fungsional" type="button" role="tab">
                    <i class="fa-solid fa-gavel me-1 text-primary"></i> Fungsional ({{ $fungsional->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small px-4 py-3" id="tab-pelaksana-tab" data-bs-toggle="tab" data-bs-target="#tab-pelaksana" type="button" role="tab">
                    <i class="fa-solid fa-users-gear me-1 text-primary"></i> Pelaksana ({{ $pelaksana->count() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="jabatanTabContent">
            <!-- 1. Struktural -->
            <div class="tab-pane fade show active" id="tab-struktural" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Pejabat &amp; NIP</th>
                                <th>Jabatan Struktural</th>
                                <th>Seksi / Unit</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($struktural as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                            {{ str_contains(strtolower($p->nama_jabatan_raw), 'kepala kpknl') ? 'Eselon III.a' : 'Eselon IV.a' }}
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</td>
                                    <td>
                                        <span class="badge badge-subtle badge-navy">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Fungsional -->
            <div class="tab-pane fade" id="tab-fungsional" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Pejabat Fungsional &amp; NIP</th>
                                <th>Jabatan Fungsional</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fungsional as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.8rem; background: linear-gradient(135deg, #1e40af, #3b82f6);">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.68rem;">
                                            Fungsional Tertentu
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit }}</td>
                                    <td>
                                        <span class="badge badge-subtle badge-navy">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Pelaksana -->
            <div class="tab-pane fade" id="tab-pelaksana" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Pelaksana &amp; NIP</th>
                                <th>Jabatan Pelaksana</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelaksana as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.8rem; background: linear-gradient(135deg, #059669, #10b981);">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                            Pelaksana
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit }}</td>
                                    <td>
                                        <span class="badge badge-subtle badge-navy">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
