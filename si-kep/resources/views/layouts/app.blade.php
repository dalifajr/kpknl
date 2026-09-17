<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SI-KEP — Sistem Informasi Kepegawaian KPKNL Palembang')</title>

    <!-- Google Fonts: Outfit (From referensi_desain) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Full CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">

    <!-- DataTables 2.x Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

    <!-- Core Layout CSS (Replicated & Enhanced from referensi_desain) -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-app.css') }}">

    <!-- Chart.js 4.4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    @stack('styles')
</head>
<body class="bg-body-tertiary">

    <!-- Top Fixed Navbar (From referensi_desain/desain1.html) -->
    <nav class="navbar navbar-expand fixed-top shadow-sm px-3 bg-body border-bottom navbar-top-fixed">
        <div class="d-flex align-items-center gap-3 w-100">
            <!-- Mobile Toggle Button (Visible on LG and below) -->
            <button class="btn btn-light border-0 d-lg-none me-2" type="button" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-bars text-secondary fs-5"></i>
            </button>

            <!-- Brand Identity -->
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none m-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);">
                    <i class="fas fa-users-rectangle fs-6"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold fs-5 text-dark lh-1">
                        SI-KEP <span class="fw-light text-secondary fs-6">| Kepegawaian</span>
                    </span>
                    <span class="text-muted small d-none d-md-inline" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                        KPKNL PALEMBANG &bull; DJKN KEMENKEU
                    </span>
                </div>
            </a>

            <!-- Right Actions & Quick Buttons -->
            <div class="ms-auto d-flex align-items-center gap-2">
                @php
                    $lastSyncTimestamp = \App\Models\AppSetting::get('last_synced_at');
                    $lastSyncDisplay = $lastSyncTimestamp ? \Carbon\Carbon::parse($lastSyncTimestamp)->locale('id')->diffForHumans() : 'Belum pernah';
                    $lastSyncFullDate = $lastSyncTimestamp ? \Carbon\Carbon::parse($lastSyncTimestamp)->locale('id')->translatedFormat('d F Y, H:i') : 'Belum ada data sinkronisasi';
                @endphp

                <!-- Last Sync Status Info -->
                <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-light border text-secondary" style="font-size: 0.78rem;" title="Terakhir disinkronkan: {{ $lastSyncFullDate }}" id="headerLastSyncInfo">
                    <i class="fas fa-clock-rotate-left text-primary"></i>
                    <span class="d-none d-md-inline text-muted">Sinkron:</span>
                    <span class="fw-bold text-dark" id="headerLastSyncText">{{ $lastSyncDisplay }}</span>
                </div>

                <!-- Live Google Sheets Sync Button -->
                <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-2 rounded-pill px-3 shadow-sm" id="btnSyncSpreadsheet">
                    <i class="fas fa-arrows-rotate" id="syncIcon"></i> <span id="syncText">Sinkronisasi</span>
                </button>

                @auth
                    <!-- User Account Dropdown -->
                    <div class="dropdown ms-1">
                        <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-pill ps-2 pe-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar-initial" style="width: 28px; height: 28px; font-size: 0.72rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <span class="fw-bold small text-dark d-none d-lg-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 16) }}</span>
                            <i class="fas fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 py-2" style="min-width: 230px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                <div class="text-muted small">{{ auth()->user()->email }}</div>
                                <span class="badge bg-primary-subtle text-primary mt-1 text-uppercase" style="font-size: 0.65rem;">
                                    SSO {{ auth()->user()->role }}
                                </span>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0 p-0" id="logoutForm">
                                    @csrf
                                    <button type="button" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2" id="btnLogoutTrigger">
                                        <i class="fas fa-sign-out-alt"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('sso.redirect') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1">
                        <i class="fas fa-key me-1"></i> Masuk SSO
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Overlay (From referensi_desain) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Master App Container -->
    <div class="app-container">
        <!-- Sidebar Navigation (From referensi_desain/desain1.html) -->
        <div class="sidebar" id="sidebar">
            <!-- Sidebar User Profile Header -->
            <div class="sidebar-header d-flex align-items-center gap-3">
                <div class="avatar-initial" style="width: 42px; height: 42px; font-size: 1rem;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="d-flex flex-column overflow-hidden">
                    <span class="fw-bold text-body text-truncate">{{ auth()->user()->name ?? 'Kepala Kantor' }}</span>
                    <small class="text-secondary">{{ strtoupper(auth()->user()->role ?? 'Superadmin') }} &bull; KPKNL</small>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="py-3">
                <div class="menu-group">
                    <div class="menu-header">Menu Utama</div>
                    
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-gauge-high"></i> <span>Dashboard Eksekutif</span>
                    </a>

                    <a href="{{ route('pegawai.index') }}" class="menu-item {{ request()->routeIs('pegawai.*') ? 'active' : '' }}">
                        <i class="fas fa-address-book"></i> <span>Data Kepegawaian</span>
                    </a>

                    <a href="{{ route('jabatan.index') }}" class="menu-item {{ request()->routeIs('jabatan.*') ? 'active' : '' }}">
                        <i class="fas fa-id-badge"></i> <span>Struktur & Jabatan</span>
                    </a>

                    <a href="{{ route('unit_kerja.index') }}" class="menu-item {{ request()->routeIs('unit_kerja.*') ? 'active' : '' }}">
                        <i class="fas fa-sitemap"></i> <span>Formasi Unit Kerja</span>
                    </a>

                    <a href="{{ route('diagram.index') }}" class="menu-item {{ request()->routeIs('diagram.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie"></i> <span>Diagram & Analitika</span>
                    </a>

                    <div class="menu-header mt-3">Pangkalan Data</div>

                    @auth
                        @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'maintenance']))
                            <a href="{{ route('spreadsheet.raw') }}" class="menu-item {{ request()->routeIs('spreadsheet.raw') ? 'active' : '' }}">
                                <i class="fas fa-table-cells"></i> <span>Spreadsheet Mentah</span>
                            </a>
                        @endif
                    @endauth

                    <a href="javascript:void(0)" class="menu-item" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-sliders"></i> <span>Pengaturan</span>
                    </a>

                    <div class="menu-header mt-3">Sistem</div>

                    @auth
                        <a href="javascript:void(0)" class="menu-item text-danger" onclick="$('#btnLogoutTrigger').click()">
                            <i class="fas fa-sign-out-alt"></i> <span>Keluar Sistem</span>
                        </a>
                    @else
                        <a href="{{ route('sso.redirect') }}" class="menu-item text-primary">
                            <i class="fas fa-key"></i> <span>Masuk SSO</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Main Content Canvas (From referensi_desain) -->
        <div class="main-content position-relative">
            <!-- Main Background Gradient Banner -->
            <div class="main-background"></div>

            <div class="container-fluid position-relative px-3 px-md-4 py-4" style="z-index: 1;">
                <!-- Page Title & Header Bar -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 text-white">
                    <div>
                        <h2 class="mb-1 text-white fw-bold">@yield('hero-title', 'Dashboard Eksekutif Kepegawaian')</h2>
                        <p class="mb-0 text-white-50">@yield('hero-subtitle', 'Sistem Informasi Manajemen Profil & Analitika Terpadu Kepegawaian (SIMPATIK) KPKNL Palembang')</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @yield('header-actions')
                        <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur rounded-pill px-3 py-1 border border-white border-opacity-20 text-start">
                            <i class="fas fa-clock-rotate-left text-warning"></i>
                            <div class="small text-white" style="font-size: 0.75rem;">
                                <span class="opacity-75">Sync:</span> <strong id="lastSyncDisplay">{{ \App\Models\AppSetting::get('last_synced_at') ? \Carbon\Carbon::parse(\App\Models\AppSetting::get('last_synced_at'))->translatedFormat('d M H:i') . ' WIB' : 'Belum Pernah' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Toast / Flash Notification -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fas fa-circle-check fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fas fa-triangle-exclamation fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Dynamic Page Content -->
                @yield('content')

                <!-- Integrated Executive Footer -->
                <footer class="mt-5 pt-4 pb-2 border-top text-muted small d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div>
                        <strong>SI-KEP SIMPATIK</strong> &copy; {{ date('Y') }} &bull; KPKNL Palembang &bull; DJKN Kementerian Keuangan RI.
                    </div>
                    <div class="text-secondary">
                        Terintegrasi Google Spreadsheet &amp; SSO KPKNL Palembang.
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Universal Employee Detail Modal -->
    <div class="modal fade" id="pegawaiDetailModal" tabindex="-1" aria-labelledby="pegawaiDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;" id="pegawaiModalBody">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <div class="mt-2 text-muted fw-semibold">Memuat profil pegawai...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom px-4 py-3 bg-white">
                    <h5 class="modal-title fw-bold text-dark" id="settingsModalLabel">
                        <i class="fas fa-sliders text-primary me-2"></i> Pengaturan Sumber Spreadsheet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('settings.sheet_url') }}" method="POST" id="formSettingsSheet">
                    @csrf
                    <div class="modal-body p-4 bg-white">
                        <!-- Quick Instant Sync Button -->
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 border">
                            <div>
                                <div class="fw-bold text-dark small"><i class="fas fa-arrows-rotate text-primary me-1"></i> Sinkronisasi Data</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Perbarui data dari lembar kerja Google Sheets</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm" onclick="$('#settingsModal').modal('hide'); setTimeout(() => $('#btnSyncSpreadsheet').click(), 350);">
                                Sinkron Sekarang
                            </button>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <label class="form-label fw-bold small text-muted text-uppercase">Tautan Google Spreadsheet (Publik)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                            <input type="url" name="sheet_url" id="inputSheetUrl" class="form-control" required
                                   value="{{ \App\Models\AppSetting::get('google_sheet_url', \App\Services\GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL) }}">
                        </div>
                        <div class="form-text small text-muted">
                            Pastikan spreadsheet telah diset dengan akses <em>"Anyone with the link can view"</em>. Lembar kerja default yang diproses adalah <strong>Daftar Pegawai</strong>.
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

    <!-- App Scripts (From referensi_desain) -->
    <script src="{{ asset('assets/js/admin-app.js') }}"></script>

    <script>
        // Setup CSRF Token for AJAX
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
                        <i class="fas fa-triangle-exclamation fs-1 mb-2"></i>
                        <h5>Gagal Memuat Profil</h5>
                        <p class="text-muted">Terjadi kesalahan saat mengambil data pegawai.</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `);
            });
        }

        // Logout Confirmation via SweetAlert2
        $('#btnLogoutTrigger').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sesi aplikasi kepegawaian?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c62828',
                cancelButtonColor: '#6e7881',
                confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logoutForm').submit();
                }
            });
        });

        // Live Google Sheets Sync trigger via button with SweetAlert2
        $('#btnSyncSpreadsheet').on('click', function() {
            const btn = $(this);
            const icon = $('#syncIcon');
            const text = $('#syncText');

            Swal.fire({
                title: 'Sinkronisasi Spreadsheet?',
                text: 'Aplikasi akan membaca ulang data dari Google Spreadsheet dan memperbarui basis data lokal.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0c306b',
                cancelButtonColor: '#6e7881',
                confirmButtonText: '<i class="fas fa-arrows-rotate me-1"></i> Mulai Sinkronisasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true);
                    icon.addClass('spin');
                    text.text('Menyinkronkan...');

                    Swal.fire({
                        title: 'Sedang Menyinkronkan...',
                        text: 'Mengunduh data pegawai dari Google Spreadsheet...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.post("{{ route('sync') }}", {}, function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Sinkronisasi Berhasil!',
                                text: response.message || 'Data kepegawaian berhasil diperbarui.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal Sinkronisasi',
                                text: response.message || 'Terjadi kesalahan saat memproses data.',
                                icon: 'error'
                            });
                            btn.prop('disabled', false);
                            icon.removeClass('spin');
                            text.text('Sinkronisasi');
                        }
                    }).fail(function() {
                        Swal.fire({
                            title: 'Kesalahan Jaringan',
                            text: 'Tidak dapat terhubung ke server atau Google Spreadsheet.',
                            icon: 'error'
                        });
                        btn.prop('disabled', false);
                        icon.removeClass('spin');
                        text.text('Sinkronisasi');
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
