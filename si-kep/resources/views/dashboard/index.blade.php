@extends('layouts.app')

@section('title', 'Dashboard — SI-KEP KPKNL Palembang')
@section('hero-title', 'Dashboard')
@section('hero-subtitle', 'Sistem Informasi Kepegawaian KPKNL Palembang.')

@section('content')

<!-- KPI Accent Summary Cards (Clickable Drill-down to Filter Modal) -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary card-clickable transition-all"
             onclick="showAggregateModal('total_pegawai', '', 'Seluruh Personil KPKNL Palembang')"
             title="Klik untuk melihat seluruh personil">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Personil</small>
                    <i class="fas fa-users text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $totalPegawai }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">KPKNL Palembang &bull; <span class="text-primary fw-semibold">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span></small>
            </div>
        </div>
    </div>

    <!-- ASN Definitif -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info card-clickable transition-all"
             onclick="showAggregateModal('pns_definitif', '', 'Aparatur Sipil Negara (ASN) Definitif')"
             title="Klik untuk melihat daftar ASN definitif">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">ASN Definitif</small>
                    <i class="fas fa-user-shield text-info opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $totalPns }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">
                    @if($pnsMismatchHris > 0)
                        <span class="text-success fw-semibold">{{ $pnsClearHris }} Clear</span> &bull; <span class="text-warning-emphasis fw-semibold">{{ $pnsMismatchHris }} Beda HRIS</span>
                    @else
                        <span class="text-success fw-semibold">100% Sesuai HRIS</span>
                    @endif
                    &bull; <span class="text-info fw-semibold">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span>
                </small>
            </div>
        </div>
    </div>

    <!-- Alert KGB -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning card-clickable transition-all"
             onclick="showAggregateModal('kgb_alert', '', 'Pegawai Jatuh Tempo Kenaikan Gaji Berkala (KGB)')"
             title="Klik untuk melihat daftar pegawai alert KGB">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Alert KGB</small>
                    <i class="fas fa-bell text-warning opacity-50"></i>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $totalKgbAlerts }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">&le; 90 Hari / Lewat &bull; <span class="text-warning fw-semibold">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span></small>
            </div>
        </div>
    </div>

    <!-- Radar Pensiun -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-danger card-clickable transition-all"
             onclick="showAggregateModal('pensiun_radar', '', 'Radar Pegawai Mendekati Batas Usia Pensiun (≥ 53 Tahun)')"
             title="Klik untuk melihat daftar radar pensiun">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Radar Pensiun</small>
                    <i class="fas fa-user-clock text-danger opacity-50"></i>
                </div>
                <h3 class="fw-bold text-danger mb-0">{{ $totalPensiunRadar }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Usia &ge; 53 Tahun &bull; <span class="text-danger fw-semibold">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span></small>
            </div>
        </div>
    </div>

    <!-- Tour of Duty -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-secondary card-clickable transition-all" style="border-left-color: #7c3aed !important;"
             onclick="showAggregateModal('tour_of_duty', '', 'Pegawai Tour of Duty (> 4 Tahun di Palembang)')"
             title="Klik untuk melihat daftar Tour of Duty">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Tour of Duty</small>
                    <i class="fas fa-arrows-spin text-purple opacity-50" style="color: #7c3aed;"></i>
                </div>
                <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ $totalTourOfDuty }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">&gt; 4 Thn di Palembang &bull; <span class="fw-semibold" style="color: #7c3aed;">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span></small>
            </div>
        </div>
    </div>

    <!-- Formasi Unit -->
    <div class="col-sm-6 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success card-clickable transition-all"
             onclick="showAggregateModal('total_pegawai', '', 'Distribusi Formasi Seluruh Unit Kerja')"
             title="Klik untuk melihat seluruh formasi">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Seksi / Subbag</small>
                    <i class="fas fa-building-columns text-success opacity-50"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $unitStats->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Unit Kerja Aktif &bull; <span class="text-success fw-semibold">Lihat <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i></span></small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section Row (With Interactive Click-to-Modal on chart elements) -->
