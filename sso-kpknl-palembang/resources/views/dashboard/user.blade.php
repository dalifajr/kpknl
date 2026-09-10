@extends('layouts.app')

@section('title', 'Dashboard User | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Portal Aplikasi SSO</h3>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Klik ikon aplikasi untuk langsung masuk tanpa login ulang.</p>
    </div>
</div>

<!-- Assigned Applications Grid -->
<div class="card-m3 p-4 mb-4">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-shapes me-2 text-primary"></i>Aplikasi Terintegrasi Anda</h5>
    <div class="row g-3">
        @forelse($assignedApps as $app)
            <div class="col-md-4 col-sm-6">
                <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-between" style="background-color: var(--md-sys-color-surface-container-high); border-radius: var(--md-shape-corner-lg);">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="p-2.5 rounded-circle d-inline-flex align-items-center justify-content-center text-primary overflow-hidden" style="width: 44px; height: 44px; background-color: var(--md-sys-color-primary-container);">
                                @if($app->icon && !in_array($app->icon, ['box', 'desktop', 'window-maximize']))
                                    <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fa-solid fa-{{ $app->icon ?: 'box' }} fs-5"></i>
                                @endif
                            </div>
                            <span class="badge badge-user px-3 py-1">Tersedia</span>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark fs-5" style="font-family: var(--font-heading);">{{ $app->name }}</h6>
                        <p class="text-muted fs-7 mb-3 text-truncate-2" style="min-height: 40px;">{{ $app->description ?: 'Aplikasi internal KPKNL Palembang.' }}</p>
                    </div>
                    <a href="{{ route('oauth.authorize', ['client_id' => $app->client_id]) }}" class="btn btn-primary btn-sm w-100 rounded-pill fw-bold py-2 mt-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> MASUK APLIKASI
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
                <h6 class="fw-bold text-dark">Belum ada aplikasi yang di-assign untuk akun Anda.</h6>
                <p class="fs-7 mb-0">Silakan hubungi Superadmin KPKNL Palembang untuk mendapatkan akses aplikasi yang dibutuhkan.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

