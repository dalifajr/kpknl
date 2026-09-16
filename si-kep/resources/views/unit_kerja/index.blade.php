@extends('layouts.app')

@section('title', 'Formasi Unit Kerja — SIMPATIK KPKNL Palembang')
@section('hero-title', 'Formasi & Distribusi Unit Kerja')
@section('hero-subtitle', 'Sebaran personil ASN pada Subbagian Umum, Seksi Teknis Operasional, serta Seksi Pendukung di lingkungan KPKNL Palembang.')

@section('content')

<!-- Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="kpi-card" style="--card-accent: #0c306b; --icon-bg: #eff6ff; --icon-color: #0c306b;">
            <div>
                <div class="kpi-title">Total Seksi / Subbagian</div>
                <div class="kpi-val">{{ $unitKerjas->count() }}</div>
                <div class="kpi-desc">Unit Kerja Aktif</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-sitemap"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="kpi-card" style="--card-accent: #1e40af; --icon-bg: #dbeafe; --icon-color: #1e40af;">
            <div>
                <div class="kpi-title">Total Personil Terpetakan</div>
                <div class="kpi-val text-primary">{{ $totalPegawai }}</div>
                <div class="kpi-desc">100% Pegawai Terdistribusi</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="kpi-card" style="--card-accent: #10b981; --icon-bg: #d1fae5; --icon-color: #059669;">
            <div>
                <div class="kpi-title">Rata-rata Formasi</div>
                <div class="kpi-val text-success">{{ $unitKerjas->count() > 0 ? round($totalPegawai / $unitKerjas->count(), 1) : 0 }}</div>
                <div class="kpi-desc">Pegawai per Seksi</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-calculator"></i>
            </div>
        </div>
    </div>
</div>

<!-- List of Unit Kerja Cards -->
<div class="row g-4">
    @foreach($unitKerjas as $unit)
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header-clean bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; background: linear-gradient(135deg, #0c306b, #1e40af) !important;">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $unit->nama_unit }}</h6>
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.68rem;">
                                {{ $unit->singkatan ?: 'SEKSI' }}
                            </span>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-1">
                        {{ $unit->pegawai->count() }} Personil
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($unit->pegawai->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            Belum ada personil yang ditempatkan pada unit ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-custom table-hover align-middle mb-0">
                                <tbody>
                                    @foreach($unit->pegawai as $p)
                                        @php
                                            $isKepala = str_contains(strtolower($p->nama_jabatan_raw), 'kepala');
                                        @endphp
                                        <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap" style="{{ $isKepala ? 'background-color: #f8fafc;' : '' }}">
                                            <td style="width: 42px;">
                                                <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.8rem; background: linear-gradient(135deg, {{ $isKepala ? '#d97706, #f59e0b' : '#0c306b, #1e40af' }});">
                                                    {{ strtoupper(substr($p->nama, 0, 2)) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                                    {{ $p->display_name }}
                                                    @if($isKepala)
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Pimpinan Unit</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted font-monospace">NIP: {{ $p->nip }}</div>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold text-dark">{{ $p->nama_jabatan_raw }}</div>
                                                <div class="small text-muted">{{ $p->pangkatGolongan?->golongan_ruang }} &bull; Grade {{ $p->job_grade }}</div>
                                            </td>
                                            <td class="text-end" style="width: 40px;">
                                                <i class="fa-solid fa-chevron-right text-muted small"></i>
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
    @endforeach
</div>

@endsection
