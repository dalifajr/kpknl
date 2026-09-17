@extends('layouts.app')

@section('title', 'Formasi Unit Kerja — SI-KEP KPKNL Palembang')
@section('hero-title', 'Formasi & Distribusi Unit Kerja')
@section('hero-subtitle', 'Sebaran personil ASN pada Subbagian Umum, Seksi Teknis Operasional, serta Seksi Pendukung di lingkungan KPKNL Palembang.')

@section('content')

<!-- Overview Stats (From referensi_desain/desain2.html) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Unit Kerja</small>
                    <i class="fas fa-sitemap text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $unitKerjas->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Seksi &amp; Subbagian Aktif</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Personil</small>
                    <i class="fas fa-users text-info opacity-50"></i>
                </div>
                <h3 class="fw-bold text-info mb-0">{{ $totalPegawai }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">100% Pegawai Terpetakan</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">PNS Definitif</small>
                    <i class="fas fa-user-shield text-success opacity-50"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $totalPns }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Aparatur Sipil Negara</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">PPNPN</small>
                    <i class="fas fa-id-card-clip text-warning opacity-50"></i>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $totalPpnpn }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pegawai Non-PNS</small>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Toolbar: Live Search & Expand/Collapse Controls -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 bg-white rounded-3">
        <!-- Live Search -->
        <div class="input-group" style="max-width: 420px;">
            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="unitSearchInput" class="form-control border-start-0" placeholder="Cari seksi, nama pejabat, atau staf...">
            <button class="btn btn-outline-secondary border-start-0" type="button" id="btnClearSearch" style="display: none;">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <!-- Global Toggle Controls -->
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted me-1 d-none d-lg-inline">Kontrol Formasi:</span>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" id="btnExpandAll">
                <i class="fas fa-chevron-down me-1"></i> Buka Semua
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm" id="btnCollapseAll">
                <i class="fas fa-chevron-up me-1"></i> Tutup Semua
            </button>
        </div>
    </div>
</div>

