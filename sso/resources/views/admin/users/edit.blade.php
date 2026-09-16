@extends('layouts.app')

@section('title', 'Edit User | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Edit User: {{ $user->name }}</h3>
        <p class="text-muted mb-0">Perbarui informasi profil, role, status, atau assign aplikasi untuk user ini.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar User
    </a>
</div>

<div class="card-flat p-4" style="max-width: 800px;">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark fs-7">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="col-md-6">
                <label for="username" class="form-label fw-semibold text-dark fs-7">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $user->username) }}" required>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold text-dark fs-7">Email Resmi <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="col-md-6">
                <label for="role_id" class="form-label fw-bold text-dark fs-7">Role Akun <span class="text-danger">*</span></label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role_id" value="{{ $userRoleId }}">
                    <select class="form-select opacity-75" disabled style="background-color: var(--md-sys-color-surface-container);">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ $userRoleId == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} - {{ $role->description }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger fs-8 fw-semibold mt-1 d-block"><i class="fa-solid fa-lock me-1"></i> Anda tidak dapat mengubah role akun Anda sendiri.</small>
                @else
                    <select name="role_id" id="role_id" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $userRoleId) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} - {{ $role->description }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>


            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold text-dark fs-7">Password Baru (Opsional)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                <small class="text-muted fs-8">Jika diisi, wajib memenuhi aturan NIST (min 8 char, uppercase, lowercase, angka, simbol).</small>
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold text-dark fs-7">Status Akun <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select" required>
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active (Dapat Login)</option>
                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive (Non-aktif)</option>
                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended (Ditangguhkan)</option>
                </select>
            </div>

            <!-- Element nth-of-type(7): Assign Aplikasi Popup SweetAlert2 -->
            <div class="col-12 mt-4">
                <label class="form-label fw-bold text-dark fs-7 d-block mb-2">Assign Aplikasi yang Dapat Diakses</label>
                
                <div class="p-3.5 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: var(--md-sys-color-surface-container-high);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: var(--md-sys-color-primary-container);">
                            <i class="fa-solid fa-shapes fs-5"></i>
                        </div>
                        <div>
                            @php
                                $selectedAppIds = old('applications', $userAppIds);
                                $selectedApps = $applications->filter(fn($app) => in_array($app->id, $selectedAppIds));
                            @endphp
                            <span class="d-block fw-bold text-dark fs-6" id="appSummaryTitle" style="font-family: var(--font-heading);">
                                {{ count($selectedApps) }} Aplikasi Dipilih
                            </span>
                            <small class="text-muted fs-7" id="appSummaryText">
                                {{ count($selectedApps) > 0 ? implode(', ', $selectedApps->pluck('name')->toArray()) : 'Belum ada aplikasi yang di-assign.' }}
                            </small>
                        </div>
                    </div>

                    <button type="button" class="btn btn-tonal rounded-pill px-4 py-2" id="openAssignAppsModal">
                        <i class="fa-solid fa-pen-to-square me-1.5"></i> Pilih / Edit Aplikasi
                    </button>
                </div>

                <!-- Hidden inputs for POST form data -->
                <div id="hiddenAppCheckboxes" class="d-none">
                    @foreach($applications as $app)
                        <input type="checkbox" name="applications[]" value="{{ $app->id }}" id="app_{{ $app->id }}" {{ in_array($app->id, $selectedAppIds) ? 'checked' : '' }}>
                    @endforeach
                </div>
            </div>

            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-check me-1"></i> UPDATE USER
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-tonal rounded-pill px-4">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('openAssignAppsModal')?.addEventListener('click', function() {
        let appsHtml = '<div class="text-start py-2" style="max-height: 340px; overflow-y: auto;">';
        @foreach($applications as $app)
            const isChecked_{{ $app->id }} = document.getElementById('app_{{ $app->id }}')?.checked ? 'checked' : '';
            appsHtml += `
                <div class="p-3 mb-2 rounded-4 d-flex align-items-center justify-content-between" style="background-color: var(--md-sys-color-surface-container);">
                    <div class="d-flex align-items-center gap-3 ms-1">
                        <div class="p-2 rounded-circle text-primary" style="background-color: var(--md-sys-color-primary-container);">
                            <i class="fa-solid fa-cube fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ addslashes($app->name) }}</div>
                            <small class="text-muted fs-8">{{ addslashes($app->url) }}</small>
                        </div>
                    </div>
                    <div class="form-check form-switch me-2">
                        <input class="form-check-input swal-app-switch" type="checkbox" data-app-id="{{ $app->id }}" data-app-name="{{ addslashes($app->name) }}" id="swal_app_{{ $app->id }}" ${isChecked_{{ $app->id }}} style="width: 2.5em; height: 1.25em; cursor: pointer;">
                    </div>
                </div>
            `;
        @endforeach
        appsHtml += '</div>';

        Swal.fire({
            title: 'Assign Aplikasi User',
            html: appsHtml,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Simpan Pilihan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
                confirmButton: 'btn btn-primary rounded-pill px-4 me-2',
                cancelButton: 'btn btn-tonal rounded-pill px-4'
            },
            buttonsStyling: false,
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                let selectedCount = 0;
                let selectedNames = [];

                document.querySelectorAll('.swal-app-switch').forEach(el => {
                    const appId = el.getAttribute('data-app-id');
                    const appName = el.getAttribute('data-app-name');
                    const mainCheckbox = document.getElementById('app_' + appId);

                    if (mainCheckbox) {
                        mainCheckbox.checked = el.checked;
                        if (el.checked) {
                            selectedCount++;
                            selectedNames.push(appName);
                        }
                    }
                });

                const titleEl = document.getElementById('appSummaryTitle');
                const textEl = document.getElementById('appSummaryText');
                if (titleEl) titleEl.innerText = selectedCount + ' Aplikasi Dipilih';
                if (textEl) textEl.innerText = selectedNames.length > 0 ? selectedNames.join(', ') : 'Belum ada aplikasi yang di-assign.';
            }
        });
    });
</script>
@endsection

