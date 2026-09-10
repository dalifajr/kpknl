@extends('layouts.app')

@section('title', 'Status Pengajuan Risalah Pending')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Status Pengajuan Risalah Pending</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Daftar pengajuan risalah Anda yang sedang menunggu verifikasi dan penomoran Box/Lemari oleh Admin.</p>
        </div>
        <a href="{{ route('pelelang.risalah.create') }}" class="btn btn-primary">
            <span>➕ Ajukan Risalah Baru</span>
        </a>
    </div>

    <!-- Search Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('pelelang.pending') }}" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pemohon..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('pelelang.pending') }}" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Risalah</th>
                    <th>Jenis</th>
                    <th>Tanggal Risalah</th>
                    <th>Pemohon Lelang</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingList as $index => $item)
                    <tr>
                        <td>{{ $pendingList->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td><span class="badge" style="background: #e0f2fe; color: #0369a1;">{{ strtoupper($item->jenis) }}</span></td>
                        <td>{{ $item->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td><span class="badge badge-pending">Menunggu Verifikasi Admin</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Tidak ada risalah pending saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan {{ $pendingList->firstItem() ?? 0 }} - {{ $pendingList->lastItem() ?? 0 }} dari {{ $pendingList->total() }} data
        </div>
        <div>
            {{ $pendingList->links() }}
        </div>
    </div>
</div>
@endsection