<!-- List of Unit Kerja Cards (Collapsible Executive Cards) -->
<div class="row g-4" id="unitCardsContainer">
    @foreach($unitKerjas as $unit)
        @php
            $unitPns = $unit->pegawai->where('tipe_pegawai', 'pns')->count();
            $unitPpnpn = $unit->pegawai->where('tipe_pegawai', 'ppnpn')->count();
            $kepala = $unit->pegawai->first(function($p) {
                return str_contains(strtolower($p->nama_jabatan_raw), 'kepala');
            });
            $avgGrade = $unit->pegawai->whereNotNull('job_grade')->avg('job_grade');
        @endphp

        <div class="col-lg-6 unit-card-wrapper" data-unit-name="{{ strtolower($unit->nama_unit . ' ' . $unit->singkatan) }}">
            <div class="card shadow-sm border-0 h-100 unit-card">
                <!-- Card Header with Collapsible Trigger -->
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" 
                             style="width: 38px; height: 38px; font-size: 0.9rem; background: linear-gradient(135deg, var(--primary-color), var(--primary-light));">
                            <i class="fas fa-building-columns"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $unit->nama_unit }}</h6>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">
                                {{ $unit->singkatan ?: 'SEKSI' }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                            {{ $unit->pegawai->count() }} Personil
                        </span>
                        <button class="btn btn-sm btn-light border-0 rounded-circle btn-collapse-toggle" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#unitCollapse{{ $unit->id }}" 
                                aria-expanded="false" 
                                aria-controls="unitCollapse{{ $unit->id }}"
                                title="Buka/Tutup Rincian Formasi"
                                style="width: 32px; height: 32px; padding: 0;">
                            <i class="fas fa-chevron-down text-muted transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Executive Summary Box -->
                <div class="card-body p-3 bg-light bg-opacity-40 border-bottom">
                    <div class="row align-items-center g-2">
                        <!-- Head of Unit Info -->
                        <div class="col-sm-7">
                            @if($kepala)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-initial flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.75rem; background: linear-gradient(135deg, #d97706, #f59e0b);">
                                        {{ strtoupper(substr($kepala->nama, 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate small" title="{{ $kepala->display_name }}">
                                            {{ $kepala->display_name }}
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 0.7rem;">
                                            <span class="badge bg-warning text-dark me-1" style="font-size: 0.62rem;">Pimpinan</span>
                                            {{ $kepala->nama_jabatan_raw }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="small text-muted fst-italic">
                                    <i class="fas fa-user-slash me-1 opacity-50"></i> Pimpinan belum terisi definitif
                                </div>
                            @endif
                        </div>

                        <!-- Mini Stats Breakdown -->
                        <div class="col-sm-5 text-sm-end">
                            <div class="d-inline-flex gap-1">
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;" title="PNS Definitif">
                                    {{ $unitPns }} PNS
                                </span>
                                @if($unitPpnpn > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.68rem;" title="PPNPN">
                                        {{ $unitPpnpn }} PPNPN
                                    </span>
                                @endif
                                @if($avgGrade)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;" title="Rata-rata Grade">
                                        Rata-rata G.{{ round($avgGrade, 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collapsible Personnel Table Drawer (Neatly Closed by Default) -->
                <div class="collapse" id="unitCollapse{{ $unit->id }}">
                    <div class="card-body p-0">
                        @if($unit->pegawai->isEmpty())
                            <div class="p-4 text-center text-muted small">
                                Belum ada personil yang ditempatkan pada unit kerja ini.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 employee-table">
                                    <tbody>
                                        @foreach($unit->pegawai as $p)
                                            @php
                                                $isKepala = str_contains(strtolower($p->nama_jabatan_raw), 'kepala');
                                            @endphp
                                            <tr class="employee-row" 
                                                data-employee-name="{{ strtolower($p->nama . ' ' . $p->nip . ' ' . $p->nama_jabatan_raw) }}"
                                                onclick="showPegawaiDetail({{ $p->id }})" 
                                                title="Klik untuk melihat profil lengkap" 
                                                style="{{ $isKepala ? 'background-color: #fbfcfe;' : '' }}; cursor: pointer;">
                                                <td class="ps-3" style="width: 44px;">
                                                    <div class="avatar-initial" style="width: 32px; height: 32px; font-size: 0.75rem; background: linear-gradient(135deg, {{ $isKepala ? '#d97706, #f59e0b' : '#0c306b, #1e40af' }});">
                                                        {{ strtoupper(substr($p->nama, 0, 2)) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-1.5">
                                                        <span class="text-truncate">{{ $p->display_name }}</span>
                                                        @if($isKepala)
                                                            <span class="badge bg-warning text-dark flex-shrink-0" style="font-size: 0.62rem;">Pimpinan</span>
                                                        @endif
                                                    </div>
                                                    <div class="small text-muted font-monospace" style="font-size: 0.72rem;">NIP: {{ $p->nip ?: '-' }}</div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-dark text-truncate" style="max-width: 180px;">{{ $p->nama_jabatan_raw }}</div>
                                                    <div class="small text-muted" style="font-size: 0.72rem;">{{ $p->pangkatGolongan?->golongan_ruang }} &bull; Grade {{ $p->job_grade ?: '-' }}</div>
                                                </td>
                                                <td class="text-end pe-3" style="width: 32px;">
                                                    <i class="fas fa-chevron-right text-muted small"></i>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Action Bar -->
                <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 0.72rem;">
                        <i class="fas fa-info-circle me-1 opacity-50"></i> Klik baris untuk detail profil
                    </small>
                    <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none fw-semibold p-0" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#unitCollapse{{ $unit->id }}"
                            style="font-size: 0.75rem;">
                        <span class="toggle-text">Lihat Anggota</span>
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection

@push('styles')
<style>
    .btn-collapse-toggle .fa-chevron-down {
        transition: transform 0.25s ease-in-out;
    }
    .btn-collapse-toggle[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
    }
    .unit-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .unit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('unitSearchInput');
        const clearBtn = document.getElementById('btnClearSearch');
        const unitWrappers = document.querySelectorAll('.unit-card-wrapper');
        const btnExpandAll = document.getElementById('btnExpandAll');
        const btnCollapseAll = document.getElementById('btnCollapseAll');

        // Expand All
        btnExpandAll.addEventListener('click', function() {
            document.querySelectorAll('.collapse').forEach(function(el) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                bsCollapse.show();
            });
            document.querySelectorAll('.btn-collapse-toggle').forEach(btn => btn.setAttribute('aria-expanded', 'true'));
            document.querySelectorAll('.toggle-text').forEach(el => el.textContent = 'Tutup Anggota');
        });

        // Collapse All
        btnCollapseAll.addEventListener('click', function() {
            document.querySelectorAll('.collapse').forEach(function(el) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                bsCollapse.hide();
            });
            document.querySelectorAll('.btn-collapse-toggle').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
            document.querySelectorAll('.toggle-text').forEach(el => el.textContent = 'Lihat Anggota');
        });

        // Keep toggle text synchronized
        document.querySelectorAll('.collapse').forEach(function(collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', function() {
                const card = this.closest('.unit-card');
                if (card) {
                    const btnToggle = card.querySelector('.btn-collapse-toggle');
                    if (btnToggle) btnToggle.setAttribute('aria-expanded', 'true');
                    const text = card.querySelector('.toggle-text');
                    if (text) text.textContent = 'Tutup Anggota';
                }
            });
            collapseEl.addEventListener('hide.bs.collapse', function() {
                const card = this.closest('.unit-card');
                if (card) {
                    const btnToggle = card.querySelector('.btn-collapse-toggle');
                    if (btnToggle) btnToggle.setAttribute('aria-expanded', 'false');
                    const text = card.querySelector('.toggle-text');
                    if (text) text.textContent = 'Lihat Anggota';
                }
            });
        });

        // Real-time Search Filter
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            clearBtn.style.display = query.length > 0 ? 'inline-block' : 'none';

            unitWrappers.forEach(function(wrapper) {
                const unitName = wrapper.getAttribute('data-unit-name') || '';
                const employeeRows = wrapper.querySelectorAll('.employee-row');
                let unitMatches = unitName.includes(query);
                let employeeMatches = false;

                employeeRows.forEach(function(row) {
                    const employeeData = row.getAttribute('data-employee-name') || '';
                    if (query === '' || employeeData.includes(query)) {
                        row.style.display = '';
                        employeeMatches = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (query === '' || unitMatches || employeeMatches) {
                    wrapper.style.display = '';
                    // If searching and employee matched, auto expand
                    if (query.length > 1 && employeeMatches) {
                        const collapseEl = wrapper.querySelector('.collapse');
                        if (collapseEl) {
                            bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                        }
                    }
                } else {
                    wrapper.style.display = 'none';
                }
            });
        });

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    });
</script>
@endpush
