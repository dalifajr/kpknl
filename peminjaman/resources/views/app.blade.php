<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Informasi Peminjaman | Risalah Lelang — KPKNL Palembang</title>

    <!-- Google Sans & Plus Jakarta Sans Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js 4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Executive Theme Styles matching Dashboard Pengelolaan BMN -->
    <style>
        @font-face {
            font-family: 'Google Sans';
            font-style: normal;
            font-weight: 400;
            src: local('Google Sans Regular'), local('GoogleSans-Regular'), url(https://fonts.gstatic.com/s/googlesans/v58/4UaGrENHsxJlGDuGo1OIlL3Kwp5MKg.woff2) format('woff2');
        }
        @font-face {
            font-family: 'Google Sans';
            font-style: normal;
            font-weight: 500;
            src: local('Google Sans Medium'), local('GoogleSans-Medium'), url(https://fonts.gstatic.com/s/googlesans/v58/4UabrENHsxJlGDuGo1OIlLU94Yt3CwZ-Pw.woff2) format('woff2');
        }
        @font-face {
            font-family: 'Google Sans';
            font-style: normal;
            font-weight: 700;
            src: local('Google Sans Bold'), local('GoogleSans-Bold'), url(https://fonts.gstatic.com/s/googlesans/v58/4UabrENHsxJlGDuGo1OIlLV154t3CwZ-Pw.woff2) format('woff2');
        }

        :root {
            --primary-color: #0c306b;       /* DJKN Navy Master */
            --primary-light: #1e40af;       /* Royal Blue */
            --accent-color: #d97706;        /* DJKN Gold */
            --accent-light: #f59e0b;
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --sidebar-width: 280px;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Google Sans', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.95rem;
            color: var(--text-color);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Top Navbar */
        .navbar {
            height: 60px;
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1030;
        }

        /* Main Blue Hero Backdrop (240px Height) */
        .main-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 240px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            z-index: 0;
        }

        /* App Container with Fixed Sidebar Offset */
        .app-container {
            position: relative;
            z-index: 1;
            padding-top: 60px;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* Left Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            z-index: 1020;
            overflow-y: auto;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1015;
            display: none;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
            .app-container {
                margin-left: 0;
            }
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.25rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Sidebar Menu Items */
        .menu-group {
            padding: 0.5rem 0.75rem;
        }
        .menu-header {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            padding: 0.75rem 0.5rem 0.35rem;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.85rem;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            font-weight: 500;
            margin-bottom: 0.25rem;
            gap: 0.85rem;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .menu-item i {
            color: #64748b;
            width: 22px;
            text-align: center;
            font-size: 1.05rem;
            transition: color 0.15s ease;
            flex-shrink: 0;
        }
        .menu-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .menu-item:hover i {
            color: #0c306b;
        }
        .menu-item.active {
            background: #e2e8f0 !important;
            color: #0c306b !important;
            font-weight: 700;
        }
        .menu-item.active i {
            color: #0c306b !important;
        }

        /* Executive Cards */
        .card {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            margin-bottom: 1.5rem;
            overflow: hidden;
            background-color: #ffffff;
        }
        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem !important;
        }
        .interactive-card {
            cursor: pointer !important;
            transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.35s ease, border-color 0.35s ease !important;
            position: relative;
            background: #ffffff;
        }
        .interactive-card:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px -4px rgba(12, 48, 107, 0.12) !important;
        }
        .interactive-card:active {
            transform: translateY(0) !important;
        }
        .interactive-card:hover .icon-box,
        .interactive-card:hover i.fas,
        .interactive-card:hover i.far,
        .interactive-card:hover i.fa-solid {
            transform: translateY(-1px);
            transition: transform 0.35s ease;
        }

        /* Realtime Live Pulse Beacon Indicator */
        .pulse-beacon {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            animation: pulse-ring 2.8s infinite ease-in-out;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Primary Button */
        .btn-primary {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--primary-light) !important;
            border-color: var(--primary-light) !important;
        }

        /* Modal Smooth Entrance */
        .modal.fade .modal-dialog {
            transform: translateY(-8px);
            transition: transform 0.32s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.32s ease-out;
        }
        .modal.show .modal-dialog {
            transform: translateY(0);
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <!-- Passing Session User Data to JavaScript Context -->
    <script>
        window.__SSO_CONFIG__ = {
            sso_base_url: "{{ env('SSO_BASE_URL', 'http://localhost/sso-kpknl-palembang/public') }}",
            sso_dashboard_url: "{{ env('SSO_BASE_URL', 'http://localhost/sso-kpknl-palembang/public') }}/dashboard",
            app_url: "{{ url('/') }}",
        };
        window.__USER__ = @json(Auth::user());
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="bg-body-tertiary">
    <div id="root">
        <!-- React App Mounted Here -->
        <div class="d-flex align-items-center justify-content-center min-vh-100 flex-column">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Memuat...</span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Memuat Sistem Informasi Peminjaman Risalah Lelang...</h6>
            <p class="text-muted fs-7">KPKNL Palembang - Direktorat Jenderal Kekayaan Negara</p>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
