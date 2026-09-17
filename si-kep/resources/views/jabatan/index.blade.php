@extends('layouts.app')

@section('title', 'Struktur & Jabatan — SI-KEP KPKNL Palembang')
@section('hero-title', 'Struktur & Formasi Jabatan')
@section('hero-subtitle', 'Pemetaan aparatur berdasarkan jenjang struktural, fungsional tertentu (Pelelang & Penilai), pelaksana, serta hierarki kelas jabatan (grading).')

@section('content')

<!-- KPI Jabatan Row (From referensi_desain/desain2.html) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Pejabat Struktural</small>
                    <i class="fas fa-user-tie text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $struktural->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Eselon III.a &amp; IV.a</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Fungsional Tertentu</small>
                    <i class="fas fa-gavel text-info opacity-50"></i>
                </div>
                <h3 class="fw-bold text-info mb-0">{{ $fungsional->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pelelang &amp; Penilai</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Staf Pelaksana</small>
                    <i class="fas fa-users-gear text-success opacity-50"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $pelaksana->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pengolah Data &amp; Teknis</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Sebaran Grading</small>
                    <i class="fas fa-layer-group text-warning opacity-50"></i>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $grades->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Grade 7 s.d. Grade 18</small>
            </div>
        </div>
    </div>
</div>

<!-- Sebaran Job Grading (From referensi_desain) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-chart-simple text-primary me-2"></i>Distribusi Kelas Jabatan (Job Grading Kemenkeu)
        </h6>
        <span class="badge bg-light text-secondary border">Tingkat Remunerasi</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($grades as $g)
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="p-3 rounded-4 border bg-body-tertiary text-center h-100">
                        <span class="badge bg-primary-subtle text-primary mb-1" style="font-size: 0.72rem;">Kelas Jabatan</span>
                        <div class="fw-bold fs-4 text-primary">Grade {{ $g->job_grade }}</div>
                        <div class="text-muted small fw-semibold">{{ $g->total }} Pegawai</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Tabbed Personnel List by Category (From referensi_desain) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-0 border-bottom">
        <ul class="nav nav-tabs border-0 px-3 pt-2" id="jabatanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small px-4 py-3" id="tab-struktural-tab" data-bs-toggle="tab" data-bs-target="#tab-struktural" type="button" role="tab">
                    <i class="fas fa-user-tie me-1 text-primary"></i> Struktural ({{ $struktural->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small px-4 py-3" id="tab-fungsional-tab" data-bs-toggle="tab" data-bs-target="#tab-fungsional" type="button" role="tab">
                    <i class="fas fa-gavel me-1 text-primary"></i> Fungsional ({{ $fungsional->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small px-4 py-3" id="tab-pelaksana-tab" data-bs-toggle="tab" data-bs-target="#tab-pelaksana" type="button" role="tab">
                    <i class="fas fa-users-gear me-1 text-primary"></i> Pelaksana ({{ $pelaksana->count() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="jabatanTabContent">
            <!-- 1. Struktural -->
            <div class="tab-pane fade show active" id="tab-struktural" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">No</th>
                                <th>Pejabat &amp; NIP</th>
                                <th>Jabatan Struktural</th>
                                <th>Seksi / Unit</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-4" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($struktural as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-4">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem;">
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
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
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
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">No</th>
                                <th>Pejabat Fungsional &amp; NIP</th>
                                <th>Jabatan Fungsional</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-4" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fungsional as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-4">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #1e40af, #3b82f6);">
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
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
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
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">No</th>
                                <th>Pelaksana &amp; NIP</th>
                                <th>Jabatan Pelaksana</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-4" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelaksana as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-4">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #059669, #10b981);">
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
                                            Staf Pelaksana
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
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
