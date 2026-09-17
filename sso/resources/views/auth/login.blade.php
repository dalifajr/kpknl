<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Single Sign-On | KPKNL Palembang</title>

    <!-- Google Fonts: Google Sans, Roboto & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* Material You / Google Design Tokens */
            --gcp-primary: #1a73e8;
            --gcp-primary-hover: #1557b0;
            --gcp-primary-container: #e8f0fe;
            --gcp-on-primary-container: #174ea6;

            --gcp-surface: #edf2f7;
            --gcp-surface-card: #ffffff;
            --gcp-surface-variant: #f1f3f4;
            --gcp-border: #dadce0;
            --gcp-border-subtle: #e8eaed;

            --gcp-text-primary: #202124;
            --gcp-text-secondary: #5f6368;
            --gcp-text-muted: #70757a;

            --gcp-success: #1e8e3e;
            --gcp-success-container: #e6f4ea;
            --gcp-error: #d93025;
            --gcp-error-container: #fce8e6;
            --gcp-on-error-container: #a50e0e;

            --font-heading: 'Google Sans', 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-body: 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            --info-side-bg: linear-gradient(160deg, #eef4fe 0%, #f0f4f9 60%, #ffffff 100%);
            --card-shadow: 0 4px 24px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(60, 64, 67, 0.08);
        }

        [data-bs-theme="dark"] {
            --gcp-primary: #8ab4f8;
            --gcp-primary-hover: #aecbfa;
            --gcp-primary-container: #004a77;
            --gcp-on-primary-container: #c2e7ff;

            --gcp-surface: #121212;
            --gcp-surface-card: #1e1e1e;
            --gcp-surface-variant: #2d2d2d;
            --gcp-border: #3c4043;
            --gcp-border-subtle: #2f3336;

            --gcp-text-primary: #e8eaed;
            --gcp-text-secondary: #9aa0a6;
            --gcp-text-muted: #80868b;

            --info-side-bg: linear-gradient(160deg, #1b263b 0%, #161f2e 60%, #12161f 100%);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 1px 3px rgba(255, 255, 255, 0.05);
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--gcp-surface);
            color: var(--gcp-text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
            position: relative;
        }

        /* Top-Right Theme Toggle */
        .theme-toggle-btn {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--gcp-surface-card);
            border: 1px solid var(--gcp-border);
            color: var(--gcp-text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            z-index: 100;
        }

        .theme-toggle-btn:hover {
            color: var(--gcp-primary);
            border-color: var(--gcp-primary);
            transform: scale(1.05);
        }

        /* Main Login Card Wrapper */
        .login-card-wrapper {
            background-color: var(--gcp-surface-card);
            border-radius: 16px;
            border: 1px solid var(--gcp-border);
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: {{ ($loginInfoStatus ?? 'active') === 'active' ? '940px' : '460px' }};
            overflow: hidden;
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Left: Form Side */
        .login-form-side {
            padding: 3rem 2.5rem 2.5rem;
        }

        .login-brand-logo {
            height: 44px;
            width: auto;
            border-radius: 4px;
        }

        .login-brand-title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--gcp-text-primary);
            letter-spacing: -0.01em;
            margin-bottom: 0.25rem;
        }

        .login-brand-subtitle {
            font-size: 0.8125rem;
            color: var(--gcp-text-secondary);
            margin-bottom: 0;
        }

        /* Right: Info Side */
        .login-info-side {
            background: var(--info-side-bg);
            color: var(--gcp-text-primary);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-left: 1px solid var(--gcp-border-subtle);
            position: relative;
            transition: background 0.3s ease;
        }

        .info-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.35rem 0.85rem;
            background-color: var(--gcp-primary-container);
            color: var(--gcp-on-primary-container);
            border-radius: 100px;
            font-size: 0.72rem;
            font-family: var(--font-heading);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .info-pill-badge.maintenance {
            background-color: var(--gcp-error-container);
            color: var(--gcp-on-error-container);
        }

        .info-title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.45rem;
            line-height: 1.35;
            color: var(--gcp-on-primary-container);
            margin-bottom: 1rem;
        }

        [data-bs-theme="dark"] .info-title {
            color: #8ab4f8;
        }

        .info-content {
            font-size: 0.875rem;
            line-height: 1.65;
            color: var(--gcp-text-secondary);
            margin-bottom: 2rem;
            white-space: pre-line;
        }

        .info-footer {
            padding-top: 1.25rem;
            border-top: 1px solid var(--gcp-border-subtle);
        }

        .info-footer-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background-color: var(--gcp-primary-container);
            color: var(--gcp-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        /* Form Controls */
        .form-label {
            font-family: var(--font-heading);
            font-weight: 500;
            color: var(--gcp-text-primary);
            font-size: 0.8125rem;
            margin-bottom: 0.35rem;
        }

        .input-group {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--gcp-border);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-group:focus-within {
            border-color: var(--gcp-primary);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.18);
        }

        [data-bs-theme="dark"] .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(138, 180, 248, 0.25);
        }

        .input-group-text {
            background-color: var(--gcp-surface-card);
            border: none;
            color: var(--gcp-text-secondary);
            padding: 0.65rem 0.85rem;
            font-size: 0.9rem;
        }

        .form-control {
            background-color: var(--gcp-surface-card);
            border: none;
            border-radius: 0;
            padding: 0.65rem 0.85rem;
            font-size: 0.875rem;
            color: var(--gcp-text-primary);
        }

        .form-control:focus {
            background-color: var(--gcp-surface-card);
            border: none;
            box-shadow: none;
            color: var(--gcp-text-primary);
        }

        .form-control::placeholder {
            color: var(--gcp-text-muted);
            font-size: 0.8125rem;
        }

        /* Toggle Password Button */
        .btn-toggle-password {
            background-color: var(--gcp-surface-card);
            border: none;
            color: var(--gcp-text-secondary);
            padding: 0 0.85rem;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .btn-toggle-password:hover {
            color: var(--gcp-text-primary);
        }

        /* Material You Pill Button */
        .btn-login-pill {
            background-color: var(--gcp-primary);
            border: none;
            color: #ffffff;
            font-family: var(--font-heading);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 100px;
            width: 100%;
            font-size: 0.875rem;
            letter-spacing: 0.02em;
            transition: background-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 4px rgba(26, 115, 232, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login-pill:hover {
            background-color: var(--gcp-primary-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.35);
            transform: translateY(-1px);
        }

        .btn-login-pill:active {
            transform: translateY(0);
        }

        [data-bs-theme="dark"] .btn-login-pill {
            background-color: #1a73e8;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .btn-login-pill:hover {
            background-color: #1557b0;
            color: #ffffff;
        }

        /* Remember Me Checkbox */
        .form-check-input {
            background-color: var(--gcp-surface-card);
            border-color: var(--gcp-border);
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--gcp-primary);
            border-color: var(--gcp-primary);
        }

        .form-check-label {
            font-size: 0.8125rem;
            color: var(--gcp-text-secondary);
            cursor: pointer;
        }

        /* Maintenance Alert */
        .maintenance-alert {
            background-color: var(--gcp-error-container);
            color: var(--gcp-on-error-container);
            border: 1px solid rgba(217, 48, 37, 0.25);
            border-radius: 8px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .maintenance-alert-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            background-color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gcp-error);
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }

        .maintenance-alert-title {
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.15rem;
        }

        .maintenance-alert-text {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        /* Help Footer */
        .login-help-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gcp-border-subtle);
        }

        .login-help-footer small {
            color: var(--gcp-text-muted);
            font-size: 0.75rem;
            line-height: 1.5;
        }

        /* Bottom Branding */
        .login-bottom-brand {
            font-size: 0.75rem;
            color: var(--gcp-text-muted);
            margin-top: 1.5rem;
            text-align: center;
        }

        /* SweetAlert2 Material You Theme */
        .swal2-popup.swal2-m3e {
            background-color: var(--gcp-surface-card) !important;
            border-radius: 16px !important;
            color: var(--gcp-text-primary) !important;
            padding: 2rem !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid var(--gcp-border) !important;
        }

        .swal2-title.swal2-m3e-title {
            font-family: var(--font-heading) !important;
            font-weight: 700 !important;
            color: var(--gcp-text-primary) !important;
            font-size: 1.25rem !important;
            margin-bottom: 0.5rem !important;
        }

        /* Global Loading Overlay */
        .global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.15s ease, visibility 0.15s ease;
        }

        [data-bs-theme="dark"] .global-loading-overlay {
            background-color: rgba(18, 18, 18, 0.75);
        }

        .global-loading-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .simple-loading-box {
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .login-info-side {
                border-left: none;
                border-top: 1px solid var(--gcp-border-subtle);
                padding: 2rem 1.5rem;
            }
            .login-form-side {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Theme Toggle Button -->
    <button type="button" class="theme-toggle-btn" id="themeToggle" title="Ganti Mode Tampilan (Gelap/Terang)">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

    <!-- Global Loading Overlay -->
    <div id="globalLoadingOverlay" class="global-loading-overlay">
        <div class="simple-loading-box">
            <div class="spinner-border text-primary" role="status" style="width: 2.25rem; height: 2.25rem; border-width: 0.2em;">
                <span class="visually-hidden">Memuat...</span>
            </div>
            <span class="d-block mt-2 fw-medium text-secondary fs-7" id="globalLoadingText" style="font-family: var(--font-heading); letter-spacing: 0.02em;">Memuat...</span>
        </div>
    </div>

    <!-- Login Card Container -->
    <div class="login-card-wrapper">
        <div class="row g-0">
            <!-- Left Side: Login Form -->
            <div class="{{ ($loginInfoStatus ?? 'active') === 'active' ? 'col-lg-6' : 'col-12' }} login-form-side">
                <div class="mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL Palembang" class="login-brand-logo mb-3" onerror="this.onerror=null; this.src='https://placehold.co/150x40/1a73e8/ffffff?text=KPKNL';">
                    <h4 class="login-brand-title">SSO PORTAL</h4>
                    <p class="login-brand-subtitle">KPKNL Palembang &bull; Masuk dengan akun resmi Anda</p>
                </div>

                @if($isMaintenance ?? false)
                    <div class="maintenance-alert" role="alert">
                        <div class="maintenance-alert-icon">
                            <i class="fa-solid fa-screwdriver-wrench" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="maintenance-alert-title">Sistem Dalam Pemeliharaan</div>
                            <div class="maintenance-alert-text">{{ $maintenanceMessage ?? 'Hanya Superadmin yang diperbolehkan untuk masuk saat ini.' }}</div>
                        </div>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input type="text" name="login" id="login" class="form-control" placeholder="Masukkan username / email..." value="{{ old('login') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password..." required>
                            <button class="btn-toggle-password" type="button" id="togglePassword" aria-label="Lihat Password">
                                <i class="fa-regular fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Ingat sesi login di perangkat ini
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login-pill">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>{{ ($isMaintenance ?? false) ? 'LOGIN SUPERADMIN' : 'Masuk ke SSO' }}</span>
                    </button>
                </form>

                <div class="login-help-footer">
                    <small>
                        Lupa password atau butuh bantuan akun?<br>
                        Hubungi <strong>Superadmin IT KPKNL Palembang</strong>.
                    </small>
                </div>
            </div>

            <!-- Right Side: Info Panel -->
            @if(($loginInfoStatus ?? 'active') === 'active')
                <div class="col-lg-6 login-info-side {{ ($isMaintenance ?? false) ? 'maintenance-mode' : '' }}">
                    <div>
                        <div class="mb-3">
                            <span class="info-pill-badge {{ ($isMaintenance ?? false) ? 'maintenance' : '' }}">
                                <i class="fa-solid {{ ($isMaintenance ?? false) ? 'fa-screwdriver-wrench' : 'fa-shield-halved' }}" style="font-size: 0.65rem;"></i>
                                {{ ($isMaintenance ?? false) ? 'PEMELIHARAAN SISTEM' : ($loginInfoBadge ?? 'INFO LAYANAN & KEAMANAN') }}
                            </span>
                        </div>

                        <h3 class="info-title">
                            {{ ($isMaintenance ?? false) ? 'Sistem Dalam Pemeliharaan Berkala' : ($loginInfoTitle ?? 'Selamat Datang di Portal Single Sign-On (SSO)') }}
                        </h3>

                        <p class="info-content">{{ ($isMaintenance ?? false) ? $maintenanceMessage : ($loginInfoContent ?? 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.') }}</p>
                    </div>

                    <div class="info-footer d-flex align-items-center gap-3">
                        <div class="info-footer-icon">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold" style="font-family: var(--font-heading); font-size: 0.8125rem; color: var(--gcp-text-primary);">
                                KPKNL Palembang &copy; 2026
                            </span>
                            <small style="font-size: 0.75rem; color: var(--gcp-text-muted);">
                                Direktorat Jenderal Kekayaan Negara
                            </small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Caption -->
    <div class="login-bottom-brand">
        Portal Single Sign-On (SSO) OAuth2 &bull; KPKNL Palembang
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password Visibility Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword?.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        // Theme Toggle Manager (Synced with sso_theme in localStorage)
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            if (themeIcon) {
                if (theme === 'dark') {
                    themeIcon.className = 'fa-solid fa-sun text-warning';
                } else {
                    themeIcon.className = 'fa-solid fa-moon';
                }
            }
            localStorage.setItem('sso_theme', theme);
        }

        const savedTheme = localStorage.getItem('sso_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);

        themeToggle?.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });

        // Global Loading Manager
        window.showGlobalLoading = function(text) {
            const overlay = document.getElementById('globalLoadingOverlay');
            const textEl = document.getElementById('globalLoadingText');
            if (overlay) {
                if (text && textEl) textEl.innerText = text;
                overlay.classList.add('show');
            }
        };

        window.hideGlobalLoading = function() {
            const overlay = document.getElementById('globalLoadingOverlay');
            if (overlay) {
                overlay.classList.remove('show');
            }
        };

        window.addEventListener('load', hideGlobalLoading);
        window.addEventListener('pageshow', hideGlobalLoading);

        document.querySelector('form')?.addEventListener('submit', function() {
            showGlobalLoading('Mengautentikasi Kredensial...');
        });
    </script>

    <!-- SweetAlert2 Popups for Session Flash & Validation Errors -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                        confirmButton: 'btn btn-login-pill px-4'
                    },
                    buttonsStyling: false
                });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak / Perhatian',
                    text: @json($errors->first()),
                    confirmButtonText: 'Mengerti',
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                        confirmButton: 'btn btn-login-pill px-4'
                    },
                    buttonsStyling: false
                });
            });
        </script>
    @endif

</body>
</html>
