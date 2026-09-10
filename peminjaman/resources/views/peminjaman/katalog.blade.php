@extends('layouts.app')

@section('title', 'Katalog Risalah Tersedia untuk Dipinjam')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Katalog Risalah Tersedia</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Pilih satu atau beberapa berkas risalah berstatus <strong>Tersedia</strong> untuk diajukan peminjaman.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('peminjam.katalog', ['type' => 'minuta']) }}" class="btn {{ $type === 'minuta' ? 'btn-primary' : 'btn-outline' }}">Minuta</a>
            <a href="{{ route('peminjam.katalog', ['type' => 'tap']) }}" class="btn {{ $type === 'tap' ? 'btn-primary' : 'btn-outline' }}">TAP</a>
            <a href="{{ route('peminjam.katalog', ['type' => 'batal']) }}" class="btn {{ $type === 'batal' ? 'btn-primary' : 'btn-outline' }}">Batal</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('peminjam.katalog') }}" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 10px;">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor risalah / pelelang / pemohon..." value="{{ request('search') }}">
            <input type="text" name="box" class="form-control" placeholder="Box..." value="{{ request('box') }}">
            <input type="text" name="lemari" class="form-control" placeholder="Lemari..." value="{{ request('lemari') }}">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('peminjam.katalog', ['type' => $type]) }}" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <form id="borrowForm" action="{{ route('peminjam.pinjam.store') }}" method="POST">
        @csrf

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAll" title="Pilih Semua di Halaman Ini">
                        </th>
                        <th>Nomor Risalah</th>
                        <th>Tanggal Risalah</th>
                        <th>Pelelang</th>
                        <th>Pemohon Lelang</th>
                        <th>Box / Lemari</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_items[]" value="{{ $type }}:{{ $item->id }}" class="item-checkbox">
                            </td>
                            <td><strong>{{ $item->no_risalah }}</strong></td>
                            <td>{{ $item->tgl_risalah?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $item->nama_pelelang }}</td>
                            <td>{{ $item->pemohon_lelang }}</td>
                            <td>Box: {{ $item->box ?? '-' }} / Lemari: {{ $item->lemari ?? '-' }}</td>
                            <td><span class="badge badge-tersedia">Tersedia</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                Tidak ada risalah berstatus tersedia pada kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>

        <!-- Borrow Action Section -->
        <div class="card" style="margin-top: 24px; background: #f8fafc; border: 1px dashed var(--primary-color);">
            <h3 style="font-size: 1rem; color: var(--primary-color); margin-bottom: 12px;">Konfirmasi Pengajuan Peminjaman</h3>
            <div class="form-group">
                <label class="form-label" for="alasan_peminjaman">Alasan / Keperluan Peminjaman <span style="color: var(--danger);">*</span></label>
                <textarea name="alasan_peminjaman" id="alasan_peminjaman" class="form-control" rows="2" placeholder="contoh: Keperluan audit berkas lelang tahun berjalan..." required></textarea>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span id="selectedCount" style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted);">
                    0 berkas dipilih
                </span>
                <button type="submit" id="btnSubmitBorrow" class="btn btn-primary" style="padding: 10px 24px;" disabled>
                    Ajukan Peminjaman Risalah Terpilih
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const countSpan = document.getElementById('selectedCount');
        const submitBtn = document.getElementById('btnSubmitBorrow');

        function updateCount() {
            const checked = document.querySelectorAll('.item-checkbox:checked');
            const total = checked.length;
            countSpan.textContent = total + ' berkas dipilih';
            submitBtn.disabled = total === 0;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateCount();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });
    });
</script>
@endpush
