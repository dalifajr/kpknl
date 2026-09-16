@extends('layouts.app')

@section('title', 'Pengaturan Info Card Login | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Pengaturan Card Informasi Halaman Login</h3>
        <p class="text-muted mb-0">Kelola konten pengumuman/informasi yang ditampilkan pada kartu di samping form login SSO.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form Edit Setting -->
    <div class="col-md-7">
        <div class="card-m3 p-4">
            <h5 class="fw-bold mb-3 text-primary" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-pen-to-square me-2"></i>Edit Konten Card Informasi
            </h5>

            <form action="{{ route('admin.settings.login-info.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="login_info_status" class="form-label fw-bold text-dark fs-7">Status Tampilkan Card <span class="text-danger">*</span></label>
                    <select name="login_info_status" id="login_info_status" class="form-select" required>
                        <option value="active" {{ old('login_info_status', $status) == 'active' ? 'selected' : '' }}>Aktif (Tampilkan di Halaman Login)</option>
                        <option value="inactive" {{ old('login_info_status', $status) == 'inactive' ? 'selected' : '' }}>Non-Aktif (Sembunyikan Card)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="login_info_badge" class="form-label fw-bold text-dark fs-7">Label Badge Header <span class="text-danger">*</span></label>
                    <input type="text" name="login_info_badge" id="login_info_badge" class="form-control" placeholder="Contoh: INFO LAYANAN & KEAMANAN" value="{{ old('login_info_badge', $badge) }}" required>
                </div>

                <div class="mb-3">
                    <label for="login_info_title" class="form-label fw-bold text-dark fs-7">Judul Utama Card <span class="text-danger">*</span></label>
                    <input type="text" name="login_info_title" id="login_info_title" class="form-control" placeholder="Judul informasi..." value="{{ old('login_info_title', $title) }}" required>
                </div>

                <div class="mb-4">
                    <label for="login_info_content" class="form-label fw-bold text-dark fs-7">Isi Deskripsi / Informasi <span class="text-danger">*</span></label>
                    <textarea name="login_info_content" id="login_info_content" rows="5" class="form-control" placeholder="Tuliskan pesan informasi atau pengumuman yang ingin disampaikan..." required>{{ old('login_info_content', $content) }}</textarea>
                </div>

                <div class="d-flex gap-2 border-top pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN PENGATURAN
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-tonal rounded-pill px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Card -->
    <div class="col-md-5">
        <div class="card-m3 p-4 bg-light">
            <h5 class="fw-bold mb-3 text-dark" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-eye me-2 text-primary"></i>Live Preview Tampilan Login
            </h5>
            <p class="text-muted fs-8 mb-3">Berikut adalah simulasi visual bagaimana card ini akan tampak bagi pengguna di halaman login:</p>

            <div class="p-4 rounded-4 shadow-sm" style="background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 fs-8 fw-bold" style="background-color: rgba(255, 255, 255, 0.4); color: var(--md-sys-color-on-primary-container);">
                    <i class="fa-solid fa-bullhorn text-primary"></i> <span id="previewBadge">{{ $badge }}</span>
                </div>
                <h4 class="fw-bold mb-3 fs-5" id="previewTitle" style="font-family: var(--font-heading);">{{ $title }}</h4>
                <p class="mb-0 fs-7 lh-base" id="previewContent" style="white-space: pre-line;">{{ $content }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('login_info_badge')?.addEventListener('input', function(e) {
        document.getElementById('previewBadge').innerText = e.target.value || 'INFO LAYANAN';
    });
    document.getElementById('login_info_title')?.addEventListener('input', function(e) {
        document.getElementById('previewTitle').innerText = e.target.value || 'Judul Card';
    });
    document.getElementById('login_info_content')?.addEventListener('input', function(e) {
        document.getElementById('previewContent').innerText = e.target.value || 'Isi deskripsi...';
    });
</script>
@endsection
