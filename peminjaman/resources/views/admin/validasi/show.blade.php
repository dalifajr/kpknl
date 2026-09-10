@extends('layouts.app')

@section('title', 'Validasi Risalah No. ' . $pending->no_risalah)

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Left: Data Details -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Detail Berkas Risalah</h2>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.9rem;">
                {{ strtoupper($pending->jenis) }}
            </span>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; font-weight: 600; width: 140px; color: var(--text-muted);">Nomor Risalah:</td>
                <td style="padding: 10px 0; font-weight: 700; font-size: 1.1rem; color: var(--primary-color);">{{ $pending->no_risalah }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Jenis Risalah:</td>
                <td style="padding: 10px 0; font-weight: 600;">{{ ucfirst($pending->jenis) }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Tanggal Risalah:</td>
                <td style="padding: 10px 0;">{{ $pending->tgl_risalah?->format('d F Y') ?? '-' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Pejabat Lelang:</td>
                <td style="padding: 10px 0;"><strong>{{ $pending->nama_pelelang }}</strong></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Pemohon Lelang:</td>
                <td style="padding: 10px 0;">{{ $pending->pemohon_lelang }}</td>
            </tr>
            @if ($pending->link_erisalah)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Link E-Risalah:</td>
                    <td style="padding: 10px 0;">
                        <a href="{{ $pending->link_erisalah }}" target="_blank" class="btn btn-sm btn-outline">Buka Dokumen ↗</a>
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; font-weight: 600; color: var(--text-muted);">Keterangan:</td>
                <td style="padding: 10px 0;">{{ $pending->keterangan ?? '-' }}</td>
            </tr>
        </table>

        <a href="{{ route('admin.validasi.index') }}" class="btn btn-outline" style="width: 100%;">
            ← Kembali ke Daftar Antrian
        </a>
    </div>

    <!-- Right: Decision Forms (Approve / Revisi) -->
    <div>
        <!-- Approve Form -->
        <div class="card" style="border-top: 4px solid var(--success);">
            <div class="card-header">
                <h3 style="color: var(--success); font-size: 1.15rem; font-weight: 700;">✅ Setujui & Tetapkan Lokasi Arsip</h3>
            </div>
            <form action="{{ route('admin.validasi.approve', $pending->id) }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="box">Nomor Box <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="box" id="box" class="form-control" value="{{ old('box', $pending->box) }}" placeholder="contoh: B-12" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lemari">Nomor Lemari <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="lemari" id="lemari" class="form-control" value="{{ old('lemari', $pending->lemari) }}" placeholder="contoh: L-04" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tgl_validasi">Tanggal Validasi <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="tgl_validasi" id="tgl_validasi" class="form-control" value="{{ old('tgl_validasi', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="link_erisalah">Link E-Risalah (Opsional)</label>
                    <input type="url" name="link_erisalah" id="link_erisalah" class="form-control" value="{{ old('link_erisalah', $pending->link_erisalah) }}" placeholder="https://drive.google.com/...">
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%; padding: 10px; font-weight: 700;" onclick="return confirm('Validasi dan masukkan risalah ini ke register resmi {{ strtoupper($pending->jenis) }}?')">
                    Validasi & Pindahkan ke Register {{ strtoupper($pending->jenis) }}
                </button>
            </form>
        </div>

        <!-- Revisi Form -->
        <div class="card" style="border-top: 4px solid var(--danger); margin-top: 20px;">
            <div class="card-header">
                <h3 style="color: var(--danger); font-size: 1.15rem; font-weight: 700;">↩️ Kembalikan untuk Revisi</h3>
            </div>
            <form action="{{ route('admin.validasi.revisi', $pending->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan Umum / Alasan Pengembalian</label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Jelaskan kesalahan atau kekurangan data secara umum">{{ old('catatan') }}</textarea>
                </div>

                <details style="margin-bottom: 16px;">
                    <summary style="cursor: pointer; font-size: 0.85rem; font-weight: 600; color: var(--primary-color);">+ Rincian Catatan Per Kolom (Opsional)</summary>
                    <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <input type="text" name="catatan_no" class="form-control" placeholder="Catatan Nomor Risalah" value="{{ old('catatan_no') }}">
                        <input type="text" name="catatan_jenis" class="form-control" placeholder="Catatan Jenis Risalah" value="{{ old('catatan_jenis') }}">
                        <input type="text" name="catatan_tgl" class="form-control" placeholder="Catatan Tanggal Risalah" value="{{ old('catatan_tgl') }}">
                        <input type="text" name="catatan_pemohon" class="form-control" placeholder="Catatan Pemohon Lelang" value="{{ old('catatan_pemohon') }}">
                    </div>
                </details>

                <button type="submit" class="btn btn-danger" style="width: 100%; padding: 10px; font-weight: 700;" onclick="return confirm('Kembalikan risalah ini ke pejabat lelang untuk perbaikan?')">
                    Kembalikan ke Pelelang
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
