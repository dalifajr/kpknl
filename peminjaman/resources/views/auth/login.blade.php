<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem Risalah KPKNL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <img src="{{ asset('images/logo.png') }}" alt="KPKNL Logo" class="logo" onerror="this.onerror=null; this.src='{{ asset('logo.png') }}';">
            <h2>Sistem Risalah Lelang</h2>
            <p class="subtitle">Silakan masukkan username/email dan password Anda</p>

            @include('components.alerts')

            <form action="{{ route('login') }}" method="POST" style="text-align: left;">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="login">Username atau Email</label>
                    <input type="text" name="login" id="login" class="form-control" value="{{ old('login') }}" placeholder="Masukkan username / email" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-muted); cursor: pointer;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
                    Masuk ke Sistem
                </button>
            </form>

            <div style="margin-top: 24px; font-size: 0.875rem; color: var(--text-muted);">
                Belum memiliki akun? <a href="{{ route('register') }}" style="font-weight: 600;">Daftar di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
