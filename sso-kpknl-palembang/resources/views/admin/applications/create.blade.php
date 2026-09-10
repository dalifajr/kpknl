@extends('layouts.app')

@section('title', 'Tambah Aplikasi Baru | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Daftarkan Aplikasi Baru</h3>
        <p class="text-muted mb-0">Isi detail aplikasi internal yang akan diintegrasikan dengan Single Sign-On (SSO).</p>
    </div>
    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Aplikasi
    </a>
</div>

<style>
    .card-m3 {
        background: linear-gradient(145deg, var(--md-sys-color-surface-container-low), var(--md-sys-color-surface-container));
        border-radius: var(--md-shape-corner-xl);
    }
    .m3e-input, .m3e-select {
        background-color: var(--md-sys-color-surface-container-highest) !important;
        border: 1px solid transparent !important;
        transition: all 0.25s cubic-bezier(0.2, 0, 0, 1) !important;
    }
    .m3e-input:focus, .m3e-select:focus {
        background-color: var(--md-sys-color-surface-container-lowest) !important;
        border-color: var(--md-sys-color-primary) !important;
        box-shadow: 0 0 0 4px rgba(59, 95, 229, 0.15) !important;
    }
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        margin-top: 0;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked {
        background-color: var(--md-sys-color-primary);
        border-color: var(--md-sys-color-primary);
    }
    .form-switch .form-check-label {
        padding-top: 0.15em;
        cursor: pointer;
    }
</style>

<div class="card-m3 shadow-sm border-0 p-4" style="max-width: 900px;">
    <form action="{{ route('admin.applications.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark fs-7">Nama Aplikasi <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control m3e-input rounded-3 py-2" placeholder="Contoh: Aplikasi Inventaris BMN" value="{{ old('name') }}" required>
            </div>

            <div class="col-md-6">
                <label for="slug" class="form-label fw-semibold text-dark fs-7">Slug Kode Aplikasi <span class="text-danger">*</span></label>
                <input type="text" name="slug" id="slug" class="form-control m3e-input rounded-3 py-2" placeholder="Contoh: inventaris-bmn" value="{{ old('slug') }}" required>
                <small class="text-muted fs-8">Hanya huruf kecil, angka, dan strip (-).</small>
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold text-dark fs-7">Deskripsi Singkat Aplikasi</label>
                <textarea name="description" id="description" rows="2" class="form-control m3e-input rounded-3 py-2" placeholder="Jelaskan fungsi dan kegunaan aplikasi ini...">{{ old('description') }}</textarea>
            </div>

            <div class="col-md-6">
                <label for="url" class="form-label fw-semibold text-dark fs-7">URL Utama Aplikasi <span class="text-danger">*</span></label>
                <input type="url" name="url" id="url" class="form-control m3e-input rounded-3 py-2" placeholder="http://localhost/inventaris" value="{{ old('url') }}" required>
            </div>

            <div class="col-md-6">
                <label for="redirect_uri" class="form-label fw-semibold text-dark fs-7">OAuth Redirect / Callback URI <span class="text-danger">*</span></label>
                <input type="url" name="redirect_uri" id="redirect_uri" class="form-control m3e-input rounded-3 py-2" placeholder="http://localhost/inventaris/callback" value="{{ old('redirect_uri') }}" required>
            </div>

            <div class="col-md-6">
                <label for="icon" class="form-label fw-semibold text-dark fs-7">Ikon Aplikasi (Opsional)</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width: 46px; height: 46px; border: 1px dashed var(--md-sys-color-outline-variant);">
                        <i class="fa-solid fa-image"></i>
                    </div>
                    <input type="file" name="icon" id="icon" class="form-control m3e-input rounded-3 py-2" accept="image/jpeg,image/png,image/svg+xml">
                </div>
                <small class="text-muted fs-8 mt-1 d-block">Gunakan file JPG, PNG, atau SVG (Maks. 2MB). Kosongkan jika ingin menggunakan ikon box bawaan.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark fs-7 d-block">Status Aplikasi <span class="text-danger">*</span></label>
                <!-- Hidden input untuk fallback jika toggle dimatikan -->
                <input type="hidden" name="status" value="inactive">
                <div class="form-check form-switch mt-3 d-flex align-items-center">
                    <input class="form-check-input me-2 shadow-sm" type="checkbox" role="switch" name="status" id="status" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                    <label class="form-check-label fw-medium text-dark fs-6" for="status">Aktif (Dapat Diakses)</label>
                </div>
            </div>

            <div class="col-12 mt-4 pt-3 border-top border-secondary-subtle d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1"></i> SIMPAN & GENERATE CREDENTIALS
                </button>
                <a href="{{ route('admin.applications.index') }}" class="btn btn-tonal rounded-pill px-4 fw-semibold">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
