@extends('layouts.app')

@section('title', 'Log Perubahan & Audit Sinkronisasi — SI-KEP KPKNL Palembang')
@section('hero-title', 'Log Perubahan & Audit Sinkronisasi')
@section('hero-subtitle', 'Rekam jejak audit riwayat pembuatan & pembaruan profil pegawai serta status pengiriman ke Google Spreadsheet.')

@section('header-actions')
    <form action="{{ route('change_log.sync_pending') }}" method="POST" class="m-0" id="formSyncPending">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" id="btnSyncPendingSubmit">
            <i class="fas fa-cloud-arrow-up" id="iconSyncPending"></i> 
            <span>Sinkronkan Antrian Sekarang ({{ $pendingLogs }})</span>
        </button>
    </form>
@endsection

@section('content')

<!-- Quick Summary Stats Bar (KPI Audit & Sinkronisasi) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Logs -->
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('change_log.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary card-clickable transition-all">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Aktivitas Audit</small>
                        <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-clock-rotate-left" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-dark mb-0">{{ $totalLogs }}</h3>
                        <span class="text-muted small">Perubahan</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Seluruh riwayat pembuatan &amp; edit</small>
                </div>
            </div>
        </a>
    </div>

    <!-- 2. Pending Sync -->
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('change_log.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning card-clickable transition-all {{ request('status') === 'pending' ? 'bg-warning-subtle' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Menunggu Sinkronisasi</small>
                        <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-cloud-arrow-up" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-warning mb-0">{{ $pendingLogs }}</h3>
                        <span class="text-muted small">Antrian</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Data tersimpan lokal, belum ke spreadsheet</small>
                </div>
            </div>
        </a>
    </div>

    <!-- 3. Synced Success -->
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('change_log.index', ['status' => 'synced']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-success card-clickable transition-all {{ request('status') === 'synced' ? 'bg-success-subtle' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Berhasil Tersinkron</small>
                        <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-circle-check" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-success mb-0">{{ $syncedLogs }}</h3>
                        <span class="text-muted small">Berhasil</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Tercatat identik di Google Spreadsheet</small>
                </div>
            </div>
        </a>
    </div>

    <!-- 4. Failed Sync -->
    <div class="col-sm-6 col-md-3">
        <a href="{{ route('change_log.index', ['status' => 'failed']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-danger card-clickable transition-all {{ request('status') === 'failed' ? 'bg-danger-subtle' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Gagal Sinkronisasi</small>
                        <div class="rounded-circle p-2 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-triangle-exclamation" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-danger mb-0">{{ $failedLogs }}</h3>
                        <span class="text-muted small">Kendala</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Gagal kirim ke endpoint webhook spreadsheet</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Main Card & Data Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom">
        <form method="GET" action="{{ route('change_log.index') }}" class="row g-2 align-items-center">
            <!-- Filter Status -->
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border"><i class="fas fa-filter text-muted"></i></span>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status Sync</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Sync (Pending)</option>
                        <option value="synced" {{ request('status') === 'synced' ? 'selected' : '' }}>Tersinkron (Synced)</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal Kirim (Failed)</option>
                    </select>
                </div>
            </div>

            <!-- Search Keyword -->
            <div class="col-md-6 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm" 
                           placeholder="Cari nama pegawai, NIP, deskripsi perubahan, atau operator..." 
                           value="{{ request('q') }}">
                    @if(request('q') || request('status'))
                        <a href="{{ route('change_log.index') }}" class="btn btn-outline-secondary btn-sm" title="Hapus Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    <button class="btn btn-primary btn-sm px-3" type="submit">Cari</button>
                </div>
            </div>

            <div class="col-md-3 text-md-end text-muted small">
                Menampilkan <strong>{{ $logs->count() }}</strong> dari <strong>{{ $logs->total() }}</strong> rekam audit
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 w-100" style="width: 100% !important;">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 60px;">No</th>
                        <th style="width: 160px;">Waktu &amp; Tanggal</th>
                        <th>Pegawai / Target</th>
                        <th>Aksi &amp; Ringkasan Perubahan</th>
                        <th>Operator</th>
                        <th style="width: 150px;">Status Sinkron</th>
                        <th class="text-end pe-3" style="width: 90px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-center fw-bold text-muted ps-3">{{ $logs->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($log->pegawai_id)
                                    <div role="button" onclick="showPegawaiDetail({{ $log->pegawai_id }})" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                        <div class="avatar-initial" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($log->nama_pegawai, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary hover-underline">{{ $log->nama_pegawai }}</div>
                                            <div class="small text-muted font-monospace">{{ $log->nip ?: '-' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="fw-bold text-dark">{{ $log->nama_pegawai }}</div>
                                    <div class="small text-muted font-monospace">{{ $log->nip ?: '-' }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if(strtoupper($log->action) === 'CREATE')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">
                                            <i class="fas fa-plus me-1"></i>TAMBAH BARU
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">
                                            <i class="fas fa-pencil me-1"></i>UPDATE DATA
                                        </span>
                                    @endif
                                    <span class="small fw-semibold text-dark">{{ $log->description }}</span>
                                </div>
                                @if(!empty($log->changes) && is_array($log->changes))
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach(array_slice($log->changes, 0, 4) as $field => $val)
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.65rem;">
                                                {{ $field }}
                                            </span>
                                        @endforeach
                                        @if(count($log->changes) > 4)
                                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                                                +{{ count($log->changes) - 4 }} field lainnya
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $log->user_name }}</div>
                                <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.65rem;">
                                    {{ $log->user?->role ?? 'Petugas' }}
                                </span>
                            </td>
                            <td>
                                @if($log->sync_status === 'synced')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-circle-check me-1"></i> Tersinkron
                                    </span>
                                    @if($log->synced_at)
                                        <div class="text-muted mt-0.5" style="font-size: 0.68rem;">{{ $log->synced_at->translatedFormat('H:i:s') }}</div>
                                    @endif
                                @elseif($log->sync_status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-clock me-1"></i> Menunggu Sync
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;" title="{{ $log->sync_error }}">
                                        <i class="fas fa-triangle-exclamation me-1"></i> Gagal Kirim
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" 
                                        style="width: 32px; height: 32px; padding: 0;" 
                                        onclick="showLogDiffModal({{ $log->id }})"
                                        title="Lihat Detail Perubahan Payload">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <!-- Hidden details payload for modal -->
                                <div id="logData_{{ $log->id }}" class="d-none"
                                     data-title="{{ $log->action }} - {{ $log->nama_pegawai }}"
                                     data-user="{{ $log->user_name }}"
                                     data-time="{{ $log->created_at->translatedFormat('d F Y, H:i:s') }}"
                                     data-desc="{{ $log->description }}"
                                     data-status="{{ $log->sync_status }}"
                                     data-error="{{ $log->sync_error ?: '' }}"
                                     data-changes="{{ json_encode($log->changes) }}"
                                     data-before="{{ json_encode($log->payload_before) }}"
                                     data-after="{{ json_encode($log->payload_after) }}">
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-clock-rotate-left fs-1 text-muted opacity-25 d-block mb-3"></i>
                                <h6>Belum Ada Riwayat Perubahan</h6>
                                <p class="small text-muted mb-0">Setiap perubahan profil atau pembuatan pegawai baru akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
            </div>
            <div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Log Detail Diff -->
<div class="modal fade" id="logDiffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <h5 class="modal-title fw-bold text-dark" id="logDiffModalTitle">
                    <i class="fas fa-file-lines text-primary me-2"></i> Detail Rekam Perubahan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="logDiffModalBody">
                <!-- Injected via JavaScript -->
            </div>
            <div class="modal-footer px-4 py-3 bg-white border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function showLogDiffModal(id) {
        const holder = document.getElementById('logData_' + id);
        if (!holder) return;

        const title = holder.getAttribute('data-title');
        const user = holder.getAttribute('data-user');
        const time = holder.getAttribute('data-time');
        const desc = holder.getAttribute('data-desc');
        const status = holder.getAttribute('data-status');
        const error = holder.getAttribute('data-error');
        const changes = JSON.parse(holder.getAttribute('data-changes') || '{}');

        let statusBadge = '';
        if (status === 'synced') {
            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1"><i class="fas fa-circle-check me-1"></i> Tersinkron ke Spreadsheet</span>';
        } else if (status === 'pending') {
            statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1"><i class="fas fa-clock me-1"></i> Menunggu Antrian Sinkronisasi</span>';
        } else {
            statusBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1"><i class="fas fa-triangle-exclamation me-1"></i> Gagal Kirim: ${error}</span>`;
        }

        let changesHtml = '';
        if (Object.keys(changes).length > 0) {
            changesHtml += '<div class="table-responsive bg-white rounded-3 border p-2 mb-3"><table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>Atribut / Kolom</th><th>Nilai Baru</th></tr></thead><tbody>';
            for (const [key, val] of Object.entries(changes)) {
                changesHtml += `<tr><td class="fw-bold font-monospace small text-primary">${key}</td><td class="font-monospace small">${val !== null ? val : '<em>(kosong)</em>'}</td></tr>`;
            }
            changesHtml += '</tbody></table></div>';
        } else {
            changesHtml = '<div class="alert alert-light border small text-muted">Seluruh atribut awal berhasil disimpan secara komprehensif.</div>';
        }

        $('#logDiffModalBody').html(`
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">${title}</h6>
                        <div class="text-muted small"><i class="fas fa-user text-primary me-1"></i> Oleh: <strong>${user}</strong> &bull; <i class="fas fa-clock text-muted me-1"></i> ${time}</div>
                    </div>
                    <div>${statusBadge}</div>
                </div>
                <p class="text-secondary small mb-0">${desc}</p>
            </div>

            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-list-check text-primary me-1"></i> Nilai Field yang Diubah / Diinput:</h6>
            ${changesHtml}
        `);

        const modal = new bootstrap.Modal(document.getElementById('logDiffModal'));
        modal.show();
    }

    // Ajax Submit for Manual Sync
    $('#formSyncPending').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnSyncPendingSubmit');
        const icon = $('#iconSyncPending');

        btn.prop('disabled', true);
        icon.removeClass('fa-cloud-arrow-up').addClass('fa-spinner fa-spin');

        $.ajax({
            url: "{{ route('change_log.sync_pending') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sinkronisasi Berhasil',
                    text: res.message || 'Antrian perubahan telah berhasil disinkronkan ke Google Spreadsheet.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sinkronisasi Terkendala',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses antrian sinkronisasi.'
                });
            },
            complete: function() {
                btn.prop('disabled', false);
                icon.removeClass('fa-spinner fa-spin').addClass('fa-cloud-arrow-up');
            }
        });
    });
</script>
@endpush
