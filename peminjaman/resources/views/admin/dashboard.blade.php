@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="grafik-hero-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1>Dashboard Administrator</h1>
            <p>Selamat datang di Pusat Kendali Pengelolaan Risalah Lelang KPKNL.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.validasi.index') }}" class="btn btn-warning">
                ⚡ Validasi Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-primary">
                📋 Kelola Pinjaman
            </a>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-warning">
        <div class="stat-label">Menunggu Validasi</div>
        <div class="stat-value">{{ number_format($pendingCount) }}</div>
        <a href="{{ route('admin.validasi.index') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat antrian →</a>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-label">Risalah Minuta</div>
        <div class="stat-value">{{ number_format($minutaCount) }}</div>
        <a href="{{ route('risalah.minuta') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat daftar →</a>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-label">Risalah TAP</div>
        <div class="stat-value">{{ number_format($tapCount) }}</div>
        <a href="{{ route('risalah.tap') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat daftar →</a>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-label">Risalah Batal</div>
        <div class="stat-value">{{ number_format($batalCount) }}</div>
        <a href="{{ route('risalah.batal') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat daftar →</a>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-label">Pinjaman Aktif</div>
        <div class="stat-value">{{ number_format($loanActiveCount) }}</div>
        <a href="{{ route('admin.peminjaman.index') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Kelola pinjaman →</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
    <!-- Pending Validation Queue -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title" style="font-size: 1.15rem;">Antrian Validasi Terbaru</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem;">Berkas yang baru diajukan oleh Pejabat Lelang.</p>
            </div>
            <a href="{{ route('admin.validasi.index') }}" class="btn btn-sm btn-outline">Semua Pending</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Risalah</th>
                        <th>Jenis</th>
                        <th>Pelelang</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPending as $p)
                        <tr>
                            <td><strong>{{ $p->no_risalah }}</strong></td>
                            <td><span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">{{ strtoupper($p->jenis) }}</span></td>
                            <td>{{ $p->nama_pelelang }}</td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.validasi.show', $p->id) }}" class="btn btn-sm btn-primary">Tinjau</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 26px;">
                                Tidak ada risalah yang sedang menunggu validasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Loans -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title" style="font-size: 1.15rem;">Peminjaman Terkini</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem;">Aktivitas permohonan peminjaman berkas fisik.</p>
            </div>
            <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-sm btn-outline">Semua Pinjaman</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Risalah</th>
                        <th>Peminjam</th>
                        <th>Status</th>
                        <th>Tgl Pinjam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLoans as $l)
                        <tr>
                            <td><strong>{{ $l->no_risalah }}</strong></td>
                            <td>{{ $l->nama_peminjam }}</td>
                            <td>
                                <span class="badge {{ $l->status === 'Sedang Dipinjam' ? 'badge-sedang_dipinjam' : ($l->status === 'Sudah Dikembalikan' ? 'badge-tersedia' : 'badge-pending') }}">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td>{{ $l->tgl_peminjaman?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 26px;">
                                Belum ada aktivitas peminjaman terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
