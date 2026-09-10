@extends('layouts.app')

@section('title', 'Maintenance & Lifecycle Orchestrator | SSO KPKNL Palembang')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
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
            <p class="text-muted mb-0">Pusat komando siklus hidup aplikasi klien: pembaruan (*deploy*), *downgrade*, dan cadangan basis data terisolasi.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="{{ route('admin.maintenance.fetch-git') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary rounded-pill px-3 py-2">
                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Fetch Remote Git
                </button>
            </form>
            <a href="{{ route('admin.maintenance.deployments') }}" class="btn btn-tonal rounded-pill px-3 py-2">
                <i class="fa-solid fa-timeline me-1"></i> Riwayat Deployment
            </a>
            <a href="{{ route('admin.maintenance.backups') }}" class="btn btn-tonal rounded-pill px-3 py-2">
                <i class="fa-solid fa-database me-1"></i> Snapshot Vault
            </a>
        </div>
    </div>

    <!-- Compact Git Remote Banner -->
    <div class="card-m3 p-3 mb-4 bg-light border-0 shadow-sm">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
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
                <div class="text-muted small fw-semibold">Active Monorepo Branch</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-dark text-white px-2 py-1"><i class="fa-solid fa-code-fork me-1"></i>main</span>
                    <span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Synchronized</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small fw-semibold">Isolation Strategy</div>
                <div class="small text-secondary mt-1">
                    <code>git checkout &lt;hash&gt; -- &lt;app_folder&gt;/</code> (Path-Isolated & Zero-Collision)
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-m3 p-3 border-0 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-cubes fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Aplikasi Klien</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $kpis['total_apps'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-m3 p-3 border-0 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Status Operasional</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $kpis['online_apps'] }} Live <span class="text-muted fs-6 fw-normal">({{ $kpis['maintenance_apps'] }} 503)</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-m3 p-3 border-0 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-database fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Database Snapshots</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $kpis['total_backups'] }} <span class="text-muted fs-6 fw-normal">File</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-m3 p-3 border-0 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-rocket fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Deployment</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $kpis['total_deployments'] }} <span class="text-muted fs-6 fw-normal">Kali</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Fleet Cards Grid -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-layer-group text-primary"></i> Armada Aplikasi Klien Terhubung
        </h5>
        <span class="text-muted small">Klik <strong>Buka Konsol</strong> pada aplikasi untuk melakukan Deploy / Rollback terisolasi.</span>
    </div>

    <div class="row g-4 mb-4">
        @foreach($applications as $app)
            @php $isMaint = $app->maintenance_mode; @endphp
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card-m3 p-4 h-100 border-0 shadow-sm bg-white d-flex flex-column justify-content-between transition-all hover-shadow">
                    <div>
                        <!-- Card Top Header -->
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-cubes-stacked fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-truncate text-dark" style="max-width: 170px;" title="{{ $app->name }}">{{ $app->name }}</h6>
                                    <code class="small text-muted">{{ $app->folder_path ?? 'root' }}</code>
                                </div>
                            </div>
                            <div>
                                @if($isMaint)
                                    <span class="badge bg-warning-subtle text-warning fw-bold rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-screwdriver-wrench me-1"></i> 503 Maint
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle-dot me-1"></i> Live
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Metadata Box -->
                        <div class="bg-light p-3 rounded-4 mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><i class="fa-solid fa-database me-1 text-info"></i> Database:</span>
                                <span class="fw-bold text-dark font-monospace">{{ $app->database_name ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><i class="fa-solid fa-tag me-1 text-secondary"></i> Versi Aktif:</span>
                                <span class="badge bg-secondary-subtle text-secondary px-2">{{ $app->current_version ?? 'v1.0.0' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><i class="fa-solid fa-code-commit me-1 text-warning"></i> Commit SHA:</span>
                                <code class="text-primary fw-bold">{{ substr($app->current_commit ?? '0000000', 0, 7) }}</code>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Riwayat:</span>
                                <span class="text-dark">{{ $app->deployments_count }} Deploy / {{ $app->backups_count }} Snapshots</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div>
                        <a href="{{ route('admin.maintenance.app-console', $app->id) }}" class="btn btn-primary w-100 rounded-pill py-2 mb-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-terminal"></i> Buka Konsol Orkestrasi
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill flex-grow-1" onclick="checkHealthModal({{ $app->id }}, '{{ addslashes($app->name) }}')">
                                <i class="fa-solid fa-stethoscope me-1"></i> Health Ping
                            </button>
                            <form action="{{ route('admin.maintenance.toggle', $app->id) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn {{ $isMaint ? 'btn-success' : 'btn-outline-warning text-dark' }} btn-sm rounded-pill w-100" onclick="return confirm('Ubah status pemeliharaan aplikasi {{ $app->name }}?');">
                                    <i class="fa-solid {{ $isMaint ? 'fa-play' : 'fa-pause' }} me-1"></i> {{ $isMaint ? 'Bring Live' : 'Set 503' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Activity Overview Cards (Two-Column Modular Layout) -->
    <div class="row g-4">
        <!-- Recent Deployments Snippet -->
        <div class="col-12 col-xl-7">
            <div class="card-m3 p-4 bg-white border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-timeline text-primary"></i> Deployment Terkini (Lintas Aplikasi)
                    </h6>
                    <a href="{{ route('admin.maintenance.deployments') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        Lihat Semua Riwayat <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Aplikasi</th>
                                <th>Target Commit</th>
                                <th>Status</th>
                                <th class="text-end">Log</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeployments as $dep)
                                <tr>
                                    <td class="text-muted">{{ $dep->created_at->diffForHumans() }}</td>
                                    <td><span class="fw-bold text-dark">{{ $dep->application->name ?? 'Aplikasi' }}</span></td>
                                    <td><code>{{ substr($dep->target_commit, 0, 7) }}</code></td>
                                    <td>
                                        @if($dep->status === 'success')
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="fa-solid fa-check me-1"></i>Success</span>
                                        @elseif($dep->status === 'rolled_back')
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1"><i class="fa-solid fa-rotate-left me-1"></i>Rolled Back</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">{{ strtoupper($dep->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="viewDeploymentLogs({{ $dep->id }})">
                                            <i class="fa-solid fa-file-code me-1"></i> Log
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada deployment yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Database Snapshots Snippet -->
        <div class="col-12 col-xl-5">
            <div class="card-m3 p-4 bg-white border-0 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-database text-info"></i> Snapshot DB Terkini
                    </h6>
                    <a href="{{ route('admin.maintenance.backups') }}" class="btn btn-outline-info btn-sm rounded-pill px-3">
                        Buka Vault <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Database</th>
                                <th>Ukuran</th>
                                <th>Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBackups as $bk)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $bk->application->name ?? '-' }}</div>
                                        <code class="small text-muted">{{ $bk->filename }}</code>
                                    </td>
                                    <td><span class="badge bg-light text-dark fw-bold">{{ $bk->formatted_size }}</span></td>
                                    <td class="text-muted">{{ $bk->created_at->diffForHumans() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.maintenance.download-backup', $bk->id) }}" class="btn btn-tonal btn-sm rounded-pill px-2" title="Unduh SQL">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada file snapshot tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Deployment Pipeline Logs Viewer -->
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

<!-- Modal 2: Health Check Result Modal -->
<div class="modal fade" id="healthModal" tabindex="-1" aria-labelledby="healthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark" id="healthModalLabel">
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
