@extends('layouts.app')

@section('title', 'Dashboard Admin | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Dashboard Admin Aplikasi</h3>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda memiliki akses pengawasan ke aplikasi yang di-assign oleh Superadmin.</p>
    </div>
</div>


<!-- Assigned Applications Grid -->
<div class="card-m3 p-4 mb-4">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-shapes me-2 text-primary"></i>Aplikasi yang Dapat Anda Akses</h5>
    <div class="row g-3">
        @forelse($assignedApps as $app)
            <div class="col-md-4 col-sm-6">
                <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-between" style="background-color: var(--md-sys-color-surface-container-high); border-radius: var(--md-shape-corner-lg);">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2.5 rounded-circle d-inline-flex align-items-center justify-content-center text-primary overflow-hidden" style="width: 44px; height: 44px; background-color: var(--md-sys-color-primary-container);">
                                    @if($app->icon && !in_array($app->icon, ['box', 'desktop', 'window-maximize']))
                                        <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <i class="fa-solid fa-{{ $app->icon ?: 'box' }} fs-5"></i>
                                    @endif
                                </div>
                                <span class="badge badge-admin px-3 py-1">Integrated App</span>
                            </div>
                            <span class="badge {{ $app->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2.5 py-1 fs-8 fw-bold">{{ strtoupper($app->status) }}</span>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark fs-5" style="font-family: var(--font-heading);">{{ $app->name }}</h6>
                        <p class="text-muted fs-7 mb-3 text-truncate-2" style="min-height: 40px;">{{ $app->description ?: 'Aplikasi internal KPKNL Palembang.' }}</p>
                    </div>
                    <a href="{{ route('oauth.authorize', ['client_id' => $app->client_id]) }}" class="btn btn-primary btn-sm w-100 rounded-pill fw-bold py-2 mt-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Aplikasi (SSO)
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fa-3x mb-2 d-block opacity-50"></i> Belum ada aplikasi yang di-assign ke akun Anda. Silakan hubungi Superadmin.
            </div>
        @endforelse
    </div>
</div>
@endsection

