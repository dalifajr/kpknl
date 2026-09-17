@extends('layouts.app')

@section('title', 'Aplikasi Terintegrasi | SSO KPKNL Palembang')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 pb-3 border-bottom">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2.5 py-1 rounded">SSO Ecosystem</span>
            <span class="text-muted fs-7">•</span>
            <span class="text-muted fs-7">OAuth2 Identity Provider</span>
        </div>
        <h3 class="mb-1 fw-bold text-dark" style="font-family: var(--font-heading); letter-spacing: -0.01em;">
            Aplikasi Terintegrasi
        </h3>
        <p class="text-muted mb-0 fs-7">
            Kelola aplikasi internal KPKNL Palembang, pantau status koneksi, dan atur kredensial Client ID & Client Secret.
        </p>
    </div>
    @if(auth()->user()->isMaintenance())
    <div>
        <a href="{{ route('admin.applications.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-medium shadow-sm">
            <i class="fa-solid fa-plus fs-7"></i>
            <span>Daftarkan Aplikasi Baru</span>
        </a>
    </div>
    @endif
</div>

<!-- KPI Summary Tiles (Google Cloud Console Style) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="gcp-stat-card p-3 h-100 bg-white border rounded-3 shadow-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fs-7 fw-medium">Total Aplikasi</span>
                <div class="stat-icon-pill bg-primary-subtle text-primary">
                    <i class="fa-solid fa-cubes fs-6"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="mb-0 fw-bold text-dark fs-3" style="font-family: var(--font-heading);">{{ $stats['total'] ?? $applications->total() }}</h4>
                <span class="text-muted fs-8">sistem terdaftar</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="gcp-stat-card p-3 h-100 bg-white border rounded-3 shadow-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fs-7 fw-medium">Aplikasi Aktif</span>
                <div class="stat-icon-pill bg-success-subtle text-success">
                    <i class="fa-solid fa-circle-check fs-6"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="mb-0 fw-bold text-success fs-3" style="font-family: var(--font-heading);">{{ $stats['active'] ?? '-' }}</h4>
                <span class="text-muted fs-8">siap SSO</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="gcp-stat-card p-3 h-100 bg-white border rounded-3 shadow-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fs-7 fw-medium">Non-Aktif</span>
                <div class="stat-icon-pill bg-secondary-subtle text-secondary">
                    <i class="fa-solid fa-circle-pause fs-6"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="mb-0 fw-bold text-secondary fs-3" style="font-family: var(--font-heading);">{{ $stats['inactive'] ?? '-' }}</h4>
                <span class="text-muted fs-8">dalam pemeliharaan</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="gcp-stat-card p-3 h-100 bg-white border rounded-3 shadow-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fs-7 fw-medium">Total Akun Pengguna</span>
                <div class="stat-icon-pill bg-info-subtle text-info">
                    <i class="fa-solid fa-users fs-6"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="mb-0 fw-bold text-dark fs-3" style="font-family: var(--font-heading);">{{ $stats['total_users'] ?? '-' }}</h4>
                <span class="text-muted fs-8">terotentikasi</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Toolbar (Google Cloud Console Style) -->
<div class="bg-white border rounded-3 p-3 mb-4 shadow-none">
    <form action="{{ route('admin.applications.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6 col-lg-7">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 0.875rem; pointer-events: none;"></i>
                <input type="text" name="search" class="form-control ps-5 pe-3 py-2 fs-7" placeholder="Filter atau cari berdasarkan nama aplikasi, slug, deskripsi..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-sm-8 col-md-4 col-lg-3">
            <select name="status" class="form-select py-2 fs-7" onchange="this.form.submit()">
                <option value="">Semua Status Operasional</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hanya Aktif (Active)</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Hanya Non-Aktif (Inactive)</option>
            </select>
        </div>
        <div class="col-sm-4 col-md-2 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary w-100 py-2 fs-7 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5">
                <i class="fa-solid fa-filter fs-8"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-danger py-2 px-2.5 fs-7" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Quick Filter Chips -->
    <div class="d-flex align-items-center gap-2 mt-3 pt-2.5 border-top flex-wrap fs-8">
        <span class="text-muted fw-medium"><i class="fa-solid fa-sliders me-1"></i> Status:</span>
        <a href="{{ route('admin.applications.index') }}" class="badge text-decoration-none px-2.5 py-1.5 rounded-pill {{ !request()->anyFilled(['status', 'search']) ? 'bg-primary text-white' : 'bg-light text-secondary border' }}">
            Semua ({{ $stats['total'] ?? $applications->total() }})
        </a>
        <a href="{{ route('admin.applications.index', ['status' => 'active']) }}" class="badge text-decoration-none px-2.5 py-1.5 rounded-pill {{ request('status') == 'active' ? 'bg-success text-white' : 'bg-light text-secondary border' }}">
            <i class="fa-solid fa-circle text-success me-1" style="font-size: 6px;"></i> Aktif ({{ $stats['active'] ?? '-' }})
        </a>
        <a href="{{ route('admin.applications.index', ['status' => 'inactive']) }}" class="badge text-decoration-none px-2.5 py-1.5 rounded-pill {{ request('status') == 'inactive' ? 'bg-secondary text-white' : 'bg-light text-secondary border' }}">
            <i class="fa-solid fa-circle text-secondary me-1" style="font-size: 6px;"></i> Non-Aktif ({{ $stats['inactive'] ?? '-' }})
        </a>
    </div>
