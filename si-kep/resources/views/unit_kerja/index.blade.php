@extends('layouts.app')

@section('title', 'Formasi & Distribusi Unit Kerja — SI-KEP KPKNL Palembang')
@section('hero-title', 'Formasi & Distribusi Unit Kerja')
@section('hero-subtitle', 'Monitoring peta penempatan personil aparatur sipil negara pada Subbagian dan Seksi operasional KPKNL Palembang.')

@section('content')

<!-- Quick Summary Stats Bar (Clickable Drill-Down) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary card-clickable transition-all" onclick="showAggregateModal('total_pegawai', '', 'Seluruh Personil KPKNL Palembang')" title="Klik untuk melihat seluruh personil">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Kekuatan SDM</small>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="fas fa-users" style="font-size: 0.75rem;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $totalPegawai }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Seluruh Personil Terdata</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success card-clickable transition-all" onclick="showAggregateModal('pns_definitif', '', 'Aparatur Sipil Negara (ASN) Definitif')" title="Klik untuk melihat pegawai ASN PNS">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">PNS Definitif</small>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="fas fa-user-check" style="font-size: 0.75rem;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $totalPns }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Aparatur Sipil Negara</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning card-clickable transition-all" onclick="showAggregateModal('all', '', 'Pegawai Non-PNS (PPNPN)')" title="Klik untuk melihat personil PPNPN">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">PPNPN</small>
                    <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="fas fa-user-shield" style="font-size: 0.75rem;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $totalPpnpn }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pegawai Non-PNS</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Seksi &amp; Subbagian</small>
                    <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="fas fa-sitemap" style="font-size: 0.75rem;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-info mb-0">{{ $unitKerjas->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Unit Kerja Organisasi</small>
            </div>
        </div>
    </div>
</div>

<!-- Master Tabbed Table: Formasi per Unit & Subbagian -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-sitemap text-primary"></i> Formasi Aparatur per Subbagian &amp; Seksi
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                Total {{ $allPegawai->count() }} Pegawai
            </span>
        </div>
        <ul class="nav nav-pills gap-1 p-1 bg-light rounded-3 border flex-wrap" id="unitTab" role="tablist">
            <!-- Tab 0: Semua Unit -->
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-semibold small px-3 py-2" id="tab-all-tab" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab">
                    <i class="fas fa-layer-group me-1"></i> Semua Unit <span class="badge bg-white text-dark rounded-pill ms-1">{{ $allPegawai->count() }}</span>
                </button>
            </li>

            <!-- Tabs per Unit Kerja -->
            @foreach($unitKerjas as $unit)
                @php
                    $unitIcons = [
                        'Pimpinan' => 'fa-crown',
                        'Subbagian Umum' => 'fa-folder-open',
                        'Pengelolaan Kekayaan Negara' => 'fa-landmark',
                        'Pelayanan Penilaian' => 'fa-scale-balanced',
                        'Piutang Negara' => 'fa-receipt',
                        'Hukum dan Informasi' => 'fa-gavel',
                        'Kepatuhan Internal' => 'fa-shield-halved',
                        'Pejabat Lelang' => 'fa-hammer'
                    ];
                    $icon = 'fa-building';
                    foreach($unitIcons as $key => $ic) {
                        if(str_contains(strtolower($unit->nama_unit), strtolower($key))) {
                            $icon = $ic;
                            break;
                        }
                    }
                @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold small px-3 py-2" id="tab-unit-{{ $unit->id }}-tab" data-bs-toggle="pill" data-bs-target="#tab-unit-{{ $unit->id }}" type="button" role="tab">
                        <i class="fas {{ $icon }} me-1"></i> {{ $unit->singkatan ?: $unit->nama_unit }} <span class="badge bg-white text-dark rounded-pill ms-1">{{ $unit->pegawai->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="card-body p-3">
        <div class="tab-content" id="unitTabContent">
            <!-- 1. Tab Semua Unit -->
            <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle table-unit-data w-100" id="tableUnitAll" style="width: 100% !important;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Personil &amp; NIP</th>
                                <th>Jabatan Definitif</th>
                                <th>Unit / Subbagian</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th>Status KGB</th>
                                <th class="text-end pe-3" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allPegawai as $p)
                                @php $kgb = $p->kgb_status; @endphp
                                <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil lengkap">
                                    <td class="text-center fw-bold text-muted ps-3">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">{{ $p->jenis_jabatan ?: 'Fungsional Umum' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->unitKerja?->singkatan ?: ($p->unitKerja?->nama_unit ?: 'KPKNL Palembang') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade ?: '-' }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td>
                                        <span class="badge bg-{{ $kgb['badge'] }} px-2 py-1" style="font-size: 0.68rem;">
                                            {{ $kgb['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
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

            <!-- 2. Tabs per Unit Kerja -->
            @foreach($unitKerjas as $unit)
                <div class="tab-pane fade" id="tab-unit-{{ $unit->id }}" role="tabpanel">
                    <!-- Unit Executive Header Banner -->
                    <div class="p-3 mb-3 rounded-3 bg-light border d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-2 bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                                <i class="fas fa-building-flag fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $unit->nama_unit }}</h6>
                                <div class="text-muted small">
                                    <span class="me-3"><i class="fas fa-users text-primary me-1"></i> Formasi: <strong>{{ $unit->pegawai->count() }} Personil</strong></span>
                                    <span><i class="fas fa-chart-pie text-success me-1"></i> Rasio: <strong>{{ $totalPegawai > 0 ? round(($unit->pegawai->count() / $totalPegawai) * 100, 1) : 0 }}%</strong> dari total kantor</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pimpinan / Pejabat Seksi -->
                        @php
                            $pimpinan = $unit->pegawai->first(function($p) {
                                $jab = strtolower($p->nama_jabatan_raw);
                                return str_contains($jab, 'kepala') || str_contains($jab, 'kasubbag');
                            });
                        @endphp
                        @if($pimpinan)
                            <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border">
                                <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.75rem; background: linear-gradient(135deg, #0c306b, #2563eb);">
                                    {{ strtoupper(substr($pimpinan->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Pimpinan Unit</small>
                                    <span class="fw-bold text-dark small">{{ $pimpinan->display_name }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Unit Table -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-unit-data w-100" id="tableUnit{{ $unit->id }}" style="width: 100% !important;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Personil &amp; NIP</th>
                                    <th>Jabatan Definitif</th>
                                    <th>Gol. / Grade</th>
                                    <th>Masa Tugas Palembang</th>
                                    <th>Status KGB</th>
                                    <th class="text-end pe-3" style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unit->pegawai as $p)
                                    @php $kgb = $p->kgb_status; @endphp
                                    <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil">
                                        <td class="text-center fw-bold text-muted ps-3">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($p->nama, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                    <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-primary">{{ $p->nama_jabatan_raw }}</div>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">{{ $p->jenis_jabatan ?: 'Fungsional Umum' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                            <span class="small text-muted ms-1">Grade {{ $p->job_grade ?: '-' }}</span>
                                        </td>
                                        <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                        <td>
                                            <span class="badge bg-{{ $kgb['badge'] }} px-2 py-1" style="font-size: 0.68rem;">
                                                {{ $kgb['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                            <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fs-3 mb-2 d-block opacity-50"></i>
                                            Belum ada personil definitif yang terdaftar pada unit ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Paginasi Tabel DataTables per Tab (Maks 10 data per halaman)
        $('.table-unit-data').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50],
                    autoWidth: false,
                    language: {
                        search: "Cari Pegawai:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ s.d. _END_ dari _TOTAL_ pegawai",
                        infoEmpty: "Tidak ada data personil",
                        paginate: {
                            previous: "<i class='fas fa-chevron-left'></i>",
                            next: "<i class='fas fa-chevron-right'></i>"
                        }
                    }
                });
            }
        });

        // Re-adjust columns saat berganti tab pill
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
            setTimeout(function() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }, 150);
        });
    });
</script>
@endpush
