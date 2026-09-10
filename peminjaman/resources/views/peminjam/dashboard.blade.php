@extends('layouts.app')

@section('title', 'Dashboard Peminjam')

@section('content')
<div class="grafik-hero-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1>Dashboard Peminjam</h1>
            <p>Selamat datang, <strong>{{ Auth::user()->username }}</strong>. Akses katalog risalah dan pantau status peminjaman berkas Anda.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('peminjam.katalog') }}" class="btn btn-primary">
                🔍 Pilih & Pinjam Risalah
            </a>
            <a href="{{ route('peminjam.pinjaman') }}" class="btn btn-secondary">
                📑 Pinjaman Saya
            </a>
        </div>
    </div>
</div>

@if ($waitingConfirmation->isNotEmpty())
    <div class="alert alert-warning">
        <span style="font-size: 1.2rem;">🔔</span>
        <div>
            <strong>Perhatian:</strong> Terdapat <strong>{{ $waitingConfirmation->count() }}</strong> berkas risalah yang telah disetujui Admin dan siap untuk Anda terima.
            <a href="{{ route('peminjam.pinjaman') }}" style="font-weight: 700; margin-left: 8px;">Lihat Pinjaman →</a>
        </div>
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card stat-info">
        <div class="stat-label">Pinjaman Aktif Saat Ini</div>
        <div class="stat-value">{{ $activeLoans->count() }}</div>
        <a href="{{ route('peminjam.pinjaman') }}" style="font-size: 0.82rem; margin-top: 8px; font-weight: 700; display: inline-block;">Lihat detail →</a>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-label">Menunggu Konfirmasi Anda</div>
        <div class="stat-value">{{ $waitingConfirmation->count() }}</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-label">Total Riwayat Peminjaman</div>
        <div class="stat-value">{{ $totalBorrowed }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title" style="font-size: 1.15rem;">Berkas yang Sedang Anda Pinjam</h3>
            <p style="color: var(--text-muted); font-size: 0.82rem;">Daftar berkas aktif yang saat ini berada dalam penggunaan Anda.</p>
        </div>
        <a href="{{ route('peminjam.pinjaman') }}" class="btn btn-sm btn-outline">Lihat Semua Pinjaman</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Risalah</th>
                    <th>Pelelang</th>
                    <th>Pemohon</th>
                    <th>Box / Lemari</th>
                    <th>Tanggal Pinjam</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activeLoans as $loan)
                    <tr>
                        <td><strong>{{ $loan->no_risalah }}</strong></td>
                        <td>{{ $loan->nama_pelelang }}</td>
                        <td>{{ $loan->pemohon_lelang }}</td>
                        <td>Box {{ $loan->box ?? '-' }} / Lemari {{ $loan->lemari ?? '-' }}</td>
                        <td>{{ $loan->tgl_peminjaman?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $loan->status === 'Sedang Dipinjam' ? 'badge-sedang_dipinjam' : ($loan->status === 'Sudah Dikembalikan' ? 'badge-tersedia' : 'badge-pending') }}">
                                {{ $loan->status }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if ($loan->status === 'Menunggu Konfirmasi Peminjam')
                                <form action="{{ route('peminjam.pinjaman.terima', $loan->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi bahwa Anda telah menerima fisik risalah ini?')">Terima Berkas</button>
                                </form>
                            @elseif ($loan->status === 'Sedang Dipinjam')
                                <form action="{{ route('peminjam.pinjaman.kembalikan', $loan->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Ajukan pengembalian untuk risalah ini?')">Kembalikan</button>
                                </form>
                            @else
                                <span style="font-size: 0.8rem; color: var(--text-muted);">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                            Saat ini Anda tidak memiliki berkas risalah yang sedang dipinjam.
                            <div style="margin-top: 12px;">
                                <a href="{{ route('peminjam.katalog') }}" class="btn btn-sm btn-primary">Buka Katalog Risalah</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
