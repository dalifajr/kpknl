@extends('layouts.app')

@section('title', 'Log Aktivitas | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Log Aktivitas Sistem</h3>
        <p class="text-muted mb-0">Catatan audit jejak aktivitas pengguna dan sistem SSO KPKNL Palembang.</p>
    </div>
</div>

<!-- Filter Card M3 Expressive -->
<div class="card-m3 p-3 mb-4">
    <form action="{{ route('activity-logs.index') }}" method="GET" class="row g-2 align-items-center">
        @if(auth()->user()->isSuperadmin())
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">-- Semua User --</option>
                    @foreach($availableUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->username }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-3">
            <select name="application_id" class="form-select">
                <option value="">-- Semua Aplikasi --</option>
                @foreach($availableApps as $app)
                    <option value="{{ $app->id }}" {{ request('application_id') == $app->id ? 'selected' : '' }}>{{ $app->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <input type="text" name="action" class="form-control" placeholder="Cari aksi (login, user...)" value="{{ request('action') }}">
        </div>

        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" title="Dari Tanggal" value="{{ request('date_from') }}">
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            <a href="{{ route('activity-logs.index') }}" class="btn btn-tonal rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="fa-solid fa-rotate-left"></i></a>
        </div>
    </form>
</div>

<!-- Logs Table M3 Expressive -->
<div class="table-expressive-container p-3 mb-4">
    <div class="table-responsive">
        <table class="table align-middle fs-7 mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aplikasi</th>
                    <th>Aksi</th>
                    <th>Deskripsi Aktivitas</th>
                    <th>IP & Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted text-nowrap fw-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            @if($log->user)
                                <div class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ $log->user->name }}</div>
                                <small class="text-muted">@ {{ $log->user->username }}</small>
                            @else
                                <span class="text-muted">Guest / Sistem</span>
                            @endif
                        </td>
                        <td>
                            @if($log->application)
                                <span class="badge badge-admin px-3 py-1">{{ $log->application->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold">{{ $log->action }}</span>
                        </td>
                        <td class="fw-medium text-dark">{{ $log->description }}</td>
                        <td class="text-muted">
                            <code class="px-2 py-0.5 bg-light rounded text-primary">{{ $log->ip_address }}</code>
                            @if($log->user_agent)
                                <small class="d-block text-truncate text-secondary mt-1" style="max-width: 200px;" title="{{ $log->user_agent }}">{{ $log->user_agent }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Tidak ada catatan aktivitas log yang sesuai dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-2">
        {{ $logs->links() }}
    </div>
</div>
@endsection

