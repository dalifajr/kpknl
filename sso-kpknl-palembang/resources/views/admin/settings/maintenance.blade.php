@extends('layouts.app')

@section('title', 'Modus Pemeliharaan (Maintenance) | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Modus Pemeliharaan (Maintenance Mode)</h3>
        <p class="text-muted mb-0">Kelola status aktifkan/non-aktifkan pemeliharaan sistem SSO dan tentukan pesan pemberitahuan untuk pengguna.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card-m3 p-4">
            <h5 class="fw-bold mb-3 text-primary" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i>Pengaturan Modus Pemeliharaan
            </h5>

            <form action="{{ route('admin.settings.maintenance.update') }}" method="POST" id="maintenanceForm">
                @csrf
                <input type="hidden" name="sudo_password" id="sudo_password">
                <input type="hidden" name="maintenance_mode" id="maintenance_mode" value="{{ old('maintenance_mode', $status) }}">

                <!-- Status Banner Box -->
                <div id="statusBannerBox" class="p-3.5 rounded-4 mb-4" style="background-color: {{ old('maintenance_mode', $status) === 'active' ? 'var(--md-sys-color-error-container)' : 'var(--md-sys-color-surface-container-high)' }};">
                    <div class="d-flex align-items-center gap-3">
                        <div id="statusIconCircle" class="p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: {{ old('maintenance_mode', $status) === 'active' ? '#BA1A1A' : 'var(--md-sys-color-primary-container)' }}; color: #FFFFFF;">
                            <i id="statusIcon" class="fa-solid {{ old('maintenance_mode', $status) === 'active' ? 'fa-triangle-exclamation' : 'fa-check' }} fs-5"></i>
                        </div>
                        <div>
                            <span id="statusTitleText" class="d-block fw-bold fs-6" style="font-family: var(--font-heading); color: {{ old('maintenance_mode', $status) === 'active' ? '#410002' : 'var(--md-sys-color-on-surface)' }};">
                                Status: {{ old('maintenance_mode', $status) === 'active' ? 'SISTEM DALAM PEMELIHARAAN (AKTIF)' : 'Sistem Berjalan Normal (Non-Aktif)' }}
                            </span>
                            <small id="statusSubText" class="{{ old('maintenance_mode', $status) === 'active' ? 'text-danger' : 'text-muted' }} fs-7">
                                {{ old('maintenance_mode', $status) === 'active' ? 'Hanya Superadmin yang dapat mengakses sistem SSO.' : 'Seluruh pengguna dan aplikasi OAuth dapat mengakses SSO secara normal.' }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Modern M3E Toggle Switch -->
                <div class="p-3.5 rounded-4 mb-4 d-flex align-items-center justify-content-between border" style="background-color: var(--md-sys-color-surface-container-low);">
                    <div class="pe-3">
                        <label for="maintenance_mode_toggle" class="form-label fw-bold text-dark fs-6 mb-1 d-block" style="font-family: var(--font-heading); cursor: pointer;">
                            Modus Pemeliharaan (Maintenance Mode)
                        </label>
                        <small class="text-muted fs-7 d-block">
                            Geser ke kanan untuk mengunci SSO & memblokir login pengguna biasa.
                        </small>
                    </div>
                    <div class="form-check form-switch m-0 p-0">
                        <input class="form-check-input" type="checkbox" id="maintenance_mode_toggle" {{ old('maintenance_mode', $status) === 'active' ? 'checked' : '' }} style="width: 3.2em; height: 1.6em; cursor: pointer;">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="maintenance_message" class="form-label fw-bold text-dark fs-7">Pesan Pemberitahuan Pemeliharaan <span class="text-danger">*</span></label>
                    <textarea name="maintenance_message" id="maintenance_message" rows="4" class="form-control" placeholder="Tuliskan alasan pemeliharaan atau estimasi waktu perbaikan..." required>{{ old('maintenance_message', $message) }}</textarea>
                    <small class="text-muted fs-8 mt-1 d-block">Pesan ini akan ditampilkan pada halaman login dan layar maintenance bagi pengguna.</small>
                </div>

                <div class="d-flex gap-2 border-top pt-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold" id="btnSaveMaintenance">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> SIMPAN PERUBAHAN MAINTENANCE
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-tonal rounded-pill px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Card for Maintenance Screen -->
    <div class="col-md-5">
        <div class="card-m3 p-4 bg-light">
            <h5 class="fw-bold mb-3 text-dark" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-eye me-2 text-primary"></i>Preview Tampilan Maintenance
            </h5>
            <p class="text-muted fs-8 mb-3">Tampilan yang akan dilihat oleh pengguna biasa saat mengakses aplikasi ketika maintenance mode aktif:</p>

            <div class="p-4 rounded-4 shadow-sm text-center bg-white border">
                <div class="p-3 rounded-circle text-danger bg-danger-subtle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-screwdriver-wrench fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1" style="font-family: var(--font-heading);">Sistem Dalam Pemeliharaan</h5>
                <p class="text-muted fs-8 mb-3">SSO KPKNL PALEMBANG</p>

                <div class="p-3 rounded-3 text-start bg-light fs-8 text-secondary mb-3" id="previewMsg">
                    {{ $message }}
                </div>

                <span class="btn btn-primary btn-sm rounded-pill px-3 disabled opacity-75 fs-8">
                    <i class="fa-solid fa-user-shield me-1"></i> Login Superadmin
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const toggle = document.getElementById('maintenance_mode_toggle');
    const hiddenModeInput = document.getElementById('maintenance_mode');

    // UI elements
    const statusBannerBox = document.getElementById('statusBannerBox');
    const statusIconCircle = document.getElementById('statusIconCircle');
    const statusIcon = document.getElementById('statusIcon');
    const statusTitleText = document.getElementById('statusTitleText');
    const statusSubText = document.getElementById('statusSubText');

    toggle?.addEventListener('change', function() {
        if (this.checked) {
            hiddenModeInput.value = 'active';
            statusBannerBox.style.backgroundColor = 'var(--md-sys-color-error-container)';
            statusIconCircle.style.backgroundColor = '#BA1A1A';
            statusIcon.className = 'fa-solid fa-triangle-exclamation fs-5';
            statusTitleText.style.color = '#410002';
            statusTitleText.innerText = 'Status: SISTEM DALAM PEMELIHARAAN (AKTIF)';
            statusSubText.className = 'text-danger fs-7';
            statusSubText.innerText = 'Hanya Superadmin yang dapat mengakses sistem SSO.';
        } else {
            hiddenModeInput.value = 'inactive';
            statusBannerBox.style.backgroundColor = 'var(--md-sys-color-surface-container-high)';
            statusIconCircle.style.backgroundColor = 'var(--md-sys-color-primary-container)';
            statusIcon.className = 'fa-solid fa-check fs-5';
            statusTitleText.style.color = 'var(--md-sys-color-on-surface)';
            statusTitleText.innerText = 'Status: Sistem Berjalan Normal (Non-Aktif)';
            statusSubText.className = 'text-muted fs-7';
            statusSubText.innerText = 'Seluruh pengguna dan aplikasi OAuth dapat mengakses SSO secara normal.';
        }
    });

    document.getElementById('maintenance_message')?.addEventListener('input', function(e) {
        document.getElementById('previewMsg').innerText = e.target.value || 'Pesan pemeliharaan...';
    });

    // Form submission with SweetAlert2 Password Verification
    document.getElementById('maintenanceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Verifikasi Password Superadmin',
            text: 'Masukkan password akun Superadmin Anda ({{ auth()->user()->username }}) untuk mengonfirmasi perubahan modus pemeliharaan.',
            input: 'password',
            inputAttributes: {
                autocapitalize: 'off',
                placeholder: 'Masukkan password Anda...',
                required: true,
                style: 'font-family: var(--font-body); font-size: 0.95rem;'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Verifikasi & Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
                confirmButton: 'btn btn-primary rounded-pill px-4 me-2',
                cancelButton: 'btn btn-tonal rounded-pill px-4'
            },
            buttonsStyling: false,
            focusConfirm: false,
            preConfirm: (password) => {
                if (!password) {
                    Swal.showValidationMessage('Password verifikasi wajib diisi.');
                }
                return password;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                document.getElementById('sudo_password').value = result.value;
                form.submit();
            }
        });
    });
</script>
@endsection

