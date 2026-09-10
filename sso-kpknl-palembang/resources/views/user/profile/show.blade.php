@extends('layouts.app')

@section('title', 'Pengaturan Profil Saya | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Pengaturan Profil & Keamanan Akun</h3>
        <p class="text-muted mb-0">Kelola data diri dan perbarui kata sandi akun SSO Anda.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Info Card -->
    <div class="col-md-6">
        <div class="card-m3 p-4 h-100">
            <h5 class="fw-bold mb-4 text-dark" style="font-family: var(--font-heading);"><i class="fa-regular fa-user me-2 text-primary"></i>Informasi Akun</h5>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold mb-1">Username (Tidak Dapat Diubah)</label>
                    <input type="text" class="form-control opacity-75" value="{{ $user->username }}" readonly style="background-color: var(--md-sys-color-surface-container);">
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold mb-1">Email Resmi</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-7 fw-bold mb-1">Role Akun</label>
                    <div>
                        <span class="badge badge-role badge-{{ $user->roles->first()?->name ?? 'user' }} px-3 py-2 fs-7">
                            {{ strtoupper($user->roles->first()?->display_name ?? 'User') }}
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold mt-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> SIMPAN PERUBAHAN PROFIL
                </button>
            </form>
        </div>
    </div>

    <!-- Password Change Card (NIST Policy) -->
    <div class="col-md-6">
        <div class="card-m3 p-4 h-100">
            <h5 class="fw-bold mb-4 text-dark" style="font-family: var(--font-heading);"><i class="fa-solid fa-key me-2 text-primary"></i>Ganti Password Akun</h5>

            <div class="alert alert-expressive p-3 fs-8 mb-4" style="background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);">
                <i class="fa-solid fa-shield-halved fs-5 me-1"></i> 
                <div>
                    <strong>Kebijakan Keamanan (NIST SP 800-63B):</strong><br>
                    Password wajib memiliki <strong>minimal 8 karakter</strong>, kombinasi <strong>huruf besar (A-Z)</strong>, <strong>huruf kecil (a-z)</strong>, <strong>angka (0-9)</strong>, dan <strong>simbol khusus (!@#$%^&*)</strong>.
                </div>
            </div>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="current_password" class="form-label fs-7 fw-bold mb-1">Password Saat Ini <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Masukkan password lama..." required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fs-7 fw-bold mb-1">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password baru yang kuat..." required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fs-7 fw-bold mb-1">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ketik ulang password baru..." required>
                </div>

                <button type="submit" class="btn btn-tonal rounded-pill px-4 fw-bold mt-2">
                    <i class="fa-solid fa-shield-check me-1"></i> UPDATE PASSWORD AKUN
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

