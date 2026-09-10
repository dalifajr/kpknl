@extends('layouts.app')

@section('title', 'Daftar Risalah Perlu Revisi')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Daftar Risalah Perlu Revisi</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Risalah berikut dikembalikan oleh Administrator untuk diperbaiki sebelum dapat divalidasi.</p>
        </div>
        <a href="{{ route('pelelang.dashboard') }}" class="btn btn-outline">
            Dashboard
        </a>
    </div>

    <!-- Search Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('pelelang.revisi.index') }}" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pemohon..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('pelelang.revisi.index') }}" class="btn btn-outline">Reset</a>
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
                    <th>Tanggal Dikembalikan</th>
                    <th>Catatan Admin</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($revisiList as $index => $item)
                    <tr>
                        <td>{{ $revisiList->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td><span class="badge" style="background: #fed7aa; color: #9a3412;">{{ strtoupper($item->jenis) }}</span></td>
                        <td>{{ $item->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tgl_revisi?->format('d/m/Y') ?? '-' }}</td>
                        <td style="max-width: 250px;">
                            <div style="font-size: 0.85rem; color: #991b1b; font-weight: 500;">
                                {{ $item->catatan ?: 'Lihat rincian perbaikan pada form edit' }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('pelelang.revisi.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                ✏️ Perbaiki & Kirim Ulang
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            🎉 Tidak ada data risalah yang memerlukan revisi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan {{ $revisiList->firstItem() ?? 0 }} - {{ $revisiList->lastItem() ?? 0 }} dari {{ $revisiList->total() }} data
        </div>
        <div>
            {{ $revisiList->links() }}
        </div>
    </div>
</div>
@endsection