</div>

<!-- Applications Cards Grid -->
<div class="row g-3">
    @forelse($applications as $app)
        <div class="col-md-6 col-xl-4">
            <div class="gcp-app-card bg-white border rounded-3 p-3.5 h-100 d-flex flex-column justify-content-between position-relative">
                <div>
                    <!-- Top Bar in Card: App Icon + Status Badge -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="app-icon-wrapper rounded-3 border d-inline-flex align-items-center justify-content-center bg-light overflow-hidden">
                            @if($app->icon && !in_array($app->icon, ['box', 'desktop', 'window-maximize', 'cube', 'cubes']))
                                <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" class="app-icon-img" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-cube text-primary fs-5\'></i>';">
                            @else
                                <i class="fa-solid fa-{{ $app->icon ?: 'cube' }} text-primary fs-5"></i>
                            @endif
                        </div>
                        
                        @if($app->status === 'active')
                            <span class="status-chip status-chip-active">
                                <span class="status-pulse-dot bg-success"></span>
                                AKTIF
                            </span>
                        @else
                            <span class="status-chip status-chip-inactive">
                                <span class="status-dot bg-secondary"></span>
                                NON-AKTIF
                            </span>
                        @endif
                    </div>

                    <!-- App Title & Slug -->
                    <h5 class="fw-bold mb-1 text-dark fs-6 text-truncate" title="{{ $app->name }}" style="font-family: var(--font-heading);">
                        {{ $app->name }}
                    </h5>
                    <div class="d-flex align-items-center gap-1.5 mb-2">
                        <code class="px-2 py-0.5 rounded text-primary bg-primary-subtle fs-8 font-monospace text-truncate" style="max-width: 220px;" title="Slug: {{ $app->slug }}">
                            {{ $app->slug }}
                        </code>
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted hover-primary btn-copy-slug" data-copy="{{ $app->slug }}" title="Salin Slug">
                            <i class="fa-regular fa-copy fs-8"></i>
                        </button>
                    </div>

                    <!-- Description -->
                    <p class="text-secondary fs-7 mb-3 text-truncate-2" style="min-height: 2.6em; line-height: 1.45;">
                        {{ $app->description ?: 'Aplikasi terintegrasi Single Sign-On (SSO) KPKNL Palembang.' }}
                    </p>

                    <!-- Key-Value Metadata Container (Google Cloud Console style) -->
                    <div class="bg-light border rounded-2 p-2.5 mb-3 fs-8">
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted">Target URL</span>
                            <a href="{{ $app->url }}" target="_blank" class="text-primary text-decoration-none text-truncate fw-medium ms-2" style="max-width: 180px;" title="{{ $app->url }}">
                                {{ parse_url($app->url, PHP_URL_HOST) ?? $app->url }}
                                <i class="fa-solid fa-arrow-up-right-from-square fs-9 ms-0.5"></i>
                            </a>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted">Redirect URI</span>
                            <span class="text-dark font-monospace text-truncate ms-2" style="max-width: 180px;" title="{{ $app->redirect_uri }}">
                                {{ $app->redirect_uri }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted">User Terdaftar</span>
                            <span class="badge bg-white text-dark border px-2 py-0.5 rounded-pill fw-semibold">
                                <i class="fa-solid fa-user-check text-primary me-1"></i>{{ $app->users_count }} Pengguna
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-1">
                    <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-outline-primary btn-sm px-3 py-1.5 fs-7 fw-medium d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-key fs-8"></i>
                        <span>Kredensial & Izin</span>
                    </a>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-light btn-sm text-secondary border px-2 py-1.5 rounded" title="Ubah Konfigurasi">
                            <i class="fa-solid fa-pen-to-square fs-7"></i>
                        </a>
                        <form action="{{ route('admin.applications.destroy', $app->id) }}" method="POST" class="d-inline form-delete-app" data-app-name="{{ $app->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light btn-sm text-danger border px-2 py-1.5 rounded hover-danger-btn" title="Hapus Aplikasi">
                                <i class="fa-regular fa-trash-can fs-7"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="bg-white border rounded-3 p-5 text-center my-3">
                <div class="stat-icon-pill bg-light text-muted mx-auto mb-3" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-box-open fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1" style="font-family: var(--font-heading);">Tidak ada aplikasi yang sesuai</h5>
                <p class="text-muted fs-7 mb-3" style="max-width: 480px; margin: 0 auto;">
                    Tidak ditemukan aplikasi dengan kriteria filter yang Anda pilih. Coba sesuaikan kata kunci pencarian atau reset filter.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset Pencarian
                    </a>
                    @if(auth()->user()->isMaintenance())
                    <a href="{{ route('admin.applications.create') }}" class="btn btn-primary btn-sm px-3 py-2">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Aplikasi Baru
                    </a>
                    @endif
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span class="text-muted fs-7">
        Menampilkan {{ $applications->firstItem() ?? 0 }} - {{ $applications->lastItem() ?? 0 }} dari total {{ $applications->total() }} aplikasi
    </span>
    <div>
        {{ $applications->links() }}
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Google Cloud Console Style Custom CSS */
    .stat-icon-pill {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .app-icon-wrapper {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
    }

    .app-icon-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 4px;
    }

    .gcp-app-card {
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .gcp-app-card:hover {
        border-color: #1a73e8 !important;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.08);
        transform: translateY(-2px);
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 0.3rem 0.65rem;
        border-radius: 4px;
    }

    .status-chip-active {
        background-color: #e6f4ea;
        color: #137333;
        border: 1px solid #ceead6;
    }

    .status-chip-inactive {
        background-color: #f1f3f4;
        color: #5f6368;
        border: 1px solid #dadce0;
    }

    .status-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 2px rgba(30, 142, 62, 0.25);
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .hover-primary:hover {
        color: #1a73e8 !important;
    }

    .hover-danger-btn:hover {
        background-color: #fce8e6 !important;
        border-color: #fce8e6 !important;
    }

    /* Dark Mode Overrides for Application Page */
    [data-bs-theme="dark"] .bg-white {
        background-color: #202124 !important;
        border-color: #3c4043 !important;
    }

    [data-bs-theme="dark"] .bg-light {
        background-color: #292a2d !important;
        border-color: #3c4043 !important;
    }

    [data-bs-theme="dark"] .gcp-app-card:hover {
        border-color: #8ab4f8 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    [data-bs-theme="dark"] .status-chip-active {
        background-color: rgba(30, 142, 62, 0.2);
        color: #81c995;
        border-color: rgba(30, 142, 62, 0.4);
    }

    [data-bs-theme="dark"] .status-chip-inactive {
        background-color: #2d2f31;
        color: #9aa0a6;
        border-color: #3c4043;
    }

    [data-bs-theme="dark"] .text-dark {
        color: #e8eaed !important;
    }

    [data-bs-theme="dark"] .text-secondary {
        color: #9aa0a6 !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Copy Slug Helper
    document.querySelectorAll('.btn-copy-slug').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const slug = this.getAttribute('data-copy');
            navigator.clipboard.writeText(slug).then(() => {
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fa-solid fa-check text-success fs-8';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 1500);
            });
        });
    });

    // Delete Confirmation
    document.querySelectorAll('.form-delete-app').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const appName = this.getAttribute('data-app-name');

            Swal.fire({
                title: 'Hapus Aplikasi?',
                html: `Apakah Anda yakin ingin menghapus aplikasi <strong>${appName}</strong>?<br><small class="text-danger">Aplikasi yang dihapus tidak dapat melakukan SSO lagi.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash me-1.5"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal2-m3e',
                    title: 'swal2-m3e-title',
                    confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
                    cancelButton: 'btn btn-outline-secondary rounded-pill px-4'
                },
                buttonsStyling: false,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
