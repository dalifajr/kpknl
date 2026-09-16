<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem — SIMPATIK KPKNL Palembang</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary-color: #0c306b;       /* DJKN Navy Master */
            --primary-light: #1e40af;       /* Royal Blue */
            --accent-color: #d97706;        /* DJKN Gold */
            --accent-light: #f59e0b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #07193b 0%, #0c306b 50%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.18), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(30, 64, 175, 0.25), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: #ffffff;
            padding: 36px 36px 20px;
            text-align: center;
        }

        .brand-icon-box {
            width: 68px;
            height: 68px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.85rem;
            box-shadow: 0 10px 20px -5px rgba(12, 48, 107, 0.35);
        }

        .brand-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            font-size: 0.84rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.4;
        }

        .login-body {
            padding: 20px 36px 36px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            font-size: 0.83rem;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .btn-sso {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 15px 24px;
            font-size: 1rem;
            font-weight: 700;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 8px 20px -4px rgba(12, 48, 107, 0.35);
            text-decoration: none;
        }

        .btn-sso:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -6px rgba(12, 48, 107, 0.45);
            background: linear-gradient(135deg, #092552 0%, #173b75 100%);
        }

        .btn-sso:active {
            transform: translateY(0);
        }

        .login-footer {
            border-top: 1px solid #f1f5f9;
            padding: 16px 36px;
            background: #fafafa;
            font-size: 0.74rem;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Login Header -->
        <div class="login-header">
            <div class="brand-icon-box">
                <i class="fa-solid fa-users-rectangle"></i>
            </div>
            <h1 class="brand-title">SIMPATIK <span class="text-warning">KPKNL</span></h1>
            <div class="brand-subtitle">
                Sistem Informasi Manajemen Profil &amp; Analitika Terpadu Kepegawaian<br>
                <strong class="text-dark">KPKNL Palembang &bull; DJKN Kemenkeu</strong>
            </div>
        </div>

        <!-- Login Body -->
        <div class="login-body">
            <!-- Flash Notification -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 small p-3 mb-3" role="alert">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- SSO Integration Guide Notice -->
            <div class="info-box">
                <i class="fa-solid fa-shield-halved text-primary fs-5 mt-1"></i>
                <div>
                    <strong>Otentikasi Terpusat SSO:</strong><br>
                    Aplikasi ini menggunakan sistem keamanan Single Sign-On (SSO). Seluruh pegawai dapat masuk langsung menggunakan akun terpadu KPKNL Palembang.
                </div>
            </div>

            <!-- Single Sign-On Action Button (Replaces conventional login form) -->
            <a href="{{ route('sso.redirect') }}" class="btn-sso">
                <i class="fa-solid fa-key text-warning"></i>
                <span>Masuk Melalui SSO KPKNL</span>
                <i class="fa-solid fa-arrow-right ms-auto"></i>
            </a>

            <div class="text-center mt-3">
                <span class="badge bg-light text-muted border px-3 py-1">
                    <i class="fa-solid fa-lock me-1 text-success"></i> 256-Bit SSL Encrypted &bull; OAuth 2.0
                </span>
            </div>
        </div>

        <!-- Login Footer -->
        <div class="login-footer">
            Direktorat Jenderal Kekayaan Negara &copy; {{ date('Y') }}<br>
            Kantor Pelayanan Kekayaan Negara dan Lelang Palembang
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
