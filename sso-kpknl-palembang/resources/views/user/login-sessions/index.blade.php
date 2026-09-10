@extends('layouts.app')

@section('title', 'Sesi Login & Perangkat | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Sesi Login & Perangkat Terhubung</h3>
        <p class="text-muted mb-0">Daftar browser dan perangkat yang sedang atau pernah digunakan untuk mengakses akun SSO Anda.</p>
    </div>
</div>

<div class="table-expressive-container p-3 mb-4">
    <div class="table-responsive">
        <table class="table align-middle fs-7 mb-0">
            <thead>
                <tr>
                    <th>Waktu Login</th>
                    <th>Browser</th>
                    <th>Sistem Operasi / Perangkat</th>
                    <th>Tipe Perangkat</th>
                    <th>IP Address</th>
                    <th>Status Sesi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr style="{{ $session->session_id === $currentSessionId ? 'background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);' : '' }}">
                        <td class="text-muted fw-medium">{{ $session->login_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">
                                <i class="fa-solid fa-globe me-1 text-primary"></i> {{ $session->browser }} <span class="fs-7 fw-normal text-muted">(v{{ $session->browser_version }})</span>
                            </div>
                        </td>
                        <td class="fw-medium">
                            <i class="fa-solid fa-desktop me-1 text-secondary"></i> {{ $session->platform }} <span class="text-muted">({{ $session->device_model }})</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold">
                                <i class="fa-solid fa-{{ $session->device_type === 'mobile' ? 'mobile-screen' : ($session->device_type === 'tablet' ? 'tablet' : 'laptop') }} me-1"></i>
                                {{ strtoupper($session->device_type) }}
                            </span>
                        </td>
                        <td><code class="px-2 py-0.5 bg-light rounded text-primary">{{ $session->ip_address }}</code></td>
                        <td>
                            @if($session->is_active && $session->session_id === $currentSessionId)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-circle me-1"></i> PERANGKAT INI</span>
                            @elseif($session->is_active)
                                <span class="badge badge-admin rounded-pill px-3 py-1"><i class="fa-solid fa-circle me-1"></i> SESI AKTIF</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold">LOGOUT</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($session->is_active && $session->session_id !== $currentSessionId)
                                <form action="{{ route('login-sessions.destroy', $session->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Akhiri sesi login pada perangkat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-bold">
                                        <i class="fa-solid fa-power-off me-1"></i> Akhiri Sesi
                                    </button>
                                </form>
                            @elseif($session->session_id === $currentSessionId)
                                <span class="fw-bold text-primary fs-7">Aktif Sekarang</span>
                            @else
                                <span class="text-muted fs-8">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Belum ada riwayat sesi login.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-2">
        {{ $sessions->links() }}
    </div>
</div>
@endsection

