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

    <style>
        .dataTables_wrapper, table.dataTable {
            width: 100% !important;
        }
        .table-responsive {
            width: 100% !important;
            overflow-x: auto;
        }
        .cursor-pointer {
            cursor: pointer !important;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .transition-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
        }
        .card-clickable {
            cursor: pointer;
            user-select: none;
        }
    </style>

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

            <!-- Brand Identity with Logo KPKNL Palembang -->
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none m-0">
                <img src="{{ asset('images/logo-kpknl.png') }}" alt="Logo KPKNL Palembang" style="height: 38px; width: auto; object-fit: contain;">
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

                @if(auth()->check() && in_array(auth()->user()->role, ['superadmin', 'admin', 'maintenance', 'administrator']))
                    <!-- Live Google Sheets Sync Button -->
                    <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-2 rounded-pill px-3 shadow-sm" id="btnSyncSpreadsheet">
                        <i class="fas fa-arrows-rotate" id="syncIcon"></i> <span id="syncText">Sinkronisasi</span>
                    </button>
                @endif

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
                        <i class="fas fa-gauge-high"></i> <span>Dashboard</span>
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
                        @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'maintenance', 'administrator']))
                            <a href="{{ route('spreadsheet.raw') }}" class="menu-item {{ request()->routeIs('spreadsheet.raw') ? 'active' : '' }}">
                                <i class="fas fa-table-cells"></i> <span>Spreadsheet Mentah</span>
                            </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['superadmin', 'maintenance', 'administrator']))
                            <a href="{{ route('change_log.index') }}" class="menu-item {{ request()->routeIs('change_log.*') ? 'active' : '' }}">
                                <i class="fas fa-clock-rotate-left"></i> <span>Log Perubahan</span>
                            </a>
                        @endif

                        @if(auth()->user()->role === 'maintenance')
                            <a href="javascript:void(0)" class="menu-item" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                <i class="fas fa-sliders"></i> <span>Pengaturan</span>
                            </a>
                        @endif
                    @endauth

                    <div class="menu-header mt-3">Informasi Sistem</div>
                    <a href="{{ route('about') }}" class="menu-item {{ request()->routeIs('about') ? 'active' : '' }}">
                        <i class="fas fa-circle-info"></i> <span>Tentang Aplikasi</span>
                    </a>
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
                        <h4 class="fw-bold mb-1">@yield('hero-title', 'SI-KEP')</h4>
                        @if(trim($__env->yieldContent('hero-subtitle')))
                            <p class="mb-0 text-white-50 small">
                                @yield('hero-subtitle')
                            </p>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @yield('header-actions')
                    </div>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-circle-check fs-5 me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-triangle-exclamation fs-5 me-2"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Yield Page Content -->
                @yield('content')

                <!-- Footer (From referensi_desain) -->
                <footer class="mt-5 pt-4 pb-2 border-top text-muted small d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div>
                        &copy; {{ date('Y') }} <strong>KPKNL Palembang</strong> &bull; DJKN Kementerian Keuangan RI
                    </div>
                    <div>
                        SI-KEP v2.5 &bull; Terintegrasi Google Spreadsheet &amp; SSO
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Universal Employee Detail Modal -->
    <div class="modal fade" id="pegawaiDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;" id="pegawaiModalBody">
                <!-- Content loaded via AJAX from pegawai.detail -->
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted fw-semibold">Memuat profil pegawai...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aggregate List Modal (For clickable cards & charts drill-down) -->
    <div class="modal fade" id="aggregateListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;" id="aggregateListModalContent">
                <!-- Loaded via AJAX from pegawai.filter_modal -->
            </div>
        </div>
    </div>

    <!-- Pegawai Form Modal (Create & Edit Pegawai with Avatar Upload) -->
    <div class="modal fade" id="pegawaiFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
                    <h5 class="modal-title fw-bold text-dark" id="pegawaiFormModalTitle">
                        <i class="fas fa-user-pen text-primary me-2"></i> Form Pegawai
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="pegawaiForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="formPegawaiId" name="id" value="">
                    <div class="modal-body p-4 bg-white" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row g-3">
                            <!-- Avatar Upload Section -->
                            <div class="col-12 text-center pb-3 border-bottom">
                                <div class="position-relative d-inline-block mb-2">
                                    <img id="formAvatarPreview" src="" alt="Avatar Preview" class="rounded-circle border shadow-sm" style="width: 80px; height: 80px; object-fit: cover; display: none;">
                                    <div id="formAvatarPlaceholder" class="avatar-initial shadow-sm fs-3 text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0c306b, #2563eb);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div>
                                    <label for="formInputAvatar" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-camera me-1"></i> Pilih Foto Profil
                                    </label>
                                    <input type="file" id="formInputAvatar" name="avatar" class="d-none" accept="image/*" onchange="previewFormAvatar(this)">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Maksimal 2MB (JPG, PNG, WEBP)</small>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">NAMA PANGGILAN / UTAMA <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="formNama" class="form-control" required placeholder="Contoh: Muhammad Syukur">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">NAMA LENGKAP DENGAN GELAR</label>
                                <input type="text" name="nama_lengkap_gelar" id="formNamaGelar" class="form-control" placeholder="Contoh: Muhammad Syukur, S.E., M.M.">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">NIP PEGAWAI (18 DIGIT)</label>
                                <input type="text" name="nip" id="formNip" class="form-control font-monospace" placeholder="197011221996021001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">NIK KEPENDUDUKAN (16 DIGIT)</label>
                                <input type="text" name="nik" id="formNik" class="form-control font-monospace" placeholder="1671042211700001">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TIPE PEGAWAI</label>
                                <select name="tipe_pegawai" id="formTipePegawai" class="form-select">
                                    <option value="pns">PNS Definitif</option>
                                    <option value="ppnpn">PPNPN</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">JENIS KELAMIN</label>
                                <select name="jenis_kelamin" id="formGender" class="form-select">
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TEMPAT LAHIR</label>
                                <input type="text" name="tempat_lahir" id="formTempatLahir" class="form-control" placeholder="Kota Kelahiran">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">TANGGAL LAHIR</label>
                                <input type="date" name="tanggal_lahir" id="formTanggalLahir" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">UNIT KERJA / SEKSI</label>
                                <select name="unit_kerja_id" id="formUnitKerja" class="form-select">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                </select>
                            </div>

                            <!-- Position & Rank -->
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">JABATAN DEFINITIF <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jabatan_raw" id="formJabatan" class="form-control" required placeholder="Contoh: Pelelang Ahli Muda">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">JOB GRADE (KELAS JABATAN)</label>
                                <input type="number" name="job_grade" id="formJobGrade" class="form-control" placeholder="7 - 18">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">PANGKAT &amp; GOLONGAN RUANG</label>
                                <select name="pangkat_golongan_id" id="formPangkat" class="form-select">
                                    <option value="">-- Pilih Pangkat/Gol --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">TMT GOLONGAN TERAKHIR</label>
                                <input type="date" name="tmt_golongan" id="formTmtGolongan" class="form-control">
                            </div>

                            <!-- Dates & Early Alerts -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">TMT PENUGASAN PALEMBANG</label>
                                <input type="date" name="tmt_palembang" id="formTmtPalembang" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">TMT KGB TERAKHIR</label>
                                <input type="date" name="tmt_kgb" id="formTmtKgb" class="form-control">
                                <div class="form-text" style="font-size: 0.7rem;">Jatuh tempo KGB berikutnya otomatis dihitung +2 tahun.</div>
                            </div>

                            <!-- Education & Title Status -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">JENJANG PENDIDIKAN</label>
                                <input type="text" name="pendidikan_terakhir" id="formPendidikan" class="form-control" placeholder="S1 / S2 / D3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">PROGRAM STUDI / JURUSAN</label>
                                <input type="text" name="jurusan" id="formJurusan" class="form-control" placeholder="Manajemen / Hukum">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TAHUN LULUS</label>
                                <input type="number" name="tahun_lulus" id="formTahunLulus" class="form-control" placeholder="2015">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">PERGURUAN TINGGI / UNIVERSITAS</label>
                                <input type="text" name="nama_universitas" id="formUniversitas" class="form-control" placeholder="Universitas Sriwijaya">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">STATUS VERIFIKASI GELAR HRIS</label>
                                <select name="status_gelar" id="formStatusGelar" class="form-select">
                                    <option value="Sudah Clear (sesuai dengan HRIS)">Sudah Clear (sesuai dengan HRIS)</option>
                                    <option value="Data tidak sesuai di HRIS">Data tidak sesuai di HRIS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSubmitPegawaiForm">
                            <i class="fas fa-save me-1"></i> Simpan Data Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Settings Modal (Maintenance Only) -->
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

                        <!-- Permission Detection Widget -->
                        <div class="mb-3 p-3 rounded-3 border bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <div class="fw-bold text-dark small"><i class="fas fa-shield-halved text-primary me-1"></i> Deteksi Izin File Spreadsheet</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Memeriksa apakah izin file berupa Read Only atau Read &amp; Write</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm d-flex align-items-center gap-1" id="btnCheckPermission">
                                    <i class="fas fa-stethoscope" id="iconCheckPermission"></i>
                                    <span id="textCheckPermission">Uji Izin</span>
                                </button>
                            </div>
                            <div id="permissionCheckResult" class="d-none mt-2 pt-2 border-top">
                                <!-- Injected via JS -->
                            </div>
                        </div>

                        <label class="form-label fw-bold small text-muted text-uppercase">Tautan Google Spreadsheet (Publik)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                            <input type="url" name="sheet_url" id="inputSheetUrl" class="form-control" required
                                   value="{{ \App\Models\AppSetting::get('google_sheet_url', \App\Services\GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL) }}">
                        </div>
                        <div class="form-text small text-muted mb-3">
                            Pastikan spreadsheet telah diset dengan akses <em>"Anyone with the link can view"</em>. Lembar kerja default yang diproses adalah <strong>Daftar Pegawai</strong>.
                        </div>

                        <label class="form-label fw-bold small text-muted text-uppercase">Tautan Webhook Apps Script (Opsional)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light"><i class="fas fa-cloud-arrow-up text-muted"></i></span>
                            <input type="url" name="webhook_url" id="inputWebhookUrl" class="form-control" placeholder="https://script.google.com/macros/s/.../exec"
                                   value="{{ \App\Models\AppSetting::get('google_sheet_webhook_url', '') }}">
                        </div>
                        <div class="form-text small text-muted">
                            Untuk mekanisme Dual-Write langsung saat pegawai dibuat/diedit ke Google Spreadsheet.
                        </div>

                        @if(auth()->check() && auth()->user()->role === 'maintenance')
                            <hr class="my-3 text-danger opacity-25">
                            <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-danger small"><i class="fas fa-triangle-exclamation me-1"></i> Wipe Data Pegawai</div>
                                    <div class="text-danger-emphasis" style="font-size: 0.72rem;">Hapus seluruh 33 data pegawai di database lokal.</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold" id="btnWipeDataTrigger">
                                    Wipe Data
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Additional Modals from Child Views (outside container-fluid to prevent backdrop stacking trap) -->
    @stack('modals')

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
            // Close list modal if open
            const listModalEl = bootstrap.Modal.getInstance(document.getElementById('aggregateListModal'));
            if (listModalEl) {
                listModalEl.hide();
            }

            const modalEl = new bootstrap.Modal(document.getElementById('pegawaiDetailModal'));
            $('#pegawaiModalBody').html(`
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted fw-semibold">Memuat profil pegawai...</div>
                </div>
            `);
            modalEl.show();

            const detailUrl = "{{ route('pegawai.detail', ['id' => ':id']) }}".replace(':id', id);
            $.get(detailUrl, function(html) {
                $('#pegawaiModalBody').html(html);
            }).fail(function(xhr) {
                console.error("Gagal memuat profil pegawai:", xhr);
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

        // Universal Function for Clickable Cards & Charts Modal Drill-Down
        function showAggregateModal(type, value = '', title = '') {
            const modalEl = new bootstrap.Modal(document.getElementById('aggregateListModal'));
            $('#aggregateListModalContent').html(`
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted fw-semibold">Memuat daftar personil...</div>
                </div>
            `);
            modalEl.show();

            $.get("{{ route('pegawai.filter_modal') }}", { type: type, value: value, title: title }, function(html) {
                $('#aggregateListModalContent').html(html);
            }).fail(function(xhr) {
                $('#aggregateListModalContent').html(`
                    <div class="modal-body text-center py-5 text-danger">
                        <i class="fas fa-triangle-exclamation fs-1 mb-2"></i>
                        <h5>Gagal Memuat Data</h5>
                        <p class="text-muted">Terjadi kesalahan saat memfilter data pegawai.</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `);
            });
        }

        // Toggle NIK Visibility (Sensitive Data Privacy)
        function toggleNikVisibility(btn) {
            const span = document.getElementById('nikDisplay');
            const icon = document.getElementById('iconEyeNik');
            if (!span || !icon) return;

            const isMasked = span.innerText.includes('*');
            if (isMasked) {
                span.innerText = btn.getAttribute('data-unmasked');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                btn.setAttribute('title', 'Sembunyikan NIK Lengkap');
            } else {
                span.innerText = btn.getAttribute('data-masked');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                btn.setAttribute('title', 'Tampilkan NIK Lengkap');
            }
        }

        // Open Pegawai Form Modal (Create or Edit)
        function openPegawaiFormModal(id = null) {
            // Close detail modal if open
            const detailModalEl = bootstrap.Modal.getInstance(document.getElementById('pegawaiDetailModal'));
            if (detailModalEl) {
                detailModalEl.hide();
            }

            const formModal = new bootstrap.Modal(document.getElementById('pegawaiFormModal'));
            $('#pegawaiForm')[0].reset();
            $('#formPegawaiId').val(id || '');
            $('#formAvatarPreview').hide();
            $('#formAvatarPlaceholder').show();

            $('#pegawaiFormModalTitle').html(id ? '<i class="fas fa-user-pen text-primary me-2"></i> Edit Data Pegawai' : '<i class="fas fa-user-plus text-primary me-2"></i> Tambah Pegawai Baru');

            const url = id ? "{{ route('pegawai.form_data', ['id' => ':id']) }}".replace(':id', id) : "{{ route('pegawai.form_data') }}";

            $.get(url, function(res) {
                // Populate Unit Kerjas
                let unitOptions = '<option value="">-- Pilih Unit Kerja --</option>';
                (res.unit_kerjas || []).forEach(u => {
                    unitOptions += `<option value="${u.id}">${u.nama_unit}</option>`;
                });
                $('#formUnitKerja').html(unitOptions);

                // Populate Pangkats
                let pangkatOptions = '<option value="">-- Pilih Pangkat/Gol --</option>';
                (res.pangkats || []).forEach(p => {
                    pangkatOptions += `<option value="${p.id}">${p.golongan_ruang} - ${p.nama_pangkat}</option>`;
                });
                $('#formPangkat').html(pangkatOptions);

                // If Edit, populate form values
                if (res.pegawai) {
                    const p = res.pegawai;
                    $('#formNama').val(p.nama || '');
                    $('#formNamaGelar').val(p.nama_lengkap_gelar || '');
                    $('#formNip').val(p.nip || '');
                    $('#formNik').val(p.nik || '');
                    $('#formTipePegawai').val(p.tipe_pegawai || 'pns');
                    $('#formGender').val(p.jenis_kelamin || 'L');
                    $('#formTempatLahir').val(p.tempat_lahir || '');
                    $('#formTanggalLahir').val(p.tanggal_lahir ? p.tanggal_lahir.substring(0, 10) : '');
                    $('#formUnitKerja').val(p.unit_kerja_id || '');
                    $('#formJabatan').val(p.nama_jabatan_raw || '');
                    $('#formJobGrade').val(p.job_grade || '');
                    $('#formPangkat').val(p.pangkat_golongan_id || '');
                    $('#formTmtGolongan').val(p.tmt_golongan ? p.tmt_golongan.substring(0, 10) : '');
                    $('#formTmtPalembang').val(p.tmt_palembang ? p.tmt_palembang.substring(0, 10) : '');
                    $('#formTmtKgb').val(p.tmt_kgb ? p.tmt_kgb.substring(0, 10) : '');
                    $('#formPendidikan').val(p.pendidikan_terakhir || '');
                    $('#formJurusan').val(p.jurusan || '');
                    $('#formTahunLulus').val(p.tahun_lulus || '');
                    $('#formUniversitas').val(p.nama_universitas || '');
                    $('#formStatusGelar').val(p.status_gelar || 'Sudah Clear (sesuai dengan HRIS)');

                    if (p.avatar_url) {
                        $('#formAvatarPreview').attr('src', '/storage/' + p.avatar_url).show();
                        $('#formAvatarPlaceholder').hide();
                    }
                }

                formModal.show();
            }).fail(function() {
                Swal.fire('Error', 'Gagal memuat form pegawai.', 'error');
            });
        }

        // Preview Avatar
        function previewFormAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#formAvatarPreview').attr('src', e.target.result).show();
                    $('#formAvatarPlaceholder').hide();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Handle Pegawai Form Submit (Create & Update)
        $('#pegawaiForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#formPegawaiId').val();
            const url = id ? "{{ route('pegawai.update', ['id' => ':id']) }}".replace(':id', id) : "{{ route('pegawai.store') }}";
            const formData = new FormData(this);
            const submitBtn = $('#btnSubmitPegawaiForm');

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Data Pegawai');
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('pegawaiFormModal')).hide();
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Data Pegawai');
                    let title = xhr.status === 422 ? 'Validasi Gagal' : 'Gagal Menyimpan';
                    let errMsg = 'Terjadi kesalahan saat menyimpan data pegawai.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                    }
                    Swal.fire({
                        title: title,
                        html: errMsg,
                        icon: 'error'
                    });
                }
            });
        });

        // Wipe Data Trigger with Double Confirmation SweetAlert
        $('#btnWipeDataTrigger').on('click', function() {
            // Hide settings modal first to release Bootstrap 5 focus trap so SweetAlert input can be clicked & typed into
            const settingsModalEl = document.getElementById('settingsModal');
            const settingsModalInstance = bootstrap.Modal.getInstance(settingsModalEl);
            if (settingsModalInstance) {
                settingsModalInstance.hide();
            }

            Swal.fire({
                title: 'PERINGATAN: WIPE DATA!',
                text: 'Tindakan ini akan MENGHAPUS SEMUA DATA PEGAWAI di database lokal! Ketik "WIPE" untuk melanjutkan:',
                input: 'text',
                inputPlaceholder: 'Ketik WIPE',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6e7881',
                confirmButtonText: '<i class="fas fa-trash-can me-1"></i> Ya, Hapus Semua Data!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                didOpen: () => {
                    setTimeout(() => {
                        const swalInput = Swal.getInput();
                        if (swalInput) swalInput.focus();
                    }, 200);
                },
                preConfirm: (val) => {
                    if (val !== 'WIPE') {
                        Swal.showValidationMessage('Teks konfirmasi salah. Harap ketik WIPE secara tepat.');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang membersihkan basis data...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.post("{{ route('settings.wipe_data') }}", function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    }).fail(function(xhr) {
                        const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses Wipe Data.';
                        Swal.fire('Error!', errMsg, 'error');
                    });
                } else {
                    // Reopen settings modal if user cancelled or dismissed
                    if (settingsModalEl) {
                        const modal = bootstrap.Modal.getOrCreateInstance(settingsModalEl);
                        modal.show();
                    }
                }
            });
        });

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
                text: 'Aplikasi akan membaca ulang data dari Google Spreadsheet dan memperbarui basis data lokal dengan metode Smart Upsert.',
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
                        text: 'Membaca data pegawai dari Google Spreadsheet...',
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
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal Sinkronisasi',
                                html: `<div class="text-danger fw-semibold mb-2">${response.message || 'Terjadi kesalahan saat memproses data.'}</div>
                                       <div class="text-muted small">Silakan periksa izin akses di Pengaturan Spreadsheet (pastikan Anyone with the link can view).</div>`,
                                icon: 'error'
                            });
                            btn.prop('disabled', false);
                            icon.removeClass('spin');
                            text.text('Sinkronisasi');
                        }
                    }).fail(function(xhr) {
                        Swal.fire({
                            title: 'Kendala Sinkronisasi',
                            html: `<div class="text-danger mb-2">${xhr.responseJSON?.message || 'Tidak dapat terhubung ke server atau Google Spreadsheet.'}</div>`,
                            icon: 'error'
                        });
                        btn.prop('disabled', false);
                        icon.removeClass('spin');
                        text.text('Sinkronisasi');
                    });
                }
            });
        });

        // Check Spreadsheet Permission Button Handler
        $('#btnCheckPermission').on('click', function() {
            const btn = $(this);
            const icon = $('#iconCheckPermission');
            const text = $('#textCheckPermission');
            const resultBox = $('#permissionCheckResult');
            const sheetUrl = $('#inputSheetUrl').val();
            const webhookUrl = $('#inputWebhookUrl').val();

            btn.prop('disabled', true);
            icon.removeClass('fa-stethoscope').addClass('fa-spinner fa-spin');
            text.text('Memeriksa...');
            resultBox.removeClass('d-none').html('<div class="text-muted small py-2"><i class="fas fa-circle-notch fa-spin me-1 text-primary"></i> Sedang mendeteksi izin berkas Google Spreadsheet...</div>');

            $.post("{{ route('settings.check_permission') }}", {
                sheet_url: sheetUrl,
                webhook_url: webhookUrl
            }, function(res) {
                let badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                let iconClass = 'fa-circle-xmark';

                if (res.permission === 'read_and_write') {
                    badgeClass = 'bg-success-subtle text-success border-success-subtle';
                    iconClass = 'fa-circle-check';
                } else if (res.permission === 'read_only') {
                    badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                    iconClass = 'fa-triangle-exclamation';
                }

                let adviceHtml = '';
                if (res.advice) {
                    adviceHtml = `<div class="p-2 mt-2 rounded-2 bg-light border text-secondary small" style="font-size: 0.72rem;">
                        <strong><i class="fas fa-circle-info text-info me-1"></i> Saran Tindakan:</strong> ${res.advice}
                    </div>`;
                }

                resultBox.html(`
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge ${badgeClass} border rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="fas ${iconClass} me-1"></i> ${res.permission_label}
                        </span>
                        <small class="text-muted font-monospace" style="font-size: 0.68rem;">Baca: ${res.can_read ? 'YA' : 'TIDAK'} &bull; Tulis: ${res.can_write ? 'YA' : 'TIDAK'}</small>
                    </div>
                    <div class="small text-dark mt-1" style="font-size: 0.75rem;">${res.message}</div>
                    ${adviceHtml}
                `);
            }).fail(function(xhr) {
                resultBox.html(`
                    <div class="alert alert-danger py-2 px-3 small mb-0 mt-1" style="font-size: 0.75rem;">
                        <i class="fas fa-triangle-exclamation me-1"></i> Gagal memeriksa perizinan spreadsheet: ${xhr.responseJSON?.message || 'Koneksi terputus'}.
                    </div>
                `);
            }).always(function() {
                btn.prop('disabled', false);
                icon.removeClass('fa-spinner fa-spin').addClass('fa-stethoscope');
                text.text('Uji Izin');
            });
        });

        // Auto-Adjust DataTables columns when switching tabs
        $('button[data-bs-toggle="tab"], button[data-bs-toggle="pill"], a[data-bs-toggle="tab"], a[data-bs-toggle="pill"]').on('shown.bs.tab', function () {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
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
