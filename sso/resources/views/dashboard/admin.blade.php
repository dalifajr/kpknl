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
                <i class="fa-solid fa-box-open fa-3x mb-2 d-block opacity-50"></i> Belum ada aplikasi yang di-assign ke akun Anda. Silakan hubungi Superadmin.
            </div>
        @endforelse
    </div>
</div>
@endsection

