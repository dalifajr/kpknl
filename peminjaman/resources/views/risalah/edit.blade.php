@extends('layouts.app')

@section('title', 'Edit Risalah ' . strtoupper($type))

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <h1 class="card-title">Edit Data Risalah {{ strtoupper($type) }}</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Perbarui data risalah yang sudah tervalidasi di database resmi.</p>
        </div>
        <a href="{{ route('risalah.' . $type) }}" class="btn btn-outline">
            Kembali
        </a>
    </div>

    <form action="{{ route('risalah.update', ['type' => $type, 'id' => $model->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="no_risalah">Nomor Risalah <span style="color: var(--danger);">*</span></label>
                <input type="text" name="no_risalah" id="no_risalah" class="form-control" value="{{ old('no_risalah', $model->no_risalah) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="nama_pelelang">Pejabat Lelang <span style="color: var(--danger);">*</span></label>
                <input type="text" name="nama_pelelang" id="nama_pelelang" class="form-control" value="{{ old('nama_pelelang', $model->nama_pelelang) }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="tgl_risalah">Tanggal Risalah <span style="color: var(--danger);">*</span></label>
                <input type="date" name="tgl_risalah" id="tgl_risalah" class="form-control" value="{{ old('tgl_risalah', $model->tgl_risalah?->format('Y-m-d') ?? $model->tgl_risalah) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="tgl_validasi">Tanggal Validasi</label>
                <input type="date" name="tgl_validasi" id="tgl_validasi" class="form-control" value="{{ old('tgl_validasi', $model->tgl_validasi?->format('Y-m-d') ?? $model->tgl_validasi) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="pemohon_lelang">Pemohon Lelang <span style="color: var(--danger);">*</span></label>
            <input type="text" name="pemohon_lelang" id="pemohon_lelang" class="form-control" value="{{ old('pemohon_lelang', $model->pemohon_lelang) }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="box">Nomor Box</label>
                <input type="text" name="box" id="box" class="form-control" value="{{ old('box', $model->box) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="lemari">Nomor Lemari</label>
                <input type="text" name="lemari" id="lemari" class="form-control" value="{{ old('lemari', $model->lemari) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="status">Status Ketersediaan <span style="color: var(--danger);">*</span></label>
                <select name="status" id="status" class="form-control" required>
                    <option value="tersedia" {{ old('status', strtolower($model->status)) === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="sedang_dipinjam" {{ old('status', strtolower($model->status)) === 'sedang_dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="link_erisalah">Link E-Risalah</label>
                <input type="url" name="link_erisalah" id="link_erisalah" class="form-control" value="{{ old('link_erisalah', $model->link_erisalah) }}">
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('risalah.' . $type) }}" class="btn btn-outline">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
