<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMPATIK KPKNL Palembang — Sistem Informasi Kepegawaian')</title>

    <!-- Typography: Google Sans & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Full CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- DataTables 2.x Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary-color: #0c306b;       /* DJKN Navy Master */
            --primary-light: #1e40af;       /* Royal Blue */
            --accent-color: #d97706;        /* DJKN Gold */
            --accent-light: #f59e0b;
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --card-shadow-hover: 0 10px 15px -3px rgba(12, 48, 107, 0.08), 0 4px 6px -4px rgba(12, 48, 107, 0.05);
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.925rem;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Executive Navbar */
        .navbar-executive {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-brand-text {
            line-height: 1.2;
        }

        .navbar-brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--primary-color);
        }

        .navbar-brand-sub {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Nav Pills Navigation */
        .nav-executive {
            gap: 4px;
        }

        .nav-executive .nav-link {
            color: #475569;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .nav-executive .nav-link i {
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .nav-executive .nav-link:hover {
            color: var(--primary-light);
            background-color: #f1f5f9;
        }

        .nav-executive .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            box-shadow: 0 4px 8px -2px rgba(12, 48, 107, 0.25);
        }

        .nav-executive .nav-link.active i {
            opacity: 1;
        }

        /* Hero Banner Area */
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, #173b75 60%, var(--primary-light) 100%);
            color: #ffffff;
            padding: 28px 0 65px;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 45%;
            background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.15), transparent 70%);
            pointer-events: none;
        }

        .hero-title {
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .hero-subtitle {
            color: #cbd5e1;
            font-size: 0.92rem;
            font-weight: 400;
            max-width: 680px;
        }

        .content-container {
            margin-top: -45px;
            z-index: 10;
            position: relative;
            padding-bottom: 50px;
        }

        /* Executive Cards */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
        }

        .card-custom:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .card-custom.hover-lift:hover {
            transform: translateY(-2px);
        }

        .card-header-clean {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 20px;
            font-weight: 700;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* KPI Metric Cards */
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-hover);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 4px;
            background: var(--card-accent, var(--primary-light));
        }

        .kpi-title {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .kpi-val {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-desc {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: var(--icon-bg, #eff6ff);
            color: var(--icon-color, var(--primary-light));
        }

        /* Tables & Clickable Rows */
        .table-custom {
            margin-bottom: 0;
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
            cursor: pointer;
        }

        /* Badges */
        .badge-subtle {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.76rem;
            letter-spacing: 0.02em;
        }

        .badge-navy { background: #e0e7ff; color: #3730a3; }
        .badge-gold { background: #fef3c7; color: #92400e; }
        .badge-emerald { background: #d1fae5; color: #065f46; }
        .badge-rose { background: #ffe4e6; color: #9f1239; }
        .badge-slate { background: #f1f5f9; color: #475569; }

        /* Floating Footer */
        .footer-executive {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 0;
            font-size: 0.82rem;
            color: #64748b;
        }

        /* Avatars */
        .avatar-initial {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            flex-shrink: 0;
        }

        /* Spin Animation */
        .spin {
            animation: spin 1s infinite linear;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-executive">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <div class="bg-primary text-white rounded-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0c306b 0%, #1e40af 100%) !important;">
                    <i class="fa-solid fa-users-rectangle"></i>
                </div>
                <div class="navbar-brand-text">
                    <div class="navbar-brand-title">SIMPATIK <span class="text-warning">KPKNL</span></div>
                    <div class="navbar-brand-sub">KPKNL Palembang — DJKN Kemenkeu</div>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Main Nav Links -->
                <ul class="navbar-nav mx-auto nav-executive my-2 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                            <i class="fa-solid fa-address-book"></i> Kepegawaian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('jabatan.*') ? 'active' : '' }}" href="{{ route('jabatan.index') }}">
                            <i class="fa-solid fa-id-badge"></i> Jabatan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('unit_kerja.*') ? 'active' : '' }}" href="{{ route('unit_kerja.index') }}">
                            <i class="fa-solid fa-sitemap"></i> Unit Kerja
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('diagram.*') ? 'active' : '' }}" href="{{ route('diagram.index') }}">
                            <i class="fa-solid fa-chart-pie"></i> Diagram
                        </a>
                    </li>
                </ul>

                <!-- Right Action Buttons & User Profile -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2 rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fa-solid fa-gear"></i> <span class="d-none d-md-inline">Pengaturan Sheets</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-2 rounded-pill px-3 shadow-sm" id="btnSyncSpreadsheet" style="background: linear-gradient(135deg, #0c306b 0%, #1e40af 100%); border: none;">
                        <i class="fa-solid fa-arrows-rotate" id="syncIcon"></i> <span id="syncText">Sinkronisasi</span>
                    </button>

                    @auth
                        <!-- User Account Dropdown -->
                        <div class="dropdown ms-1">
                            <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-pill ps-2 pe-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar-initial" style="width: 28px; height: 28px; font-size: 0.75rem; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--primary-light));">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <span class="fw-bold small text-dark d-none d-lg-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 16) }}</span>
                                <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 py-2" style="min-width: 220px;">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                    <div class="text-muted small">{{ auth()->user()->email }}</div>
                                    <span class="badge bg-primary-subtle text-primary mt-1 text-uppercase" style="font-size: 0.65rem;">
                                        SSO {{ auth()->user()->role }}
                                    </span>
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('sso.redirect') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1">
                            <i class="fa-solid fa-key me-1"></i> Masuk SSO
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Banner Area -->
    <header class="hero-banner">
        <div class="container-fluid px-lg-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark mb-2 px-3 py-1 rounded-pill fw-semibold">
                        <i class="fa-solid fa-bolt me-1"></i> Data Source: Google Spreadsheet Aktif
                    </span>
                    <h1 class="hero-title">@yield('hero-title', 'Dashboard Eksekutif Kepegawaian')</h1>
                    <p class="hero-subtitle mb-0">@yield('hero-subtitle', 'Sistem Informasi Manajemen Profil & Analitika Terpadu Kepegawaian (SIMPATIK) KPKNL Palembang')</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur rounded-3 px-3 py-2 border border-white border-opacity-20 text-start">
                        <i class="fa-solid fa-clock-rotate-left text-warning fs-4"></i>
                        <div>
                            <div class="small text-white-50" style="font-size: 0.72rem; text-transform: uppercase;">Sinkronisasi Terakhir</div>
                            <div class="fw-bold text-white" id="lastSyncDisplay">
                                {{ \App\Models\AppSetting::get('last_synced_at') ? \Carbon\Carbon::parse(\App\Models\AppSetting::get('last_synced_at'))->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum Pernah' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="content-container">
        <div class="container-fluid px-lg-4">
            <!-- Toast / Flash Notification -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-executive">
        <div class="container-fluid px-lg-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div>
                <strong>SIMPATIK KPKNL Palembang</strong> &copy; {{ date('Y') }} — Kantor Pelayanan Kekayaan Negara dan Lelang Palembang.
            </div>
            <div class="text-muted small">
                Direktorat Jenderal Kekayaan Negara — Kementerian Keuangan Republik Indonesia
            </div>
        </div>
    </footer>

    <!-- Universal Employee Detail Modal -->
    <div class="modal fade" id="pegawaiDetailModal" tabindex="-1" aria-labelledby="pegawaiDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;" id="pegawaiModalBody">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <div class="mt-2 text-muted">Memuat data profil pegawai...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 py-3" style="background: #f8fafc;">
                    <h5 class="modal-title fw-bold" id="settingsModalLabel">
                        <i class="fa-solid fa-sliders text-primary me-2"></i> Pengaturan Sumber Spreadsheet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('settings.sheet_url') }}" method="POST" id="formSettingsSheet">
                    @csrf
                    <div class="modal-body p-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Tautan Google Spreadsheet (Publik)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-link text-muted"></i></span>
                            <input type="url" name="sheet_url" id="inputSheetUrl" class="form-control" required
                                   value="{{ \App\Models\AppSetting::get('google_sheet_url', \App\Services\GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL) }}">
                        </div>
                        <div class="form-text small text-muted">
                            Pastikan spreadsheet telah diset dengan akses <em>"Anyone with the link can view"</em>. Sheet default yang diproses adalah <strong>Daftar Pegawai</strong>.
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" style="background: var(--primary-color);">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 & jQuery & DataTables CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

    <script>
        // Setup CSRF Token for all AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Universal Function to open employee detail modal
        function showPegawaiDetail(id) {
            const modalEl = new bootstrap.Modal(document.getElementById('pegawaiDetailModal'));
            $('#pegawaiModalBody').html(`
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted fw-semibold">Memuat profil pegawai...</div>
                </div>
            `);
            modalEl.show();

            $.get(`{{ url('/pegawai') }}/${id}/detail`, function(html) {
                $('#pegawaiModalBody').html(html);
            }).fail(function() {
                $('#pegawaiModalBody').html(`
                    <div class="modal-body text-center py-5 text-danger">
                        <i class="fa-solid fa-triangle-exclamation fs-1 mb-2"></i>
                        <h5>Gagal Memuat Profil</h5>
                        <p class="text-muted">Terjadi kesalahan saat mengambil data pegawai.</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `);
            });
        }

        // Live Google Sheets Sync trigger via button
        $('#btnSyncSpreadsheet').on('click', function() {
            const btn = $(this);
            const icon = $('#syncIcon');
            const text = $('#syncText');

            btn.prop('disabled', true);
            icon.addClass('spin');
            text.text('Menyinkronkan...');

            $.post("{{ route('sync') }}", {}, function(response) {
                if (response.success) {
                    text.text('Berhasil!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    alert(response.message || 'Sinkronisasi gagal.');
                    btn.prop('disabled', false);
                    icon.removeClass('spin');
                    text.text('Sinkronisasi');
                }
            }).fail(function(xhr) {
                alert('Terjadi kesalahan jaringan saat sinkronisasi.');
                btn.prop('disabled', false);
                icon.removeClass('spin');
                text.text('Sinkronisasi');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
