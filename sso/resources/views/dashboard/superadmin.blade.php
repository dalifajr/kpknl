@extends('layouts.app')

@section('title', 'Dashboard Superadmin | SSO KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Dashboard Superadmin</h3>
        <p class="text-muted mb-0">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong>. Anda memiliki kendali penuh atas sistem SSO.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
        </a>
    </div>
</div>

@if(auth()->user()->username === 'mardanus' && Hash::check('admin123', auth()->user()->password))
    <div class="alert alert-expressive alert-expressive-danger d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
            <div>
                <strong>Peringatan Keamanan:</strong> Anda masih menggunakan password default (<code>admin123</code>). Segera perbarui password Anda demi keamanan sistem!
            </div>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-sm btn-danger text-white rounded-pill px-3 ms-2">Ganti Password Sekarang</a>
    </div>
@endif



<!-- Integrated Applications Grid -->
<div class="card-m3 p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-shapes me-2 text-primary"></i>Aplikasi Terintegrasi Single Sign-On</h5>
        <a href="{{ route('admin.applications.index') }}" class="text-primary text-decoration-none fs-7 fw-bold">Lihat Semua <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
        @forelse($applications as $app)
            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                <a href="{{ route('oauth.authorize', ['client_id' => $app->client_id]) }}" target="_blank" rel="noopener noreferrer" class="app-launch-card p-3 rounded-4 h-100 d-flex flex-column justify-content-between" title="Buka {{ $app->name }}">
                    <div>
                        <!-- Horizontal Layout: Large Image on Left, Title & Desc on Right -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center overflow-hidden rounded-3 shadow-xs" style="width: 58px; height: 58px; background-color: #ffffff; border: 1px solid rgba(0,0,0,0.06);">
                                @if($app->icon_url)
                                    <img src="{{ $app->icon_url }}" alt="{{ $app->name }}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary\'><i class=\'fa-solid fa-cube fs-3\'></i></div>';">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                                        <i class="fa-solid fa-{{ $app->icon ?: 'cube' }} fs-3"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge {{ $app->status_badge_class }} rounded-pill px-2 py-0.5 fs-9 fw-bold">
                                        @if($app->is_maintenance)
                                            <i class="fa-solid fa-wrench me-1"></i>
                                        @endif
                                        {{ $app->status_label }}
                                    </span>
                                    <i class="fa-solid fa-arrow-up-right-from-square fs-9 app-launch-indicator" title="Buka di tab baru"></i>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark fs-7 lh-sm text-truncate app-title" title="{{ $app->name }}" style="font-family: var(--font-heading);">
                                    {{ $app->name }}
                                </h6>
                                <p class="text-secondary fs-9 mb-0 text-truncate-2" style="line-height: 1.35; max-height: 2.7em;" title="{{ $app->description }}">
                                    {{ $app->description ?: 'Aplikasi internal resmi KPKNL Palembang.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fa-3x mb-2 d-block opacity-50"></i> Belum ada aplikasi terintegrasi. Klik tombol "Tambah Aplikasi" untuk menambahkan.
            </div>
        @endforelse
    </div>
</div>
@endsection

