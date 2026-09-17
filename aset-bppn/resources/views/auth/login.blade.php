<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Aplikasi Manajemen Aset Eks BPPN KPKNL Palembang</title>
    <!-- Material Icons & Google Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="{{ asset('css/materialize.min.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 440px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            position: relative;
        }
        .logo-circle {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.35);
        }
        .logo-circle i {
            font-size: 36px;
        }
        h4 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px;
        }
        p.subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 28px;
            line-height: 1.4;
        }
        .btn-sso {
            background: #1e40af !important;
            border-radius: 12px;
            height: 48px;
            line-height: 48px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: none;
            font-size: 0.95rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.3);
            transition: all 0.2s ease-in-out;
        }
        .btn-sso:hover {
            background: #1d4ed8 !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.4);
        }
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 0.85rem;
            text-align: left;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error i {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .footer-note {
            margin-top: 30px;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-circle">
            <i class="material-icons">account_balance</i>
        </div>
        <h4>Aset Eks BPPN</h4>
        <p class="subtitle">Sistem Informasi Pengelolaan &amp; Monitoring Aset Eks BPPN KPKNL Palembang</p>

        @if(session('sso_error') || !empty($ssoError))
            <div class="alert-error">
                <i class="material-icons">error_outline</i>
                <div>
                    <strong>Gagal Autentikasi SSO</strong><br>
                    <span>{{ session('sso_error') ?? $ssoError }}</span>
                </div>
            </div>
        @endif

        <a href="{{ route('auth.redirect') }}" class="btn waves-effect waves-light btn-sso">
            <i class="material-icons">vpn_key</i>
            <span>Masuk Melalui Single Sign-On (SSO)</span>
        </a>

        <div class="footer-note">
            &copy; {{ date('Y') }} Seksi Pengelolaan Kekayaan Negara (PKN)<br>
            KPKNL Palembang &bull; Kementerian Keuangan RI
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="{{ asset('js/materialize.min.js') }}"></script>
</body>
</html>
