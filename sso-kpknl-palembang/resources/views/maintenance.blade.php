<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modus Pemeliharaan | SSO KPKNL Palembang</title>

    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --md-sys-color-primary: #3B5FE5;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #D8E2FF;
            --md-sys-color-on-primary-container: #001A41;
            --md-sys-color-surface: #FBF8FF;
            --md-sys-color-surface-container-low: #F5F3FB;
            --md-sys-color-surface-container-high: #E9E7EF;
            --md-sys-color-on-surface: #1B1B21;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;

            --md-shape-corner-xl: 28px;
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            margin: 0;
        }

        .maintenance-card {
            background-color: var(--md-sys-color-surface-container-low);
            border-radius: var(--md-shape-corner-xl);
            box-shadow: 0 20px 48px rgba(27, 27, 33, 0.1);
            max-width: 580px;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
        }

        .icon-pulse {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background-color: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(186, 26, 26, 0.2); }
            70% { box-shadow: 0 0 0 20px rgba(186, 26, 26, 0); }
            100% { box-shadow: 0 0 0 0 rgba(186, 26, 26, 0); }
        }
    </style>
</head>
<body>

    <div class="maintenance-card">
        <div class="icon-pulse">
            <i class="fa-solid fa-screwdriver-wrench fs-1"></i>
        </div>

        <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL Palembang" class="mb-3" style="height: 48px;" onerror="this.onerror=null; this.src='https://placehold.co/160x45/3b5fe5/ffffff?text=KPKNL+Palembang';">

        <h3 class="fw-bold mb-2 text-dark" style="font-family: var(--font-heading);">Sistem Dalam Pemeliharaan</h3>
        <p class="text-muted fs-7 mb-4">SINGLE SIGN-ON (SSO) KPKNL PALEMBANG</p>

        <div class="p-3.5 rounded-4 mb-4 text-start" style="background-color: var(--md-sys-color-surface-container-high);">
            <div class="d-flex gap-3">
                <i class="fa-solid fa-circle-info text-primary fs-5 mt-0.5"></i>
                <div>
                    <div class="fw-bold text-dark fs-7 mb-1" style="font-family: var(--font-heading);">Pemberitahuan Sistem</div>
                    <div class="text-secondary fs-7 lh-base">{{ $message ?? 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.' }}</div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold" style="background-color: var(--md-sys-color-primary);">
                <i class="fa-solid fa-user-shield me-1.5"></i> Login Superadmin
            </a>
            <button onclick="window.location.reload();" class="btn btn-light rounded-pill px-4 py-2.5 fw-semibold border">
                <i class="fa-solid fa-rotate me-1.5"></i> Coba Muat Ulang
            </button>
        </div>

        <div class="mt-4 pt-3 border-top text-muted fs-8">
            Kantor Pelayanan Kekayaan Negara dan Lelang Palembang &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>
