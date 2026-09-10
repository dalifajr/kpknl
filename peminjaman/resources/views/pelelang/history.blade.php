@extends('layouts.app')

@section('title', 'Riwayat Risalah Pelelang')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Riwayat Risalah Saya</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Seluruh data risalah (Minuta, TAP, Batal) yang telah Anda ajukan dan tervalidasi.</p>
        </div>
        <a href="{{ route('pelelang.dashboard') }}" class="btn btn-outline">
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Search Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('pelelang.history') }}" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pemohon..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('pelelang.history') }}" class="btn btn-outline">Reset</a>
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
                    <th>Tanggal Validasi</th>
                    <th>Pemohon Lelang</th>
                    <th>Box / Lemari</th>
                    <th>Status Ketersediaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historyList as $index => $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1;">
                                {{ $item->jenis }}
                            </span>
                        </td>
                        <td>{{ $item->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tgl_validasi?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>Box: {{ $item->box ?? '-' }} / Lemari: {{ $item->lemari ?? '-' }}</td>
                        <td>
                            <span class="badge {{ strtolower($item->status) === 'tersedia' ? 'badge-tersedia' : 'badge-sedang_dipinjam' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Belum ada riwayat risalah yang tervalidasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
