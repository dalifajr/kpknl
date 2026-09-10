@extends('layouts.app')

@section('title', 'Kelola Peminjaman Risalah')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Kelola Permohonan Peminjaman Risalah</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Setujui permohonan peminjaman risalah yang diajukan oleh pengguna.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('export.peminjaman', request()->query()) }}" class="btn btn-success">
                <span>📊 Export Data Peminjaman</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.peminjaman.index') }}" style="display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / peminjam / pemohon..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="Proses Peminjaman" {{ request('status') === 'Proses Peminjaman' ? 'selected' : '' }}>Proses Peminjaman</option>
                <option value="Menunggu Konfirmasi Peminjam" {{ request('status') === 'Menunggu Konfirmasi Peminjam' ? 'selected' : '' }}>Menunggu Konfirmasi Peminjam</option>
                <option value="Sedang Dipinjam" {{ request('status') === 'Sedang Dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="Proses Pengembalian" {{ request('status') === 'Proses Pengembalian' ? 'selected' : '' }}>Proses Pengembalian</option>
                <option value="Sudah Dikembalikan" {{ request('status') === 'Sudah Dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Risalah</th>
                    <th>Peminjam</th>
                    <th>Pelelang</th>
                    <th>Pemohon Lelang</th>
                    <th>Box / Lemari</th>
                    <th>Tgl Pinjam</th>
                    <th>Status</th>
                    <th>Alasan</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pinjamanList as $index => $item)
                    <tr>
                        <td>{{ $pinjamanList->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td><strong>{{ $item->nama_peminjam }}</strong></td>
                        <td>{{ $item->nama_pelelang }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>Box: {{ $item->box ?? '-' }} / Lemari: {{ $item->lemari ?? '-' }}</td>
                        <td>{{ $item->tgl_peminjaman?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $item->status === 'Sedang Dipinjam' ? 'badge-sedang_dipinjam' : ($item->status === 'Sudah Dikembalikan' ? 'badge-tersedia' : 'badge-pending') }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td style="max-width: 200px; font-size: 0.8rem;">{{ $item->alasan_peminjaman ?? '-' }}</td>
                        <td style="text-align: center;">
                            @if ($item->status === 'Proses Peminjaman')
                                <form action="{{ route('admin.peminjaman.approve', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Validasi peminjaman risalah No. {{ $item->no_risalah }}?')">
                                        ✓ Validasi Peminjaman
                                    </button>
                                </form>
                            @elseif ($item->status === 'Proses Pengembalian')
                                <form action="{{ route('admin.pengembalian.approve', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Validasi pengembalian risalah No. {{ $item->no_risalah }}?')">
                                        ✓ Validasi Pengembalian
                                    </button>
                                </form>
                            @elseif ($item->status === 'Sedang Dipinjam')
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Menunggu Peminjam Mengembalikan</span>
                            @elseif ($item->status === 'Sudah Dikembalikan')
                                <span style="font-size: 0.8rem; color: var(--success); font-weight: 600;">✓ Selesai Dikembalikan</span>
                            @else
                                <span style="font-size: 0.8rem; color: var(--text-muted);">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Tidak ada data peminjaman yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan {{ $pinjamanList->firstItem() ?? 0 }} - {{ $pinjamanList->lastItem() ?? 0 }} dari {{ $pinjamanList->total() }} data
        </div>
        <div>
            {{ $pinjamanList->links() }}
        </div>
    </div>
</div>
@endsection
