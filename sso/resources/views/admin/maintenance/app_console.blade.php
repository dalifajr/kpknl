@extends('layouts.app')

@section('title', 'Konsol Orkestrasi: ' . $application->name . ' | SSO KPKNL Palembang')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumbs Navigation -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.maintenance.index') }}" class="text-decoration-none">
                    <i class="fa-solid fa-layer-group me-1"></i> Fleet Hub
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $application->name }}</li>
        </ol>
    </nav>

    <!-- Top Application Banner -->
    <div class="card-m3 p-4 mb-4 bg-white border-0 shadow-sm">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-cubes-stacked fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold mb-0 text-dark">{{ $application->name }}</h4>
                        @if($application->maintenance_mode)
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                                <i class="fa-solid fa-lock me-1"></i> MODE PEMELIHARAAN (503)
                            </span>
                        @else
                            <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> LIVE OPERATIONAL
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 text-muted small flex-wrap">
                        <span><i class="fa-regular fa-folder-open text-primary me-1"></i> Folder: <code>{{ $application->folder_path }}</code></span>
                        <span><i class="fa-solid fa-database text-info me-1"></i> DB: <strong class="text-dark">{{ $application->database_name }}</strong></span>
                        <span><i class="fa-solid fa-code-commit text-warning me-1"></i> Commit: <code class="text-primary fw-bold">{{ substr($application->current_commit ?? '', 0, 7) }}</code></span>
                        <span><i class="fa-solid fa-tag text-secondary me-1"></i> Versi: <span class="badge bg-light text-dark">{{ $application->current_version ?? 'v1.0.0' }}</span></span>
                        <span><i class="fa-solid fa-globe text-muted me-1"></i> URL: <a href="{{ $application->url }}" target="_blank" class="text-decoration-none">{{ $application->url }}</a></span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-info rounded-pill px-3 py-2" onclick="checkHealthModal({{ $application->id }}, '{{ addslashes($application->name) }}')">
                    <i class="fa-solid fa-stethoscope me-1"></i> Health Ping
                </button>
                <form action="{{ route('admin.maintenance.toggle', $application->id) }}" method="POST">
                    @csrf
                    @if($application->maintenance_mode)
                        <button type="submit" class="btn btn-success rounded-pill px-3 py-2" onclick="return confirm('Nonaktifkan mode pemeliharaan dan bawa aplikasi kembali ONLINE?');">
                            <i class="fa-solid fa-play me-1"></i> Bring App Live
                        </button>
                    @else
                        <button type="submit" class="btn btn-warning rounded-pill px-3 py-2 text-dark fw-semibold" onclick="return confirm('Aktifkan mode pemeliharaan? Traffic publik akan diarahkan ke 503.');">
                            <i class="fa-solid fa-pause me-1"></i> Set Maintenance (503)
                        </button>
                    @endif
                </form>
                <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Fleet
                </a>
            </div>
        </div>
    </div>

    <!-- Workspace Tabs Navigation -->
    <div class="card-m3 p-4 bg-white border-0 shadow-sm">
        <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="appWorkspaceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2 fw-semibold" id="tab-deploy" data-bs-toggle="pill" data-bs-target="#content-deploy" type="button" role="tab">
                    <i class="fa-solid fa-rocket me-1 text-primary"></i> 7-Stage Deployment
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="tab-rollback" data-bs-toggle="pill" data-bs-target="#content-rollback" type="button" role="tab">
                    <i class="fa-solid fa-clock-rotate-left me-1 text-warning"></i> Rollback & Downgrade
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="tab-backups" data-bs-toggle="pill" data-bs-target="#content-backups" type="button" role="tab">
                    <i class="fa-solid fa-database me-1 text-info"></i> Snapshot Vault ({{ $application->backups->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="tab-secret" data-bs-toggle="pill" data-bs-target="#content-secret" type="button" role="tab">
                    <i class="fa-solid fa-key me-1 text-secondary"></i> Token Bypass 503
                </button>
            </li>
        </ul>

        <div class="tab-content" id="appWorkspaceTabsContent">
            
            <!-- TAB 1: 7-STAGE DEPLOYMENT -->
            <div class="tab-pane fade show active" id="content-deploy" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Pipeline Architecture Card -->
                    <div class="col-lg-5">
                        <div class="bg-light p-4 rounded-4 border">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-diagram-project text-primary"></i> 7-Stage Automated Pipeline
                            </h6>
                            <p class="text-muted small mb-3">Setiap eksekusi deployment dijalankan dalam urutan ketat untuk menjamin kontinuitas dan keamanan basis data:</p>

                            <div class="d-flex flex-column gap-2 small">
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">1</span>
                                    <div><strong>Preflight Inspection:</strong> Verifikasi direktori & status Git</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">2</span>
                                    <div><strong>Auto-Snapshot DB:</strong> Backup otomatis via <code>mysqldump</code></div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">3</span>
                                    <div><strong>Maintenance Mode On:</strong> Alihkan traffic publik ke 503</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">4</span>
                                    <div><strong>Path-Isolated Sync:</strong> Checkout khusus folder aplikasi</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">5</span>
                                    <div><strong>Composer & Migrate:</strong> Dump autoload & migrasi skema</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-circle p-2" style="width: 24px; height: 24px;">6</span>
                                    <div><strong>Cache Warmup:</strong> Config cache & route optimize</div>
                                </div>
                                <div class="p-2 rounded bg-white border d-flex align-items-center gap-3">
                                    <span class="badge bg-success rounded-circle p-2" style="width: 24px; height: 24px;">7</span>
                                    <div><strong>Smoke Test & Go Live:</strong> Verifikasi HTTP status lalu buka traffic</div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 px-3 mt-3 mb-0 small rounded-3">
                                <i class="fa-solid fa-shield-halved me-1"></i> Dilengkapi <strong>Automated Rollback Protocol</strong> jika terjadi error pada skema atau HTTP crash.
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Git Commits & Trigger Modal -->
                    <div class="col-lg-7">
                        <div class="p-4 bg-white rounded-4 border">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-code-commit text-primary"></i> Riwayat Commit Git Tersedia (origin/main)
                                </h6>
                                <form action="{{ route('admin.maintenance.fetch-git') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-rotate me-1"></i> Fetch Git
                                    </button>
                                </form>
                            </div>

                            @if(count($recentCommits) > 0)
                                <form id="deployFormSelection">
                                    <div class="list-group mb-3 fs-7" style="max-height: 320px; overflow-y: auto;">
                                        @foreach($recentCommits as $idx => $cmt)
                                            @php $isCurr = $cmt['is_current'] || ($application->current_commit === $cmt['hash']); @endphp
                                            <label class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3 {{ $isCurr ? 'bg-primary-subtle' : '' }}" style="cursor: pointer;">
                                                <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="selected_commit" value="{{ $cmt['hash'] }}" data-short="{{ $cmt['short_hash'] }}" data-message="{{ $cmt['message'] }}" data-author="{{ $cmt['author'] }}" {{ $isCurr ? 'checked' : ($idx === 0 ? 'checked' : '') }}>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="fw-bold text-dark">{{ $cmt['message'] }}</span>
                                                        @if($isCurr)
                                                            <span class="badge bg-primary text-white rounded-pill px-2 py-1">Versi Terpasang</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3 text-muted small">
                                                        <span><i class="fa-regular fa-user me-1"></i>{{ $cmt['author'] }}</span>
                                                        <span><i class="fa-regular fa-calendar me-1"></i>{{ $cmt['date'] }}</span>
                                                        <code class="fw-bold">{{ $cmt['short_hash'] }}</code>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-2">
                                        <span class="text-muted small">
                                            <i class="fa-solid fa-circle-check text-success me-1"></i> Snapshot database otomatis sebelum eksekusi.
                                        </span>
                                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2" onclick="openDeployModal()">
                                            <i class="fa-solid fa-rocket me-1"></i> Siapkan & Jalankan Deployment
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-code-branch fs-1 text-secondary mb-3"></i>
                                    <p class="mb-2">Tidak ditemukan commit untuk direktori ini.</p>
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
            <div class="tab-pane fade" id="content-rollback" role="tabpanel">
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-4 rounded-4">
                    <i class="fa-solid fa-triangle-exclamation fs-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Protokol Downgrade Aman & Zero Data Loss</h6>
                        <p class="mb-0 small">Sistem akan secara otomatis membuat snapshot darurat sebelum proses rollback, kemudian memulihkan snapshot database target dan mengembalikan source code secara terisolasi tanpa mengganggu aplikasi klien lain.</p>
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
                                <th>Snapshot Terkait</th>
                                <th>Operator</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($application->deployments as $dep)
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
                                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold" onclick="openRollbackModal({{ $dep->id }}, '{{ substr($dep->target_commit, 0, 7) }}', '{{ $dep->backup ? $dep->backup->filename : '' }}')">
                                                <i class="fa-solid fa-backward me-1"></i> Rollback
                                            </button>
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
            <div class="tab-pane fade" id="content-backups" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Database Snapshot Vault: <code>{{ $application->database_name }}</code></h6>
                        <p class="text-muted small mb-0">Cadangan snapshot database aplikasi sebelum setiap deployment atau yang dibuat manual.</p>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill btn-sm px-3" data-bs-toggle="modal" data-bs-target="#manualSnapshotModal">
                        <i class="fa-solid fa-camera me-1"></i> Buat Snapshot Manual
                    </button>
                </div>

                <div class="table-responsive bg-white rounded-4 border p-3">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Nama File Snapshot</th>
                                <th>Tipe</th>
                                <th>Commit SHA</th>
                                <th>Ukuran File</th>
                                <th>Waktu Pembuatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($application->backups as $bk)
                                <tr>
                                    <td><code class="text-primary fw-bold">{{ $bk->filename }}</code></td>
                                    <td>
                                        @if($bk->backup_type === 'auto_pre_deploy')
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">Auto Pre-Deploy</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Manual</span>
                                        @endif
                                    </td>
                                    <td><code>{{ substr($bk->commit_hash ?? '', 0, 7) ?: '-' }}</code></td>
                                    <td><span class="badge bg-light text-dark fw-bold">{{ $bk->formatted_size }}</span></td>
                                    <td class="text-muted">{{ $bk->created_at->format('d M Y, H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.maintenance.download-backup', $bk->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1">
                                            <i class="fa-solid fa-download me-1"></i> Unduh
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="openRestoreModal({{ $bk->id }}, '{{ $bk->filename }}')">
                                            <i class="fa-solid fa-database me-1"></i> Restore
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada snapshot database tersimpan untuk aplikasi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: TOKEN BYPASS 503 -->
            <div class="tab-pane fade" id="content-secret" role="tabpanel">
                <div class="p-4 bg-light rounded-4 border">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-key text-warning me-2"></i>Token Bypass Mode Pemeliharaan</h6>
                    <p class="text-muted small mb-4">Ketika aplikasi dalam mode pemeliharaan (HTTP 503), pengembang dan tester dapat melewati layar pemeliharaan dengan mengakses tautan khusus yang berisi secret token di bawah ini.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Active Bypass Token:</label>
                            <div class="input-group mb-3">
                                <input type="text" id="bypassTokenInput" class="form-control font-monospace" value="{{ $application->maintenance_bypass_token ?? 'Belum aktif (Aplikasi sedang Online)' }}" readonly>
                                @if($application->maintenance_bypass_token)
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tautan Langsung Pengujian (Direct Access):</label>
                            @if($application->maintenance_bypass_token)
                                @php $bypassUrl = rtrim($application->url, '/') . '/' . $application->maintenance_bypass_token; @endphp
                                <div class="input-group">
                                    <input type="text" id="bypassUrlInput" class="form-control font-monospace small" value="{{ $bypassUrl }}" readonly>
                                    <a href="{{ $bypassUrl }}" target="_blank" class="btn btn-primary">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka
                                    </a>
                                </div>
                            @else
                                <div class="text-muted small mt-2">Aktifkan mode pemeliharaan terlebih dahulu untuk membuat token bypass.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Konfirmasi Deployment 7-Tahap -->
<div class="modal fade" id="deployConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.maintenance.deploy', $application->id) }}" method="POST">
                @csrf
                <input type="hidden" name="target_commit" id="modalTargetCommitInput">
                <input type="hidden" name="deployment_type" value="update">

                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-rocket text-primary me-2"></i> Konfirmasi 7-Stage Deployment
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-primary py-2 px-3 mb-3 small rounded-3">
                        Target Aplikasi: <strong>{{ $application->name }}</strong>
                    </div>

                    <div class="bg-light p-3 rounded-4 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Target Commit:</span>
                            <code class="fw-bold text-primary" id="modalShortCommit">-</code>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Author:</span>
                            <span class="fw-semibold" id="modalAuthor">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Commit Message:</span>
                            <span class="fw-semibold text-truncate" style="max-width: 250px;" id="modalMessage">-</span>
                        </div>
                    </div>

                    <div class="small text-secondary mb-3">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Sistem akan secara otomatis:
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Membuat snapshot database <code>{{ $application->database_name }}</code></li>
                            <li>Mengalihkan traffic publik ke mode 503 dengan bypass token</li>
                            <li>Melakukan sinkronisasi kode Git terisolasi</li>
                            <li>Menjalankan <code>composer dump-autoload</code> & <code>artisan migrate</code></li>
                            <li>Melakukan cache warmup & smoke test sebelum membawa aplikasi kembali live</li>
                        </ul>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="deployConsentCheck" required>
                        <label class="form-check-label small" for="deployConsentCheck">
                            Saya telah memeriksa target commit dan menyetujui eksekusi pipeline.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-play me-1"></i> Mulai Eksekusi Pipeline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Konfirmasi Rollback -->
<div class="modal fade" id="rollbackConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.maintenance.rollback', $application->id) }}" method="POST">
                @csrf
                <input type="hidden" name="deployment_id" id="modalRollbackDeploymentId">

                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Konfirmasi Downgrade / Rollback
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Anda akan mengembalikan aplikasi <strong>{{ $application->name }}</strong> ke versi sebelumnya:</p>

                    <div class="bg-light p-3 rounded-4 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Target Commit:</span>
                            <code class="fw-bold text-danger" id="modalRollbackCommit">-</code>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Snapshot Pemulihan:</span>
                            <code class="fw-bold text-dark" id="modalRollbackSnapshot">-</code>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-3 small rounded-3">
                        <i class="fa-solid fa-shield-halved me-1"></i> Snapshot darurat akan dibuat sebelum proses rollback dieksekusi untuk mencegah data loss.
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rollbackConsentCheck" required>
                        <label class="form-check-label small" for="rollbackConsentCheck">
                            Saya menyetujui pemulihan database dan source code ke versi ini.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-rotate-left me-1"></i> Konfirmasi Rollback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Snapshot Database Manual -->
<div class="modal fade" id="manualSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.maintenance.backup', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-camera text-info me-2"></i> Buat Snapshot Database Manual
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Database Target:</label>
                        <input type="text" class="form-control font-monospace" value="{{ $application->database_name }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan / Keterangan Snapshot:</label>
                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Backup sebelum migrasi risalah lelang manual" required>
                    </div>
                    <p class="small text-muted mb-0">File snapshot akan dibuat menggunakan <code>mysqldump</code> dan dapat diunduh atau dipulihkan kapan saja melalui tab Snapshot Vault.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-camera me-1"></i> Buat Snapshot Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Restore Snapshot Confirmation -->
<div class="modal fade" id="restoreDbModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="restoreDbForm" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-database text-danger me-2"></i> Konfirmasi Restore Database
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Anda akan me-restore database <strong>{{ $application->database_name }}</strong> dari file:</p>
                    <div class="bg-light p-3 rounded-4 small mb-3">
                        <code class="text-danger fw-bold" id="restoreFilenameDisplay">-</code>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mb-3 small rounded-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Perhatian: Data saat ini akan ditimpa dengan isi dari file snapshot tersebut.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="restoreConsentCheck" required>
                        <label class="form-check-label small" for="restoreConsentCheck">
                            Saya memahami risiko dan mengonfirmasi pemulihan database ini.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-database me-1"></i> Eksekusi Restore Database
                    </button>
                </div>
            </form>
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

<!-- Modal: Health Check Result Modal -->
<div class="modal fade" id="healthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-stethoscope text-info me-2"></i> Hasil Health Check
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="healthModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Memeriksa koneksi HTTP dan status operasional...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeployModal() {
        const selectedRadio = document.querySelector('input[name="selected_commit"]:checked');
        if (!selectedRadio) {
            alert('Pilih salah satu commit Git terlebih dahulu.');
            return;
        }

        document.getElementById('modalTargetCommitInput').value = selectedRadio.value;
        document.getElementById('modalShortCommit').innerText = selectedRadio.dataset.short;
        document.getElementById('modalAuthor').innerText = selectedRadio.dataset.author;
        document.getElementById('modalMessage').innerText = selectedRadio.dataset.message;
        document.getElementById('deployConsentCheck').checked = false;

        new bootstrap.Modal(document.getElementById('deployConfirmModal')).show();
    }

    function openRollbackModal(depId, shortCommit, snapshotFile) {
        document.getElementById('modalRollbackDeploymentId').value = depId;
        document.getElementById('modalRollbackCommit').innerText = shortCommit;
        document.getElementById('modalRollbackSnapshot').innerText = snapshotFile || 'Snapshot referensi pipeline';
        document.getElementById('rollbackConsentCheck').checked = false;

        new bootstrap.Modal(document.getElementById('rollbackConfirmModal')).show();
    }

    function openRestoreModal(bkId, filename) {
        document.getElementById('restoreFilenameDisplay').innerText = filename;
        document.getElementById('restoreDbForm').action = "{{ url('admin/maintenance-orchestrator/restore-backup') }}/" + bkId;
        document.getElementById('restoreConsentCheck').checked = false;

        new bootstrap.Modal(document.getElementById('restoreDbModal')).show();
    }

    function copyToken() {
        const tokenInput = document.getElementById('bypassTokenInput');
        tokenInput.select();
        document.execCommand('copy');
        alert('Token bypass berhasil disalin ke clipboard!');
    }

    function checkHealthModal(appId, appName) {
        const modal = new bootstrap.Modal(document.getElementById('healthModal'));
        const body = document.getElementById('healthModalBody');
        body.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h6 class="fw-bold text-dark mb-1">Memeriksa ${appName}...</h6>
                <p class="text-muted small">Mengirimkan HTTP request dan memeriksa status server...</p>
            </div>
        `;
        modal.show();

        fetch("{{ url('admin/maintenance-orchestrator/health') }}/" + appId)
            .then(res => res.json())
            .then(data => {
                const isHealthy = (data.status === 'healthy');
                const isMaint = (data.status === 'maintenance');
                let badgeClass = isHealthy ? 'bg-success text-white' : (isMaint ? 'bg-warning text-dark' : 'bg-danger text-white');
                let statusLabel = isHealthy ? 'HEALTHY (ONLINE)' : (isMaint ? 'MAINTENANCE (503)' : 'DEGRADED / DOWN');

                body.innerHTML = `
                    <div class="text-center mb-3">
                        <div class="display-6 mb-2">
                            <i class="fa-solid ${isHealthy ? 'fa-circle-check text-success' : (isMaint ? 'fa-screwdriver-wrench text-warning' : 'fa-circle-xmark text-danger')}"></i>
                        </div>
                        <span class="badge ${badgeClass} px-3 py-2 rounded-pill fw-bold fs-7">${statusLabel}</span>
                        <h5 class="fw-bold mt-2 text-dark">${appName}</h5>
                    </div>
                    <div class="bg-light p-3 rounded-4 small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">HTTP Response Code:</span>
                            <span class="fw-bold font-monospace">${data.status_code || '-'}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Response Latency:</span>
                            <span class="fw-bold text-primary font-monospace">${data.latency_ms} ms</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Mode Pemeliharaan Aktif:</span>
                            <span class="fw-bold">${data.is_maintenance ? '<span class="text-warning">YA (503)</span>' : '<span class="text-success">TIDAK (Live)</span>'}</span>
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                body.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Gagal memeriksa health: ${err.message}
                    </div>
                `;
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
