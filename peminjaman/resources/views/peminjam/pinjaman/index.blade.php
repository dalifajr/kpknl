@extends('layouts.app')

@section('title', 'Daftar Pinjaman Saya')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Daftar Pinjaman Saya</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Pantau status peminjaman, konfirmasi penerimaan berkas fisik, dan ajukan pengembalian risalah.</p>
        </div>
        <a href="{{ route('peminjam.katalog') }}" class="btn btn-primary">
            <span>➕ Pinjam Risalah Baru</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('peminjam.pinjaman') }}" style="display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pemohon..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="Proses Peminjaman" {{ request('status') === 'Proses Peminjaman' ? 'selected' : '' }}>Proses Peminjaman</option>
                <option value="Menunggu Konfirmasi Peminjam" {{ request('status') === 'Menunggu Konfirmasi Peminjam' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                <option value="Sedang Dipinjam" {{ request('status') === 'Sedang Dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="Proses Pengembalian" {{ request('status') === 'Proses Pengembalian' ? 'selected' : '' }}>Proses Pengembalian</option>
                <option value="Sudah Dikembalikan" {{ request('status') === 'Sudah Dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('peminjam.pinjaman') }}" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Risalah</th>
                    <th>Pelelang</th>
                    <th>Pemohon Lelang</th>
                    <th>Box / Lemari</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pinjamanList as $index => $item)
                    <tr>
                        <td>{{ $pinjamanList->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td>{{ $item->nama_pelelang }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>Box: {{ $item->box ?? '-' }} / Lemari: {{ $item->lemari ?? '-' }}</td>
                        <td>{{ $item->tgl_peminjaman?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tgl_pengembalian?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $item->status === 'Sedang Dipinjam' ? 'badge-sedang_dipinjam' : ($item->status === 'Sudah Dikembalikan' ? 'badge-tersedia' : 'badge-pending') }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if ($item->status === 'Menunggu Konfirmasi Peminjam')
                                <form action="{{ route('peminjam.pinjaman.terima', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi bahwa Anda telah menerima fisik risalah ini?')">
                                        ✓ Konfirmasi Terima
                                    </button>
                                </form>
                            @elseif ($item->status === 'Sedang Dipinjam')
                                <form action="{{ route('peminjam.pinjaman.kembalikan', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Ajukan pengembalian untuk risalah ini?')">
                                        ↩️ Kembalikan Berkas
                                    </button>
                                </form>
                            @elseif ($item->status === 'Proses Peminjaman')
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Menunggu Persetujuan Admin</span>
                            @elseif ($item->status === 'Proses Pengembalian')
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Menunggu Verifikasi Admin</span>
                            @else
                                <span style="font-size: 0.8rem; color: var(--success); font-weight: 600;">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Belum ada riwayat peminjaman risalah.
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
