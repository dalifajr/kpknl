<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Informasi Peminjaman Risalah Lelang') — KPKNL Palembang</title>

    <!-- Google Sans & Plus Jakarta Sans Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 Full CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js 4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            padding: 0.68rem 0.88rem;
            color: #334155;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 500;
            margin-bottom: 0.28rem;
            gap: 0.85rem;
            border: 1px solid transparent;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 0.9rem;
            position: relative;
        }
        .menu-item i {
            color: #64748b;
            width: 22px;
            text-align: center;
            font-size: 1.05rem;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), color 0.22s ease;
            flex-shrink: 0;
        }
        .menu-item:hover {
            background: #f8fafc;
            color: #0c306b;
            transform: translateX(4px);
            border-color: #e2e8f0;
        }
        .menu-item:hover i {
            color: #0c306b;
            transform: scale(1.12);
        }
        .menu-item.active {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%) !important;
            color: #0c306b !important;
            font-weight: 700;
            border: 1px solid #c7d2fe !important;
            box-shadow: 0 2px 8px rgba(12, 48, 107, 0.08);
            transform: translateX(3px);
        }
        .menu-item.active i {
            color: #0c306b !important;
            transform: scale(1.1);
        }
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18%;
            bottom: 18%;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: #0c306b;
        }

        /* Interactive & Standard Cards */
        .card {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            margin-bottom: 1.5rem;
            overflow: hidden;
            background-color: #ffffff;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem !important;
        }
        .interactive-card {
            cursor: pointer !important;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease, border-color 0.28s ease !important;
            position: relative;
            background: #ffffff;
            border-radius: 16px !important;
        }
        .interactive-card:hover {
            transform: translateY(-4px) scale(1.008) !important;
            box-shadow: 0 14px 28px -6px rgba(12, 48, 107, 0.12), 0 4px 10px -2px rgba(12, 48, 107, 0.06) !important;
        }
        .interactive-card:active {
            transform: translateY(0) scale(1) !important;
        }
        .interactive-card:hover i.fa-2x {
            transform: scale(1.15) rotate(3deg);
            transition: transform 0.25s ease;
        }
        .interactive-badge-hint {
            font-size: 0.65rem;
            opacity: 0.75;
            transition: all 0.3s ease;
        }
        .interactive-card:hover .interactive-badge-hint {
            opacity: 1;
            color: #0c306b !important;
            transform: translateX(2px);
        }

        /* High-Contrast Role Badges */
        .badge-role-admin {
            background-color: #dbeafe !important;
            color: #1e3a8a !important;
            border: 1px solid #93c5fd !important;
            font-weight: 700 !important;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 2px rgba(30, 58, 138, 0.08);
        }
        .badge-role-pelelang {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            border: 1px solid #fcd34d !important;
            font-weight: 700 !important;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 2px rgba(146, 64, 14, 0.08);
        }
        .badge-role-peminjam {
            background-color: #dcfce7 !important;
            color: #14532d !important;
            border: 1px solid #86efac !important;
            font-weight: 700 !important;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 2px rgba(20, 83, 45, 0.08);
        }

        /* Instant Display - No Delay for Critical Text & Content */
        .animate-fade-in-up, .animate-delay-1, .animate-delay-2, .animate-delay-3 {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }

        /* Table Interactions */
        .table tbody tr {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .table tbody tr:hover {
            background-color: rgba(12, 48, 107, 0.028) !important;
        }

        /* Interactive Buttons */
        .btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn:active {
            transform: scale(0.97) !important;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn-primary {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--primary-light) !important;
            border-color: var(--primary-light) !important;
            box-shadow: 0 4px 12px rgba(12, 48, 107, 0.25);
        }

        /* Form Inputs Focus Glow */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 0 3.5px rgba(12, 48, 107, 0.14) !important;
        }

        /* Sticky Non-Scrolling Footer (Fixed to Viewport Bottom) */
        #mainContentArea > footer {
            position: fixed !important;
            bottom: 0 !important;
            right: 0 !important;
            left: var(--sidebar-width) !important;
            z-index: 1040 !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-top: 1px solid #e2e8f0 !important;
            box-shadow: 0 -4px 16px rgba(12, 48, 107, 0.08) !important;
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            transition: left 0.3s ease;
        }
        @media (max-width: 991.98px) {
            #mainContentArea > footer {
                left: 0 !important;
            }
        }
        /* Fluid Non-Blocking Page Fade Transition (No transform to preserve fixed footer) */
        .main-content {
            min-height: calc(100vh - 60px);
            padding-bottom: 68px !important;
            animation: fluidPageFade 0.20s ease-out forwards;
        }
        @keyframes fluidPageFade {
            0% {
                opacity: 0.92;
            }
            100% {
                opacity: 1;
            }
        }

        /* Modal & Backdrop Layering (Permanent Z-Index Leveling + Ultra-Fluid Spring Animation) */
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal-backdrop.fade {
            opacity: 0;
            transition: opacity 0.26s cubic-bezier(0.16, 1, 0.3, 1) !important;
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
        }
        .modal-backdrop.show {
            opacity: 0.52 !important;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal {
            z-index: 1060 !important;
        }
        .modal.fade .modal-dialog {
            transform: scale(0.93) translateY(-18px);
            opacity: 0;
            transition: transform 0.30s cubic-bezier(0.34, 1.25, 0.64, 1), opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            will-change: transform, opacity;
            transform-origin: center 25%;
        }
        .modal.show .modal-dialog {
            transform: scale(1) translateY(0) !important;
            opacity: 1 !important;
        }
        .modal-content {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 25px 50px -12px rgba(12, 48, 107, 0.28), 0 0 0 1px rgba(12, 48, 107, 0.08) !important;
            overflow: hidden;
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
    @stack('styles')
</head>
<body class="bg-body-tertiary">

    @php
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Pengguna';
        $userRole = $currentUser ? $currentUser->role : 'peminjam';
        $pendingCountBadge = \App\Models\RisalahPending::where('status', 'belum_validasi')->count();
        $revisiCountBadge = \App\Models\RisalahRevisi::where('status', 'revisi')->count();
    @endphp

    <!-- 1. TOPBAR NAVIGATION -->
    <nav class="navbar navbar-expand fixed-top shadow-sm px-3 bg-body border-bottom" style="z-index: 1030;">
        <div class="d-flex align-items-center gap-3 w-100">
            <!-- Mobile Toggle Button -->
            <button class="btn btn-light border-0 d-lg-none me-2" type="button" id="sidebarToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars text-secondary fs-5"></i>
            </button>

            <!-- Brand Logo & Official Title -->
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-primary fw-bold m-0 text-decoration-none">
                <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo KPKNL" height="36" onerror="this.style.display='none'">
                <div class="d-flex flex-column">
                    <span class="d-none d-sm-inline fs-6 text-dark fw-bold">
                        SISTEM INFORMASI PEMINJAMAN <span class="fw-light text-secondary fs-6">| Risalah Lelang</span>
                    </span>
                    <span class="d-inline d-sm-none fs-6 text-dark fw-bold">Peminjaman Risalah</span>
                </div>
            </a>

            <!-- Right Actions: Logout -->
            <div class="ms-auto d-flex align-items-center gap-2">

                <!-- Direct Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1.5 rounded-pill px-3 shadow-sm" title="Logout dari Aplikasi" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Mobile Drawer Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- 2. APP CONTAINER -->
    <div class="app-container">
        <!-- Fixed Left Sidebar (280px) -->
        <div class="sidebar" id="sidebar">
            <!-- User Header Profile -->
            <div class="sidebar-header d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </div>
                <div class="d-flex flex-column" style="line-height: 1.25; min-width: 0; flex: 1;">
                    <span class="fw-bold text-body" style="word-break: break-word; overflow-wrap: break-word; white-space: normal;">
                        {{ $userName }}
                    </span>
                    <small class="mt-1">
                        <span class="badge {{ $currentUser ? $currentUser->getRoleBadgeClass() : 'badge-role-peminjam' }} px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.68rem;">
                            @if($currentUser && $currentUser->isAdmin())
                                <i class="fa-solid fa-shield-halved"></i>
                            @elseif($currentUser && $currentUser->isPelelang())
                                <i class="fa-solid fa-gavel"></i>
                            @else
                                <i class="fa-solid fa-book-reader"></i>
                            @endif
                            {{ $currentUser ? $currentUser->getRoleLabel() : 'Guest' }}
                        </span>
                    </small>
                </div>
            </div>

            <!-- Grouped Monochrome Menu Navigation -->
            <div class="py-3 px-2 flex-grow-1">
                <div class="menu-group">
                    <div class="menu-header">Menu Utama</div>

                    <!-- 1. Monitoring Risalah Lelang -->
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Monitoring Risalah</span>
                    </a>

                    <!-- 2. Statistik & Tren Grafik -->
                    <a href="{{ route('statistik.index') }}" class="menu-item {{ request()->routeIs('statistik.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-column"></i>
                        <span>Statistik & Tren</span>
                    </a>

                    <!-- 3. Katalog Risalah Lelang -->
                    <a href="{{ route('katalog.index') }}" class="menu-item {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes-stacked"></i>
                        <span class="flex-grow-1">Katalog Risalah</span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 d-none" id="sidebarCartBadge" style="font-size: 0.68rem;">0</span>
                    </a>

                    <!-- 4. Peminjaman Berkas -->
                    <a href="{{ route('peminjaman.index') }}" class="menu-item {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                        <i class="fas fa-handshake-angle"></i>
                        <span>Peminjaman Berkas</span>
                    </a>

                    <div class="menu-header mt-3">Manajemen Risalah</div>

                    <!-- 4. Validasi Risalah (Admin Seksi HI) -->
                    @if($userRole === 'admin')
                        <a href="{{ route('validasi.index') }}" class="menu-item {{ request()->routeIs('validasi.*') ? 'active' : '' }}">
                            <i class="fas fa-file-circle-check"></i>
                            <span class="flex-grow-1">Validasi Risalah</span>
                            @if($pendingCountBadge > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">{{ $pendingCountBadge }}</span>
                            @endif
                        </a>
                    @endif

                    <!-- 5. Pendaftaran Risalah Baru (Pelelang & Admin) -->
                    @if($userRole === 'pelelang' || $userRole === 'admin')
                        <a href="{{ route('pendaftaran.index') }}" class="menu-item {{ request()->routeIs('pendaftaran.*') ? 'active' : '' }}">
                            <i class="fas fa-file-circle-plus"></i>
                            <span>Pendaftaran Baru</span>
                        </a>
                    @endif

                    <!-- 6. Revisi Risalah (Pelelang & Admin) -->
                    @if($userRole === 'pelelang' || $userRole === 'admin')
                        <a href="{{ route('revisi.index') }}" class="menu-item {{ request()->routeIs('revisi.*') ? 'active' : '' }}">
                            <i class="fas fa-file-pen"></i>
                            <span class="flex-grow-1">Revisi Risalah</span>
                            @if($revisiCountBadge > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">{{ $revisiCountBadge }}</span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Cart Widget in Sidebar -->
            <div class="p-3 border-top bg-light d-none" id="sidebarCartWidget">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong class="text-dark small">
                        <i class="fa-solid fa-cart-shopping text-warning me-1"></i> Keranjang (<span id="sidebarCartCount">0</span>)
                    </strong>
                    <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none small" onclick="clearCart()">Reset</button>
                </div>
                <button class="btn btn-sm btn-warning w-100 fw-bold shadow-sm" onclick="openBorrowModal()">
                    Ajukan Peminjaman
                </button>
            </div>
        </div>

        <!-- 3. MAIN CONTENT AREA -->
        <div class="main-content position-relative" id="mainContentArea">
            <!-- Deep Navy Blue Gradient Hero Backdrop -->
            <div class="main-background"></div>

            <!-- Container Fluid -->
            <div class="container-fluid px-3 px-md-4 py-4 position-relative" style="z-index: 1;">
                <!-- Page Hero Header (Putih Kontras) -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 text-white">
                    <div>
                        <h1 class="page-title fw-bold mb-1 text-white fs-3 d-flex align-items-center" id="pageHeroTitle">
                            @yield('page_title', 'Monitoring Risalah Lelang')
                        </h1>
                        <p class="text-white-50 mb-0 small">
                            @yield('page_subtitle', 'Sistem Informasi Peminjaman dan Validasi Dokumen Risalah Lelang — KPKNL Palembang')
                        </p>
                    </div>
                    @hasSection('page_actions')
                        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
                            @yield('page_actions')
                        </div>
                    @endif
                </div>

                <!-- Alert Notifications -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Yield Content -->
                @yield('content')
            </div>

            <!-- Sticky Non-Scrolling Footer -->
            <footer class="py-2 px-4 bg-white border-top text-center text-muted" style="font-size: 0.75rem;">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between w-100">
                    <div>
                        <strong>KPKNL Palembang</strong> &bull; Seksi Hukum dan Informasi &bull; Direktorat Jenderal Kekayaan Negara
                    </div>
                    <div class="text-muted mt-1 mt-md-0">
                        Sistem Informasi Peminjaman Risalah Lelang terintegrasi SSO &copy; {{ date('Y') }}
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Shared Cart Storage
        let borrowCart = JSON.parse(localStorage.getItem('borrowCart') || '[]');

        function updateCartUI() {
            const cartWidget = document.getElementById('sidebarCartWidget');
            const cartBadge = document.getElementById('sidebarCartBadge');
            const cartCount = document.getElementById('sidebarCartCount');

            if (borrowCart.length > 0) {
                if (cartWidget) cartWidget.classList.remove('d-none');
                if (cartBadge) {
                    cartBadge.classList.remove('d-none');
                    cartBadge.textContent = borrowCart.length;
                }
                if (cartCount) cartCount.textContent = borrowCart.length;
            } else {
                if (cartWidget) cartWidget.classList.add('d-none');
                if (cartBadge) cartBadge.classList.add('d-none');
                if (cartCount) cartCount.textContent = '0';
            }
            localStorage.setItem('borrowCart', JSON.stringify(borrowCart));
        }

        function clearCart() {
            borrowCart = [];
            updateCartUI();
            if (typeof renderKatalogCheckboxes === 'function') {
                renderKatalogCheckboxes();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateCartUI();

            // Reparent all modals directly under <body> to guarantee they are never trapped in parent stacking contexts
            document.querySelectorAll('.modal').forEach(function (modal) {
                document.body.appendChild(modal);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
