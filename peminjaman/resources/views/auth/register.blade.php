<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - Sistem Risalah KPKNL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 480px;">
            <img src="{{ asset('images/logo.png') }}" alt="KPKNL Logo" class="logo" onerror="this.onerror=null; this.src='{{ asset('logo.png') }}';">
            <h2>Pendaftaran Akun Baru</h2>
            <p class="subtitle">Lengkapi formulir untuk membuat akun pada sistem KPKNL</p>

            @include('components.alerts')

            <form action="{{ route('register') }}" method="POST" style="text-align: left;">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" placeholder="contoh: pelelang_ahmad" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="nama@domain.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">Role / Peran</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="peminjam" {{ old('role', $defaultRole ?? '') === 'peminjam' ? 'selected' : '' }}>Peminjam (Borrower)</option>
                        <option value="pelelang" {{ old('role', $defaultRole ?? '') === 'pelelang' ? 'selected' : '' }}>Pelelang (Auctioneer)</option>
                        <option value="admin" {{ old('role', $defaultRole ?? '') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 karakter" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; margin-top: 10px;">
                    Daftar Sekarang
                </button>
            </form>

            <div style="margin-top: 24px; font-size: 0.875rem; color: var(--text-muted);">
                Sudah memiliki akun? <a href="{{ route('login') }}" style="font-weight: 600;">Masuk ke sistem</a>
            </div>
        </div>
    </div>
</body>
</html>
