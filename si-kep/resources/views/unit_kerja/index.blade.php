@extends('layouts.app')

@section('title', 'Formasi Unit Kerja — SI-KEP KPKNL Palembang')
@section('hero-title', 'Formasi & Distribusi Unit Kerja')
@section('hero-subtitle', 'Sebaran personil ASN pada Subbagian Umum, Seksi Teknis Operasional, serta Seksi Pendukung di lingkungan KPKNL Palembang.')

@section('content')

<!-- Overview Stats (From referensi_desain/desain2.html) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Seksi / Subbagian</small>
                    <i class="fas fa-sitemap text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ $unitKerjas->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Unit Kerja Aktif</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Personil Terpetakan</small>
                    <i class="fas fa-users text-info opacity-50"></i>
                </div>
                <h3 class="fw-bold text-info mb-0">{{ $totalPegawai }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">100% Pegawai Terdistribusi</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Rata-rata Formasi</small>
                    <i class="fas fa-calculator text-success opacity-50"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $unitKerjas->count() > 0 ? round($totalPegawai / $unitKerjas->count(), 1) : 0 }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pegawai per Seksi</small>
            </div>
        </div>
    </div>
</div>

<!-- List of Unit Kerja Cards (From referensi_desain) -->
<div class="row g-4">
    @foreach($unitKerjas as $unit)
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 34px; height: 34px; font-size: 0.85rem; background: linear-gradient(135deg, var(--primary-color), var(--primary-light));">
                            <i class="fas fa-building-columns"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $unit->nama_unit }}</h6>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">
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
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    @foreach($unit->pegawai as $p)
                                        @php
                                            $isKepala = str_contains(strtolower($p->nama_jabatan_raw), 'kepala');
                                        @endphp
                                        <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap" style="{{ $isKepala ? 'background-color: #f8fafc;' : '' }}">
                                            <td class="ps-4" style="width: 48px;">
                                                <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, {{ $isKepala ? '#d97706, #f59e0b' : '#0c306b, #1e40af' }});">
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
                                            <td class="text-end pe-4" style="width: 40px;">
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
        </div>
    @endforeach
</div>

@endsection
