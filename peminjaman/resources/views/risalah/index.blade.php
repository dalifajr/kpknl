@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">{{ $title }}</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Total Data: {{ number_format($data->total()) }} berkas risalah</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('export.' . $type, request()->query()) }}" class="btn btn-success">
                <span>📊 Export Excel / CSV</span>
            </a>
            @if (Auth::user()->isPelelang())
                <a href="{{ route('pelelang.risalah.create') }}" class="btn btn-primary">
                    <span>➕ Tambah Risalah</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Horizontal Filter Form -->
    <div class="filter-bar" style="overflow-x: auto;">
        <form method="GET" action="{{ route('risalah.' . $type) }}" style="display: flex; align-items: flex-end; gap: 10px; min-width: 980px;">
            <div style="flex: 2.2; min-width: 180px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Pencarian (No/Pelelang/Pemohon)</label>
                <input type="text" name="search" class="form-control" placeholder="Cari kata kunci..." value="{{ request('search') }}">
            </div>
            <div style="flex: 1; min-width: 85px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Box</label>
                <input type="text" name="box" class="form-control" placeholder="Box" value="{{ request('box') }}">
            </div>
            <div style="flex: 1; min-width: 85px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Lemari</label>
                <input type="text" name="lemari" class="form-control" placeholder="Lemari" value="{{ request('lemari') }}">
            </div>
            <div style="flex: 1.3; min-width: 120px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ request('status') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="sedang_dipinjam" {{ request('status') === 'sedang_dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 85px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Tahun</label>
                <input type="number" name="tahun" class="form-control" placeholder="YYYY" value="{{ request('tahun') }}" min="1990" max="{{ date('Y') + 1 }}">
            </div>
            <div style="flex: 1.1; min-width: 100px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Bulan Awal</label>
                <select name="bulan_awal" class="form-control">
                    <option value="">Bln Awal</option>
                    @foreach (['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'] as $num => $name)
                        <option value="{{ $num }}" {{ request('bulan_awal') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1.1; min-width: 100px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 4px;">Bulan Akhir</label>
                <select name="bulan_akhir" class="form-control">
                    <option value="">Bln Akhir</option>
                    @foreach (['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'] as $num => $name)
                        <option value="{{ $num }}" {{ request('bulan_akhir') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 16px;">Cari</button>
                <a href="{{ route('risalah.' . $type) }}" class="btn btn-outline" style="padding: 10px 12px;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor Risalah</th>
                    <th>Tanggal Risalah</th>
                    <th>Tanggal Validasi</th>
                    <th>Pelelang</th>
                    <th>Pemohon Lelang</th>
                    <th>Box / Lemari</th>
                    <th>Status</th>
                    <th>E-Risalah</th>
                    @if (Auth::user()->isAdmin())
                        <th style="width: 80px; text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    <tr>
                        <td>{{ $data->firstItem() + $index }}</td>
                        <td><strong>{{ $item->no_risalah }}</strong></td>
                        <td>{{ $item->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->tgl_validasi?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->nama_pelelang }}</td>
                        <td>{{ $item->pemohon_lelang }}</td>
                        <td>
                            @if ($item->box || $item->lemari)
                                <span style="font-size: 0.8rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                    Box: {{ $item->box ?? '-' }} | Lemari: {{ $item->lemari ?? '-' }}
                                </span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ strtolower($item->status) === 'tersedia' ? 'badge-tersedia' : 'badge-sedang_dipinjam' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                        <td>
                            @if ($item->link_erisalah)
                                <a href="{{ $item->link_erisalah }}" target="_blank" class="btn btn-sm btn-outline" style="font-size: 0.75rem;">Link ↗</a>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                            @endif
                        </td>
                        @if (Auth::user()->isAdmin())
                            <td style="text-align: center;">
                                <a href="{{ route('risalah.edit', ['type' => $type, 'id' => $item->id]) }}" class="btn btn-sm btn-secondary" title="Edit Risalah">
                                    Edit
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isAdmin() ? 10 : 9 }}" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Tidak ada data risalah yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} dari {{ number_format($data->total()) }} data
        </div>
        <div>
            {{ $data->links() }}
        </div>
    </div>
</div>
@endsection
