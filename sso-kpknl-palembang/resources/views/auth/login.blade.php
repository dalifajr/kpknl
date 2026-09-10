<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Single Sign-On | KPKNL Palembang</title>

    <!-- Google Fonts: Outfit (Display/Headings) & Inter (Body/Labels) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* M3 Expressive Color Tokens (Seed: #2563EB) */
            --md-sys-color-primary: #3B5FE5;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #D8E2FF;
            --md-sys-color-on-primary-container: #001A41;
            --md-sys-color-surface: #FBF8FF;
            --md-sys-color-surface-container-low: #F5F3FB;
            --md-sys-color-surface-container: #EFEDF5;
            --md-sys-color-surface-container-high: #E9E7EF;
            --md-sys-color-on-surface: #1B1B21;
            --md-sys-color-on-surface-variant: #44464F;
            --md-sys-color-outline: #757780;
            --md-sys-color-outline-variant: #C5C6D0;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;

            --md-shape-corner-md: 16px;
            --md-shape-corner-lg: 20px;
            --md-shape-corner-xl: 28px;

            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        /* SweetAlert2 M3E Theme Overrides */
        .swal2-popup.swal2-m3e {
            background-color: var(--md-sys-color-surface-container-low) !important;
            border-radius: var(--md-shape-corner-xl) !important;
            color: var(--md-sys-color-on-surface) !important;
            padding: 2rem !important;
            box-shadow: 0 16px 40px rgba(27, 27, 33, 0.15) !important;
            border: none !important;
        }

        .swal2-title.swal2-m3e-title {
            font-family: var(--font-heading) !important;
            font-weight: 700 !important;
            color: var(--md-sys-color-on-surface) !important;
            font-size: 1.35rem !important;
            margin-bottom: 0.5rem !important;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .login-card-wrapper {
            background-color: var(--md-sys-color-surface-container-low);
            border-radius: var(--md-shape-corner-xl);
            box-shadow: 0 20px 48px rgba(27, 27, 33, 0.1);
            width: 100%;
            max-width: {{ ($loginInfoStatus ?? 'active') === 'active' ? '920px' : '460px' }};
            overflow: hidden;
        }

        .login-header-logo {
            height: 52px;
            width: auto;
            border-radius: var(--md-shape-corner-md);
        }

        .login-form-side {
            padding: 2.75rem 2.25rem 2.25rem;
        }

        .login-info-side {
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.4rem 1rem;
            background-color: rgba(255, 255, 255, 0.45);
            color: var(--md-sys-color-on-primary-container);
            border-radius: 9999px;
            font-size: 0.775rem;
            font-family: var(--font-heading);
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .form-label {
            font-family: var(--font-heading);
            font-weight: 600;
            color: var(--md-sys-color-on-surface);
        }

        .input-group .form-control {
            border-radius: 0 var(--md-shape-corner-md) var(--md-shape-corner-md) 0;
        }

        .input-group-text {
            border-radius: var(--md-shape-corner-md) 0 0 var(--md-shape-corner-md);
            background-color: var(--md-sys-color-surface-container-high);
            border: 1px solid transparent;
            color: var(--md-sys-color-on-surface-variant);
        }

        .form-control {
            background-color: var(--md-sys-color-surface-container-high);
            border: 1px solid transparent;
            border-radius: var(--md-shape-corner-md);
            padding: 0.8rem 1.15rem;
            font-size: 0.95rem;
            color: var(--md-sys-color-on-surface);
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            background-color: var(--md-sys-color-surface-container-lowest);
            border-color: var(--md-sys-color-primary);
            box-shadow: 0 0 0 3px rgba(59, 95, 229, 0.2);
            color: var(--md-sys-color-on-surface);
        }

        .btn-primary-expressive {
            background-color: var(--md-sys-color-primary);
            border: none;
            color: var(--md-sys-color-on-primary);
            font-family: var(--font-heading);
            font-weight: 700;
            padding: 0.9rem 1.5rem;
            border-radius: var(--md-shape-corner-lg);
            width: 100%;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            letter-spacing: 0.02em;
        }

        .btn-primary-expressive:hover {
            background-color: #2F50C6;
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(59, 95, 229, 0.25);
        }

        /* Simple Full-Screen Loading Overlay */
        .global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.65);
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

        .global-loading-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .simple-loading-box {
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Simple Full-Screen Loading Overlay -->
    <div id="globalLoadingOverlay" class="global-loading-overlay">
        <div class="simple-loading-box">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem; border-width: 0.22em;">
                <span class="visually-hidden">Memuat...</span>
            </div>
            <span class="d-block mt-2 fw-semibold text-secondary fs-7" id="globalLoadingText" style="font-family: var(--font-heading); letter-spacing: 0.02em;">Memuat...</span>
        </div>
    </div>



    <div class="login-card-wrapper">
        <div class="row g-0">
            <!-- Left Side: Login Form -->
            <div class="{{ ($loginInfoStatus ?? 'active') === 'active' ? 'col-lg-6' : 'col-12' }} login-form-side">
                <div class="mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL Palembang" class="login-header-logo mb-3" onerror="this.onerror=null; this.src='https://placehold.co/180x50/3b5fe5/ffffff?text=KPKNL+Palembang';">
                    <h4 class="fw-bold mb-1" style="font-family: var(--font-heading); letter-spacing: -0.02em; color: var(--md-sys-color-primary);">SINGLE SIGN-ON (SSO)</h4>
                    <p class="text-muted fs-7 mb-0">Kantor Pelayanan Kekayaan Negara dan Lelang Palembang</p>
                </div>

                @if($isMaintenance ?? false)
                    <div class="alert alert-danger shadow-sm border-0 mb-4 p-3 rounded-4 d-flex align-items-center gap-3" role="alert" style="background-color: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container);">
                        <div class="p-2 rounded-circle bg-white text-danger d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="fa-solid fa-screwdriver-wrench fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-7 mb-0.5 text-uppercase" style="font-family: var(--font-heading); letter-spacing: 0.02em;">Sistem Dalam Pemeliharaan (Maintenance)</div>
                            <small class="fs-8 opacity-90 d-block">{{ $maintenanceMessage ?? 'Hanya Superadmin yang diperbolehkan untuk masuk saat ini.' }}</small>
                        </div>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label fs-7 mb-1">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input type="text" name="login" id="login" class="form-control border-start-0 ps-2" placeholder="Masukkan username / email..." value="{{ old('login') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fs-7 mb-1">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 ps-2" placeholder="Masukkan password..." required>
                            <button class="btn border-0 bg-light text-muted rounded-end-3" type="button" id="togglePassword">
                                <i class="fa-regular fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label fs-7 text-secondary" for="remember">
                                Ingat saya di perangkat ini
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-expressive">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> {{ ($isMaintenance ?? false) ? 'LOGIN SUPERADMIN' : 'MASUK KE SSO' }}
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary-subtle">
                    <small class="text-muted fs-8">
                        Butuh bantuan akun atau lupa password?<br>
                        Silakan hubungi <strong>Superadmin IT KPKNL Palembang</strong>.
                    </small>
                </div>
            </div>

            <!-- Right Side: Card Informasi (Managed by Superadmin) -->
            @if(($loginInfoStatus ?? 'active') === 'active')
                <div class="col-lg-6 login-info-side" style="{{ ($isMaintenance ?? false) ? 'background-color: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container);' : '' }}">
                    <div class="mb-3">
                        <span class="info-badge" style="{{ ($isMaintenance ?? false) ? 'background-color: rgba(186, 26, 26, 0.15); color: #BA1A1A;' : '' }}">
                            <i class="fa-solid {{ ($isMaintenance ?? false) ? 'fa-screwdriver-wrench text-danger' : 'fa-bullhorn text-primary' }}"></i>
                            {{ ($isMaintenance ?? false) ? 'PEMELIHARAAN SISTEM' : ($loginInfoBadge ?? 'INFO LAYANAN & KEAMANAN') }}
                        </span>
                    </div>

                    <h3 class="fw-bold mb-3" style="font-family: var(--font-heading); font-size: 1.6rem; line-height: 1.3;">
                        {{ ($isMaintenance ?? false) ? 'Sistem Dalam Pemeliharaan Berkala' : ($loginInfoTitle ?? 'Selamat Datang di Portal Single Sign-On (SSO)') }}
                    </h3>

                    <p class="fs-7 lh-base opacity-90 mb-4" style="white-space: pre-line;">
                        {{ ($isMaintenance ?? false) ? $maintenanceMessage : ($loginInfoContent ?? 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.') }}
                    </p>

                    <div class="pt-3 border-top border-secondary-subtle d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; min-width: 42px;">
                            <i class="fa-solid fa-building-columns fs-5 text-primary"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold fs-7" style="font-family: var(--font-heading);">
                                Kantor Pelayanan Kekayaan Negara dan Lelang &copy; 2026
                            </span>
                            <small class="fs-8 opacity-75">
                                Palembang - Direktorat Jenderal Kekayaan Negara
                            </small>
                        </div>
                    </div>
                </div>

            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword?.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
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


    <!-- SweetAlert2 Popups for Session Flash & Form Validation Errors -->
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
                        confirmButton: 'btn btn-primary-expressive px-4'
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
                        confirmButton: 'btn btn-primary-expressive px-4'
                    },
                    buttonsStyling: false
                });
            });
        </script>
    @endif

</body>
</html>


