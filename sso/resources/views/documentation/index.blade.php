@extends('layouts.app')

@section('title', 'Buku Panduan & Dokumentasi Fitur SSO')

@section('styles')
<style>
    .doc-hero {
        background: linear-gradient(135deg, var(--md-sys-color-primary) 0%, #002d4f 100%);
        color: white;
        border-radius: var(--md-shape-corner-xl);
        padding: 2.25rem 2rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(11, 59, 96, 0.25);
    }
    .doc-hero::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .role-nav-pill {
        border-radius: var(--md-shape-corner-full);
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid var(--md-sys-color-outline-variant);
        background-color: var(--md-sys-color-surface-container);
        color: var(--md-sys-color-on-surface-variant);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .role-nav-pill:hover {
        background-color: var(--md-sys-color-surface-container-high);
        color: var(--md-sys-color-primary);
        transform: translateY(-1px);
    }
    .role-nav-pill.active {
        background-color: var(--md-sys-color-primary);
        color: var(--md-sys-color-on-primary);
        border-color: var(--md-sys-color-primary);
        box-shadow: 0 4px 12px rgba(11, 59, 96, 0.2);
    }
    .feature-card {
        background-color: var(--md-sys-color-surface-container-low);
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-shape-corner-lg);
        padding: 1.5rem;
        transition: all 0.25s ease;
        height: 100%;
    }
    .feature-card:hover {
        background-color: var(--md-sys-color-surface-container);
        border-color: var(--md-sys-color-primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08);
    }
    .step-item {
        position: relative;
        padding-left: 2.5rem;
        margin-bottom: 1.25rem;
    }
    .step-number {
        position: absolute;
        left: 0;
        top: 0;
        width: 1.85rem;
        height: 1.85rem;
        background-color: var(--md-sys-color-primary-container);
        color: var(--md-sys-color-on-primary-container);
        font-weight: 700;
        font-size: 0.85rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .guide-callout {
        border-left: 4px solid var(--md-sys-color-tertiary);
        background-color: var(--md-sys-color-tertiary-container);
        color: var(--md-sys-color-on-tertiary-container);
        border-radius: 0 var(--md-shape-corner-md) var(--md-shape-corner-md) 0;
        padding: 0.85rem 1.25rem;
        font-size: 0.875rem;
    }
    .app-badge-pill {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: var(--md-shape-corner-full);
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- Hero Banner Documentation -->
    <div class="doc-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-15 mb-3 fs-8 fw-semibold">
                    <i class="fa-solid fa-book-bookmark text-warning"></i> Dokumentasi Resmi Ekosistem KPKNL Palembang
                </div>
                <h2 class="fw-bold mb-2 text-white" style="font-family: var(--font-heading);">
                    Pusat Panduan & Dokumentasi Fitur SSO
                </h2>
                <p class="text-white-50 mb-4 fs-6" style="max-width: 680px;">
                    Panduan lengkap pengoperasian, arsitektur integrasi OAuth 2.0, manajemen sesi, serta tata kelola hak akses pengguna sesuai peran (role) pada sistem Single Sign-On KPKNL Palembang.
                </p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('documentation.pdf') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold text-dark shadow-sm">
                        <i class="fa-solid fa-file-pdf me-1.5 text-danger"></i> Unduh PDF Panduan Saya ({{ ucfirst($primaryRole) }})
                    </a>

                    <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-semibold" onclick="window.print()">
                        <i class="fa-solid fa-print me-1.5"></i> Cetak Halaman
                    </button>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-flex justify-content-end">
                <div class="p-4 rounded-4 bg-white bg-opacity-10 text-white text-center border border-white border-opacity-15 shadow-sm" style="max-width: 280px;">
                    <div class="display-6 mb-2 text-warning"><i class="fa-solid fa-id-badge"></i></div>
                    <div class="fs-8 text-uppercase tracking-wider text-white-50">Role Aktif Anda</div>
                    <div class="fs-5 fw-bold mt-1 text-white">{{ ucfirst($primaryRole) }}</div>
                    <div class="mt-2 pt-2 border-top border-white border-opacity-15 fs-8 text-white-70">
                        {{ $user->name }} ({{ $user->username }})
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Panduan Khusus Role & Live Search -->
    <div class="card-m3 p-3.5 mb-4 shadow-sm">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-lg-7">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fs-7 fw-bold text-muted me-1"><i class="fa-solid fa-shield-halved me-1 text-primary"></i>Panduan Khusus Role:</span>
                    <span class="badge rounded-pill px-3 py-2 fs-7 fw-bold text-white shadow-xs" style="background-color: {{ $docSections[$primaryRole]['color'] ?? '#0b3b60' }};">
                        <i class="fa-solid fa-user-check me-1.5"></i> {{ $docSections[$primaryRole]['role_name'] ?? ucfirst($primaryRole) }}
                    </span>
                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1.5 fs-8">
                        <i class="fa-solid fa-lock me-1 text-muted"></i> Terkunci sesuai sesi akun aktif
                    </span>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="docSearchInput" class="form-control border-start-0" placeholder="Cari topik atau fitur dalam panduan ini..." aria-label="Cari panduan">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 1: Ekosistem & Arsitektur Inti SSO -->
    <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-overview">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-network-wired me-2"></i>{{ $docSections['overview']['title'] }}
                </h4>
                <p class="text-muted mb-0 fs-7">{{ $docSections['overview']['description'] }}</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fs-8">
                Prinsip Dasar
            </span>
        </div>

        <div class="row g-4 mt-1">
            @foreach($docSections['overview']['features'] as $feature)
                <div class="col-md-6 searchable-item">
                    <div class="feature-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="p-3 rounded-circle bg-primary-subtle text-primary flex-shrink-0" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="{{ $feature['icon'] }} fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1 fs-6" style="font-family: var(--font-heading);">{{ $feature['name'] }}</h5>
                                <p class="text-secondary fw-semibold mb-2 fs-7">{{ $feature['summary'] }}</p>
                                <p class="text-muted mb-0 fs-7" style="line-height: 1.55;">{{ $feature['details'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 2: Panduan Pegawai / User -->
    @if($primaryRole === 'user')
        <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-user">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: #0284c7;">
                        <i class="fa-solid fa-user fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                            {{ $docSections['user']['role_name'] }}
                        </h4>
                        <p class="text-muted mb-0 fs-7">{{ $docSections['user']['summary'] }}</p>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fs-8 text-white" style="background-color: #0284c7;">
                    Panduan Pegawai
                </span>
            </div>

            <div class="row g-4">
                @foreach($docSections['user']['guides'] as $guide)
                    <div class="col-lg-6 searchable-item">
                        <div class="card h-100 border-0 rounded-4 p-3.5" style="background-color: var(--md-sys-color-surface-container);">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-family: var(--font-heading);">
                                <i class="{{ $guide['icon'] }} text-info"></i> {{ $guide['title'] }}
                            </h6>
                            <div class="mb-3">
                                @foreach($guide['steps'] as $index => $step)
                                    <div class="step-item">
                                        <div class="step-number">{{ $index + 1 }}</div>
                                        <div class="fs-7 text-dark" style="line-height: 1.5;">{{ $step }}</div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!empty($guide['notes']))
                                <div class="guide-callout mt-auto">
                                    <i class="fa-solid fa-circle-info me-1"></i> <strong>Catatan:</strong> {{ $guide['notes'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECTION 3: Panduan Administrator -->
    @if($primaryRole === 'admin')
        <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-admin">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: #4f46e5;">
                        <i class="fa-solid fa-users-gear fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                            {{ $docSections['admin']['role_name'] }}
                        </h4>
                        <p class="text-muted mb-0 fs-7">{{ $docSections['admin']['summary'] }}</p>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fs-8 text-white" style="background-color: #4f46e5;">
                    Panduan Administrator
                </span>
            </div>

            <div class="row g-4">
                @foreach($docSections['admin']['guides'] as $guide)
                    <div class="col-lg-6 searchable-item">
                        <div class="card h-100 border-0 rounded-4 p-3.5" style="background-color: var(--md-sys-color-surface-container);">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-family: var(--font-heading);">
                                <i class="{{ $guide['icon'] }}" style="color: #4f46e5;"></i> {{ $guide['title'] }}
                            </h6>
                            <div class="mb-3">
                                @foreach($guide['steps'] as $index => $step)
                                    <div class="step-item">
                                        <div class="step-number" style="background-color: #e0e7ff; color: #4338ca;">{{ $index + 1 }}</div>
                                        <div class="fs-7 text-dark" style="line-height: 1.5;">{{ $step }}</div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!empty($guide['notes']))
                                <div class="guide-callout mt-auto" style="border-left-color: #4f46e5; background-color: #f5f3ff; color: #3730a3;">
                                    <i class="fa-solid fa-circle-info me-1"></i> <strong>Tips Admin:</strong> {{ $guide['notes'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECTION 4: Panduan Tim Maintenance (Fitur Unggulan) -->
    @if($primaryRole === 'maintenance')
        <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-maintenance">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: #d97706;">
                        <i class="fa-solid fa-wrench fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                            {{ $docSections['maintenance']['role_name'] }}
                        </h4>
                        <p class="text-muted mb-0 fs-7">{{ $docSections['maintenance']['summary'] }}</p>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fs-8 text-white" style="background-color: #d97706;">
                    Panduan Tim Maintenance
                </span>
            </div>

            <div class="row g-4">
                @foreach($docSections['maintenance']['guides'] as $guide)
                    <div class="col-lg-6 searchable-item">
                        <div class="card h-100 border-0 rounded-4 p-3.5" style="background-color: var(--md-sys-color-surface-container);">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-family: var(--font-heading);">
                                <i class="{{ $guide['icon'] }}" style="color: #d97706;"></i> {{ $guide['title'] }}
                            </h6>
                            <div class="mb-3">
                                @foreach($guide['steps'] as $index => $step)
                                    <div class="step-item">
                                        <div class="step-number" style="background-color: #fef3c7; color: #92400e;">{{ $index + 1 }}</div>
                                        <div class="fs-7 text-dark" style="line-height: 1.5;">{{ $step }}</div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!empty($guide['notes']))
                                <div class="guide-callout mt-auto" style="border-left-color: #d97706; background-color: #fffbeb; color: #92400e;">
                                    <i class="fa-solid fa-shield-check me-1"></i> <strong>Perhatian Teknis:</strong> {{ $guide['notes'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECTION 5: Panduan Superadmin -->
    @if($primaryRole === 'superadmin')
        <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-superadmin">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: #dc2626;">
                        <i class="fa-solid fa-shield-halved fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                            {{ $docSections['superadmin']['role_name'] }}
                        </h4>
                        <p class="text-muted mb-0 fs-7">{{ $docSections['superadmin']['summary'] }}</p>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fs-8 text-white" style="background-color: #dc2626;">
                    Panduan Superadmin
                </span>
            </div>

            <div class="row g-4">
                @foreach($docSections['superadmin']['guides'] as $guide)
                    <div class="col-lg-6 searchable-item">
                        <div class="card h-100 border-0 rounded-4 p-3.5" style="background-color: var(--md-sys-color-surface-container);">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-family: var(--font-heading);">
                                <i class="{{ $guide['icon'] }}" style="color: #dc2626;"></i> {{ $guide['title'] }}
                            </h6>
                            <div class="mb-3">
                                @foreach($guide['steps'] as $index => $step)
                                    <div class="step-item">
                                        <div class="step-number" style="background-color: #fee2e2; color: #991b1b;">{{ $index + 1 }}</div>
                                        <div class="fs-7 text-dark" style="line-height: 1.5;">{{ $step }}</div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!empty($guide['notes']))
                                <div class="guide-callout mt-auto" style="border-left-color: #dc2626; background-color: #fef2f2; color: #991b1b;">
                                    <i class="fa-solid fa-lock me-1"></i> <strong>Kepatuhan Tata Kelola:</strong> {{ $guide['notes'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECTION 6: Daftar Aplikasi Ekosistem Terintegrasi -->
    <div class="card-m3 p-4 mb-4 shadow-sm doc-section" id="section-apps">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-cubes-stacked me-2"></i>Katalog Aplikasi Ekosistem SSO Terintegrasi
                </h4>
                <p class="text-muted mb-0 fs-7">Aplikasi resmi yang saat ini telah terhubung ke Identity Provider SSO KPKNL Palembang.</p>
            </div>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1.5 fs-8">
                {{ $applications->count() }} Aplikasi Terdaftar
            </span>
        </div>

        <div class="row g-3">
            @forelse($applications as $app)
                <div class="col-md-6 col-lg-4 searchable-item">
                    <div class="p-3 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background-color: var(--md-sys-color-surface-container-low);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-8">
                                    {{ $app->slug }}
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-8">
                                    <i class="fa-solid fa-circle-check me-1"></i> Aktif
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-family: var(--font-heading);">{{ $app->name }}</h6>
                            <p class="text-muted fs-8 mb-2">{{ $app->description ?: 'Aplikasi pendukung kedinasan KPKNL Palembang.' }}</p>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle d-flex align-items-center justify-content-between">
                            <span class="text-muted fs-8 text-truncate" style="max-width: 170px;">
                                <i class="fa-solid fa-link me-1"></i>{{ $app->url }}
                            </span>
                            @if(str_contains($app->slug, 'lelang') || str_contains($app->slug, 'peminjam'))
                                <span class="app-badge-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    Role: Peminjam/Pelelang
                                </span>
                            @else
                                <span class="app-badge-pill bg-info-subtle text-info-emphasis border border-info-subtle">
                                    Role: User/Operator/Admin
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted fs-7">
                    Belum ada aplikasi yang terdaftar dalam status aktif.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('docSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.searchable-item');

                items.forEach(function (item) {
                    const text = item.innerText.toLowerCase();
                    if (keyword === '' || text.includes(keyword)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
