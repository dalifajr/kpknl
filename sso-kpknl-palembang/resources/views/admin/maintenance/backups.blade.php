@extends('layouts.app')

@section('title', 'Database Snapshot Vault | SSO KPKNL Palembang')

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
            <li class="breadcrumb-item active" aria-current="page">Database Snapshot Vault</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Database Snapshot Vault</h3>
            <p class="text-muted mb-0">Pusat arsip snapshot database seluruh aplikasi klien yang dibuat secara otomatis sebelum deployment maupun manual.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.maintenance.deployments') }}" class="btn btn-tonal rounded-pill px-3 py-2">
                <i class="fa-solid fa-timeline me-1"></i> Riwayat Deployment
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Fleet Hub
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="card-m3 p-3 mb-4 bg-white border-0 shadow-sm">
        <form action="{{ route('admin.maintenance.backups') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold">Filter Aplikasi / Database:</label>
                <select name="app_id" class="form-select fs-7">
                    <option value="">-- Semua Aplikasi Klien --</option>
                    @foreach($applications as $a)
                        <option value="{{ $a->id }}" {{ request('app_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->name }} (DB: {{ $a->database_name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label small fw-semibold">Tipe Snapshot:</label>
                <select name="type" class="form-select fs-7">
                    <option value="">-- Semua Tipe --</option>
                    <option value="auto_pre_deploy" {{ request('type') === 'auto_pre_deploy' ? 'selected' : '' }}>Otomatis (Pre-Deploy)</option>
                    <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['app_id', 'type']))
                    <a href="{{ route('admin.maintenance.backups') }}" class="btn btn-outline-secondary rounded-pill py-2" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Backups Table Card -->
    <div class="card-m3 p-4 bg-white border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table align-middle fs-7 mb-0">
                <thead>
                    <tr>
                        <th>Nama File Snapshot</th>
                        <th>Aplikasi / Database</th>
                        <th>Tipe</th>
                        <th>Commit SHA</th>
                        <th>Ukuran File</th>
                        <th>Waktu Pembuatan</th>
                        <th>Pembuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $bk)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-invoice text-info"></i>
                                    <div>
                                        <code class="fw-bold text-dark">{{ $bk->filename }}</code>
                                        @if($bk->notes)
                                            <div class="text-muted small">{{ $bk->notes }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.maintenance.app-console', $bk->application_id) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $bk->application->name ?? 'Aplikasi' }}
                                </a>
                                <div class="text-muted small">DB: <code>{{ $bk->application->database_name ?? '-' }}</code></div>
                            </td>
                            <td>
                                @if($bk->backup_type === 'auto_pre_deploy')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">Auto Pre-Deploy</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">Manual</span>
                                @endif
                            </td>
                            <td>
                                <code>{{ substr($bk->commit_hash ?? '', 0, 7) ?: '-' }}</code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark fw-bold">{{ $bk->formatted_size }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $bk->created_at->format('d M Y, H:i') }}</div>
                                <span class="text-muted small">{{ $bk->created_at->diffForHumans() }}</span>
                            </td>
                            <td>{{ $bk->creator->name ?? 'System' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.maintenance.download-backup', $bk->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1">
                                    <i class="fa-solid fa-download me-1"></i> Unduh
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="openRestoreModal({{ $bk->id }}, '{{ $bk->filename }}', '{{ $bk->application->name ?? 'Aplikasi' }}', '{{ $bk->application->database_name ?? '-' }}')">
                                    <i class="fa-solid fa-database me-1"></i> Restore
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada snapshot database yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $backups->links() }}
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
                    <p class="small text-muted mb-3">Anda akan me-restore database untuk aplikasi <strong id="restoreAppName">-</strong>:</p>

                    <div class="bg-light p-3 rounded-4 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Target Database:</span>
                            <span class="fw-bold font-monospace" id="restoreDbTarget">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">File Snapshot:</span>
                            <code class="fw-bold text-danger" id="restoreFilenameDisplay">-</code>
                        </div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 mb-3 small rounded-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Perhatian: Data saat ini pada database tersebut akan ditimpa dengan seluruh isi tabel dan baris data dari snapshot ini.
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="restoreConsentCheck" required>
                        <label class="form-check-label small" for="restoreConsentCheck">
                            Saya memahami risiko penimpaan data dan mengonfirmasi restore.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-database me-1"></i> Jalankan Restore Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openRestoreModal(bkId, filename, appName, dbName) {
        document.getElementById('restoreFilenameDisplay').innerText = filename;
        document.getElementById('restoreAppName').innerText = appName;
        document.getElementById('restoreDbTarget').innerText = dbName;
        document.getElementById('restoreDbForm').action = "{{ url('admin/maintenance-orchestrator/restore-backup') }}/" + bkId;
        document.getElementById('restoreConsentCheck').checked = false;

        new bootstrap.Modal(document.getElementById('restoreDbModal')).show();
    }
</script>
@endpush
