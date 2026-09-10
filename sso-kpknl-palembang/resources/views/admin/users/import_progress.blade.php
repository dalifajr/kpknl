@extends('layouts.app')

@section('title', 'Progress Import User Excel | SSO KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Progress Import User Excel</h3>
        <p class="text-muted mb-0">Memproses dan mengintegrasikan data pengguna dari file Excel ke sistem SSO.</p>
    </div>
    <div class="d-flex gap-2" id="actionButtonsHeader">
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 py-2 fw-bold" id="btnCancelImport">
            <i class="fa-solid fa-ban me-1.5"></i> Batal Import
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-tonal btn-sm rounded-pill px-4 py-2 d-none" id="btnBackToUsers">
            <i class="fa-solid fa-arrow-left me-1.5"></i> Kembali ke Manajemen User
        </a>
    </div>
</div>

<!-- Progress Card -->
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card-m3 p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2.5 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-spinner fa-spin fs-5" id="statusSpinner"></i>
                    </div>
                    <div>
                        <span class="d-block fw-bold fs-6 text-dark" style="font-family: var(--font-heading);" id="statusTitle">
                            Memproses Data Import...
                        </span>
                        <small class="text-muted fs-8" id="statusSubtext">Estimasi sisa waktu: <strong text-primary id="etaText">Menghitung...</strong></small>
                    </div>
                </div>
                <span class="fs-4 fw-bold text-primary" style="font-family: var(--font-heading);" id="percentageText">0%</span>
            </div>

            <!-- Progress Bar -->
            <div class="progress rounded-pill mb-4" style="height: 14px; background-color: var(--md-sys-color-surface-container-high);">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" id="progressBar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <!-- Metrics Row -->
            <div class="row g-3 text-center border-top pt-3">
                <div class="col-md-3 col-6">
                    <small class="text-muted fs-8 text-uppercase d-block fw-semibold mb-0.5">Total Baris</small>
                    <span class="fw-bold fs-5 text-dark" id="metricTotal">{{ $job['total_rows'] ?? 0 }}</span>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted fs-8 text-uppercase d-block fw-semibold mb-0.5 text-success">Berhasil</small>
                    <span class="fw-bold fs-5 text-success" id="metricSuccess">{{ $job['success_count'] ?? 0 }}</span>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted fs-8 text-uppercase d-block fw-semibold mb-0.5 text-warning">Dilewati (Duplikat)</small>
                    <span class="fw-bold fs-5 text-warning" id="metricSkipped">{{ $job['skipped_count'] ?? 0 }}</span>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted fs-8 text-uppercase d-block fw-semibold mb-0.5 text-danger">Gagal</small>
                    <span class="fw-bold fs-5 text-danger" id="metricFailed">{{ $job['failed_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Log Stream Box -->
<div class="card-m3 p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-terminal me-2 text-primary"></i>Live Import Log Stream
        </h6>
        <span class="badge rounded-pill bg-light text-secondary border fs-8">Real-Time Update</span>
    </div>

    <div class="p-3 rounded-4 font-monospace fs-8 border overflow-y-auto" id="logBox" style="height: 280px; background-color: var(--md-sys-color-surface-container-high); color: var(--md-sys-color-on-surface); font-family: 'Consolas', 'Courier New', monospace;">
        <div class="text-muted mb-1">[INFO] Memulai sinkronisasi proses import job #{{ $job['job_id'] }}...</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const jobId = @json($job['job_id']);
    let isProcessing = false;
    let isFinished = false;
    let isDuplicatePromptActive = false;

    const progressBar = document.getElementById('progressBar');
    const percentageText = document.getElementById('percentageText');
    const statusTitle = document.getElementById('statusTitle');
    const statusSubtext = document.getElementById('statusSubtext');
    const etaText = document.getElementById('etaText');
    const statusSpinner = document.getElementById('statusSpinner');

    const metricTotal = document.getElementById('metricTotal');
    const metricSuccess = document.getElementById('metricSuccess');
    const metricSkipped = document.getElementById('metricSkipped');
    const metricFailed = document.getElementById('metricFailed');
    const logBox = document.getElementById('logBox');

    const btnCancelImport = document.getElementById('btnCancelImport');
    const btnBackToUsers = document.getElementById('btnBackToUsers');

    function renderLogs(logs) {
        if (!logs || !logs.length) return;
        let html = '';
        logs.forEach(item => {
            let badgeColor = 'text-secondary';
            if (item.type === 'success') badgeColor = 'text-success font-weight-bold';
            if (item.type === 'error') badgeColor = 'text-danger font-weight-bold';
            if (item.type === 'warning') badgeColor = 'text-warning font-weight-bold';

            html += `<div class="mb-1"><span class="opacity-50">[${item.time}]</span> <span class="${badgeColor}">${item.message}</span></div>`;
        });
        logBox.innerHTML = html;
        logBox.scrollTop = logBox.scrollHeight;
    }

    function updateUI(data) {
        if (!data) return;

        progressBar.style.width = data.percentage + '%';
        progressBar.setAttribute('aria-valuenow', data.percentage);
        percentageText.innerText = data.percentage + '%';

        metricTotal.innerText = data.total_rows;
        metricSuccess.innerText = data.success_count;
        metricSkipped.innerText = data.skipped_count;
        metricFailed.innerText = data.failed_count;
        etaText.innerText = data.eta;

        renderLogs(data.logs);

        if (data.status === 'completed') {
            isFinished = true;
            statusTitle.innerText = 'Import Selesai!';
            statusSubtext.innerHTML = '<span class="text-success font-weight-bold"><i class="fa-solid fa-circle-check me-1"></i> Seluruh data telah selesai diproses.</span>';
            statusSpinner.className = 'fa-solid fa-circle-check fs-4 text-success';
            progressBar.className = 'progress-bar bg-success';
            btnCancelImport.classList.add('d-none');
            btnBackToUsers.classList.remove('d-none');
        } else if (data.status === 'cancelled') {
            isFinished = true;
            statusTitle.innerText = 'Import Dibatalkan';
            statusSubtext.innerHTML = '<span class="text-danger font-weight-bold"><i class="fa-solid fa-circle-xmark me-1"></i> Proses import telah dibatalkan.</span>';
            statusSpinner.className = 'fa-solid fa-ban fs-4 text-danger';
            progressBar.className = 'progress-bar bg-danger';
            btnCancelImport.classList.add('d-none');
            btnBackToUsers.classList.remove('d-none');
        } else if (data.status === 'paused_duplicate' && !isDuplicatePromptActive) {
            promptDuplicateResolution(data);
        }
    }

    function promptDuplicateResolution(data) {
        isDuplicatePromptActive = true;
        const count = data.duplicates ? data.duplicates.length : 0;

        Swal.fire({
            title: 'Terdeteksi Data Duplikat!',
            html: `
                <div class="text-start fs-7 mb-3">
                    <p class="mb-2">Sistem menemukan <strong>${count} data duplikat</strong> yang sudah terdaftar di sistem SSO (Username atau Email) atau di dalam file import.</p>
                    <div class="p-2.5 rounded-3 border bg-light text-danger fs-8 mb-3" style="max-height: 120px; overflow-y: auto;">
                        ${data.duplicates.map(d => `<div>• Baris #${d.row_num}: <strong>'${d.username}'</strong> (${d.reason})</div>`).join('')}
                    </div>
                    <p class="mb-0 text-dark fw-bold">Pilih tindakan penyelesaian untuk melanjutkan import:</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Ubah Username & Email (_1, _2)',
            denyButtonText: '<i class="fa-solid fa-forward me-1"></i> Lewati User Duplikat',
            cancelButtonText: 'Batal Import',
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
                confirmButton: 'btn btn-primary rounded-pill px-3 py-2 text-start fs-8 mb-2 me-1',
                denyButton: 'btn btn-tonal rounded-pill px-3 py-2 text-start fs-8 mb-2 me-1',
                cancelButton: 'btn btn-danger rounded-pill px-3 py-2 fs-8'
            },
            buttonsStyling: false,
            allowOutsideClick: false
        }).then((result) => {
            isDuplicatePromptActive = false;
            let action = 'cancel';
            if (result.isConfirmed) {
                action = 'suffix';
            } else if (result.isDenied) {
                action = 'skip';
            }

            fetch(`{{ url('/admin/users/import-resolve') }}/${jobId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ action: action })
            })
            .then(res => res.json())
            .then(resData => {
                updateUI(resData);
                if (action !== 'cancel' && !isFinished) {
                    processNextStep();
                }
            });
        });
    }

    function processNextStep() {
        if (isProcessing || isFinished || isDuplicatePromptActive) return;

        isProcessing = true;
        fetch(`{{ url('/admin/users/import-step') }}/${jobId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ batch_size: 5 })
        })
        .then(res => res.json())
        .then(data => {
            isProcessing = false;
            updateUI(data);

            if (data.status === 'processing' && !isFinished) {
                setTimeout(processNextStep, 300);
            }
        })
        .catch(err => {
            isProcessing = false;
            console.error('Import Step Error:', err);
            setTimeout(processNextStep, 2000);
        });
    }

    // Cancel Import Event
    btnCancelImport?.addEventListener('click', function() {
        Swal.fire({
            title: 'Batalkan Proses Import?',
            text: 'Tindakan ini akan menghentikan pengimporan data user yang tersisa.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Lanjutkan Import',
            customClass: {
                popup: 'swal2-m3e',
                confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
                cancelButton: 'btn btn-tonal rounded-pill px-4'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/admin/users/import-cancel') }}/${jobId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(resData => {
                    isFinished = true;
                    statusTitle.innerText = 'Import Dibatalkan';
                    statusSubtext.innerHTML = '<span class="text-danger font-weight-bold">Proses import dibatalkan.</span>';
                    progressBar.className = 'progress-bar bg-danger';
                    btnCancelImport.classList.add('d-none');
                    btnBackToUsers.classList.remove('d-none');
                });
            }
        });
    });

    // Initial Status Check & Auto-start Loop
    document.addEventListener('DOMContentLoaded', function() {
        fetch(`{{ url('/admin/users/import-status') }}/${jobId}`)
            .then(res => res.json())
            .then(data => {
                updateUI(data);
                if (data.status === 'processing') {
                    processNextStep();
                }
            });
    });
</script>
@endsection
