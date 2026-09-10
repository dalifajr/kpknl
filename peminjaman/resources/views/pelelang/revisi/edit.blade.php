@extends('layouts.app')

@section('title', 'Perbaiki Risalah No. ' . $revisi->no_risalah)

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <h1 class="card-title">Perbaikan Risalah No. {{ $revisi->no_risalah }}</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Perbaiki data yang diminta oleh Admin, kemudian kirim kembali untuk divalidasi ulang.</p>
        </div>
        <a href="{{ route('pelelang.revisi.index') }}" class="btn btn-outline">
            Kembali
        </a>
    </div>

    <!-- Admin Notes Box -->
    <div class="alert alert-warning" style="display: block; margin-bottom: 24px;">
        <div style="font-weight: 700; margin-bottom: 6px;">Catatan Perbaikan dari Administrator:</div>
        <p style="margin-bottom: 8px;">{{ $revisi->catatan ?: 'Tidak ada catatan umum.' }}</p>
        @if ($revisi->catatan_no || $revisi->catatan_jenis || $revisi->catatan_tgl || $revisi->catatan_pemohon)
            <ul style="margin-left: 20px; font-size: 0.85rem;">
                @if ($revisi->catatan_no) <li><strong>Nomor:</strong> {{ $revisi->catatan_no }}</li> @endif
                @if ($revisi->catatan_jenis) <li><strong>Jenis:</strong> {{ $revisi->catatan_jenis }}</li> @endif
                @if ($revisi->catatan_tgl) <li><strong>Tanggal:</strong> {{ $revisi->catatan_tgl }}</li> @endif
                @if ($revisi->catatan_pemohon) <li><strong>Pemohon:</strong> {{ $revisi->catatan_pemohon }}</li> @endif
            </ul>
        @endif
    </div>

    <form action="{{ route('pelelang.revisi.resubmit', $revisi->id) }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="no_risalah">Nomor Risalah <span style="color: var(--danger);">*</span></label>
                <input type="text" name="no_risalah" id="no_risalah" class="form-control" value="{{ old('no_risalah', $revisi->no_risalah) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="jenis">Jenis Risalah <span style="color: var(--danger);">*</span></label>
                <select name="jenis" id="jenis" class="form-control" required>
                    <option value="minuta" {{ old('jenis', $revisi->jenis) === 'minuta' ? 'selected' : '' }}>Risalah Minuta</option>
                    <option value="tap" {{ old('jenis', $revisi->jenis) === 'tap' ? 'selected' : '' }}>Risalah TAP</option>
                    <option value="batal" {{ old('jenis', $revisi->jenis) === 'batal' ? 'selected' : '' }}>Risalah Batal</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="tgl_risalah">Tanggal Risalah <span style="color: var(--danger);">*</span></label>
                <input type="date" name="tgl_risalah" id="tgl_risalah" class="form-control" value="{{ old('tgl_risalah', $revisi->tgl_risalah?->format('Y-m-d') ?? $revisi->tgl_risalah) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="pemohon_lelang">Pemohon Lelang <span style="color: var(--danger);">*</span></label>
                <input type="text" name="pemohon_lelang" id="pemohon_lelang" class="form-control" value="{{ old('pemohon_lelang', $revisi->pemohon_lelang) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="keterangan">Keterangan Tambahan / Penjelasan Perbaikan</label>
            <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Jelaskan perbaikan yang telah dilakukan...">{{ old('keterangan', 'Telah diperbaiki sesuai catatan revisi.') }}</textarea>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('pelelang.revisi.index') }}" class="btn btn-outline">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px;" onclick="return confirm('Kirim kembali data risalah yang sudah diperbaiki ke antrian verifikasi Admin?')">
                Kirim Ulang ke Admin
            </button>
        </div>
    </form>
</div>
@endsection
