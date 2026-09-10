@extends('layouts.app')

@section('title', 'Centralized Maintenance & Lifecycle Orchestrator | SSO KPKNL Palembang')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Area -->
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                    <i class="fa-solid fa-code-branch me-1"></i> Monorepo Path-Isolated Engine
                </span>
                <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill">
                    <i class="fa-solid fa-shield-halved me-1"></i> Zero-Data-Loss Protected
                </span>
            </div>
            <h3 class="mb-1 fw-bold text-dark">Maintenance & Lifecycle Orchestrator</h3>
            <p class="text-muted mb-0">Pusat orkestrasi pemeliharaan, deployment terisolasi 7-tahap, dan downgrade aman untuk seluruh aplikasi klien KPKNL.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="{{ route('admin.maintenance.fetch-git') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary rounded-pill px-3 py-2">
                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Fetch Remote Updates
                </button>
            </form>
            <a href="{{ route('admin.maintenance.index', ['app_id' => $selectedApp?->id]) }}" class="btn btn-primary rounded-pill px-4 py-2">
                <i class="fa-solid fa-rotate me-1"></i> Refresh Status
            </a>
        </div>
    </div>

    <!-- Git Repository & Architecture Status Banner -->
    <div class="card-m3 p-3 mb-4 bg-light border-0 shadow-sm">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-brands fa-github fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Remote Git Repository</div>
                        <a href="{{ $gitRemoteUrl }}" target="_blank" class="fw-bold text-decoration-none text-dark d-flex align-items-center gap-1">
                            {{ $gitRemoteUrl }} <i class="fa-solid fa-arrow-up-right-from-square fs-8 text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small fw-semibold">Active Production Branch</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-dark text-white px-2 py-1"><i class="fa-solid fa-code-fork me-1"></i>main</span>
                    <span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Connected</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small fw-semibold">Monorepo Isolation Strategy</div>
                <div class="small text-secondary mt-1">
                    <code>git checkout &lt;hash&gt; -- &lt;app_folder&gt;/</code> (Path-Isolated & Zero-Collision)
                </div>
            </div>
        </div>
    </div>

    <!-- Application Quick Grid -->
    <div class="row g-3 mb-4">
        @foreach($applications as $app)
            @php
                $isSelected = ($selectedApp && $selectedApp->id === $app->id);
                $isMaint = $app->maintenance_mode;
            @endphp
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-m3 p-3 h-100 transition-all {{ $isSelected ? 'border-primary shadow-md' : 'border-0' }}" style="border-width: {{ $isSelected ? '2px' : '1px' }};">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-truncate" style="max-width: 150px;" title="{{ $app->name }}">{{ $app->name }}</h6>
                                <code class="small text-muted">{{ $app->folder_path ?? 'root' }}</code>
                            </div>
                        </div>
                        <div>
                            @if($isMaint)
                                <span class="badge bg-warning-subtle text-warning fw-bold rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-screwdriver-wrench me-1"></i> 503
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-circle-dot me-1"></i> Live
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-light p-2 rounded mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Database:</span>
                            <span class="fw-bold text-dark">{{ $app->database_name ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Versi Aktif:</span>
                            <span class="badge bg-secondary-subtle text-secondary px-2">{{ $app->current_version ?? 'v1.0.0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Commit SHA:</span>
                            <code class="text-primary fw-bold">{{ substr($app->current_commit ?? '0000000', 0, 7) }}</code>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.maintenance.index', ['app_id' => $app->id]) }}" class="btn {{ $isSelected ? 'btn-primary' : 'btn-outline-primary' }} btn-sm rounded-pill flex-grow-1">
                            <i class="fa-solid fa-terminal me-1"></i> {{ $isSelected ? 'Sedang Dipilih' : 'Pilih Aplikasi' }}
                        </a>
                        <form action="{{ route('admin.maintenance.toggle', $app->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ $isMaint ? 'btn-success' : 'btn-outline-warning' }} btn-sm rounded-pill" title="{{ $isMaint ? 'Aktifkan Kembali (Up)' : 'Masuk Mode Maintenance (Down)' }}" onclick="return confirm('Ubah status pemeliharaan aplikasi {{ $app->name }}?');">
                                <i class="fa-solid {{ $isMaint ? 'fa-play' : 'fa-pause' }}"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($selectedApp)
    <!-- Main Orchestrator Console for Selected Application -->
    <div class="card-m3 p-4 mb-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-3 border-bottom gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-cubes-stacked fs-4"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0">{{ $selectedApp->name }}</h4>
                        @if($selectedApp->maintenance_mode)
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                                <i class="fa-solid fa-lock me-1"></i> MAINTENANCE MODE AKTIF (503)
                            </span>
                        @else
                            <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> OPERATIONAL ONLINE
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-1 text-muted small flex-wrap">
                        <span><i class="fa-regular fa-folder-open me-1 text-primary"></i> Path: <code>{{ $selectedApp->folder_path }}</code></span>
                        <span><i class="fa-solid fa-database me-1 text-info"></i> Database: <strong>{{ $selectedApp->database_name }}</strong></span>
                        <span><i class="fa-solid fa-code-commit me-1 text-warning"></i> Current Commit: <code class="text-primary fw-bold">{{ substr($selectedApp->current_commit ?? '', 0, 7) }}</code></span>
                        <span><i class="fa-solid fa-globe me-1 text-secondary"></i> URL: <a href="{{ $selectedApp->url }}" target="_blank" class="text-decoration-none">{{ $selectedApp->url }}</a></span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-info rounded-pill btn-sm px-3" onclick="checkHealthAjax({{ $selectedApp->id }})">
                    <i class="fa-solid fa-stethoscope me-1"></i> Health Ping
                </button>
                <form action="{{ route('admin.maintenance.toggle', $selectedApp->id) }}" method="POST">
                    @csrf
                    @if($selectedApp->maintenance_mode)
                        <button type="submit" class="btn btn-success rounded-pill btn-sm px-3" onclick="return confirm('Nonaktifkan mode pemeliharaan dan bawa aplikasi kembali ONLINE?');">
                            <i class="fa-solid fa-play me-1"></i> Bring App ONLINE
                        </button>
                    @else
                        <button type="submit" class="btn btn-warning rounded-pill btn-sm px-3 text-dark fw-semibold" onclick="return confirm('Aktifkan mode pemeliharaan? Pengguna umum akan menerima 503.');">
                            <i class="fa-solid fa-pause me-1"></i> Put Under Maintenance
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-pills mb-4 gap-2" id="orchestratorTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="deploy-tab" data-bs-toggle="pill" data-bs-target="#deploy-content" type="button" role="tab">
                    <i class="fa-solid fa-rocket me-1"></i> 7-Stage Deployment (Update)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="rollback-tab" data-bs-toggle="pill" data-bs-target="#rollback-content" type="button" role="tab">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Rollback & Downgrade Center
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="backups-tab" data-bs-toggle="pill" data-bs-target="#backups-content" type="button" role="tab">
                    <i class="fa-solid fa-database me-1"></i> Database Snapshot Vault ({{ $selectedApp->backups->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="secret-tab" data-bs-toggle="pill" data-bs-target="#secret-content" type="button" role="tab">
                    <i class="fa-solid fa-key me-1"></i> Token Bypass Rahasia
                </button>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="orchestratorTabsContent">
            
            <!-- TAB 1: 7-STAGE DEPLOYMENT (UPDATE) -->
            <div class="tab-pane fade show active" id="deploy-content" role="tabpanel">
                <div class="row g-4">
                    <!-- Left: Pipeline Stage Diagram -->
                    <div class="col-lg-5">
                        <div class="bg-light p-3 rounded-4 border">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-diagram-project text-primary"></i> 7-Stage Automated Pipeline
                            </h6>
                            <div class="pipeline-steps-vertical small">
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">1</span>
                                    <div><strong>Preflight Inspection:</strong> Verifikasi folder, git tree, & DB</div>
                                </div>
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">2</span>
                                    <div><strong>Auto-Snapshot DB:</strong> Backup mysqldump sebelum perubahan</div>
                                </div>
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">3</span>
                                    <div><strong>Maintenance Mode On:</strong> Isolasi traffic dengan bypass token</div>
                                </div>
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">4</span>
                                    <div><strong>Path-Isolated Checkout:</strong> Update eksklusif folder aplikasi</div>
                                </div>
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">5</span>
                                    <div><strong>Composer & Migration:</strong> Autoload dump & php artisan migrate</div>
                                </div>
                                <div class="p-2 mb-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">6</span>
                                    <div><strong>Cache Warmup:</strong> Config cache & route cache</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-success rounded-circle p-2" style="width: 24px; height: 24px;">7</span>
                                    <div><strong>Smoke Test & Go Live:</strong> HTTP GET verification & lift 503</div>
                                </div>
                            </div>
                            <div class="alert alert-info py-2 px-3 mt-3 mb-0 small">
                                <i class="fa-solid fa-shield-halved me-1"></i> Jika tahap 5, 6, atau 7 mengalami error, sistem secara otomatis merestore database dan membatalkan commit (Zero Downtime Rollback).
                            </div>
                        </div>
                    </div>

                    <!-- Right: Commit Selection & Deployment Form -->
                    <div class="col-lg-7">
                        <div class="p-3 bg-white rounded-4 border">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center justify-content-between">
                                <span><i class="fa-solid fa-code-commit text-primary me-2"></i>Pilih Target Commit Git untuk Deploy</span>
                                <span class="badge bg-light text-muted fw-normal">origin/main</span>
                            </h6>

                            @if(count($recentCommits) > 0)
                                <form action="{{ route('admin.maintenance.deploy', $selectedApp->id) }}" method="POST" id="deployForm">
                                    @csrf
                                    <div class="list-group mb-3 fs-7" style="max-height: 280px; overflow-y: auto;">
                                        @foreach($recentCommits as $idx => $cmt)
                                            @php $isCurr = $cmt['is_current'] || ($selectedApp->current_commit === $cmt['hash']); @endphp
                                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3 {{ $isCurr ? 'bg-primary-subtle' : '' }}">
                                                <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="target_commit" value="{{ $cmt['hash'] }}" {{ $isCurr ? 'checked' : ($idx === 0 ? 'checked' : '') }}>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="fw-bold text-dark">{{ $cmt['message'] }}</span>
                                                        @if($isCurr)
                                                            <span class="badge bg-primary text-white rounded-pill px-2 py-1">Versi Terpasang Saat Ini</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3 text-muted small">
                                                        <span><i class="fa-regular fa-user me-1"></i>{{ $cmt['author'] }}</span>
                                                        <span><i class="fa-regular fa-calendar me-1"></i>{{ $cmt['date'] }}</span>
                                                        <code>{{ $cmt['short_hash'] }}</code>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-2">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-info-circle me-1 text-primary"></i> Snapshot database dibuat otomatis sebelum eksekusi.
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2" onclick="return confirm('Jalankan 7-stage deployment untuk aplikasi {{ $selectedApp->name }}? Sistem akan membuat snapshot database dan mengaktifkan mode pemeliharaan sementara.');">
                                            <i class="fa-solid fa-rocket me-1"></i> Jalankan 7-Stage Deployment
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-code-branch fs-1 text-secondary mb-3"></i>
                                    <p class="mb-2">Belum ada commit history ditemukan untuk aplikasi ini.</p>
                                    <form action="{{ route('admin.maintenance.fetch-git') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="fa-solid fa-cloud-arrow-down me-1"></i> Fetch dari Remote Repository
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ROLLBACK & DOWNGRADE CENTER -->
            <div class="tab-pane fade" id="rollback-content" role="tabpanel">
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-4 rounded-4">
                    <i class="fa-solid fa-triangle-exclamation fs-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Protokol Downgrade Aman & Zero Data Loss</h6>
                        <p class="mb-0 small">Ketika Anda melakukan rollback, sistem akan membuat snapshot darurat terlebih dahulu, memulihkan database dari snapshot terkait, dan mengembalikan file kode secara terisolasi tanpa mengganggu aplikasi lain.</p>
                    </div>
                </div>

                <div class="table-responsive bg-white rounded-4 border p-3">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Waktu Eksekusi</th>
                                <th>Tipe</th>
                                <th>Target Commit</th>
                                <th>Status Pipeline</th>
                                <th>Snapshot DB</th>
                                <th>Operator</th>
                                <th class="text-end">Aksi Rollback</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedApp->deployments as $dep)
                                <tr>
                                    <td>{{ $dep->created_at->format('d M Y, H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info fw-semibold rounded-pill px-2">{{ strtoupper($dep->deployment_type) }}</span>
                                    </td>
                                    <td><code>{{ substr($dep->target_commit, 0, 7) }}</code></td>
                                    <td>
                                        @if($dep->status === 'success')
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-check me-1"></i>Success</span>
                                        @elseif($dep->status === 'rolled_back')
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-rotate-left me-1"></i>Rolled Back</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold">{{ strtoupper($dep->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dep->backup)
                                            <span class="badge bg-secondary-subtle text-secondary px-2"><i class="fa-solid fa-database me-1"></i>{{ $dep->backup->formatted_size }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $dep->deployer->name ?? 'System/CLI' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-1" onclick="viewDeploymentLogs({{ $dep->id }})">
                                            <i class="fa-solid fa-file-lines me-1"></i> Log
                                        </button>
                                        @if($dep->status === 'success')
                                            <form action="{{ route('admin.maintenance.rollback', $selectedApp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin melakukan ROLLBACK ke commit {{ substr($dep->target_commit, 0, 7) }}? Sistem akan memulihkan database dan mengembalikan source code.');">
                                                @csrf
                                                <input type="hidden" name="deployment_id" value="{{ $dep->id }}">
                                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold">
                                                    <i class="fa-solid fa-backward me-1"></i> Rollback ke Versi Ini
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Belum ada riwayat deployment untuk aplikasi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: DATABASE SNAPSHOT VAULT -->
            <div class="tab-pane fade" id="backups-content" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Database Snapshot Vault: <code>{{ $selectedApp->database_name }}</code></h6>
                        <p class="text-muted small mb-0">Daftar snapshot database otomatis dan manual sebelum deployment.</p>
                    </div>
                    <!-- Form Snapshot Manual -->
                    <form action="{{ route('admin.maintenance.backup', $selectedApp->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill btn-sm px-3">
                            <i class="fa-solid fa-camera me-1"></i> Buat Snapshot Manual Sekarang
                        </button>
                    </form>
                </div>

                <div class="table-responsive bg-white rounded-4 border p-3">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Nama File Snapshot</th>
                                <th>Tipe Snapshot</th>
                                <th>Commit SHA</th>
                                <th>Ukuran File</th>
                                <th>Waktu Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedApp->backups as $bk)
                                <tr>
                                    <td><code class="text-primary fw-bold">{{ $bk->filename }}</code></td>
                                    <td>
                                        @if($bk->backup_type === 'auto_pre_deploy')
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">Pre-Deploy Auto</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Manual</span>
                                        @endif
                                    </td>
                                    <td><code>{{ substr($bk->commit_hash ?? '', 0, 7) ?: '-' }}</code></td>
                                    <td><span class="badge bg-light text-dark fw-bold">{{ $bk->formatted_size }}</span></td>
                                    <td class="text-muted">{{ $bk->created_at->format('d M Y, H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.maintenance.download-backup', $bk->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1">
                                            <i class="fa-solid fa-download me-1"></i> Unduh SQL
                                        </a>
                                        <form action="{{ route('admin.maintenance.restore-backup', $bk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('PERHATIAN: Apakah Anda yakin ingin me-RESTORE database {{ $selectedApp->database_name }} dari snapshot {{ $bk->filename }}? Data saat ini akan ditimpa dengan data dari snapshot ini.');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                <i class="fa-solid fa-database me-1"></i> Restore DB
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada snapshot database tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: TOKEN BYPASS RAHASIA -->
            <div class="tab-pane fade" id="secret-content" role="tabpanel">
                <div class="p-4 bg-light rounded-4 border">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-key text-warning me-2"></i>Token Bypass Mode Pemeliharaan</h6>
                    <p class="text-muted small mb-4">Ketika aplikasi dalam mode pemeliharaan (HTTP 503), pengembang dan tester dapat melewati layar pemeliharaan dengan mengakses tautan khusus yang berisi secret token di bawah ini.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Active Bypass Token:</label>
                            <div class="input-group mb-3">
                                <input type="text" id="bypassTokenInput" class="form-control font-monospace" value="{{ $selectedApp->maintenance_bypass_token ?? 'Belum aktif (Aplikasi sedang Online)' }}" readonly>
                                @if($selectedApp->maintenance_bypass_token)
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tautan Langsung Pengujian (Direct Access):</label>
                            @if($selectedApp->maintenance_bypass_token)
                                @php $bypassUrl = rtrim($selectedApp->url, '/') . '/' . $selectedApp->maintenance_bypass_token; @endphp
                                <div class="input-group">
                                    <input type="text" id="bypassUrlInput" class="form-control font-monospace small" value="{{ $bypassUrl }}" readonly>
                                    <a href="{{ $bypassUrl }}" target="_blank" class="btn btn-primary">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka
                                    </a>
                                </div>
                            @else
                                <div class="text-muted small mt-2">Aktifkan mode pemeliharaan terlebih dahulu untuk men-generate token bypass.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

    <!-- Global Deployment History Card -->
    <div class="card-m3 p-4 mb-4 shadow-sm">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-timeline text-primary"></i> Riwayat Deployment Seluruh Aplikasi
        </h5>
        <div class="table-responsive">
            <table class="table align-middle fs-7 mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aplikasi</th>
                        <th>Tipe</th>
                        <th>Target Commit</th>
                        <th>Status</th>
                        <th>Durasi</th>
                        <th>Operator</th>
                        <th class="text-end">Log Pipeline</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDeployments as $d)
                        <tr>
                            <td>{{ $d->created_at->format('d M Y, H:i') }}</td>
                            <td><span class="fw-bold text-dark">{{ $d->application->name ?? 'Aplikasi' }}</span></td>
                            <td><span class="badge bg-light text-dark">{{ strtoupper($d->deployment_type) }}</span></td>
                            <td><code>{{ substr($d->target_commit, 0, 7) }}</code></td>
                            <td>
                                @if($d->status === 'success')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="fa-solid fa-check me-1"></i>Success</span>
                                @elseif($d->status === 'rolled_back')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1"><i class="fa-solid fa-rotate-left me-1"></i>Rolled Back</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">{{ strtoupper($d->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($d->started_at && $d->finished_at)
                                    {{ $d->finished_at->diffInSeconds($d->started_at) }} detik
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $d->deployer->name ?? 'System' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="viewDeploymentLogs({{ $d->id }})">
                                    <i class="fa-solid fa-file-code me-1"></i> Lihat Log
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada aktivitas deployment yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $recentDeployments->links() }}
        </div>
    </div>
</div>

<!-- Modal: Deployment Pipeline Logs Viewer -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="logModalLabel">
                    <i class="fa-solid fa-terminal text-success me-2"></i> Pipeline Execution Logs
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-dark text-light p-3" style="font-family: monospace; font-size: 0.85rem; min-height: 350px;">
                <div id="logContent" class="text-white" style="white-space: pre-wrap; word-break: break-all;">Memuat log...</div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="copyLogs()">
                    <i class="fa-regular fa-copy me-1"></i> Salin Log
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToken() {
        const tokenInput = document.getElementById('bypassTokenInput');
        tokenInput.select();
        document.execCommand('copy');
        alert('Token bypass berhasil disalin ke clipboard!');
    }

    function checkHealthAjax(appId) {
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Checking...';

        fetch("{{ url('admin/maintenance-orchestrator/health') }}/" + appId)
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert(`Health Check Result:\nStatus: ${data.status.toUpperCase()}\nHTTP Code: ${data.status_code}\nLatency: ${data.latency_ms} ms\nMaintenance Mode: ${data.is_maintenance ? 'YES' : 'NO'}`);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Gagal menghubungi endpoint health: ' + err.message);
            });
    }

    function viewDeploymentLogs(deploymentId) {
        const logModal = new bootstrap.Modal(document.getElementById('logModal'));
        const logContent = document.getElementById('logContent');
        logContent.innerText = 'Mengambil log deployment dari server...';
        logModal.show();

        fetch("{{ url('admin/maintenance-orchestrator/logs') }}/" + deploymentId)
            .then(res => res.json())
            .then(data => {
                let info = `=== DEPLOYMENT #${data.id} [${data.app_name}] ===\n`;
                info += `Tipe: ${data.type} | Target Commit: ${data.target_commit}\n`;
                info += `Status: ${data.status} | Stage: ${data.current_stage}\n`;
                info += `Dimulai: ${data.started_at || '-'} | Selesai: ${data.finished_at || '-'}\n`;
                info += `-------------------------------------------------------------\n\n`;
                info += data.logs || 'Tidak ada log tercatat.';
                logContent.innerText = info;
            })
            .catch(err => {
                logContent.innerText = 'Gagal memuat log: ' + err.message;
            });
    }

    function copyLogs() {
        const logContent = document.getElementById('logContent');
        navigator.clipboard.writeText(logContent.innerText).then(() => {
            alert('Log berhasil disalin ke clipboard!');
        });
    }
</script>
@endpush
