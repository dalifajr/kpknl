<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SSO KPKNL Palembang')</title>

    <!-- Google Fonts: Google Sans, Roboto & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* Google Cloud Console Design System Tokens */
            --gcp-primary: #1a73e8;
            --gcp-primary-hover: #1557b0;
            --gcp-primary-container: #e8f0fe;
            --gcp-on-primary-container: #174ea6;
            
            --gcp-surface: #f8f9fa;
            --gcp-surface-container: #ffffff;
            --gcp-surface-variant: #f1f3f4;
            --gcp-border: #dadce0;
            --gcp-border-subtle: #e8eaed;
            
            --gcp-text-primary: #202124;
            --gcp-text-secondary: #5f6368;
            --gcp-text-muted: #70757a;
            
            --gcp-success: #1e8e3e;
            --gcp-success-container: #e6f4ea;
            --gcp-warning: #f9ab00;
            --gcp-warning-container: #fef7e0;
            --gcp-error: #d93025;
            --gcp-error-container: #fce8e6;

            /* Backward-compatible Material Design tokens */
            --md-sys-color-primary: var(--gcp-primary);
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: var(--gcp-primary-container);
            --md-sys-color-on-primary-container: var(--gcp-on-primary-container);
            
            --md-sys-color-secondary: var(--gcp-text-secondary);
            --md-sys-color-secondary-container: var(--gcp-surface-variant);
            --md-sys-color-on-secondary-container: var(--gcp-text-primary);
            
            --md-sys-color-tertiary: #137333;
            --md-sys-color-tertiary-container: var(--gcp-success-container);
            --md-sys-color-on-tertiary-container: #0d652d;
            
            --md-sys-color-surface: var(--gcp-surface);
            --md-sys-color-surface-dim: var(--gcp-surface-variant);
            --md-sys-color-surface-container-lowest: #FFFFFF;
            --md-sys-color-surface-container-low: #FFFFFF;
            --md-sys-color-surface-container: #FFFFFF;
            --md-sys-color-surface-container-high: var(--gcp-surface);
            --md-sys-color-surface-container-highest: var(--gcp-surface-variant);
            
            --md-sys-color-on-surface: var(--gcp-text-primary);
            --md-sys-color-on-surface-variant: var(--gcp-text-secondary);
            --md-sys-color-outline: var(--gcp-border);
            --md-sys-color-outline-variant: var(--gcp-border-subtle);
            
            --md-sys-color-error: var(--gcp-error);
            --md-sys-color-error-container: var(--gcp-error-container);
            --md-sys-color-on-error-container: #a50e0e;

            /* Standardized shapes */
            --md-shape-corner-xs: 4px;
            --md-shape-corner-sm: 8px;
            --md-shape-corner-md: 8px;
            --md-shape-corner-lg: 12px;
            --md-shape-corner-xl: 16px;
            --md-shape-corner-full: 9999px;

            --font-heading: 'Google Sans', 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-body: 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* SweetAlert2 Google Cloud Theme */
        .swal2-popup.swal2-m3e {
            background-color: var(--gcp-surface-container) !important;
            border-radius: 12px !important;
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

        body {
            font-family: var(--font-body);
            background-color: var(--gcp-surface);
            color: var(--gcp-text-primary);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: var(--font-heading);
            font-weight: 600;
            letter-spacing: -0.01em;
            color: var(--gcp-text-primary);
        }

        /* Top Google Cloud Console App Bar */
        .main-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--gcp-border);
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.08);
            height: 56px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            border-radius: 0;
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--gcp-text-primary);
            font-weight: 500;
            font-size: 1rem;
        }

        .navbar-brand-custom img {
            height: 32px;
            width: auto;
            border-radius: 4px;
        }

        .navbar-brand-project {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px;
            border-radius: 6px;
            background-color: #f1f3f4;
            border: 1px solid transparent;
            font-size: 0.85rem;
            color: #3c4043;
            font-weight: 500;
            cursor: default;
        }

        /* Global Header Search in Google Cloud Console */
        .gcp-header-search {
            background-color: #f1f3f4;
            border: 1px solid transparent;
            border-radius: 8px;
            width: 320px;
            display: flex;
            align-items: center;
            padding: 4px 12px;
            transition: all 0.2s ease;
        }

        .gcp-header-search:focus-within {
            background-color: #ffffff;
            border-color: var(--gcp-primary);
            box-shadow: 0 1px 3px 0 rgba(60, 64, 67, 0.2);
        }

        .gcp-header-search input {
            background: transparent;
            border: none;
            outline: none;
            font-size: 0.875rem;
            width: 100%;
            padding-left: 8px;
            color: var(--gcp-text-primary);
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 56px;
        }

        /* Google Cloud Console Left Sidebar */
        .sidebar {
            width: 256px;
            background-color: #ffffff;
            border-right: 1px solid var(--gcp-border);
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            padding: 0.75rem 0;
            z-index: 1020;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background-color: #dadce0;
            border-radius: 3px;
        }

        .sidebar-heading {
            font-family: var(--font-heading);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--gcp-text-muted);
            padding: 0.85rem 1.25rem 0.35rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0 0 0.5rem 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.55rem 1rem;
            margin: 1px 8px;
            color: #3c4043;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 6px;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            color: var(--gcp-text-secondary);
            transition: color 0.15s ease;
        }

        .sidebar-link:hover {
            color: #202124;
            background-color: #f1f3f4;
        }

        .sidebar-link.active {
            color: var(--gcp-primary);
            background-color: #e8f0fe;
            font-weight: 600;
        }

        .sidebar-link.active i {
            color: var(--gcp-primary);
        }

        /* Main Content */
        .main-content {
            margin-left: 256px;
            flex: 1;
            padding: 1.75rem 2rem;
            background-color: var(--gcp-surface);
            min-height: calc(100vh - 56px);
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            }
            .main-content {
                margin-left: 0;
                padding: 1.25rem 1rem;
            }
        }

        /* Google Cloud Console Cards */
        .card-flat, .card-m3, .card {
            background-color: #ffffff;
            border: 1px solid var(--gcp-border);
            border-radius: 8px;
            padding: 1.25rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.05);
        }

        .card-flat:hover, .card-m3:hover {
            border-color: #bdc1c6;
            box-shadow: 0 1px 3px 1px rgba(60, 64, 67, 0.1);
        }

        /* Clickable App Launcher Cards (Direct Click, No Button) */
        .app-launch-card {
            background-color: var(--md-sys-color-surface-container-high);
            border: 1px solid var(--gcp-border-subtle) !important;
            transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.22s cubic-bezier(0.2, 0, 0, 1), border-color 0.22s ease, background-color 0.22s ease;
            cursor: pointer;
            text-decoration: none !important;
            position: relative;
            outline: none;
        }

        .app-launch-card:hover, .app-launch-card:focus-visible {
            transform: translateY(-3px);
            background-color: #ffffff;
            border-color: #1a73e8 !important;
            box-shadow: 0 10px 24px -4px rgba(26, 115, 232, 0.16), 0 4px 10px -2px rgba(0, 0, 0, 0.04);
            text-decoration: none !important;
        }

        .app-launch-card:hover .app-title, .app-launch-card:focus-visible .app-title {
            color: var(--gcp-primary) !important;
        }

        .app-launch-card .app-launch-indicator {
            opacity: 0;
            transform: translate(-3px, 3px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            color: var(--gcp-primary);
        }

        .app-launch-card:hover .app-launch-indicator, .app-launch-card:focus-visible .app-launch-indicator {
            opacity: 1;
            transform: translate(0, 0);
        }

        [data-bs-theme="dark"] .app-launch-card {
            background-color: #292a2d;
            border-color: #3c4043 !important;
        }

        [data-bs-theme="dark"] .app-launch-card:hover, [data-bs-theme="dark"] .app-launch-card:focus-visible {
            background-color: #303134;
            border-color: #8ab4f8 !important;
            box-shadow: 0 10px 24px -4px rgba(138, 180, 248, 0.2);
        }

        [data-bs-theme="dark"] .app-launch-card:hover .app-title {
            color: #8ab4f8 !important;
        }

        /* Buttons */
        .btn {
            font-family: var(--font-heading);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 4px;
            padding: 0.45rem 1rem;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background-color: var(--gcp-primary);
            border-color: var(--gcp-primary);
            color: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.25);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--gcp-primary-hover);
            border-color: var(--gcp-primary-hover);
            color: #ffffff;
            box-shadow: 0 1px 3px 1px rgba(60, 64, 67, 0.3);
        }

        .btn-tonal, .btn-outline-secondary {
            background-color: #ffffff;
            border: 1px solid var(--gcp-border);
            color: #3c4043;
        }

        .btn-tonal:hover, .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #bdc1c6;
            color: #202124;
        }

        .btn-outline-primary {
            border-color: var(--gcp-border);
            color: var(--gcp-primary);
            background-color: #ffffff;
        }

        .btn-outline-primary:hover {
            background-color: #f8fafd;
            border-color: var(--gcp-primary);
            color: var(--gcp-primary-hover);
        }

        /* Form Inputs */
        .form-control, .form-select {
            background-color: #ffffff;
            border: 1px solid var(--gcp-border);
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            color: var(--gcp-text-primary);
            font-size: 0.875rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            border-color: var(--gcp-primary);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
            color: var(--gcp-text-primary);
        }

        /* Tables Google Cloud style */
        .table-expressive-container {
            background-color: #ffffff;
            border: 1px solid var(--gcp-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-expressive-container .table {
            margin-bottom: 0;
            --bs-table-bg: transparent;
            --bs-table-color: var(--gcp-text-primary);
        }

        .table-expressive-container thead th {
            background-color: #f8f9fa;
            color: var(--gcp-text-secondary);
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gcp-border);
        }

        .table-expressive-container tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table-expressive-container tbody tr:last-child td {
            border-bottom: none;
        }

        .table-expressive-container tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Badges */
        .badge-role {
            padding: 0.25em 0.6em;
            font-size: 0.72rem;
            font-family: var(--font-heading);
            font-weight: 600;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-superadmin { 
            background-color: #fce8e6; 
            color: #c5221f; 
            border: 1px solid #fad2cf;
        }
        .badge-admin { 
            background-color: #e8f0fe; 
            color: #1a73e8; 
            border: 1px solid #d2e3fc;
        }
        .badge-user { 
            background-color: #f1f3f4; 
            color: #5f6368; 
            border: 1px solid #dadce0;
        }

        /* Pagination */
        .pagination {
            gap: 4px;
            margin-bottom: 0;
            align-items: center;
        }

        .pagination .page-item .page-link {
            border: 1px solid var(--gcp-border) !important;
            background-color: #ffffff;
            color: #3c4043;
            font-family: var(--font-heading);
            font-weight: 500;
            font-size: 0.825rem;
            border-radius: 4px !important;
            padding: 0.35rem 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            transition: all 0.15s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--gcp-primary) !important;
            border-color: var(--gcp-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .pagination .page-item .page-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #f8f9fa !important;
            color: #9aa0a6 !important;
            border-color: #e8eaed !important;
            opacity: 0.6;
        }

        /* Notification Dropdown Card */
        .notification-dropdown-card {
            width: 360px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--gcp-border);
            overflow: hidden;
            background-color: #ffffff;
        }

        .notification-item {
            transition: background-color 0.15s ease;
            text-decoration: none;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        /* Full Screen Loading Overlay */
        .global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
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

        /* Dark Mode Styling (Google Cloud Dark Theme) */
        [data-bs-theme="dark"] {
            --gcp-surface: #1f1f1f;
            --gcp-surface-container: #292a2d;
            --gcp-surface-variant: #303134;
            --gcp-border: #3c4043;
            --gcp-border-subtle: #444746;
            --gcp-text-primary: #e8eaed;
            --gcp-text-secondary: #9aa0a6;
            --gcp-text-muted: #80868b;
        }

        [data-bs-theme="dark"] body {
            background-color: #1f1f1f !important;
            color: #e8eaed !important;
        }

        [data-bs-theme="dark"] .main-navbar {
            background-color: #202124 !important;
            border-bottom-color: #3c4043 !important;
        }

        [data-bs-theme="dark"] .sidebar {
            background-color: #202124 !important;
            border-right-color: #3c4043 !important;
        }

        [data-bs-theme="dark"] .sidebar-heading {
            color: #80868b !important;
        }

        [data-bs-theme="dark"] .sidebar-link {
            color: #bdc1c6 !important;
        }

        [data-bs-theme="dark"] .sidebar-link i {
            color: #9aa0a6 !important;
        }

        [data-bs-theme="dark"] .sidebar-link:hover {
            background-color: #303134 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .sidebar-link.active {
            background-color: #004a77 !important;
            color: #8ab4f8 !important;
        }

        [data-bs-theme="dark"] .sidebar-link.active i {
            color: #8ab4f8 !important;
        }

        [data-bs-theme="dark"] .main-content {
            background-color: #1f1f1f !important;
        }

        [data-bs-theme="dark"] .card-m3, 
        [data-bs-theme="dark"] .card-flat,
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .notification-dropdown-card,
        [data-bs-theme="dark"] .table-expressive-container {
            background-color: #292a2d !important;
            border-color: #3c4043 !important;
            color: #e8eaed !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #e8eaed !important;
        }

        [data-bs-theme="dark"] .text-secondary,
        [data-bs-theme="dark"] .text-muted {
            color: #9aa0a6 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #303134 !important;
            color: #e8eaed !important;
        }

        [data-bs-theme="dark"] .navbar-brand-project,
        [data-bs-theme="dark"] .gcp-header-search {
            background-color: #303134 !important;
            color: #bdc1c6 !important;
        }

        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #292a2d !important;
            color: #e8eaed !important;
            border-color: #3c4043 !important;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #292a2d !important;
            color: #e8eaed !important;
            border: 1px solid #3c4043 !important;
        }

        [data-bs-theme="dark"] .global-loading-overlay {
            background-color: rgba(31, 31, 31, 0.7);
        }
    </style>

    @yield('styles')
</head>
<body>

    <!-- Simple Full-Screen Loading Overlay -->
    <div id="globalLoadingOverlay" class="global-loading-overlay">
        <div class="simple-loading-box">
            <div class="spinner-border text-primary" role="status" style="width: 2.25rem; height: 2.25rem; border-width: 0.2em;">
                <span class="visually-hidden">Memuat...</span>
            </div>
            <span class="d-block mt-2 fw-medium text-secondary fs-7" id="globalLoadingText" style="font-family: var(--font-heading); letter-spacing: 0.02em;">Memuat...</span>
        </div>
    </div>

    <!-- Top Google Cloud Console App Bar -->
    <nav class="main-navbar">
        <button class="btn btn-link text-secondary d-lg-none me-2 p-1" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars fa-lg"></i>
        </button>

        <a href="{{ route('dashboard') }}" class="navbar-brand-custom me-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL Palembang" onerror="this.onerror=null; this.src='https://placehold.co/150x40/1a73e8/ffffff?text=KPKNL';">
            <div class="d-none d-sm-block">
                <span class="fw-bold text-dark fs-6 lh-1 d-block" style="font-family: var(--font-heading);">SSO PLATFORM</span>
                <span class="text-muted fs-8">KPKNL Palembang</span>
            </div>
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Dark Mode Toggle Button -->
            <button type="button" class="btn btn-tonal rounded-circle d-flex align-items-center justify-content-center p-0" id="darkModeToggle" style="width: 36px; height: 36px;" title="Ganti Mode Gelap / Terang">
                <i class="fa-solid fa-moon fs-7" id="darkModeIcon"></i>
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

                <button type="button" class="btn btn-tonal rounded-circle d-flex align-items-center justify-content-center p-0 position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 36px; height: 36px;" title="Pusat Notifikasi">
                    <i class="fa-regular fa-bell fs-6 text-secondary"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem; padding: 0.2em 0.4em;">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <!-- Notification Dropdown Panel -->
                <div class="dropdown-menu dropdown-menu-end notification-dropdown-card p-0" aria-labelledby="notificationDropdown">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bell text-primary"></i>
                            <h6 class="fw-bold mb-0 text-dark fs-7" style="font-family: var(--font-heading);">Pusat Notifikasi System</h6>
                        </div>
                        @if($unreadCount > 0)
                            <span class="badge rounded bg-primary fs-8">{{ $unreadCount }} Baru</span>
                        @endif
                    </div>

                    <div class="list-group list-group-flush border-0" style="max-height: 320px; overflow-y: auto;">
                        @forelse($sysNotifications as $notif)
                            <a href="{{ route('notifications.read', $notif->id) }}" class="list-group-item list-group-item-action notification-item p-2.5 border-bottom d-flex align-items-start gap-2.5 bg-transparent {{ is_null($notif->read_at) ? 'bg-primary-subtle bg-opacity-10' : '' }}">
                                <div class="p-2 rounded text-primary bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                    <i class="{{ $notif->icon ?? 'fa-solid fa-bell' }} fs-7"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                                        <span class="fw-semibold text-dark fs-8 text-truncate" style="font-family: var(--font-heading);">
                                            {{ $notif->title }}
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded fs-9 ms-1 flex-shrink-0">{{ $notif->app_name }}</span>
                                    </div>
                                    <p class="text-secondary fs-8 mb-1 text-truncate" style="font-size: 0.75rem;">
                                        {{ $notif->message }}
                                    </p>
                                    <small class="text-muted fs-9 d-block">
                                        <i class="fa-regular fa-clock me-1"></i>{{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Baru saja' }}
                                    </small>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted fs-8">
                                <i class="fa-solid fa-bell-slash fs-4 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada notifikasi personal terbaru.
                            </div>
                        @endforelse
                    </div>

                    <div class="p-2 text-center border-top bg-light">
                        <a href="{{ route('notifications.index') }}" class="btn btn-link btn-sm text-primary text-decoration-none fw-medium fs-8">
                            Lihat Semua Notifikasi <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown ms-1">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark p-1 rounded-pill" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-1.5" style="width: 32px; height: 32px; font-size: 0.85rem; font-family: var(--font-heading);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-start me-1.5">
                        <span class="d-block fw-semibold text-dark lh-1 fs-7" style="font-family: var(--font-heading);">{{ auth()->user()->name }}</span>
                        <span class="text-muted fs-9">@ {{ auth()->user()->username }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border p-2" style="border-radius: 8px; min-width: 220px;" aria-labelledby="userDropdown">
                    <li class="px-3 py-2 mb-1 rounded bg-light">
                        <span class="d-block fw-bold text-dark fs-7" style="font-family: var(--font-heading);">{{ auth()->user()->name }}</span>
                        <span class="badge badge-role badge-{{ auth()->user()->roles->first()?->name ?? 'user' }} mt-1">
                            {{ auth()->user()->roles->first()?->display_name ?? 'User' }}
                        </span>
                    </li>
                    <li><a class="dropdown-item py-1.5 rounded my-0.5 fs-7" href="{{ route('profile.show') }}"><i class="fa-regular fa-user me-2 text-primary"></i> Profil Saya</a></li>
                    <li><a class="dropdown-item py-1.5 rounded my-0.5 fs-7" href="{{ route('login-sessions.index') }}"><i class="fa-solid fa-laptop-code me-2 text-primary"></i> Aktivitas Sesi Login</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-1.5 rounded text-danger fw-semibold fs-7">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar / Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="wrapper">
        <!-- Google Cloud Console Sidebar Navigation -->
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

            @if(auth()->user()->isSuperadmin() || auth()->user()->isMaintenance())
                <div class="sidebar-heading">Administrasi Pengguna</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Kelola User</span>
                        </a>
                    </li>
                </ul>
            @endif

            @if(auth()->user()->isMaintenance())
                <div class="sidebar-heading">Pemeliharaan & Sistem</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('admin.applications.index') }}" class="sidebar-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-cubes"></i>
                            <span>Aplikasi Terintegrasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.maintenance.index') }}" class="sidebar-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-code-branch"></i>
                            <span>Maintenance Orchestrator</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-gear"></i>
                            <span>Pengaturan Sistem</span>
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

            <div class="mt-auto pt-4 px-3 text-center border-top">
                <small class="text-muted d-block fs-8">KPKNL Palembang &copy; 2026</small>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
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
                                confirmButton: 'btn btn-primary px-4'
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
                                confirmButton: 'btn btn-primary px-4'
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
                                confirmButton: 'btn btn-primary px-4'
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
                    darkModeIcon.className = 'fa-solid fa-sun text-warning fs-7';
                } else {
                    darkModeIcon.className = 'fa-solid fa-moon text-secondary fs-7';
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
    @stack('scripts')
</body>
</html>
