@extends('layouts.app')

@section('title', 'Tentang Aplikasi — SI-KEP KPKNL Palembang')
@section('hero-title', 'Tentang SI-KEP')
@section('hero-subtitle', 'Sistem Informasi Kepegawaian & Tata Kelola SDM Terpadu KPKNL Palembang.')

@section('content')

<!-- 1. Hero Card: Pengenalan SI-KEP -->
<div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 16px; background: linear-gradient(135deg, #0c306b 0%, #1e40af 60%, #2563eb 100%);">
    <div class="card-body p-4 p-md-5 text-white position-relative">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-2 display-6">SI-KEP KPKNL Palembang</h2>
                <p class="text-white text-opacity-90 fs-6 mb-4" style="max-width: 680px; line-height: 1.6;">
                    <strong>Sistem Informasi Kepegawaian (SI-KEP)</strong> adalah platform manajemen data aparatur sipil negara dan personil pendukung yang dirancang khusus untuk mewujudkan tata kelola SDM yang modern, akuntabel, transparan di lingkungan KPKNL Palembang.
                </p>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.8rem;">
                        <i class="fas fa-code-branch me-1"></i> Versi 2.0 (Build 2026)
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-white shadow-sm border-0" style="border: none !important;">
                    <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo KPKNL Palembang" style="max-height: 75px; width: auto; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Empat Pilar Fitur Unggulan -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-top border-4 border-primary">
            <div class="card-body p-4">
                <div class="rounded-circle p-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-arrows-rotate fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-2">Dual-Write Sync Engine</h6>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Mekanisme sinkronisasi data dua arah antara basis data internal SI-KEP dengan lembar kerja Google Spreadsheet secara instan melalui Google Apps Script Webhook.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-top border-4 border-warning">
            <div class="card-body p-4">
                <div class="rounded-circle p-3 bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-bell fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-2">Early Warning System (EWS)</h6>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Pendeteksian proaktif jatuh tempo Kenaikan Gaji Berkala (KGB) 2 tahunan dan radar batas usia pensiun aparatur sesuai ketentuan PP No. 17 Tahun 2020.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-top border-success">
            <div class="card-body p-4">
                <div class="rounded-circle p-3 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-chart-pie fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-2">Analitika &amp; Visualisasi</h6>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Eksplorasi grafis piramida generasi, kepangkatan, kualifikasi pendidikan, sebaran formasi seksi, hingga analisis masa penugasan di unit Eselon IV.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-top border-info">
            <div class="card-body p-4">
                <div class="rounded-circle p-3 bg-info-subtle text-info d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-clock-rotate-left fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-2">Audit Trail &amp; Rollback Log</h6>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Pencatatan riwayat setiap penambahan dan perubahan data personil secara mendalam (*diff before vs after*), disertai kemampuan pembatalan (*rollback*) oleh akun berwenang.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- 3. Profil Instansi & Detail Sistem -->
<div class="row g-4 mb-4">
    <!-- Informasi Kantor -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-building-flag text-primary"></i>
                <h6 class="mb-0 fw-bold text-dark">Informasi Unit Pelaksana Teknis</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 border-0" style="width: 54px; height: 54px; border: none !important;">
                        <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo KPKNL" style="max-height: 48px; max-width: 54px; object-fit: contain;">
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Kantor Pelayanan Kekayaan Negara dan Lelang Palembang</h6>
                        <div class="small text-muted mb-2">
                            Unit Eselon III di bawah naungan Kantor Wilayah DJKN Sumatera Selatan, Jambi, dan Bangka Belitung.
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kemenkeu Satu</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">WBK / WBBM</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.68rem;">Alamat Kantor</small>
                            <div class="small text-dark fw-semibold">
                                Gedung Keuangan Negara (GKN)<br>
                                Jl. Kapten A. Rivai No. 4, Palembang, Sumatera Selatan 30129
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.68rem;">Pengembang Sistem</small>
                            <div class="small text-dark fw-semibold">
                                Tim KP UIN Raden Fatah 2026 <br> Utoro Yogi Wiratama A.Md.Pnl.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="small text-muted">
                        <i class="fas fa-circle-info text-primary me-1"></i>
                        Aplikasi SI-KEP terintegrasi dengan ekosistem aplikasi lokal KPKNL Palembang melalui Single Sign-On (SSO), termasuk <strong>Monlap</strong>, <strong>Aset Eks BPPN</strong>, dan <strong>Monitoring Risalah Lelang / Peminjaman BMN</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Spesifikasi Teknis -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-microchip text-primary"></i>
                <h6 class="mb-0 fw-bold text-dark">Spesifikasi Arsitektur Sistem</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fab fa-laravel text-danger me-2"></i>Framework</span>
                        <span class="fw-bold text-dark font-monospace">Laravel 13 (PHP 8.5)</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fab fa-bootstrap text-primary me-2"></i>Antarmuka (UI)</span>
                        <span class="fw-bold text-dark font-monospace">Bootstrap 5.3 &bull; FontAwesome 6</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-database text-info me-2"></i>Basis Data</span>
                        <span class="fw-bold text-dark font-monospace">SQLite / MySQL Engine</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-chart-line text-success me-2"></i>Mesin Grafik</span>
                        <span class="fw-bold text-dark font-monospace">Chart.js Responsive</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-table text-warning me-2"></i>Data Grid</span>
                        <span class="fw-bold text-dark font-monospace">DataTables BS5 Responsive</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-key text-purple me-2" style="color: #8b5cf6;"></i>Autentikasi</span>
                        <span class="fw-bold text-dark font-monospace">SSO OAuth2 KPKNL Palembang</span>
                    </li>
                </ul>

                <div class="mt-4 p-3 bg-light rounded-3 border text-center">
                    <small class="text-muted d-block" style="font-size: 0.72rem;">STATUS SINKRONISASI TERAKHIR</small>
                    <div class="fw-bold text-primary mt-1" style="font-size: 0.85rem;">
                        <i class="fas fa-clock me-1"></i>
                        {{ $lastSynced ? \Carbon\Carbon::parse($lastSynced)->translatedFormat('d F Y, H:i') : 'Tersinkronisasi' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
