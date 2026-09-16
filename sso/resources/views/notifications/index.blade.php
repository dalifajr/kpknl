@extends('layouts.app')

@section('title', 'Pusat Notifikasi Personal | SSO KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Pusat Notifikasi Personal</h3>
        <p class="text-muted mb-0">Pemberitahuan aktivitas login Anda dan notifikasi dari aplikasi terintegrasi.</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-tonal btn-sm rounded-pill px-3 py-2 fw-bold text-primary">
                <i class="fa-solid fa-check-double me-1.5"></i> Tandai Semua Dibaca
            </button>
        </form>
    </div>
</div>

<!-- Filter & Search Section -->
<div class="card-m3 p-4 mb-4">
    <form action="{{ route('notifications.index') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label for="search" class="form-label fw-bold fs-7 text-dark">Cari Notifikasi</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" id="search" class="form-control border-start-0" placeholder="Judul, pesan, atau nama aplikasi..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <label for="start_date" class="form-label fw-bold fs-7 text-dark">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>

        <div class="col-md-3">
            <label for="end_date" class="form-label fw-bold fs-7 text-dark">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold fs-7">
                <i class="fa-solid fa-filter me-1"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'start_date', 'end_date', 'action_type']))
                <a href="{{ route('notifications.index') }}" class="btn btn-tonal rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Notification Feed Section -->
<div class="card-m3 p-0 overflow-hidden">
    <div class="p-3.5 border-bottom d-flex align-items-center justify-content-between" style="background-color: var(--md-sys-color-surface-container-high);">
        <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-bell me-2 text-primary"></i>Notifikasi Personal Anda
        </h6>
        <span class="badge rounded-pill bg-primary fs-8">Total {{ $notifications->total() }} Data</span>
    </div>

    <div class="list-group list-group-flush border-0">
        @forelse($notifications as $item)
            <div class="list-group-item p-3.5 border-bottom d-flex align-items-start gap-3 bg-transparent {{ is_null($item->read_at) ? 'bg-primary-subtle bg-opacity-10' : '' }}">
                <div class="p-2.5 rounded-circle text-primary bg-primary-subtle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="{{ $item->icon ?? 'fa-solid fa-bell' }} fs-5"></i>
                </div>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">
                                {{ $item->title }}
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill fs-8">{{ $item->app_name }}</span>
                            @if(is_null($item->read_at))
                                <span class="badge bg-danger rounded-pill fs-8">Baru</span>
                            @endif
                        </div>
                        <small class="text-muted fs-8">
                            <i class="fa-regular fa-clock me-1"></i>{{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }} ({{ $item->created_at ? $item->created_at->diffForHumans() : '' }})
                        </small>
                    </div>

                    <p class="text-secondary mb-2 fs-7">{{ $item->message }}</p>

                    @if($item->link)
                        <a href="{{ route('notifications.read', $item->id) }}" class="btn btn-sm btn-tonal rounded-pill px-3 py-1 text-primary fw-bold fs-8">
                            Buka Link Notifikasi <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="fa-solid fa-bell-slash fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                <h5 class="fw-bold text-dark mb-1">Tidak Ada Notifikasi</h5>
                <p class="fs-7 mb-0">Belum ada pemberitahuan personal yang sesuai dengan filter Anda.</p>
            </div>
        @endforelse

    </div>

    @if($notifications->hasPages())
        <div class="p-3 border-top d-flex justify-content-center" style="background-color: var(--md-sys-color-surface-container-high);">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
