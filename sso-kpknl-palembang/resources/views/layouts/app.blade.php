<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SSO KPKNL Palembang')</title>

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
            
            --md-sys-color-secondary: #575E71;
            --md-sys-color-secondary-container: #DBE2F9;
            --md-sys-color-on-secondary-container: #141B2C;
            
            --md-sys-color-tertiary: #725572;
            --md-sys-color-tertiary-container: #FDD7FA;
            --md-sys-color-on-tertiary-container: #2B122C;
            
            --md-sys-color-surface: #FBF8FF;
            --md-sys-color-surface-dim: #DCD9E1;
            --md-sys-color-surface-container-lowest: #FFFFFF;
            --md-sys-color-surface-container-low: #F5F3FB;
            --md-sys-color-surface-container: #EFEDF5;
            --md-sys-color-surface-container-high: #E9E7EF;
            --md-sys-color-surface-container-highest: #E3E1E9;
            
            --md-sys-color-on-surface: #1B1B21;
            --md-sys-color-on-surface-variant: #44464F;
            --md-sys-color-outline: #757780;
            --md-sys-color-outline-variant: #C5C6D0;
            
            --md-sys-color-error: #BA1A1A;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;

            /* M3 Expressive Shape Tokens */
            --md-shape-corner-xs: 8px;
            --md-shape-corner-sm: 12px;
            --md-shape-corner-md: 16px;
            --md-shape-corner-lg: 20px;
            --md-shape-corner-xl: 28px;
            --md-shape-corner-full: 9999px;

            /* M3 Expressive Motion Physics Tokens */
            --md-motion-easing-expressive: cubic-bezier(0.05, 0.7, 0.1, 1.0);
            --md-motion-duration-short: 200ms;
            --md-motion-duration-medium: 400ms;

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
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: var(--font-heading);
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--md-sys-color-on-surface);
        }

        /* Top Expressive Navbar */
        .main-navbar {
            background-color: var(--md-sys-color-surface-container-lowest);
            box-shadow: 0 4px 20px rgba(27, 27, 33, 0.05);
            height: 72px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            border-bottom-left-radius: var(--md-shape-corner-lg);
            border-bottom-right-radius: var(--md-shape-corner-lg);
            transition: all var(--md-motion-duration-medium) var(--md-motion-easing-expressive);
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--md-sys-color-on-surface);
            font-weight: 700;
            font-size: 1.15rem;
        }

        .navbar-brand-custom img {
            height: 40px;
            width: auto;
            border-radius: var(--md-shape-corner-xs);
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 72px;
        }

        /* Expressive Sidebar Navigation */
        .sidebar {
            width: 280px;
            background-color: var(--md-sys-color-surface-container);
            position: fixed;
            top: 72px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            padding: 1.5rem 1rem;
            z-index: 1020;
            border-top-right-radius: var(--md-shape-corner-xl);
            transition: transform var(--md-motion-duration-medium) var(--md-motion-easing-expressive);
        }

        .sidebar-heading {
            font-family: var(--font-heading);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--md-sys-color-on-surface-variant);
            padding: 0.75rem 1.25rem 0.35rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0 0 1.25rem 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.75rem 1.25rem;
            color: var(--md-sys-color-on-surface-variant);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: var(--md-shape-corner-full);
            transition: background-color 0.2s ease, color 0.2s ease;
            margin-bottom: 4px;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            width: 24px;
            text-align: center;
            transition: color 0.2s ease;
        }

        .sidebar-link:hover {
            color: var(--md-sys-color-primary);
            background-color: rgba(59, 95, 229, 0.08);
        }

        .sidebar-link.active {
            color: var(--md-sys-color-on-secondary-container);
            background-color: var(--md-sys-color-secondary-container);
            font-weight: 700;
        }

        .sidebar-link.active i {
            color: var(--md-sys-color-primary);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2.25rem;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.15);
            }
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }
        }

        /* Material You Expressive Card Component */
        .card-flat, .card-m3 {
            background-color: var(--md-sys-color-surface-container-low);
            border: none;
            border-radius: var(--md-shape-corner-xl);
            padding: 1.5rem;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .card-flat:hover, .card-m3:hover {
            background-color: var(--md-sys-color-surface-container);
            box-shadow: 0 8px 24px rgba(27, 27, 33, 0.06);
        }

        /* Buttons (Non-bouncy hover) */
        .btn, .btn-primary, .btn-tonal {
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease !important;
        }

        .btn-primary {
            background-color: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            border: none;
            border-radius: var(--md-shape-corner-lg);
            font-family: var(--font-heading);
            font-weight: 600;
            padding: 0.7rem 1.5rem;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #2F50C6;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(59, 95, 229, 0.25);
        }

        .btn-tonal {
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
            border: none;
            border-radius: var(--md-shape-corner-lg);
            font-family: var(--font-heading);
            font-weight: 600;
            padding: 0.7rem 1.5rem;
        }

        .btn-tonal:hover {
            background-color: #C5D4FF;
            color: var(--md-sys-color-on-primary-container);
        }


        /* Form Inputs M3 Expressive */
        .form-control, .form-select {
            background-color: var(--md-sys-color-surface-container-high);
            border: 1px solid transparent;
            border-radius: var(--md-shape-corner-md);
            padding: 0.75rem 1.15rem;
            color: var(--md-sys-color-on-surface);
            font-size: 0.95rem;
            transition: all var(--md-motion-duration-short) var(--md-motion-easing-expressive);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--md-sys-color-surface-container-lowest);
            border-color: var(--md-sys-color-primary);
            box-shadow: 0 0 0 3px rgba(59, 95, 229, 0.2);
            color: var(--md-sys-color-on-surface);
        }

        /* Expressive Badges / Chips */
        .badge-role {
            padding: 0.45em 0.95em;
            font-size: 0.75rem;
            font-family: var(--font-heading);
            font-weight: 700;
            border-radius: var(--md-shape-corner-xs);
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-superadmin { 
            background-color: var(--md-sys-color-error-container); 
            color: var(--md-sys-color-on-error-container); 
        }
        .badge-admin { 
            background-color: var(--md-sys-color-primary-container); 
            color: var(--md-sys-color-on-primary-container); 
        }
        .badge-user { 
            background-color: var(--md-sys-color-tertiary-container); 
            color: var(--md-sys-color-on-tertiary-container); 
        }

        /* Expressive Tables */
        .table-expressive-container {
            background-color: var(--md-sys-color-surface-container-low);
            border-radius: var(--md-shape-corner-xl);
            overflow: hidden;
        }

        .table-expressive-container .table {
            margin-bottom: 0;
            --bs-table-bg: transparent;
            --bs-table-color: var(--md-sys-color-on-surface);
        }

        .table-expressive-container thead th {
            background-color: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface-variant);
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--md-sys-color-outline-variant);
        }

        .table-expressive-container tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--md-sys-color-surface-container-highest);
            vertical-align: middle;
        }

        .table-expressive-container tbody tr:last-child td {
            border-bottom: none;
        }

        .table-expressive-container tbody tr:hover {
            background-color: rgba(59, 95, 229, 0.04);
        }

        /* Expressive Alerts */
        .alert-expressive {
            border-radius: var(--md-shape-corner-md);
            border: none;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-expressive-success {
            background-color: var(--md-sys-color-tertiary-container);
            color: var(--md-sys-color-on-tertiary-container);
        }

        .alert-expressive-danger {
            background-color: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        /* Expressive Pagination Styling (M3E) */
        .pagination {
            gap: 6px;
            margin-bottom: 0;
            align-items: center;
        }

        .pagination .page-item .page-link {
            border: none !important;
            background-color: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface);
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: var(--md-shape-corner-full) !important;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: var(--md-shape-corner-full) !important;
            padding: 0.5rem 1.15rem;
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--md-sys-color-primary) !important;
            color: var(--md-sys-color-on-primary) !important;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(59, 95, 229, 0.25);
        }

        .pagination .page-item .page-link:hover {
            background-color: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .pagination .page-item.disabled .page-link {
            background-color: var(--md-sys-color-surface-container) !important;
            color: var(--md-sys-color-outline) !important;
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Nav & Pagination Arrows / Links Fallback */
        nav[role="navigation"] a, nav[role="navigation"] span {
            border-radius: var(--md-shape-corner-full) !important;
            font-family: var(--font-heading) !important;
            border: none !important;
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

        [data-bs-theme="dark"] .global-loading-overlay {
            background-color: rgba(19, 19, 24, 0.65);
        }

        .global-loading-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .simple-loading-box {
            text-align: center;
            --md-sys-color-surface-container-lowest: #1B1B21;
            --md-sys-color-surface-container-low: #1B1B21;
            --md-sys-color-surface-container: #23232B;
            --md-sys-color-surface-container-high: #2B2B35;
            --md-sys-color-surface-container-highest: #363642;
            
            --md-sys-color-on-surface: #E3E1E9;
            --md-sys-color-on-surface-variant: #C5C6D0;

            --md-sys-color-primary: #B4C5FF;
            --md-sys-color-on-primary: #002A78;
            --md-sys-color-primary-container: #2342B3;
            --md-sys-color-on-primary-container: #D8E2FF;

            --md-sys-color-secondary-container: #3E4658;
            --md-sys-color-on-secondary-container: #DBE2F9;

            --md-sys-color-tertiary-container: #4D334E;
            --md-sys-color-on-tertiary-container: #FDD7FA;
        }

        [data-bs-theme="dark"] body {
            background-color: #131318 !important;
            color: #E3E1E9 !important;
        }

        [data-bs-theme="dark"] .main-navbar {
            background-color: rgba(23, 23, 30, 0.92) !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Sidebar Dark Mode Font & Colors (#mainSidebar) */
        [data-bs-theme="dark"] .sidebar {
            background-color: #1B1B21 !important;
            border-right-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="dark"] .sidebar-heading {
            color: #9094A6 !important;
        }

        [data-bs-theme="dark"] .sidebar-link {
            color: #D5D8E6 !important;
        }

        [data-bs-theme="dark"] .sidebar-link i {
            color: #B4C5FF !important;
        }

        [data-bs-theme="dark"] .sidebar-link:hover {
            background-color: rgba(180, 197, 255, 0.15) !important;
            color: #FFFFFF !important;
        }

        [data-bs-theme="dark"] .sidebar-link.active {
            background-color: #2342B3 !important;
            color: #FFFFFF !important;
        }

        [data-bs-theme="dark"] .sidebar-link.active i {
            color: #B4C5FF !important;
        }

        [data-bs-theme="dark"] .card-m3, 
        [data-bs-theme="dark"] .card-flat,
        [data-bs-theme="dark"] .table-expressive-container,
        [data-bs-theme="dark"] div[style*="background-color: var(--md-sys-color-surface-container-high)"] {
            background-color: #1E1E26 !important;
            color: #E3E1E9 !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #E3E1E9 !important;
        }

        [data-bs-theme="dark"] .text-secondary,
        [data-bs-theme="dark"] .text-muted {
            color: #A0A2B0 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #272732 !important;
            color: #E3E1E9 !important;
        }

        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #272732 !important;
            color: #E3E1E9 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1E1E26 !important;
            color: #E3E1E9 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        /* Notification Hub Floating Card Dropdown Dark Mode */
        .notification-dropdown-card {
            width: 380px;
            border-radius: var(--md-shape-corner-xl);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
            border: none;
            overflow: hidden;
            background-color: var(--md-sys-color-surface-container-low);
        }

        [data-bs-theme="dark"] .notification-dropdown-card {
            background-color: #1E1E26 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        .notification-item {
            transition: background-color 0.2s ease;
            text-decoration: none;
        }

        .notification-item:hover {
            background-color: var(--md-sys-color-surface-container);
        }

        [data-bs-theme="dark"] .notification-item:hover {
            background-color: #282834 !important;
        }
    </style>



    @yield('styles')
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



    <!-- Top Expressive Navbar -->
    <nav class="main-navbar">
        <button class="btn btn-link text-dark d-lg-none me-3 p-0" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars fa-lg"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="navbar-brand-custom">
            <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL Palembang" onerror="this.onerror=null; this.src='https://placehold.co/150x40/3b5fe5/ffffff?text=KPKNL+Palembang';">
            <div>
                <span class="d-block lh-1 text-primary brand-font" style="font-size: 1.05rem;">SSO INTEGRATED</span>
                <small class="text-muted fs-7 fw-normal">KPKNL Palembang</small>
            </div>
        </a>

        <div class="ms-auto d-flex align-items-center" style="gap: 1.15rem;">


            <!-- Dark Mode Toggle Button -->
            <button type="button" class="btn btn-tonal rounded-circle d-flex align-items-center justify-content-center p-0" id="darkModeToggle" style="width: 40px; height: 40px;" title="Ganti Mode Gelap / Terang">
                <i class="fa-solid fa-moon fs-6" id="darkModeIcon"></i>
            </button>

            <!-- Notification Hub Dropdown -->
            <div class="dropdown">
                @php
                    $sysNotifications = collect();
                    $unreadCount = 0;
                    if (auth()->check()) {
                        $sysNotifications = \App\Models\SsoNotification::where(function ($q) {
                                $q->where('user_id', auth()->id())
                                  ->orWhereNull('user_id');
                            })
                            ->orderBy('created_at', 'desc')
                            ->take(6)
                            ->get();
                        $unreadCount = \App\Models\SsoNotification::where('user_id', auth()->id())
                            ->whereNull('read_at')
                            ->count();
                    }
                @endphp

                <button type="button" class="btn btn-tonal rounded-circle d-flex align-items-center justify-content-center p-0 position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;" title="Notification Hub">
                    <i class="fa-regular fa-bell fs-6"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; padding: 0.25em 0.45em;">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <!-- Floating Notification Hub Card -->
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate notification-dropdown-card p-0" aria-labelledby="notificationDropdown">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: var(--md-sys-color-surface-container-high);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bell text-primary"></i>
                            <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">Notification Hub</h6>
                        </div>
                        @if($unreadCount > 0)
                            <span class="badge rounded-pill bg-primary fs-8">{{ $unreadCount }} Baru</span>
                        @endif
                    </div>

                    <div class="list-group list-group-flush border-0" style="max-height: 340px; overflow-y: auto;">
                        @forelse($sysNotifications as $notif)
                            <a href="{{ route('notifications.read', $notif->id) }}" class="list-group-item list-group-item-action notification-item p-3 border-bottom d-flex align-items-start gap-3 bg-transparent {{ is_null($notif->read_at) ? 'bg-primary-subtle bg-opacity-10' : '' }}">
                                <div class="p-2 rounded-circle text-primary bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="{{ $notif->icon ?? 'fa-solid fa-bell' }} fs-7"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark fs-7 text-truncate" style="font-family: var(--font-heading);">
                                            {{ $notif->title }}
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded-pill fs-8 ms-1 flex-shrink-0">{{ $notif->app_name }}</span>
                                    </div>
                                    <p class="text-secondary fs-8 mb-1 text-truncate" style="font-size: 0.78rem; font-weight: normal;">
                                        {{ $notif->message }}
                                    </p>
                                    <small class="text-muted fs-8 d-block">
                                        <i class="fa-regular fa-clock me-1"></i>{{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Baru saja' }}
                                    </small>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted fs-7">
                                <i class="fa-solid fa-bell-slash fs-4 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada notifikasi personal terbaru.
                            </div>
                        @endforelse
                    </div>

                    <div class="p-2.5 text-center border-top" style="background-color: var(--md-sys-color-surface-container-high);">
                        <a href="{{ route('notifications.index') }}" class="btn btn-tonal btn-sm w-100 rounded-pill fw-bold text-primary">
                            Lihat Semua Notifikasi <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>


            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark p-1 rounded-pill bg-light" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 38px; height: 38px; font-family: var(--font-heading);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-start me-2">
                        <span class="d-block fw-bold text-dark lh-1 fs-6" style="font-family: var(--font-heading);">{{ auth()->user()->name }}</span>
                        <span class="text-muted fs-7">@ {{ auth()->user()->username }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: var(--md-shape-corner-lg); min-width: 220px;" aria-labelledby="userDropdown">
                    <li class="px-3 py-2 mb-1 rounded-3" style="background-color: var(--md-sys-color-surface-container-high);">
                        <span class="d-block fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ auth()->user()->name }}</span>
                        <span class="badge badge-role badge-{{ auth()->user()->roles->first()?->name ?? 'user' }} mt-1">
                            {{ auth()->user()->roles->first()?->display_name ?? 'User' }}
                        </span>
                    </li>
                    <li><a class="dropdown-item py-2 rounded-3 my-1" href="{{ route('profile.show') }}"><i class="fa-regular fa-user me-2 text-primary"></i> Profil Saya</a></li>
                    <li><a class="dropdown-item py-2 rounded-3 my-1" href="{{ route('login-sessions.index') }}"><i class="fa-solid fa-laptop-code me-2 text-primary"></i> Aktivitas Sesi Login</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 rounded-3 text-danger fw-semibold">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar / Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </nav>

    <div class="wrapper">
        <!-- Expressive Sidebar Navigation -->
        <aside class="sidebar" id="mainSidebar">
            <div class="sidebar-heading">Menu Utama</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard SSO</span>
                    </a>
                </li>
            </ul>

            @if(auth()->user()->isSuperadmin())
                <div class="sidebar-heading">Manajemen SSO</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Kelola User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.applications.index') }}" class="sidebar-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-cubes"></i>
                            <span>Kelola Aplikasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.maintenance.index') }}" class="sidebar-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-code-branch"></i>
                            <span>Maintenance Orchestrator</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.backup.index') }}" class="sidebar-link {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-database"></i>
                            <span>Backup & Restore</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.login-info.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.login-info.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Info Card Login</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.maintenance.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.maintenance.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            <span>Modus Pemeliharaan</span>
                        </a>
                    </li>
                </ul>
            @endif



            <div class="sidebar-heading">Aktivitas & Akun</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-bell"></i>
                        <span>Pusat Notifikasi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('activity-logs.index') }}" class="sidebar-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Log Aktivitas</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('login-sessions.index') }}" class="sidebar-link {{ request()->routeIs('login-sessions.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Sesi & Perangkat</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.show') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-id-card"></i>
                        <span>Pengaturan Profil</span>
                    </a>
                </li>
            </ul>

            <div class="mt-auto pt-4 px-3 text-center border-top border-secondary-subtle">
                <small class="text-muted d-block fs-7">KPKNL Palembang &copy; 2026</small>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Expressive SweetAlert2 Notification Alerts -->
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
                                confirmButton: 'btn btn-primary rounded-pill px-4'
                            },
                            buttonsStyling: false
                        });
                    });
                </script>
            @endif

            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal / Perhatian',
                            text: @json(session('error')),
                            confirmButtonText: 'Tutup',
                            customClass: {
                                popup: 'swal2-m3e',
                                title: 'swal2-m3e-title',
                                confirmButton: 'btn btn-primary rounded-pill px-4'
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
                            title: 'Terdapat Kesalahan Input',
                            html: '{!! implode("<br>", array_map(fn($e) => "• " . e($e), $errors->all())) !!}',
                            confirmButtonText: 'Perbaiki',
                            customClass: {
                                popup: 'swal2-m3e',
                                title: 'swal2-m3e-title',
                                confirmButton: 'btn btn-primary rounded-pill px-4'
                            },
                            buttonsStyling: false
                        });
                    });
                </script>
            @endif

            @yield('content')
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('show');
        });

        // Dark Mode Toggle Manager
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            if (darkModeIcon) {
                if (theme === 'dark') {
                    darkModeIcon.className = 'fa-solid fa-sun text-warning fs-6';
                } else {
                    darkModeIcon.className = 'fa-solid fa-moon text-dark fs-6';
                }
            }
            localStorage.setItem('sso_theme', theme);
        }

        // Initialize saved theme or browser preference
        const savedTheme = localStorage.getItem('sso_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);

        darkModeToggle?.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });

        // Global Full-Screen Loading Manager
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

        // Show loading on form submits
        document.addEventListener('submit', function(e) {
            if (!e.defaultPrevented && e.target.id !== 'maintenanceForm') {
                showGlobalLoading('Memproses Data...');
            }
        });

        // Show loading on internal link clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href) {
                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                if (href && !href.startsWith('#') && !href.startsWith('javascript:') && target !== '_blank' && !link.hasAttribute('download')) {
                    if (link.hostname === window.location.hostname) {
                        showGlobalLoading('Memuat Halaman...');
                    }
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>



