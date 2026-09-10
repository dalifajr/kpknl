@extends('layouts.app')

@section('title', 'Data Risalah Sudah Dikembalikan')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Data Risalah Sudah Dikembalikan</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Daftar seluruh berkas risalah lelang yang telah disetujui pengembaliannya dan telah tersimpan kembali di rak arsip.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('export.peminjaman', ['status' => 'Sudah Dikembalikan']) }}" class="btn btn-success">
                📊 Export Excel / CSV
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.pengembalian.index') }}" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / nama peminjam / pemohon..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('admin.pengembalian.index') }}" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Risalah</th>
                    <th>Nama Peminjam</th>
                    <th>Pelelang</th>
                    <th>Pemohon Lelang</th>
                    <th>Box / Lemari</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Pengembalian</th>
                    <th>Status</th>
                    <th>Alasan Peminjaman</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengembalianList as $index => $item)
                    <tr>
                        <td>{{ $pengembalianList->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td><strong>{{ $item->nama_peminjam }}</strong></td>
                        <td>{{ $item->nama_pelelang }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>Box: {{ $item->box ?? '-' }} / Lemari: {{ $item->lemari ?? '-' }}</td>
                        <td>{{ $item->tgl_peminjaman?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tgl_pengembalian?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge badge-tersedia">
                                ✓ {{ $item->status }}
                            </span>
                        </td>
                        <td style="max-width: 220px; font-size: 0.85rem;">{{ $item->alasan_peminjaman ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Belum ada risalah yang berstatus sudah dikembalikan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan {{ $pengembalianList->firstItem() ?? 0 }} - {{ $pengembalianList->lastItem() ?? 0 }} dari {{ $pengembalianList->total() }} data yang sudah dikembalikan
        </div>
        <div>
            {{ $pengembalianList->links() }}
        </div>
    </div>
</div>
@endsection
