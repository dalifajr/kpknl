@extends('layouts.app')

@section('title', 'Riwayat Global Deployment | SSO KPKNL Palembang')

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
            <li class="breadcrumb-item active" aria-current="page">Riwayat Global Deployment</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Riwayat Global Deployment</h3>
            <p class="text-muted mb-0">Catatan audit trail lengkap seluruh eksekusi 7-stage pipeline dan status pembaruan aplikasi klien.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.maintenance.backups') }}" class="btn btn-tonal rounded-pill px-3 py-2">
                <i class="fa-solid fa-database me-1"></i> Snapshot Vault
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Fleet Hub
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="card-m3 p-3 mb-4 bg-white border-0 shadow-sm">
        <form action="{{ route('admin.maintenance.deployments') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Filter Aplikasi:</label>
                <select name="app_id" class="form-select fs-7">
                    <option value="">-- Semua Aplikasi Klien --</option>
                    @foreach($applications as $a)
                        <option value="{{ $a->id }}" {{ request('app_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Status Pipeline:</label>
                <select name="status" class="form-select fs-7">
                    <option value="">-- Semua Status --</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="rolled_back" {{ request('status') === 'rolled_back' ? 'selected' : '' }}>Rolled Back</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Tipe Eksekusi:</label>
                <select name="type" class="form-select fs-7">
                    <option value="">-- Semua Tipe --</option>
                    <option value="update" {{ request('type') === 'update' ? 'selected' : '' }}>Update (Deploy)</option>
                    <option value="downgrade" {{ request('type') === 'downgrade' ? 'selected' : '' }}>Downgrade (Rollback)</option>
                    <option value="hotfix" {{ request('type') === 'hotfix' ? 'selected' : '' }}>Hotfix</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['app_id', 'status', 'type']))
                    <a href="{{ route('admin.maintenance.deployments') }}" class="btn btn-outline-secondary rounded-pill py-2" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Deployments Table Card -->
    <div class="card-m3 p-4 bg-white border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table align-middle fs-7 mb-0">
                <thead>
                    <tr>
                        <th>Waktu Eksekusi</th>
                        <th>Aplikasi</th>
                        <th>Tipe</th>
                        <th>Perubahan Commit</th>
                        <th>Status</th>
                        <th>Durasi</th>
                        <th>Snapshot DB</th>
                        <th>Operator</th>
                        <th class="text-end">Log</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deployments as $d)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $d->created_at->format('d M Y, H:i') }}</div>
                                <span class="text-muted small">{{ $d->created_at->diffForHumans() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.maintenance.app-console', $d->application_id) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $d->application->name ?? 'Aplikasi' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark fw-semibold px-2 py-1">{{ strtoupper($d->deployment_type) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 font-monospace">
                                    <span class="text-muted">{{ substr($d->source_commit ?? '------', 0, 7) }}</span>
                                    <i class="fa-solid fa-arrow-right text-muted fs-8"></i>
                                    <code class="fw-bold text-primary">{{ substr($d->target_commit, 0, 7) }}</code>
                                </div>
                            </td>
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
                                    {{ $d->finished_at->diffInSeconds($d->started_at) }} dtk
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($d->backup)
                                    <span class="badge bg-secondary-subtle text-secondary px-2" title="{{ $d->backup->filename }}">
                                        <i class="fa-solid fa-database me-1"></i>{{ $d->backup->formatted_size }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $d->deployer->name ?? 'System' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="viewDeploymentLogs({{ $d->id }})">
                                    <i class="fa-solid fa-terminal me-1"></i> Log
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Tidak ada riwayat deployment yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $deployments->links() }}
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
