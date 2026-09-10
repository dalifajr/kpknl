<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Executive Dashboard Pengelolaan BMN KPKNL Palembang</title>

    <!-- Google Sans Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome 6 -->
    <link href="{{ asset('dashboard_files/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body {
            font-family: 'Google Sans', 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0c306b 0%, #1e40af 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #0c306b 0%, #1e40af 100%);
            padding: 2.5rem 2rem 2rem;
            color: #ffffff;
            text-align: center;
        }

        .btn-sso {
            background: linear-gradient(135deg, #0c306b 0%, #1e40af 100%);
            color: #ffffff;
            font-weight: 600;
            padding: 0.95rem 1.5rem;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(12, 48, 107, 0.3);
        }

        .btn-sso:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(12, 48, 107, 0.4);
        }
    </style>
</head>
<body>

    <div class="login-card animate__animated animate__fadeIn">
        <!-- Header -->
        <div class="login-header">
            <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo DJKN KPKNL" height="56" class="mb-3" onerror="this.style.display='none'">
            <h4 class="fw-bold mb-1">EXECUTIVE DASHBOARD</h4>
            <div class="small text-white-50 fw-semibold">Pengelolaan BMN Terindikasi Idle & Eks BMN Idle</div>
            <div class="small text-warning mt-1"><i class="fas fa-landmark me-1"></i> KPKNL Palembang</div>
        </div>

        <!-- Body (Sesuai Poin 1: Fitur simulasi pengguna telah dihapus) -->
        <div class="p-4 p-md-5">
            @if(session('error'))
                <div class="alert alert-danger rounded-3 small mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info rounded-3 small mb-4" role="alert">
                    <i class="fas fa-info-circle me-1"></i> {{ session('info') }}
                </div>
            @endif

            <p class="text-muted small text-center mb-4">
                Aplikasi ini terintegrasi secara terpusat dengan <strong>Single Sign-On (SSO) KPKNL Palembang</strong>. Akses dibatasi hanya untuk akun yang telah di-assign oleh Administrator.
            </p>

            <!-- Main SSO Login Button -->
            <a href="{{ route('auth.sso.redirect') }}" class="btn btn-sso w-100 d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="fas fa-fingerprint fs-5"></i>
                <span>Masuk dengan SSO KPKNL Palembang</span>
            </a>

            <div class="text-center text-muted small mt-4" style="font-size: 0.72rem;">
                &copy; {{ date('Y') }} KPKNL Palembang &bull; Kanwil DJKN SJB
            </div>
        </div>
    </div>

</body>
</html>