<div class="row g-4 mb-4">
    <!-- Chart: Piramida Usia & Generasi -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-chart-column text-primary me-2"></i>Komposisi Generasi &amp; Kelompok Usia
                </h6>
                <span class="badge bg-light text-secondary border">Klik grafik untuk rincian data</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; cursor: pointer;">
                    <canvas id="chartGenerasi"></canvas>
                </div>
                <div class="row text-center mt-3 pt-3 border-top g-2">
                    <div class="col-3 card-clickable py-1 rounded transition-all" onclick="showAggregateModal('generasi', 'gen_z', 'Personil Generasi Z (< 28 Tahun)')">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN Z (&lt;28 Thn)</div>
                        <div class="fw-bold fs-5 text-info">{{ $genZ }}</div>
                    </div>
                    <div class="col-3 card-clickable py-1 rounded transition-all" onclick="showAggregateModal('generasi', 'milenial', 'Personil Generasi Milenial (28 - 43 Tahun)')">
                        <div class="small text-muted" style="font-size: 0.72rem;">MILENIAL (28-43 Thn)</div>
                        <div class="fw-bold fs-5 text-primary">{{ $milenial }}</div>
                    </div>
                    <div class="col-3 card-clickable py-1 rounded transition-all" onclick="showAggregateModal('generasi', 'gen_x', 'Personil Generasi X (44 - 59 Tahun)')">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN X (44-59 Thn)</div>
                        <div class="fw-bold fs-5 text-warning">{{ $genX }}</div>
                    </div>
                    <div class="col-3 card-clickable py-1 rounded transition-all" onclick="showAggregateModal('generasi', 'boomer', 'Personil Baby Boomer (≥ 60 Tahun)')">
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
                <span class="badge bg-light text-secondary border">Klik grafik untuk data</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div style="height: 220px; position: relative; cursor: pointer;">
                    <canvas id="chartGender"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end card-clickable py-1 rounded transition-all" onclick="showAggregateModal('gender', 'L', 'Personil Pegawai Laki-laki')">
                        <div class="small text-muted"><i class="fas fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $totalLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6 card-clickable py-1 rounded transition-all" onclick="showAggregateModal('gender', 'P', 'Personil Pegawai Perempuan')">
                        <div class="small text-muted"><i class="fas fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $totalPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($totalPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Tables Row: Early Warning KGB & Tour of Duty (Maks 10 Data per Laman) -->
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
                    <div class="table-responsive p-3 w-100">
                        <table class="table table-hover mb-0 align-middle w-100" id="tableEwsKgb" style="width: 100% !important;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-2">Nama Pegawai</th>
                                    <th>Gol.</th>
                                    <th>TMT KGB</th>
                                    <th class="text-end pe-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kgbPegawai as $p)
                                    @php $st = $p->kgb_status; @endphp
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil lengkap">
                                        <td class="ps-2">
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->tmt_kgb ? $p->tmt_kgb->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td class="text-end pe-2">
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
            <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Maks. 10 data per halaman</small>
                <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">
                    Direktori Pegawai <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
                    <div class="table-responsive p-3 w-100">
                        <table class="table table-hover mb-0 align-middle w-100" id="tableTourOfDuty" style="width: 100% !important;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-2">Nama Pegawai</th>
                                    <th>Unit / Seksi</th>
                                    <th>TMT Palembang</th>
                                    <th class="text-end pe-2">Durasi Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tourOfDutyPegawai as $p)
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil lengkap">
                                        <td class="ps-2">
                                            <div class="fw-bold text-dark">{{ $p->nama }}</div>
                                            <div class="small text-muted">{{ $p->nama_jabatan_raw }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border">{{ $p->unitKerja?->singkatan ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $p->tmt_palembang ? $p->tmt_palembang->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        <td class="text-end pe-2">
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
            <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Maks. 10 data per halaman</small>
                <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">
                    Direktori Pegawai <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Chart Generasi with Click-to-Modal
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
                },
                onClick: (evt, activeElements) => {
                    if (activeElements.length > 0) {
                        const idx = activeElements[0].index;
                        const keys = ['gen_z', 'milenial', 'gen_x', 'boomer'];
                        const titles = [
                            'Personil Generasi Z (< 28 Tahun)',
                            'Personil Generasi Milenial (28 - 43 Tahun)',
                            'Personil Generasi X (44 - 59 Tahun)',
                            'Personil Baby Boomer (≥ 60 Tahun)'
                        ];
                        showAggregateModal('generasi', keys[idx], titles[idx]);
                    }
                }
            }
        });

        // Chart Gender with Click-to-Modal
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
                    hoverOffset: 8,
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
                cutout: '68%',
                onClick: (evt, activeElements) => {
                    if (activeElements.length > 0) {
                        const idx = activeElements[0].index;
                        if (idx === 0) {
                            showAggregateModal('gender', 'L', 'Personil Pegawai Laki-laki');
                        } else {
                            showAggregateModal('gender', 'P', 'Personil Pegawai Perempuan');
                        }
                    }
                }
            }
        });

        // DataTables Paginasi Maks. 10 data untuk Early Warning KGB
        if ($('#tableEwsKgb').length && !$.fn.DataTable.isDataTable('#tableEwsKgb')) {
            $('#tableEwsKgb').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: false,
                autoWidth: false,
                responsive: true,
                info: true,
                language: {
                    info: "<small class='text-muted'>Menampilkan _START_-_END_ dari _TOTAL_ pegawai</small>",
                    infoEmpty: "<small class='text-muted'>0 data</small>",
                    paginate: {
                        previous: "<i class='fas fa-chevron-left'></i>",
                        next: "<i class='fas fa-chevron-right'></i>"
                    }
                }
            });
        }

        // DataTables Paginasi Maks. 10 data untuk Tour of Duty
        if ($('#tableTourOfDuty').length && !$.fn.DataTable.isDataTable('#tableTourOfDuty')) {
            $('#tableTourOfDuty').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: false,
                autoWidth: false,
                responsive: true,
                info: true,
                language: {
                    info: "<small class='text-muted'>Menampilkan _START_-_END_ dari _TOTAL_ pegawai</small>",
                    infoEmpty: "<small class='text-muted'>0 data</small>",
                    paginate: {
                        previous: "<i class='fas fa-chevron-left'></i>",
                        next: "<i class='fas fa-chevron-right'></i>"
                    }
                }
            });
        }
    });
</script>
@endpush
