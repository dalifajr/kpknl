<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Executive Dashboard — Pengelolaan BMN KPKNL Palembang')</title>

    <!-- Google Sans Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="{{ asset('dashboard_files/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- FontAwesome 6 Full CDN for Crisp Reliable Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link href="{{ asset('dashboard_files/admin-app.css') }}" rel="stylesheet">

    <!-- DataTables 2.x CSS with Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css">

    <style>
        /* Google Sans Font Family Rule */
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
            background-color: var(--bs-body-bg);
            font-family: 'Google Sans', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.95rem;
            color: var(--text-color);
        }

        .navbar {
            height: 60px;
            background: #ffffff !important;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .main-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 240px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            z-index: 0;
        }

        /* Dynamic Interactive Drilldown Cards with Gentle Executive Elevations */
        .interactive-card {
            cursor: pointer !important;
            transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.35s ease, border-color 0.35s ease !important;
            position: relative;
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
        .interactive-card:hover i.far {
            transform: translateY(-1px);
            transition: transform 0.35s ease;
        }
        .interactive-card .icon-box,
        .interactive-card i.fas,
        .interactive-card i.far {
            transition: transform 0.35s ease;
            display: inline-block;
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
        .chart-interactive {
            cursor: pointer;
            transition: transform 0.35s ease;
        }
        .chart-interactive:hover {
            transform: translateY(-2px);
        }

        /* Smooth Tab Pane Fade & Glide Entrance Animation (Gentle Transition) */
        .dashboard-tab-pane.active {
            animation: tabFadeIn 0.38s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        @keyframes tabFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Realtime Live Pulse Beacon Indicator (Calm Breathing Rhythm) */
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

        /* Note: Angka/KPI diperlakukan statis tanpa animasi meloncat sesuai arahan eksekutif */

        /* Modern Executive Sync Orbit Spinner (Dignified Rhythm) */
        .sync-loader-wrapper {
            position: relative;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sync-orbit-spinner {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 3.5px solid rgba(12, 48, 107, 0.12);
            border-top-color: #0c306b;
            border-right-color: #f59e0b;
            animation: syncSpin 1.25s linear infinite;
        }
        .sync-center-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.35rem;
            color: #0c306b;
            animation: syncCenterPulse 2.2s ease-in-out infinite;
        }
        @keyframes syncSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes syncCenterPulse {
            0%, 100% { opacity: 0.8; transform: translate(-50%, -50%) scale(0.95); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.04); color: #f59e0b; }
        }

        /* Modal Dialog Smooth Entrance Animation */
        .modal.fade .modal-dialog {
            transform: translateY(-8px);
            transition: transform 0.32s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.32s ease-out;
        }
        .modal.show .modal-dialog {
            transform: translateY(0);
        }

        /* Button micro-interactions */
        .btn {
            transition: all 0.28s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn:active {
            transform: translateY(0);
        }
        #btn-sync-now:hover i {
            transform: rotate(90deg);
        }
        #btn-sync-now i {
            transition: transform 0.4s ease;
        }

        /* Modal Z-Index & Stacking Guarantee */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal-dialog {
            z-index: 1061 !important;
        }

        /* Highlighting style in Drilldown Search */
        mark.dt-highlight {
            background-color: #fef08a !important;
            color: #854d0e !important;
            padding: 2px 4px !important;
            border-radius: 4px !important;
            font-weight: 700 !important;
        }

        /* Modal footer pagination clean layout */
        #drilldownFooterPaging .pagination {
            margin-bottom: 0 !important;
        }
        #drilldownFooterPaging .page-link {
            padding: 0.25rem 0.65rem !important;
            font-size: 0.82rem !important;
        }
        #drilldownFooterInfo .dt-info {
            padding-top: 0 !important;
            font-size: 0.82rem !important;
            margin-bottom: 0 !important;
        }

        /* Sidebar Clean Monochrome Menu Items */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.65rem 1rem;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            font-weight: 500;
            margin-bottom: 0.25rem;
            gap: 0.85rem;
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

        .card {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            margin-bottom: 1.5rem;
            overflow: hidden;
            background-color: var(--bs-body-bg);
        }

        .card-header {
            background-color: var(--bs-body-bg) !important;
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1.25rem 1.5rem !important;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
        }

        /* Dual Scrollbar Styling for Source Tables & Potensi 19 Table */
        .top-scrollbar-container {
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .top-scrollbar-container::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .top-scrollbar-container::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .top-scrollbar-container::-webkit-scrollbar-thumb:hover,
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-body-tertiary">

    <!-- Topbar Navigation -->
    <nav class="navbar navbar-expand fixed-top shadow-sm px-3 bg-body border-bottom" style="z-index: 1030;">
        <div class="d-flex align-items-center gap-3 w-100">
            <!-- Mobile Toggle Button -->
            <button class="btn btn-light border-0 d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="fas fa-bars text-secondary fs-5"></i>
            </button>

            <!-- Brand Logo & Executive Title -->
            <div class="navbar-brand d-flex align-items-center gap-2 text-primary fw-bold m-0">
                <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo KPKNL" height="36" onerror="this.style.display='none'">
                <div class="d-flex flex-column">
                    <span class="d-none d-sm-inline fs-6 text-dark fw-bold">EXECUTIVE DASHBOARD <span class="fw-light text-secondary fs-6">| Pengelolaan BMN</span></span>
                    <span class="d-inline d-sm-none fs-6 text-dark fw-bold">Executive Dashboard</span>
                </div>
            </div>

            <!-- Right Actions (Hanya Sinkronisasi & Logout SSO) -->
            <div class="ms-auto d-flex align-items-center gap-2">
                <!-- Sync Status Badge -->
                <div class="d-none d-lg-flex align-items-center bg-light border rounded-pill px-3 py-1 text-muted small shadow-2xs">
                    <span class="pulse-beacon me-2" title="Koneksi Google Sheet Aktif"></span>
                    <i class="fas fa-file-excel text-success me-1"></i>
                    <span>Live Google Sheet:</span>
                    <strong class="text-dark ms-1" id="last-sync-text">
                        @if(isset($lastSync) && $lastSync->created_at)
                            {{ $lastSync->created_at->isoFormat('D MMM Y, HH:mm') }} WIB
                        @else
                            Tersinkron
                        @endif
                    </strong>
                </div>

                <!-- Sync Now Button -->
                <button class="btn btn-sm btn-warning d-flex align-items-center gap-2 text-white fw-semibold" id="btn-sync-now" onclick="triggerSync()">
                    <i class="fas fa-arrows-rotate" id="sync-spinner"></i>
                    <span class="d-none d-sm-inline">Sinkronisasi</span>
                </button>

                <!-- DIRECT LOGOUT BUTTON (Kembali ke Laman Dashboard SSO) -->
                <form action="{{ route('auth.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2 rounded-pill px-3 shadow-sm" title="Logout dan Kembali ke Dashboard SSO">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- App Container -->
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <!-- Sidebar Header: Nama User Bersih, Wrapping Rapi, Role di Bawahnya -->
            @php
                $currentUser = Auth::user();
                $rawName = $currentUser ? $currentUser->name : 'Pengguna Guest';
                $cleanName = preg_replace('/\s*\(.*?\)\s*/', '', $rawName);
            @endphp
            <div class="sidebar-header d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:42px;height:42px;font-size:1.1rem; flex-shrink: 0;">
                    {{ substr($cleanName, 0, 1) }}
                </div>
                <div class="d-flex flex-column" style="line-height: 1.25; min-width: 0; flex: 1;">
                    <span class="fw-bold text-body" style="word-break: break-word; overflow-wrap: break-word; white-space: normal;">
                        {{ $cleanName }}
                    </span>
                    <small class="text-secondary mt-1">
                        <span class="badge {{ $currentUser ? $currentUser->getRoleBadgeClass() : 'bg-secondary' }}" style="font-size: 0.65rem;">
                            {{ $currentUser ? $currentUser->getRoleLabel() : 'Guest' }}
                        </span>
                    </small>
                </div>
            </div>

            <!-- List Menu Sidebar Monokrom -->
            <div class="py-3 px-2">
                <div class="menu-group">
                    <div class="menu-header">Menu Utama</div>
                    
                    <!-- 1. BMN eks BMN Idle -->
                    <a href="javascript:void(0)" class="menu-item active nav-tab-link" data-target="#tab-eks-idle">
                        <i class="fas fa-boxes-stacked"></i>
                        <span>BMN eks BMN Idle</span>
                    </a>

                    <!-- 2. BMN Terindikasi Idle -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-terindikasi">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>BMN Terindikasi Idle</span>
                    </a>

                    <!-- 3. Matriks Capaian KPKNL -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-matriks">
                        <i class="fas fa-table-cells"></i>
                        <span>Matriks Capaian KPKNL</span>
                    </a>

                    <div class="menu-header mt-3">Pemetaan Klaster K/L</div>
                    
                    <!-- 4. Pemetaan per K/L -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-pemetaan-kemenkeu">
                        <i class="fas fa-building-columns"></i>
                        <span>Pemetaan per K/L</span>
                    </a>

                    <!-- 5. Potensi BMN Idle per K/L -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-potensi-kemenkeu">
                        <i class="fas fa-sack-dollar"></i>
                        <span>Potensi BMN Idle per K/L</span>
                    </a>

                    <div class="menu-header mt-3">Tabel Sumber Lengkap</div>
                    
                    <!-- 6. Tabel Sumber Potensi -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-source-potensi">
                        <i class="fas fa-table-list"></i>
                        <span>Tabel Sumber Potensi</span>
                    </a>

                    <!-- 7. Tabel Sumber Eks Idle -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-source-eks">
                        <i class="fas fa-file-excel"></i>
                        <span>Tabel Sumber Eks Idle</span>
                    </a>

                    <!-- 8. Log Sinkronisasi -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-sync-history">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span>Log Sinkronisasi</span>
                    </a>

                    <div class="menu-header mt-3">Pengaturan Sistem</div>
                    
                    <!-- 9. Menu Pengaturan Spreadsheet -->
                    <a href="javascript:void(0)" class="menu-item nav-tab-link" data-target="#tab-settings-spreadsheet">
                        <i class="fas fa-sliders"></i>
                        <span>Pengaturan Spreadsheet</span>
                    </a>

                    <!-- 10. Menu Wipe All Data -->
                    <a href="javascript:void(0)" class="menu-item text-danger border border-danger-subtle bg-danger bg-opacity-10 mt-2" onclick="confirmWipeAllData()" title="Hapus semua data di database lokal">
                        <i class="fas fa-trash-can text-danger"></i>
                        <span class="text-danger fw-bold">Wipe All Data</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content position-relative" id="mainContentArea">
            <div class="main-background"></div>
            <div class="container-fluid position-relative px-3 px-md-4 py-4" style="z-index: 1;">
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

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Modals Stack (Direct Child of Body to prevent Stacking Context bugs) -->
    @stack('modals')

    <!-- Scripts -->
    <script src="{{ asset('dashboard_files/jquery-3.7.1.min.js.download') }}"></script>
    <script src="{{ asset('dashboard_files/bootstrap.bundle.min.js.download') }}"></script>
    <script src="{{ asset('dashboard_files/sweetalert2.all.min.js.download') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <!-- DataTables 2.x JS with Bootstrap 5 & Buttons -->
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Tab Persistence
        function activateTab(targetHash) {
            if (!targetHash || !$(targetHash).length) {
                targetHash = '#tab-eks-idle';
            }

            $('.nav-tab-link').removeClass('active');
            $(`.nav-tab-link[data-target="${targetHash}"]`).addClass('active');

            $('.dashboard-tab-pane').removeClass('show active d-block').addClass('d-none');
            $(targetHash).removeClass('d-none').addClass('show active d-block');

            localStorage.setItem('active_dashboard_tab', targetHash);
            if (window.location.hash !== targetHash) {
                history.replaceState(null, null, targetHash);
            }

            // Auto-adjust DataTables, redraw charts, and recalculate responsive tables (Fixes Poin 5 & 6)
            setTimeout(function() {
                if ($.fn.dataTable) {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                }
                window.dispatchEvent(new Event('resize'));

                // Re-hydrate and resize charts across all tabs
                if (window.donutKlasterChart) {
                    window.donutKlasterChart.resize();
                }
                if (window.chartKomposisiKlarifikasiInstance) {
                    window.chartKomposisiKlarifikasiInstance.resize();
                }
                if (window.chartTindakLanjutEksInstance) {
                    window.chartTindakLanjutEksInstance.resize();
                }

                // Re-hydrate Tab 4 (Pemetaan per K/L)
                if (targetHash === '#tab-pemetaan-kemenkeu' && typeof window.filterKlasterKl === 'function') {
                    const selKl = $('#select-klaster-kl').val() || 'KEMENTERIAN KEUANGAN';
                    window.filterKlasterKl(selKl);
                }

                // Re-hydrate Tab 5 (Potensi BMN Idle per K/L)
                if (targetHash === '#tab-potensi-kemenkeu' && typeof window.filterPotensiTableKl === 'function') {
                    const selPot = $('#select-potensi-kl').val() || 'KEMENTERIAN KEUANGAN';
                    window.filterPotensiTableKl(selPot);
                }
            }, 100);

            // Trigger scroll sync resize on table switch
            if (targetHash === '#tab-source-potensi' && window.syncTopScrollPotensi) {
                setTimeout(window.syncTopScrollPotensi, 150);
            }
            if (targetHash === '#tab-source-eks' && window.syncTopScrollEks) {
                setTimeout(window.syncTopScrollEks, 150);
            }
            if (targetHash === '#tab-potensi-kemenkeu' && window.syncTopScrollPotensi19) {
                setTimeout(window.syncTopScrollPotensi19, 150);
            }
        }

        $('.nav-tab-link').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target');
            activateTab(target);

            // Close sidebar on mobile
            $('#sidebar').removeClass('show');
            $('#sidebarOverlay').removeClass('show');
        });

        // Restore Tab on Page Reload
        $(document).ready(function() {
            let savedTab = window.location.hash || localStorage.getItem('active_dashboard_tab') || '#tab-eks-idle';
            activateTab(savedTab);
        });

        // Mobile Sidebar Toggle
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('show');
            $('#sidebarOverlay').toggleClass('show');
        });

        $('#sidebarOverlay').on('click', function() {
            $('#sidebar').removeClass('show');
            $('#sidebarOverlay').removeClass('show');
        });

        // Trigger Sync Function with Rich Modern Micro-Animations
        function triggerSync() {
            const btn = $('#btn-sync-now');
            const spinner = $('#sync-spinner');
            
            btn.prop('disabled', true);
            spinner.addClass('fa-spin');

            Swal.fire({
                html: `
                    <div class="d-flex flex-column align-items-center py-3">
                        <div class="sync-loader-wrapper mb-3">
                            <div class="sync-orbit-spinner"></div>
                            <i class="fas fa-arrows-rotate sync-center-icon"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Sinkronisasi Data Realtime</h6>
                        <p class="text-muted small mb-0">Memutakhirkan dataset dari Google Spreadsheet KPKNL Palembang...</p>
                        <div class="mt-2 small text-primary fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="fas fa-server text-secondary"></i>
                            <span>Sinkronisasi 256 Aset Potensi &amp; 16 Aset Eks Idle</span>
                        </div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            $.post("{{ route('api.sync') }}")
                .done(function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="fw-bold text-success fs-5">Sinkronisasi Berhasil</span>',
                        html: `
                            <div class="py-1 text-center">
                                <p class="text-dark small mb-2">${res.message || 'Dataset berhasil disinkronkan.'}</p>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 small">
                                    <i class="fas fa-check me-1"></i> Data live &amp; terhidrasi penuh
                                </span>
                            </div>
                        `,
                        timer: 1300,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                })
                .fail(function(err) {
                    let msg = err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan saat sinkronisasi.';
                    if (msg === 'link tidak ada' || msg.includes('link tidak ada')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Link Tidak Ada',
                            text: 'Tautan Google Spreadsheet belum diatur atau kosong pada menu Pengaturan Spreadsheet. Mohon lengkapi tautan terlebih dahulu.',
                            confirmButtonText: 'Buka Pengaturan Spreadsheet',
                            confirmButtonColor: '#0c306b',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup'
                        }).then((res) => {
                            if (res.isConfirmed) {
                                activateTab('#tab-settings-spreadsheet');
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Sinkronisasi Gagal',
                            text: msg,
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .always(function() {
                    btn.prop('disabled', false);
                    spinner.removeClass('fa-spin');
                });
        }

        // Open Spreadsheet Link Helper with Validation
        function openSpreadsheetLink(url) {
            if (!url || url.trim() === '' || url === '#' || url === 'null') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Link Tidak Ada',
                    text: 'Tautan Google Spreadsheet belum diatur pada menu Pengaturan Spreadsheet.',
                    confirmButtonText: 'Buka Pengaturan',
                    confirmButtonColor: '#0c306b',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup'
                }).then((res) => {
                    if (res.isConfirmed) {
                        activateTab('#tab-settings-spreadsheet');
                    }
                });
                return false;
            }
            window.open(url, '_blank');
        }

        // Save Executive Note Function
        function saveExecutiveNote(sectionKey) {
            const $textarea = $(`#note-input-${sectionKey}`);
            const content = $textarea.val();

            if (!content || content.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Catatan Kosong',
                    text: 'Silakan isi catatan eksekutif terlebih dahulu sebelum menyimpan.',
                    confirmButtonColor: '#0c306b'
                });
                return;
            }

            Swal.fire({
                title: 'Menyimpan Catatan...',
                text: 'Mohon tunggu, catatan sedang disimpan ke database.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('api.notes.save') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    section_key: sectionKey,
                    note_content: content,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            }).done(function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: res.message || 'Catatan eksekutif pimpinan telah tersimpan dengan aman di database.',
                    confirmButtonColor: '#0c306b',
                    timer: 2500
                });
            }).fail(function(xhr) {
                console.error('Error saveNote:', xhr);
                let msg = 'Gagal menyimpan catatan eksekutif.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg,
                    confirmButtonColor: '#d33'
                });
            });
        }

        // Save Spreadsheet Settings Function
        function saveSpreadsheetConfig() {
            const potensiUrl = $('#input-spreadsheet-potensi').val();
            const eksUrl = $('#input-spreadsheet-eks').val();

            if (!potensiUrl || !eksUrl) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Pastikan kedua tautan Google Spreadsheet telah diisi.',
                    confirmButtonColor: '#0c306b'
                });
                return;
            }

            Swal.fire({
                title: 'Menyimpan Pengaturan...',
                text: 'Menyimpan tautan Google Spreadsheet ke database sistem.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('api.settings.spreadsheet') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    spreadsheet_potensi_url: potensiUrl,
                    spreadsheet_eks_url: eksUrl,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            }).done(function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pengaturan Tersimpan!',
                    text: res.message,
                    confirmButtonColor: '#0c306b',
                    showDenyButton: true,
                    denyButtonText: 'Sinkronisasi Sekarang',
                    denyButtonColor: '#d97706',
                    confirmButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isDenied) {
                        triggerSync();
                    } else {
                        location.reload();
                    }
                });
            }).fail(function(xhr) {
                let msg = 'Gagal menyimpan tautan spreadsheet.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg,
                    confirmButtonColor: '#d33'
                });
            });
        }

        function confirmWipeAllData() {
            Swal.fire({
                title: 'Kosongkan Seluruh Data Database?',
                html: '<div class="text-start p-2"><p class="text-danger fw-bold mb-2"><i class="fas fa-triangle-exclamation me-1"></i> PERINGATAN: Tindakan ini tidak dapat dibatalkan!</p><p class="small text-muted mb-0">Semua data BMN Potensi Idle dan BMN Eks Idle di database lokal akan <strong>DIHAPUS BERSIH</strong>.<br><br>Seluruh angka metrik, kartu KPI, diagram grafik, dan tabel pada aplikasi akan menjadi <strong>KOSONG (0)</strong> sampai Anda melakukan sinkronisasi ulang dari Google Spreadsheet.<br><br><span class="text-success"><i class="fas fa-shield-halved me-1"></i> Riwayat log sinkronisasi tetap disimpan untuk kebutuhan audit trail.</span></p></div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-can me-1"></i> Ya, Kosongkan Semua Data!',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengosongkan Database...',
                        text: 'Sedang menghapus seluruh data tabel lokal...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('api.wipeAllData') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json'
                    }).done(function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Database Dikosongkan!',
                            text: res.message,
                            confirmButtonColor: '#0c306b',
                            confirmButtonText: 'Muat Ulang Halaman'
                        }).then(() => {
                            location.reload();
                        });
                    }).fail(function(xhr) {
                        let msg = 'Gagal mengosongkan database.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg,
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    </script>
    @auth
    <script>
        // Single Sign-Out Real-time Sync: Check session when returning to this tab
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                fetch('{{ route("dashboard") }}', { method: 'HEAD', credentials: 'same-origin', cache: 'no-store' })
                    .then(function(res) {
                        if (res.redirected || res.status === 401) {
                            window.location.reload();
                        }
                    }).catch(function() {});
            }
        });
    </script>
    @endauth
    @stack('scripts')
</body>
</html>
