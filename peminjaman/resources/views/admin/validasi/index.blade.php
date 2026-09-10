@extends('layouts.app')

@section('title', 'Antrian Validasi Risalah')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Antrian Validasi Risalah</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Verifikasi pengajuan risalah dari pejabat lelang dan tetapkan nomor Box serta Lemari penyimpanan arsip.</p>
        </div>
        <div style="font-size: 0.9rem; font-weight: 600; background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 20px;">
            Total Pending: {{ $pendingList->total() }}
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.validasi.index') }}" style="display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pelelang / pemohon..." value="{{ request('search') }}">
            <select name="jenis" class="form-control">
                <option value="">Semua Jenis</option>
                <option value="minuta" {{ request('jenis') === 'minuta' ? 'selected' : '' }}>Minuta</option>
                <option value="tap" {{ request('jenis') === 'tap' ? 'selected' : '' }}>TAP</option>
                <option value="batal" {{ request('jenis') === 'batal' ? 'selected' : '' }}>Batal</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.validasi.index') }}" class="btn btn-outline">Reset</a>
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
                    <th>Pelelang</th>
                    <th>Pemohon Lelang</th>
                    <th>Keterangan</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingList as $index => $p)
                    <tr>
                        <td>{{ $pendingList->firstItem() + $index }}</td>
                        <td><strong>{{ $p->no_risalah }}</strong></td>
                        <td><span class="badge" style="background: #e0f2fe; color: #0369a1;">{{ strtoupper($p->jenis) }}</span></td>
                        <td>{{ $p->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $p->nama_pelelang }}</td>
                        <td>{{ $p->pemohon_lelang }}</td>
                        <td>{{ $p->keterangan ?? '-' }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.validasi.show', $p->id) }}" class="btn btn-sm btn-primary">
                                🔍 Tinjau & Validasi
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            🎉 Tidak ada risalah pending yang memerlukan validasi saat ini.
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
