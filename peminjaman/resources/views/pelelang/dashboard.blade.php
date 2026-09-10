@extends('layouts.app')

@section('title', 'Dashboard Pejabat Lelang')

@section('content')
<div class="grafik-hero-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1>Dashboard Pejabat Lelang</h1>
            <p>Selamat datang, <strong>{{ Auth::user()->username }}</strong>. Kelola dan ajukan berkas risalah lelang Anda di sini.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('pelelang.risalah.create') }}" class="btn btn-primary">
                ➕ Tambah Risalah Baru
            </a>
            <a href="{{ route('pelelang.history') }}" class="btn btn-secondary">
                📜 Riwayat Risalah Saya
            </a>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-warning">
        <div class="stat-label">Pengajuan Pending</div>
        <div class="stat-value">{{ number_format($myPendingCount) }}</div>
        <a href="{{ route('pelelang.pending') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat status →</a>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-label">Menunggu Revisi Anda</div>
        <div class="stat-value">{{ number_format($myRevisiCount) }}</div>
        <a href="{{ route('pelelang.revisi.index') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Perbaiki data →</a>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-label">Minuta Tervalidasi</div>
        <div class="stat-value">{{ number_format($myMinutaCount) }}</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-label">TAP Tervalidasi</div>
        <div class="stat-value">{{ number_format($myTapCount) }}</div>
    </div>
    <div class="stat-card stat-secondary">
        <div class="stat-label">Batal Tervalidasi</div>
        <div class="stat-value">{{ number_format($myBatalCount) }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
    <!-- Revisi Menunggu Tindakan -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title" style="font-size: 1.15rem; color: var(--danger-text);">⚠️ Perlu Perbaikan (Revisi)</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem;">Risalah yang dikembalikan oleh Admin untuk dilengkapi.</p>
            </div>
            <a href="{{ route('pelelang.revisi.index') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Risalah</th>
                        <th>Jenis</th>
                        <th>Tgl Dikembalikan</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentRevisi as $rev)
                        <tr>
                            <td><strong>{{ $rev->no_risalah }}</strong></td>
                            <td><span class="badge" style="background: #fee2e2; color: #991b1b; border-color: #fecaca;">{{ strtoupper($rev->jenis) }}</span></td>
                            <td>{{ $rev->tgl_revisi?->format('d/m/Y') ?? '-' }}</td>
                            <td style="text-align: center;">
                                <a href="{{ route('pelelang.revisi.edit', $rev->id) }}" class="btn btn-sm btn-warning">Perbaiki</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 26px;">
                                Tidak ada risalah yang memerlukan revisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Pengajuan Terakhir -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title" style="font-size: 1.15rem;">Pengajuan Pending Terakhir</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem;">Berkas yang baru Anda serahkan ke Admin.</p>
            </div>
            <a href="{{ route('pelelang.pending') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Risalah</th>
                        <th>Jenis</th>
                        <th>Tgl Risalah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPending as $pend)
                        <tr>
                            <td><strong>{{ $pend->no_risalah }}</strong></td>
                            <td><span class="badge" style="background: #f1f5f9; color: #334155;">{{ strtoupper($pend->jenis) }}</span></td>
                            <td>{{ $pend->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                            <td><span class="badge badge-pending">Menunggu Admin</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 26px;">
                                Belum ada pengajuan risalah pending.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
