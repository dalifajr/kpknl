@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Pembaruan | SSO KPKNL Palembang')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                    <i class="fa-solid fa-sliders me-1"></i> Control Hub
                </span>
                <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill">
                    <i class="fa-solid fa-shield-check me-1"></i> Superadmin Access
                </span>
            </div>
            <h3 class="mb-1 fw-bold text-dark" style="font-family: var(--font-heading);">Pengaturan Sistem</h3>
            <p class="text-muted mb-0">Pusat konfigurasi terpadu: pembaruan otomatis, modus pemeliharaan, cadangan database, dan informasi login portal SSO.</p>
        </div>

        <div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fs-7">
                <i class="fa-solid fa-code-branch text-primary me-1"></i> Versi Sistem: <strong class="text-dark" id="headerSystemVersion">{{ $systemVersion }}</strong>
            </span>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Expressive Material You Tabs Navigation -->
    <div class="card-m3 p-2 mb-4 bg-white border-0 shadow-sm">
        <ul class="nav nav-pills nav-fill gap-2" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2.5 px-3 fw-semibold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'update' ? 'active' : '' }}" 
                        id="tab-update" data-bs-toggle="tab" data-bs-target="#content-update" type="button" role="tab" aria-controls="content-update" aria-selected="{{ $activeTab === 'update' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Pembaruan Sistem</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2.5 px-3 fw-semibold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'maintenance' ? 'active' : '' }}" 
                        id="tab-maintenance" data-bs-toggle="tab" data-bs-target="#content-maintenance" type="button" role="tab" aria-controls="content-maintenance" aria-selected="{{ $activeTab === 'maintenance' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>Modus Pemeliharaan</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2.5 px-3 fw-semibold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'backup' ? 'active' : '' }}" 
                        id="tab-backup" data-bs-toggle="tab" data-bs-target="#content-backup" type="button" role="tab" aria-controls="content-backup" aria-selected="{{ $activeTab === 'backup' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-database"></i>
                    <span>Backup & Restore</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2.5 px-3 fw-semibold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'login-info' ? 'active' : '' }}" 
                        id="tab-login-info" data-bs-toggle="tab" data-bs-target="#content-login-info" type="button" role="tab" aria-controls="content-login-info" aria-selected="{{ $activeTab === 'login-info' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-id-badge"></i>
                    <span>Info Card Login</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="settingsTabContent">
        
        <!-- ========================================== -->
        <!-- TAB 1: PEMBARUAN SISTEM (1-CLICK UPDATE)    -->
        <!-- ========================================== -->
        <div class="tab-pane fade {{ $activeTab === 'update' ? 'show active' : '' }}" id="content-update" role="tabpanel" aria-labelledby="tab-update">
            
            <!-- Hero Update Card -->
            <div class="card-m3 p-4 p-md-5 mb-4 border-0 shadow-sm text-center position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, rgba(59, 95, 229, 0.08) 0%, rgba(219, 226, 249, 0.4) 100%);">
                <div class="position-relative z-1" style="max-width: 680px; margin: 0 auto;">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm mb-3" style="width: 76px; height: 76px;">
                        <i class="fa-solid fa-cloud-arrow-down fs-2"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-2" style="font-family: var(--font-heading);">Pusat Pembaruan Otomatis SSO</h4>
                    <p class="text-secondary mb-4">
                        Pemeriksaan dan penerapan pembaruan sistem kini dapat dilakukan dengan <strong>satu kali klik</strong>. Sistem akan mendeteksi rilis terbaru, menampilkan catatan perubahan, dan memperbarui seluruh komponen secara aman.
                    </p>

                    <!-- Status Metadata Badge Row -->
                    <div class="row g-2 justify-content-center mb-4">
                        <div class="col-auto">
                            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
                                <i class="fa-solid fa-tag text-primary me-1"></i> Versi Aktif: <strong id="cardSystemVersion">{{ $systemVersion }}</strong>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
                                <i class="fa-solid fa-clock-rotate-left text-info me-1"></i> Terakhir Diperiksa: <span id="cardLastChecked">{{ $lastCheckedAt }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> Terakhir Diupdate: <span id="cardLastUpdated">{{ $lastUpdatedAt }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 1-Click Check Update Button & Host CLI Notice -->
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2" id="btnCheckUpdate" onclick="triggerCheckUpdate()">
                            <i class="fa-solid fa-magnifying-glass fs-5" id="iconCheckUpdate"></i>
                            <span id="textCheckUpdate">Cek Pembaruan Sekarang</span>
                        </button>
                        <div class="badge bg-light text-secondary border px-3 py-2.5 rounded-pill d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-terminal text-primary"></i>
                            <span>Host CLI: Jalankan <code>update.bat</code> di root server untuk sinkronisasi Git pull, migrasi, dan clear cache otomatis.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separated Modules / Dedicated Links Section -->
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                            <i class="fa-solid fa-toolbox text-primary me-2"></i>Menu Konsol Lanjutan (DevOps & Armada Klien)
                        </h6>
                        <small class="text-muted">Akses alat teknis tingkat lanjut pada halaman tersendiri agar pusat pengaturan tetap rapi.</small>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Link 1: Konsol Orkestrasi Klien -->
                    <div class="col-12 col-md-4">
                        <div class="card-m3 p-4 h-100 bg-white border-0 shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fa-solid fa-cubes-stacked fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Armada Aplikasi Klien</h6>
                                        <small class="text-muted">{{ $totalApps }} Klien Terhubung</small>
                                    </div>
                                </div>
                                <p class="text-secondary fs-7 mb-3">
                                    Pantau kesehatan (*health ping*), status online/503, dan orkestrasi per folder aplikasi klien.
                                </p>
                            </div>
                            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-tonal w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <span>Buka Konsol Armada</span>
                                <i class="fa-solid fa-arrow-up-right-from-square fs-8"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Link 2: Riwayat Deployment -->
                    <div class="col-12 col-md-4">
                        <div class="card-m3 p-4 h-100 bg-white border-0 shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle bg-warning-subtle text-warning p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fa-solid fa-timeline fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Riwayat Deployment</h6>
                                        <small class="text-muted">Audit Trail & Pipeline</small>
                                    </div>
                                </div>
                                <p class="text-secondary fs-7 mb-3">
                                    Lihat riwayat rilis, log pipeline eksekusi commit, dan opsi rollback versi aman.
                                </p>
                            </div>
                            <a href="{{ route('admin.maintenance.deployments') }}" class="btn btn-tonal w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <span>Lihat Log Deployment</span>
                                <i class="fa-solid fa-arrow-up-right-from-square fs-8"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Link 3: Snapshot Vault Klien -->
                    <div class="col-12 col-md-4">
                        <div class="card-m3 p-4 h-100 bg-white border-0 shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle bg-info-subtle text-info p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fa-solid fa-vault fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Snapshot Vault Klien</h6>
                                        <small class="text-muted">Database Cadangan Klien</small>
                                    </div>
                                </div>
                                <p class="text-secondary fs-7 mb-3">
                                    Kelola arsip dump SQL pra-deployment untuk masing-masing database aplikasi klien terhubung.
                                </p>
                            </div>
                            <a href="{{ route('admin.maintenance.backups') }}" class="btn btn-tonal w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <span>Buka Snapshot Vault</span>
                                <i class="fa-solid fa-arrow-up-right-from-square fs-8"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MODUS PEMELIHARAAN (MAINTENANCE)    -->
        <!-- ========================================== -->
        <div class="tab-pane fade {{ $activeTab === 'maintenance' ? 'show active' : '' }}" id="content-maintenance" role="tabpanel" aria-labelledby="tab-maintenance">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="card-m3 p-4 p-md-5 bg-white border-0 shadow-sm">
                        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                            <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="fa-solid fa-screwdriver-wrench fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">Modus Pemeliharaan Portal SSO</h5>
                                <small class="text-muted">Batasi akses pengguna reguler saat pemeliharaan sistem atau migrasi database berlangsung.</small>
                            </div>
                        </div>

                        <form action="{{ route('admin.settings.maintenance.update') }}" method="POST">
                            @csrf
                            
                            <!-- Status Toggle Option -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Status Modus Pemeliharaan</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card-m3 p-3 border {{ $maintenanceStatus === 'inactive' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-light' }} cursor-pointer">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="maintenance_mode" id="modeInactive" value="inactive" {{ $maintenanceStatus === 'inactive' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-2" for="modeInactive">
                                                    <span class="d-block"><i class="fa-solid fa-circle-dot text-success me-1"></i> Normal / Live (Online)</span>
                                                    <small class="text-muted fw-normal">Portal SSO dapat diakses oleh seluruh pegawai dan pengguna secara normal.</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-m3 p-3 border {{ $maintenanceStatus === 'active' ? 'border-warning bg-warning-subtle bg-opacity-10' : 'bg-light' }} cursor-pointer">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="maintenance_mode" id="modeActive" value="active" {{ $maintenanceStatus === 'active' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-2" for="modeActive">
                                                    <span class="d-block"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Pemeliharaan Aktif (503)</span>
                                                    <small class="text-muted fw-normal">Hanya Superadmin yang dapat masuk. Pengguna umum melihat halaman info pemeliharaan.</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance Message -->
                            <div class="mb-4">
                                <label for="maintenance_message" class="form-label fw-bold text-dark">Pesan Tampilan Pemeliharaan</label>
                                <textarea name="maintenance_message" id="maintenance_message" rows="3" class="form-control" required placeholder="Tuliskan pesan yang ramah untuk pengguna...">{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
                                <small class="text-muted">Pesan ini akan ditampilkan kepada pengguna saat mereka mengakses portal SSO dalam mode pemeliharaan.</small>
                            </div>

                            <!-- Superadmin Password Confirmation -->
                            <div class="mb-4 bg-light p-3 rounded-4">
                                <label for="sudo_password" class="form-label fw-bold text-dark">
                                    <i class="fa-solid fa-lock text-primary me-1"></i> Verifikasi Password Superadmin
                                </label>
                                <input type="password" name="sudo_password" id="sudo_password" class="form-control" required placeholder="Masukkan password akun Anda untuk konfirmasi keamanan">
                                <small class="text-muted">Dibutuhkan verifikasi keamanan sebelum mengaktifkan atau menonaktifkan mode pemeliharaan.</small>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Status Pemeliharaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: BACKUP & RESTORE DATABASE SSO       -->
        <!-- ========================================== -->
        <div class="tab-pane fade {{ $activeTab === 'backup' ? 'show active' : '' }}" id="content-backup" role="tabpanel" aria-labelledby="tab-backup">
            <div class="row g-4">
                <!-- Backup History & Creation -->
                <div class="col-12 col-xl-8">
                    <div class="card-m3 p-4 bg-white border-0 shadow-sm h-100">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
                                    <i class="fa-solid fa-database text-primary me-2"></i>Daftar Backup Basis Data SSO
                                </h5>
                                <small class="text-muted">Arsip cadangan SQL lengkap database SSO KPKNL Palembang.</small>
                            </div>
                            <form action="{{ route('admin.backup.create') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-pill px-3 py-2 btn-sm fw-semibold shadow-xs">
                                    <i class="fa-solid fa-plus me-1"></i> Buat Backup Baru
                                </button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle fs-7 mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama File Backup</th>
                                        <th>Ukuran</th>
                                        <th>Dibuat Pada</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backups as $b)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-file-code text-primary fs-5"></i>
                                                    <div>
                                                        <span class="fw-bold text-dark font-monospace">{{ $b['filename'] }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark fw-bold border">{{ $b['formatted_size'] ?? (round(($b['size'] ?? 0) / 1024, 2) . ' KB') }}</span>
                                            </td>
                                            <td class="text-muted">
                                                {{ isset($b['created_at']) && $b['created_at'] instanceof \Carbon\Carbon ? $b['created_at']->isoFormat('D MMM Y, HH:mm') . ' WIB' : ($b['date'] ?? '-') }}
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn btn-tonal btn-sm rounded-pill px-2.5 me-1" title="Unduh File SQL">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                    <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip backup ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2.5" title="Hapus File">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-box-open fs-3 d-block mb-2 text-secondary opacity-50"></i>
                                                Belum ada arsip backup database yang tersimpan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Restore Database Box -->
                <div class="col-12 col-xl-4">
                    <div class="card-m3 p-4 bg-white border-0 shadow-sm h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-rotate-left fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Restore Basis Data</h6>
                                <small class="text-muted">Pulihkan dari file SQL</small>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2.5 px-3 rounded-3 fs-7 mb-3 border-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>Perhatian:</strong> Memulihkan database akan menimpa seluruh data SSO yang ada saat ini dengan isi file backup.
                        </div>

                        <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN TINGKAT TINGGI:\n\nApakah Anda yakin ingin melakukan RESTORE database sekarang?\nData yang ada akan ditimpa dengan file yang Anda unggah.');">
                            @csrf
                            <div class="mb-3">
                                <label for="backup_file" class="form-label fw-semibold text-dark fs-7">Pilih File Backup (.sql)</label>
                                <input type="file" name="backup_file" id="backup_file" class="form-control fs-7" accept=".sql,.txt" required>
                                <small class="text-muted fs-8 d-block mt-1">Maksimal ukuran file: 50 MB.</small>
                            </div>

                            <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 fw-bold fs-7">
                                <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Mulai Pulihkan Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: INFO CARD LOGIN                    -->
        <!-- ========================================== -->
        <div class="tab-pane fade {{ $activeTab === 'login-info' ? 'show active' : '' }}" id="content-login-info" role="tabpanel" aria-labelledby="tab-login-info">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="card-m3 p-4 p-md-5 bg-white border-0 shadow-sm">
                        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                            <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="fa-solid fa-id-badge fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">Informasi Card Halaman Login</h5>
                                <small class="text-muted">Kustomisasi banner informasi dan panduan keamanan yang tampil di halaman login depan.</small>
                            </div>
                        </div>

                        <form action="{{ route('admin.settings.login-info.update') }}" method="POST">
                            @csrf

                            <!-- Status Toggle -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Status Visibilitas</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="login_info_status" id="statusActive" value="active" {{ $loginStatus === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-success" for="statusActive">
                                            <i class="fa-solid fa-eye me-1"></i> Tampilkan Card
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="login_info_status" id="statusInactive" value="inactive" {{ $loginStatus === 'inactive' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-secondary" for="statusInactive">
                                            <i class="fa-solid fa-eye-slash me-1"></i> Sembunyikan Card
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Label -->
                            <div class="mb-3">
                                <label for="login_info_badge" class="form-label fw-bold text-dark">Label Badge / Kategori</label>
                                <input type="text" name="login_info_badge" id="login_info_badge" class="form-control" required value="{{ old('login_info_badge', $loginBadge) }}" placeholder="Contoh: INFO LAYANAN & KEAMANAN">
                            </div>

                            <!-- Title -->
                            <div class="mb-3">
                                <label for="login_info_title" class="form-label fw-bold text-dark">Judul Informasi</label>
                                <input type="text" name="login_info_title" id="login_info_title" class="form-control" required value="{{ old('login_info_title', $loginTitle) }}" placeholder="Contoh: Selamat Datang di Portal Single Sign-On">
                            </div>

                            <!-- Content -->
                            <div class="mb-4">
                                <label for="login_info_content" class="form-label fw-bold text-dark">Konten / Uraian Pesan</label>
                                <textarea name="login_info_content" id="login_info_content" rows="4" class="form-control" required placeholder="Tuliskan petunjuk atau informasi login...">{{ old('login_info_content', $loginContent) }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengaturan Card Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1-Click Check Update Handler
    function triggerCheckUpdate() {
        const btn = document.getElementById('btnCheckUpdate');
        const icon = document.getElementById('iconCheckUpdate');
        const text = document.getElementById('textCheckUpdate');

        // Button loading state
        btn.disabled = true;
        icon.className = 'fa-solid fa-arrows-rotate fa-spin fs-5';
        text.innerText = 'Memeriksa...';

        // Animasi Popup Loading M3 Expressive
        Swal.fire({
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
            },
            title: 'Memeriksa Pembaruan Sistem',
            html: `
                <div class="py-3 text-center">
                    <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-secondary fw-medium mb-1">Menghubungi repositori server...</p>
                    <p class="text-muted small mb-0">Sedang memverifikasi versi dan patch terbaru untuk SSO.</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ route('admin.settings.check-update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            icon.className = 'fa-solid fa-magnifying-glass fs-5';
            text.innerText = 'Cek Pembaruan Sekarang';

            if (data.last_checked) {
                document.getElementById('cardLastChecked').innerText = data.last_checked;
            }

            if (data.has_update) {
                // Tampilkan Popup Interaktif Pembaruan Tersedia!
                let changelogHtml = '<ul class="text-start mb-0 ps-3 mt-2 small text-secondary">';
                data.changelog.forEach(item => {
                    changelogHtml += `<li class="mb-1">${item}</li>`;
                });
                changelogHtml += '</ul>';

                Swal.fire({
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                    },
                    icon: 'info',
                    iconColor: '#3B5FE5',
                    title: 'Pembaruan Tersedia! 🎉',
                    html: `
                        <div class="text-center mb-3">
                            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold">
                                Versi Terbaru: ${data.new_version}
                            </span>
                            <div class="text-muted small mt-1">Rilis: ${data.release_date}</div>
                        </div>
                        <div class="bg-light p-3 rounded-4 text-start mb-3 border">
                            <div class="fw-bold text-dark small mb-1"><i class="fa-solid fa-list-check text-primary me-1"></i> Catatan Pembaruan (Changelog):</div>
                            ${changelogHtml}
                        </div>
                        <p class="text-muted small mb-0">Klik tombol di bawah untuk memperbarui secara otomatis tanpa gangguan.</p>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#3B5FE5',
                    cancelButtonColor: '#757780',
                    confirmButtonText: '<i class="fa-solid fa-bolt me-1"></i> Perbarui Sekarang (1-Click)',
                    cancelButtonText: 'Nanti Saja',
                    buttonsStyling: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        execute1ClickUpdate(data.new_version);
                    }
                });
            } else {
                // Sudah versi terbaru
                Swal.fire({
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                    },
                    icon: 'success',
                    iconColor: '#198754',
                    title: 'Sistem Sudah Up-to-Date! ✨',
                    html: `
                        <p class="text-secondary mb-2">Portal SSO telah menggunakan rilis terbaru (<strong>${data.current_version}</strong>).</p>
                        <small class="text-muted">Tidak ada pembaruan tertunda saat ini.</small>
                    `,
                    confirmButtonColor: '#3B5FE5',
                    confirmButtonText: 'Selesai'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            icon.className = 'fa-solid fa-magnifying-glass fs-5';
            text.innerText = 'Cek Pembaruan Sekarang';

            Swal.fire({
                customClass: {
                    popup: 'swal2-m3e',
                    title: 'swal2-m3e-title',
                },
                icon: 'error',
                title: 'Gagal Memeriksa Pembaruan',
                text: err.message || 'Terjadi kesalahan saat menghubungi server.',
                confirmButtonColor: '#3B5FE5'
            });
        });
    }

    // 1-Click Execute Update Handler
    function execute1ClickUpdate(targetVersion) {
        // Tampilkan loading progress Swal
        Swal.fire({
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
            },
            title: 'Menerapkan Pembaruan...',
            html: `
                <div class="py-3">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <div class="fw-semibold text-dark mb-1" id="updateProgressText">Mengamankan snapshot database...</div>
                    <small class="text-muted">Mohon tunggu sebentar, sistem sedang melakukan sinkronisasi otomatis.</small>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        fetch("{{ route('admin.settings.execute-update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ target_version: targetVersion })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update header and card versions
                document.getElementById('headerSystemVersion').innerText = data.new_version;
                document.getElementById('cardSystemVersion').innerText = data.new_version;
                document.getElementById('cardLastUpdated').innerText = data.updated_at;

                Swal.fire({
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                    },
                    icon: 'success',
                    iconColor: '#198754',
                    title: 'Pembaruan Berhasil! 🚀',
                    html: `
                        <p class="text-secondary mb-2">${data.message}</p>
                        <div class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-bold">
                            Versi Baru: ${data.new_version}
                        </div>
                    `,
                    confirmButtonColor: '#3B5FE5',
                    confirmButtonText: 'Selesai'
                });
            } else {
                Swal.fire({
                    customClass: {
                        popup: 'swal2-m3e',
                        title: 'swal2-m3e-title',
                    },
                    icon: 'error',
                    title: 'Pembaruan Gagal',
                    text: data.message || 'Terjadi kesalahan saat memproses pembaruan.',
                    confirmButtonColor: '#3B5FE5'
                });
            }
        })
        .catch(err => {
            Swal.fire({
                customClass: {
                    popup: 'swal2-m3e',
                    title: 'swal2-m3e-title',
                },
                icon: 'error',
                title: 'Koneksi Terputus',
                text: err.message || 'Terjadi kesalahan jaringan saat proses update.',
                confirmButtonColor: '#3B5FE5'
            });
        });
    }

    // Expose functions globally to window
    window.triggerCheckUpdate = triggerCheckUpdate;
    window.execute1ClickUpdate = execute1ClickUpdate;

    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnCheckUpdate');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                triggerCheckUpdate();
            });
        }
    });
</script>
@endpush
