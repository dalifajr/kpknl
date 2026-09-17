@extends('layouts.app')

@section('title', 'Diagram & Analitika Kepegawaian — SI-KEP KPKNL Palembang')
@section('hero-title', 'Diagram & Visualisasi Analitika')
@section('hero-subtitle', 'Pusat eksplorasi data grafis interaktif mengenai demografi, tingkat pendidikan, almamater, hierarki kepangkatan, dan masa penugasan aparatur.')

@section('content')

<!-- Grid Diagram 1: Generasi & Gender -->
<div class="row g-4 mb-4">
    <!-- 1. Piramida Generasi -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-people-group text-primary me-2"></i>Distribusi Kelompok Generasi &amp; Usia
                </h6>
                <span class="badge bg-light text-secondary border">{{ $totalPegawai }} Personil</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 290px; cursor: pointer;">
                    <canvas id="chartGenerasiFull"></canvas>
                </div>
                <div class="row text-center mt-3 pt-3 border-top g-2">
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'gen_z', 'Personil Generasi Z (< 28 Thn)')" title="Klik untuk melihat personil Generasi Z">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN Z (&lt;28 Thn)</div>
                        <div class="fw-bold fs-5 text-info">{{ $genZ }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'milenial', 'Personil Generasi Milenial (28-43 Thn)')" title="Klik untuk melihat personil Generasi Milenial">
                        <div class="small text-muted" style="font-size: 0.72rem;">MILENIAL (28-43)</div>
                        <div class="fw-bold fs-5 text-primary">{{ $milenial }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'gen_x', 'Personil Generasi X (44-59 Thn)')" title="Klik untuk melihat personil Generasi X">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN X (44-59)</div>
                        <div class="fw-bold fs-5 text-warning">{{ $genX }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'boomer', 'Personil Baby Boomer (≥ 60 Thn)')" title="Klik untuk melihat personil Baby Boomer">
                        <div class="small text-muted" style="font-size: 0.72rem;">BOOMER (&ge;60)</div>
                        <div class="fw-bold fs-5 text-danger">{{ $boomer }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Gender Ratio -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-venus-mars text-primary me-2"></i>Komposisi Gender
                </h6>
                <span class="badge bg-light text-secondary border">L / P</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div style="height: 250px; cursor: pointer;">
                    <canvas id="chartGenderFull"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end cursor-pointer transition-all p-2 rounded" onclick="showAggregateModal('gender', 'L', 'Personil Pegawai Laki-laki')" title="Klik untuk melihat pegawai laki-laki">
                        <div class="small text-muted"><i class="fas fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $genderLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6 cursor-pointer transition-all p-2 rounded" onclick="showAggregateModal('gender', 'P', 'Personil Pegawai Perempuan')" title="Klik untuk melihat pegawai perempuan">
                        <div class="small text-muted"><i class="fas fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $genderPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 2: Pangkat & Pendidikan -->
<div class="row g-4 mb-4">
    <!-- 3. Distribusi Golongan -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-ranking-star text-primary me-2"></i>Sebaran Pangkat &amp; Golongan Ruang
                </h6>
                <span class="badge bg-light text-secondary border">Jenjang Pangkat</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; cursor: pointer;">
                    <canvas id="chartGolongan"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang grafik untuk melihat personil pada golongan tersebut</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tingkat Pendidikan -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-graduation-cap text-primary me-2"></i>Kualifikasi Tingkat Pendidikan Terakhir
                </h6>
                <span class="badge bg-light text-secondary border">Jenjang Kelulusan</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; cursor: pointer;">
                    <canvas id="chartPendidikan"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik juring diagram untuk melihat personil jenjang pendidikan tersebut</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 3: Almamater & Tour of Duty -->
<div class="row g-4 mb-4">
    <!-- 5. Top Perguruan Tinggi / Universitas -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-building-columns text-primary me-2"></i>Top Almamater / Perguruan Tinggi Terbanyak
                </h6>
                <span class="badge bg-light text-secondary border">Kampus Kelulusan</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartUniv"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang kampus untuk melihat personil alumni</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Tour of Duty (Masa Tugas di Palembang) -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-arrows-split-up-and-left text-primary me-2"></i>Masa Penugasan di Palembang (Tour of Duty)
                </h6>
                <span class="badge bg-light text-secondary border">Kesiapan Mutasi</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartTourOfDuty"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik kategori donat untuk melihat personil masa tugas tersebut</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 4: Formasi Unit Kerja & Job Grading (Dipindah dari menu Jabatan) -->
<div class="row g-4 mb-4">
    <!-- 7. Formasi Unit Kerja -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-sitemap text-primary me-2"></i>Formasi Kekuatan Personil per Seksi &amp; Subbagian
                </h6>
                <span class="badge bg-light text-secondary border">Total Personil</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartUnit"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang unit kerja untuk memfilter daftar aparatur</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Distribusi Kelas Jabatan (Job Grading Kemenkeu - Pindahan dari Jabatan) -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-chart-pie" style="font-size: 0.85rem;"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">
                        Distribusi Kelas Jabatan (Job Grading Kemenkeu)
                    </h6>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                    Remunerasi DJKN
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <!-- Doughnut Canvas Container with Central Label -->
                <div class="position-relative my-auto text-center" style="min-height: 220px; cursor: pointer;">
                    <div style="position: relative; height: 210px; max-width: 230px; margin: 0 auto;">
                        <canvas id="chartGradingDoughnut"></canvas>
                        <!-- Central Hole Overlay -->
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                            <div class="fs-4 fw-bold text-dark lh-1">{{ $grades->sum('total') }}</div>
                            <small class="text-muted fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.05em;">PEGAWAI</small>
                            <div class="text-primary fw-bold" style="font-size: 0.62rem;">{{ $grades->count() }} GRADES</div>
                        </div>
                    </div>
                </div>

                <!-- Clean Horizontal Grade Chips (Clickable) -->
                <div class="mt-3 pt-3 border-top">
                    <div class="small fw-bold text-muted text-uppercase mb-2 text-center" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        Sebaran Personil per Kelas Jabatan (Grade) &bull; Klik untuk detail
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @php
                            $palette = [
                                '#0c306b', '#1e40af', '#2563eb', '#3b82f6', '#60a5fa',
                                '#059669', '#10b981', '#34d399', '#d97706', '#f59e0b',
                                '#ef4444', '#8b5cf6', '#a855f7', '#6366f1'
                            ];
                        @endphp
                        @foreach($grades as $idx => $g)
                            @php $color = $palette[$idx % count($palette)]; @endphp
                            <div class="d-inline-flex align-items-center gap-2 bg-light border px-2 py-1 rounded-pill small cursor-pointer transition-all"
                                 onclick="showAggregateModal('job_grade', '{{ $g->job_grade }}', 'Personil Kelas Jabatan Grade {{ $g->job_grade }}')"
                                 title="Klik untuk melihat pegawai Grade {{ $g->job_grade }}">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: {{ $color }}; display: inline-block;"></span>
                                <span class="fw-semibold text-dark" style="font-size: 0.75rem;">Grade {{ $g->job_grade }}</span>
                                <span class="badge bg-white text-dark border px-2 py-0 fw-bold" style="font-size: 0.72rem;">{{ $g->total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Grid Diagram 5: Peringkat Masa Tugas Eselon IV -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fas fa-award text-warning fs-6"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            Peringkat Masa Tugas Eselon IV
                        </h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Rekam Jejak Masa Penugasan Terlama Personil di Unit Eselon IV (Seksi / Subbagian)</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5">
                        <i class="fas fa-users me-1"></i> {{ $topUeIv->count() }} Personil Eselon IV
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                @if($topPersonilUeIv)
                    <!-- Top Highlight & Metrik Statistik Row -->
                    <div class="row g-4 mb-4">
                        <!-- Highlight Terlama Card -->
                        <div class="col-lg-6">
                            <div class="p-3.5 rounded-3 bg-primary-subtle border border-primary-subtle text-dark h-100 position-relative overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                        <i class="fas fa-crown me-1 text-warning-emphasis"></i> Masa Tugas Terlama di Seksi
                                    </span>
                                    <span class="badge bg-white text-primary border font-monospace px-2.5 py-1">
                                        {{ $topPersonilUeIv->lama_ue_iv_bulan }} Bulan
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="avatar-initial shadow-sm" style="width: 50px; height: 50px; font-size: 1.1rem; background: linear-gradient(135deg, #0c306b, #1e40af);">
                                        {{ strtoupper(substr($topPersonilUeIv->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-primary mb-1 cursor-pointer hover-underline" onclick="showPegawaiDetail({{ $topPersonilUeIv->id }})" title="Klik untuk melihat profil lengkap">
                                            {{ $topPersonilUeIv->display_name }}
                                        </h6>
                                        <div class="small text-muted font-monospace">{{ $topPersonilUeIv->nip ?: '-' }}</div>
                                        <div class="small fw-semibold text-dark mt-0.5">
                                            <i class="fas fa-building-user text-secondary me-1"></i> {{ $topPersonilUeIv->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 pt-2 border-top border-primary-subtle">
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">TMT Seksi (UE IV):</small>
                                        <span class="fw-semibold small text-dark">{{ $topPersonilUeIv->tmt_ue_iv ?: '-' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Masa Penugasan:</small>
                                        <span class="fw-bold text-danger-emphasis small">{{ $topPersonilUeIv->lama_ue_iv_formatted }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KPI Statistik UE IV -->
                        <div class="col-lg-6">
                            <div class="row g-3 h-100">
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-center text-center">
                                        <div class="small text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                            <i class="fas fa-clock text-primary me-1"></i> RATA-RATA UE IV
                                        </div>
                                        <div class="fw-bold fs-4 text-primary">{{ $avgUeIvFormatted }}</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Durasi rata-rata aparatur di seksi</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-center text-center">
                                        <div class="small text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                            <i class="fas fa-users-gear text-info me-1"></i> TOTAL TERDATA
                                        </div>
                                        <div class="fw-bold fs-4 text-dark">{{ $totalWithUeIv }} Aparatur</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Memiliki data TMT Eselon IV</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-circle-info text-primary"></i>
                                            <div class="small text-muted" style="font-size: 0.75rem; line-height: 1.4;">
                                                Data TMT UE IV (Unit Eselon IV) digunakan pimpinan untuk mengevaluasi rotasi berkala, penyegaran suasana kerja, dan penataan formasi kompetensi personil pada seksi terkait di KPKNL Palembang.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Peringkat Masa Tugas Eselon IV -->
                    <div class="border rounded-3 overflow-hidden bg-white shadow-xs">
                        <div class="bg-light px-3 py-2.5 border-bottom d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-list-ol me-1 text-primary"></i> Daftar Urutan Masa Tugas Personil (Terlama ke Terpendek)
                            </span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Klik baris untuk melihat profil personil</span>
                        </div>
                        <div class="table-responsive" style="max-height: 420px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top" style="z-index: 1;">
                                    <tr>
                                        <th class="ps-3 text-center" style="width: 50px;">Peringkat</th>
                                        <th>Nama Pegawai &amp; NIP</th>
                                        <th>Unit Kerja / Seksi</th>
                                        <th>TMT Eselon IV</th>
                                        <th>Masa Tugas (Bulan)</th>
                                        <th>Lama Penugasan</th>
                                        <th class="text-end pe-3" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topUeIv as $idx => $p)
                                        <tr class="cursor-pointer transition-all" onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk membuka profil {{ $p->nama }}">
                                            <td class="ps-3 text-center">
                                                @if($idx === 0)
                                                    <span class="badge bg-warning text-dark fw-bold rounded-circle p-2" style="width: 28px; height: 28px; line-height: 12px;" title="Peringkat 1">1</span>
                                                @elseif($idx === 1)
                                                    <span class="badge bg-secondary-subtle text-dark fw-bold rounded-circle p-2 border" style="width: 28px; height: 28px; line-height: 12px;" title="Peringkat 2">2</span>
                                                @elseif($idx === 2)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold rounded-circle p-2 border border-warning-subtle" style="width: 28px; height: 28px; line-height: 12px;" title="Peringkat 3">3</span>
                                                @else
                                                    <span class="text-muted fw-semibold small">#{{ $idx + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    @if($p->avatar_url)
                                                        <img src="{{ asset('storage/' . $p->avatar_url) }}" alt="{{ $p->nama }}" class="rounded-circle border" style="width: 34px; height: 34px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-initial shadow-xs" style="width: 34px; height: 34px; font-size: 0.75rem;">
                                                            {{ strtoupper(substr($p->nama, 0, 2)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold text-dark hover-underline">{{ $p->display_name }}</div>
                                                        <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1 fw-medium" style="font-size: 0.75rem;">
                                                    {{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-monospace small text-muted">{{ $p->tmt_ue_iv ?: '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                                                    {{ $p->lama_ue_iv_bulan }} Bulan
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $idx === 0 ? 'text-danger-emphasis' : 'text-primary' }} small">
                                                    {{ $p->lama_ue_iv_formatted }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 0.7rem;" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }})">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fs-2 mb-2 d-block opacity-50"></i>
                        <h6>Belum Ada Data Penugasan Eselon IV</h6>
                        <p class="small text-muted mb-0">Personil dengan tanggal TMT Eselon IV akan otomatis tercatat dan diurutkan di sini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Chart Generasi Full
        new Chart(document.getElementById('chartGenerasiFull').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Gen Z (<28 th)', 'Milenial (28-43 th)', 'Gen X (44-59 th)', 'Boomer (≥60 th)'],
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: [{{ $genZ }}, {{ $milenial }}, {{ $genX }}, {{ $boomer }}],
                    backgroundColor: ['#38bdf8', '#0284c7', '#f59e0b', '#ef4444'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const keys = ['gen_z', 'milenial', 'gen_x', 'boomer'];
                        const titles = ['Generasi Z (< 28 Thn)', 'Generasi Milenial (28 - 43 Thn)', 'Generasi X (44 - 59 Thn)', 'Baby Boomer (≥ 60 Thn)'];
                        showAggregateModal('generasi', keys[idx], titles[idx]);
                    }
                }
            }
        });

        // 2. Chart Gender Full
        new Chart(document.getElementById('chartGenderFull').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [{{ $genderLaki }}, {{ $genderPerempuan }}],
                    backgroundColor: ['#0c306b', '#e11d48'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                cutout: '68%',
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const val = idx === 0 ? 'L' : 'P';
                        const title = idx === 0 ? 'Personil Pegawai Laki-laki' : 'Personil Pegawai Perempuan';
                        showAggregateModal('gender', val, title);
                    }
                }
            }
        });

        // 3. Chart Golongan
        const golLabels = {!! json_encode($golonganDist->pluck('golongan_ruang')) !!};
        const golData = {!! json_encode($golonganDist->pluck('pegawai_count')) !!};

        new Chart(document.getElementById('chartGolongan').getContext('2d'), {
            type: 'bar',
            data: {
                labels: golLabels,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: golData,
                    backgroundColor: '#1e40af',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const gol = golLabels[idx];
                        showAggregateModal('golongan', gol, 'Pegawai Golongan Ruang ' + gol);
                    }
                }
            }
        });

        // 4. Chart Pendidikan
        const pendLabels = {!! json_encode($pendidikanDist->pluck('pendidikan_terakhir')) !!};
        const pendData = {!! json_encode($pendidikanDist->pluck('total')) !!};

        new Chart(document.getElementById('chartPendidikan').getContext('2d'), {
            type: 'pie',
            data: {
                labels: pendLabels,
                datasets: [{
                    data: pendData,
                    backgroundColor: ['#0284c7', '#10b981', '#f59e0b', '#6366f1', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const pend = pendLabels[idx];
                        showAggregateModal('pendidikan', pend, 'Pegawai Kualifikasi Pendidikan ' + pend);
                    }
                }
            }
        });

        // 5. Chart Universitas
        const univLabels = {!! json_encode($univDist->pluck('nama_universitas')) !!};
        const univData = {!! json_encode($univDist->pluck('total')) !!};

        new Chart(document.getElementById('chartUniv').getContext('2d'), {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: univLabels,
                datasets: [{
                    label: 'Lulusan',
                    data: univData,
                    backgroundColor: '#0c306b',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const u = univLabels[idx];
                        showAggregateModal('kampus', u, 'Almamater Kampus ' + u);
                    }
                }
            }
        });

        // 6. Chart Tour of Duty
        new Chart(document.getElementById('chartTourOfDuty').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['< 1 Tahun', '1 - 2 Tahun', '3 - 4 Tahun', '> 4 Tahun (Siap Rotasi)'],
                datasets: [{
                    data: [{{ $todUnder1 }}, {{ $tod1to2 }}, {{ $tod2to4 }}, {{ $todOver4 }}],
                    backgroundColor: ['#10b981', '#0ea5e9', '#f59e0b', '#dc2626'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                cutout: '62%',
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const keys = ['lt_1', '1_2', '2_4', 'gt_4'];
                        const titles = ['Masa Tugas < 1 Tahun', 'Masa Tugas 1 - 2 Tahun', 'Masa Tugas 3 - 4 Tahun', 'Masa Tugas > 4 Tahun (Siap Rotasi)'];
                        showAggregateModal('masa_tugas', keys[idx], titles[idx]);
                    }
                }
            }
        });

        // 7. Chart Unit Kerja
        const unitLabels = {!! json_encode($unitDist->pluck('singkatan')) !!};
        const unitData = {!! json_encode($unitDist->pluck('pegawai_count')) !!};

        new Chart(document.getElementById('chartUnit').getContext('2d'), {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [{
                    label: 'Jumlah Personil',
                    data: unitData,
                    backgroundColor: '#1e40af',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const unitName = unitLabels[idx];
                        showAggregateModal('unit_kerja', unitName, 'Personil Formasi Unit: ' + unitName);
                    }
                }
            }
        });

        // 8. Chart Grading Doughnut (Dipindah dari Jabatan)
        const ctxGrading = document.getElementById('chartGradingDoughnut');
        if (ctxGrading) {
            const gradeLabels = {!! json_encode($grades->map(fn($g) => 'Grade ' . $g->job_grade)) !!};
            const gradeData = {!! json_encode($grades->pluck('total')) !!};

            const colors = [
                '#0c306b', '#1e40af', '#2563eb', '#3b82f6', '#60a5fa',
                '#059669', '#10b981', '#34d399', '#d97706', '#f59e0b',
                '#ef4444', '#8b5cf6', '#a855f7', '#6366f1'
            ];

            new Chart(ctxGrading.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: gradeLabels,
                    datasets: [{
                        data: gradeData,
                        backgroundColor: colors.slice(0, gradeLabels.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} Pegawai (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '72%',
                    onClick: (evt, elements) => {
                        if (elements.length > 0) {
                            const idx = elements[0].index;
                            const rawGrade = gradeLabels[idx].replace('Grade ', '');
                            showAggregateModal('job_grade', rawGrade, 'Personil Kelas Jabatan (Grade ' + rawGrade + ')');
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
