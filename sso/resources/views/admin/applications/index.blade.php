@extends('layouts.app')

@section('title', 'Kelola Aplikasi SSO | KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Manajemen Aplikasi Terintegrasi</h3>
        <p class="text-muted mb-0">Daftarkan aplikasi internal baru dan dapatkan Client ID serta Client Secret untuk OAuth2 SSO integration.</p>
    @if(auth()->user()->isMaintenance())
    <a href="{{ route('admin.applications.create') }}" class="btn btn-primary rounded-pill px-4 py-2">
        <i class="fa-solid fa-plus me-1"></i> Tambah Aplikasi Baru
    </a>
    @endif
</div>

<style>
    /* Expressive Search & Filter Component */
    .m3e-filter-card {
        background: linear-gradient(145deg, var(--md-sys-color-surface-container-low), var(--md-sys-color-surface-container));
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-shape-corner-xl);
    }
    .m3e-input, .m3e-select {
        background-color: var(--md-sys-color-surface-container-highest) !important;
        border: 1px solid transparent !important;
        color: var(--md-sys-color-on-surface);
        transition: all 0.25s cubic-bezier(0.2, 0, 0, 1) !important;
    }
    .m3e-input:focus, .m3e-select:focus {
        background-color: var(--md-sys-color-surface-container-lowest) !important;
        border-color: var(--md-sys-color-primary) !important;
        box-shadow: 0 0 0 4px rgba(59, 95, 229, 0.15) !important;
    }
    .m3e-chip { transition: all 0.2s ease; text-decoration: none; }
    .m3e-chip:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.08); }
</style>

<!-- Filter Card M3 Expressive -->
<div class="card-m3 p-4 mb-4 m3e-filter-card shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fs-7 fw-bold text-dark text-uppercase" style="letter-spacing: 0.08em; font-family: var(--font-heading);">
            <i class="fa-solid fa-sliders me-1.5 text-primary"></i> Filter & Pencarian
        </span>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.applications.index') }}" class="btn btn-tonal btn-sm rounded-pill px-3 py-1 fs-8 text-danger fw-bold" style="background-color: var(--md-sys-color-error-container);">
                <i class="fa-solid fa-xmark me-1"></i> Reset Filter
            </a>
        @endif
    </div>

    <form action="{{ route('admin.applications.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-6">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 18px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; pointer-events: none;"></i>
                <input type="text" name="search" class="form-control rounded-pill m3e-input ps-5 pe-4 py-2" placeholder="Cari nama aplikasi, deskripsi, slug..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="position-relative">
                <i class="fa-solid fa-circle-dot position-absolute text-muted" style="left: 18px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; pointer-events: none; z-index: 4;"></i>
                <select name="status" class="form-select rounded-pill m3e-select ps-5 pe-4 py-2">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-filter me-1.5"></i> Terapkan
            </button>
        </div>
    </form>
    
    <!-- Quick Filter Chips -->
    <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top border-secondary-subtle flex-wrap">
        <span class="fs-8 fw-bold text-muted me-2"><i class="fa-solid fa-bolt text-warning me-1"></i> Filter Cepat:</span>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ !request()->anyFilled(['status', 'search']) ? 'btn-primary' : 'btn-tonal' }}">
            Semua Aplikasi
        </a>
        <a href="{{ route('admin.applications.index', ['status' => 'active']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('status') == 'active' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-circle-check me-1 text-success"></i> Aktif
        </a>
        <a href="{{ route('admin.applications.index', ['status' => 'inactive']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('status') == 'inactive' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-circle-xmark me-1 text-danger"></i> Non-Aktif
        </a>
    </div>
</div>

<!-- Applications Cards Grid M3 Expressive -->
<div class="row g-3">
    @forelse($applications as $app)
        <div class="col-md-4">
            <div class="card-m3 p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-2.5 rounded-circle d-inline-flex align-items-center justify-content-center text-primary overflow-hidden" style="width: 44px; height: 44px; background-color: var(--md-sys-color-primary-container);">
                            @if($app->icon && !in_array($app->icon, ['box', 'desktop', 'window-maximize']))
                                <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fa-solid fa-{{ $app->icon ?: 'cube' }} fs-5"></i>
                            @endif
                        </div>
                        <span class="badge {{ $app->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-1 fs-8 fw-bold">
                            {{ strtoupper($app->status) }}
                        </span>
                    </div>

                    <h5 class="fw-bold mb-1 text-dark fs-5" style="font-family: var(--font-heading);">{{ $app->name }}</h5>
                    <small class="text-muted d-block mb-2">Slug: <code class="px-2 py-0.5 bg-light rounded text-primary">{{ $app->slug }}</code></small>
                    <p class="text-muted fs-7 mb-3 text-truncate-2">{{ $app->description ?: 'Aplikasi terintegrasi Single Sign-On KPKNL Palembang.' }}</p>

                    <div class="p-3 rounded-4 mb-3 fs-7" style="background-color: var(--md-sys-color-surface-container-high);">
                        <div class="text-muted fs-8 fw-semibold text-uppercase" style="letter-spacing: 0.04em;">URL Target:</div>
                        <a href="{{ $app->url }}" target="_blank" class="text-primary text-break fw-medium text-decoration-none">{{ $app->url }}</a>
                        <div class="mt-2 text-muted fs-8 fw-semibold text-uppercase" style="letter-spacing: 0.04em;">User Terdaftar:</div>
                        <span class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ $app->users_count }} User</span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-secondary-subtle">
                    <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-key me-1"></i> Credentials & User
                    </a>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-tonal btn-sm rounded-circle" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('admin.applications.destroy', $app->id) }}" method="POST" class="d-inline form-delete-app" data-app-name="{{ $app->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
            <h6 class="fw-bold text-dark">Belum ada aplikasi yang terdaftar.</h6>
            <p class="fs-7 mb-0">Klik tombol "Tambah Aplikasi Baru" untuk mengintegrasikan aplikasi internal KPKNL Palembang.</p>
        </div>
    @endforelse
</div>

<div class="mt-4 px-2">
    {{ $applications->links() }}
</div>
@endsection

@section('scripts')
<script>
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
                    cancelButton: 'btn btn-tonal rounded-pill px-4'
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

