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
            <div class="col-md-4 col-sm-6">
                <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-between" style="background-color: var(--md-sys-color-surface-container-high); border-radius: var(--md-shape-corner-lg);">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2.5 rounded-circle d-inline-flex align-items-center justify-content-center text-primary overflow-hidden" style="width: 44px; height: 44px; background-color: var(--md-sys-color-primary-container);">
                                    @if($app->icon && !in_array($app->icon, ['box', 'desktop', 'window-maximize', 'cube', 'cubes']))
                                        <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-cube text-primary fs-5\'></i>';">
                                    @else
                                        <i class="fa-solid fa-{{ $app->icon ?: 'box' }} fs-5"></i>
                                    @endif
                                </div>
                                <span class="badge badge-admin px-3 py-1">OAuth2 App</span>
                            </div>
                            <span class="badge {{ $app->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2.5 py-1 fs-8 fw-bold">{{ strtoupper($app->status) }}</span>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark fs-5" style="font-family: var(--font-heading);">{{ $app->name }}</h6>
                        <p class="text-muted fs-7 mb-3 text-truncate-2" style="min-height: 40px;">{{ $app->description ?: 'Aplikasi internal KPKNL Palembang.' }}</p>
                    </div>
                    <a href="{{ route('oauth.authorize', ['client_id' => $app->client_id]) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm w-100 rounded-pill fw-bold py-2 mt-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Aplikasi (SSO)
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fa-3x mb-2 d-block opacity-50"></i> Belum ada aplikasi terintegrasi. Klik tombol "Tambah Aplikasi" untuk menambahkan.
            </div>
        @endforelse
    </div>
</div>
@endsection

