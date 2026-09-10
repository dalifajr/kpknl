@extends('layouts.app')

@section('title', 'Tambah Pengajuan Risalah Baru')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <h1 class="card-title">Pengajuan Risalah Baru</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Lengkapi data di bawah ini. Risalah akan masuk ke antrian verifikasi Admin sebelum dipindahkan ke register resmi.</p>
        </div>
        <a href="{{ Auth::user()->isPelelang() ? route('pelelang.dashboard') : route('admin.dashboard') }}" class="btn btn-outline">
            Kembali
        </a>
    </div>

    <form action="{{ route('pelelang.risalah.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="no_risalah">Nomor Risalah <span style="color: var(--danger);">*</span></label>
                <input type="text" name="no_risalah" id="no_risalah" class="form-control" value="{{ old('no_risalah') }}" placeholder="contoh: 01/KPKNL/2026" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="jenis">Jenis Risalah <span style="color: var(--danger);">*</span></label>
                <select name="jenis" id="jenis" class="form-control" required>
                    <option value="">-- Pilih Jenis Risalah --</option>
                    <option value="minuta" {{ old('jenis') === 'minuta' ? 'selected' : '' }}>Risalah Minuta</option>
                    <option value="tap" {{ old('jenis') === 'tap' ? 'selected' : '' }}>Risalah TAP (Tidak Ada Penawaran)</option>
                    <option value="batal" {{ old('jenis') === 'batal' ? 'selected' : '' }}>Risalah Batal</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="tgl_risalah">Tanggal Risalah <span style="color: var(--danger);">*</span></label>
                <input type="date" name="tgl_risalah" id="tgl_risalah" class="form-control" value="{{ old('tgl_risalah', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="pemohon_lelang">Pemohon Lelang <span style="color: var(--danger);">*</span></label>
                <input type="text" name="pemohon_lelang" id="pemohon_lelang" class="form-control" value="{{ old('pemohon_lelang') }}" placeholder="Nama Instansi / Perorangan Pemohon" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="nama_pelelang_display">Pejabat Lelang</label>
            <input type="text" id="nama_pelelang_display" class="form-control" value="{{ Auth::user()->username }}" disabled style="background: #f1f5f9;">
            <small style="color: var(--text-muted);">Nama pelelang otomatis terikat dengan akun Anda yang sedang aktif.</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="link_erisalah">Link E-Risalah (Opsional)</label>
            <input type="url" name="link_erisalah" id="link_erisalah" class="form-control" value="{{ old('link_erisalah') }}" placeholder="https://drive.google.com/...">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="box">Nomor Box (Opsional)</label>
                <input type="text" name="box" id="box" class="form-control" value="{{ old('box') }}" placeholder="Nomor Box Penyimpanan">
            </div>

            <div class="form-group">
                <label class="form-label" for="lemari">Nomor Lemari (Opsional)</label>
                <input type="text" name="lemari" id="lemari" class="form-control" value="{{ old('lemari') }}" placeholder="Nomor Lemari Arsip">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="keterangan">Keterangan Tambahan</label>
            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan">{{ old('keterangan') }}</textarea>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ Auth::user()->isPelelang() ? route('pelelang.dashboard') : route('admin.dashboard') }}" class="btn btn-outline">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                Kirim Pengajuan Risalah
            </button>
        </div>
    </form>
</div>
@endsection
