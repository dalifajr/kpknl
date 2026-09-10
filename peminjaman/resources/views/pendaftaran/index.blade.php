@extends('layouts.bmn_master')

@section('title', 'Formulir Pendaftaran Akta Risalah Lelang — KPKNL Palembang')
@section('page_title', 'Formulir Pendaftaran Akta Risalah Lelang')
@section('page_subtitle', 'Form input berkas risalah hasil pelaksanaan lelang untuk diverifikasi dan diarsipkan oleh Seksi HI — KPKNL Palembang')

@section('page_actions')
    <a href="{{ route('katalog.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-boxes-stacked"></i> Buka Katalog
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('pendaftaran.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                                Nomor Risalah Lelang <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="no_risalah"
                                class="form-control form-control-sm @error('no_risalah') is-invalid @enderror"
                                placeholder="Contoh: RL-105/KPKNL-PLG/2026"
                                value="{{ old('no_risalah') }}"
                                required
                            >
                            @error('no_risalah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                                Jenis Risalah <span class="text-danger">*</span>
                            </label>
                            <select name="jenis" class="form-select form-select-sm" required>
                                <option value="minuta" {{ old('jenis') === 'minuta' ? 'selected' : '' }}>Minuta (Lelang Laku)</option>
                                <option value="tap" {{ old('jenis') === 'tap' ? 'selected' : '' }}>TAP (Tidak Ada Penawaran)</option>
                                <option value="batal" {{ old('jenis') === 'batal' ? 'selected' : '' }}>Batal Lelang</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                                Tanggal Risalah Lelang <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                name="tgl_risalah"
                                class="form-control form-control-sm @error('tgl_risalah') is-invalid @enderror"
                                value="{{ old('tgl_risalah', date('Y-m-d')) }}"
                                required
                            >
                            @error('tgl_risalah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                                Nama Pejabat Lelang <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="nama_pelelang"
                                class="form-control form-control-sm @error('nama_pelelang') is-invalid @enderror"
                                value="{{ old('nama_pelelang', Auth::user()->name ?? '') }}"
                                required
                            >
                            @error('nama_pelelang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">
                                Pemohon Lelang / Penjual / Debitur <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="pemohon_lelang"
                                class="form-control form-control-sm @error('pemohon_lelang') is-invalid @enderror"
                                placeholder="Contoh: PT Bank Rakyat Indonesia (Persero) Tbk / Kantor Bea Cukai Palembang"
                                value="{{ old('pemohon_lelang') }}"
                                required
                            >
                            @error('pemohon_lelang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold px-4">
                                <i class="fas fa-paper-plane me-1"></i> Simpan & Ajukan Validasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
