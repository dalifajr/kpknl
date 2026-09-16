@extends('layouts.app')

@section('title', 'Manajemen User | SSO KPKNL Palembang')

@section('content')
<style>
    /* Expressive Search & Filter Component */
    .m3e-filter-card {
        background: linear-gradient(145deg, var(--md-sys-color-surface-container-low), var(--md-sys-color-surface-container));
        border: 1px solid var(--md-sys-color-outline-variant);
        border-radius: var(--md-shape-corner-xl);
    }
    
    .m3e-input, .m3e-select {
        background-color: var(--md-sys-color-surface-container-highest) !important;
        border: 1px solid transparent !important;
        color: var(--md-sys-color-on-surface);
        transition: all 0.25s cubic-bezier(0.2, 0, 0, 1) !important;
    }
    
    .m3e-input:focus, .m3e-select:focus {
        background-color: var(--md-sys-color-surface-container-lowest) !important;
        border-color: var(--md-sys-color-primary) !important;
        box-shadow: 0 0 0 4px rgba(59, 95, 229, 0.15) !important;
    }
    
    .m3e-input::placeholder {
        color: var(--md-sys-color-on-surface-variant);
        opacity: 0.8;
    }
    
    .m3e-chip {
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .m3e-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
</style>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Manajemen User SSO</h3>
        <p class="text-muted mb-0">Superadmin memiliki kendali penuh untuk menambah, mengedit role, meng-assign aplikasi, dan menonaktifkan user.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-tonal rounded-pill px-4 py-2" id="btnOpenImportModal">
            <i class="fa-solid fa-file-excel me-1.5 text-success"></i> Import Excel
        </button>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-pill px-4 py-2">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
        </a>
    </div>
</div>

<!-- Filter Card M3 Expressive -->
<div class="card-m3 p-4 mb-4 m3e-filter-card shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fs-7 fw-bold text-dark text-uppercase" style="letter-spacing: 0.08em; font-family: var(--font-heading);">
            <i class="fa-solid fa-sliders me-1.5 text-primary"></i> Filter & Pencarian Data
        </span>
        @if(request()->anyFilled(['search', 'role', 'status']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-tonal btn-sm rounded-pill px-3 py-1 fs-8 text-danger fw-bold" style="background-color: var(--md-sys-color-error-container);">
                <i class="fa-solid fa-xmark me-1"></i> Reset Filter
            </a>
        @endif
    </div>

    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-4">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 18px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; pointer-events: none;"></i>
                <input type="text" name="search" class="form-control rounded-pill m3e-input ps-5 pe-4 py-2" placeholder="Cari nama, username, atau email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="position-relative">
                <i class="fa-solid fa-user-tag position-absolute text-muted" style="left: 18px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; pointer-events: none; z-index: 4;"></i>
                <select name="role" class="form-select rounded-pill m3e-select ps-5 pe-4 py-2">
                    <option value="">-- Semua Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="position-relative">
                <i class="fa-solid fa-circle-dot position-absolute text-muted" style="left: 18px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; pointer-events: none; z-index: 4;"></i>
                <select name="status" class="form-select rounded-pill m3e-select ps-5 pe-4 py-2">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive (Nonaktif)</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-filter me-1.5"></i> Terapkan
            </button>
        </div>
    </form>

    <!-- Quick Filter Chips -->
    <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top border-secondary-subtle flex-wrap">
        <span class="fs-8 fw-bold text-muted me-2"><i class="fa-solid fa-bolt text-warning me-1"></i> Filter Cepat:</span>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ !request()->anyFilled(['role', 'status', 'search']) ? 'btn-primary' : 'btn-tonal' }}">
            Semua User
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'active']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('status') == 'active' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-circle-check me-1 text-success"></i> Aktif
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'inactive']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('status') == 'inactive' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-circle-xmark me-1 text-danger"></i> Nonaktif
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'superadmin']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('role') == 'superadmin' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-shield-halved me-1"></i> Superadmin
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="btn btn-sm rounded-pill px-3 py-1 fs-8 fw-bold m3e-chip {{ request('role') == 'admin' ? 'btn-primary' : 'btn-tonal' }}">
            <i class="fa-solid fa-user-gear me-1"></i> Admin Aplikasi
        </a>
    </div>
</div>

<!-- Users Table M3 Expressive -->
<div class="table-expressive-container p-3 mb-4">
    <div class="table-responsive">
        <table class="table align-middle fs-7 mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama & Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aplikasi Di-assign</th>
                    <th>Login Terakhir</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ $user->name }}</div>
                            <div class="text-muted fs-8">{{ $user->email }}</div>
                        </td>
                        <td><code>@ {{ $user->username }}</code></td>
                        <td>
                            <span class="badge badge-role badge-{{ $user->roles->first()?->name ?? 'user' }}">
                                {{ $user->roles->first()?->display_name ?? 'User' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $user->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-3 py-1 fs-8 fw-bold">
                                {{ strtoupper($user->status) }}
                            </span>
                        </td>
                        <td>
                            @forelse($user->applications as $app)
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill me-1 mb-1 fs-8">{{ $app->name }}</span>
                            @empty
                                <span class="text-muted fs-8">-</span>
                            @endforelse
                        </td>
                        <td class="text-muted">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum Pernah' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-tonal btn-sm rounded-circle me-1" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Edit User">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline form-delete-user" data-user-name="{{ $user->name }}" data-user-username="{{ $user->username }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus User">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Tidak ada data user yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-2">
        {{ $users->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('btnOpenImportModal')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Import User dari Excel / CSV',
            html: `
                <div class="text-start fs-7 mb-3">
                    <div class="p-3 rounded-4 mb-3" style="background-color: var(--md-sys-color-surface-container-high);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <strong class="d-block text-dark">Format File Excel / CSV</strong>
                                <small class="text-muted fs-8">Gunakan format file yang sesuai agar validasi sistem berjalan lancar.</small>
                            </div>
                            <a href="{{ route('admin.users.import-template') }}" class="btn btn-tonal btn-sm rounded-pill px-3 py-1.5 fw-bold text-success">
                                <i class="fa-solid fa-download me-1"></i> Download Format Import
                            </a>
                        </div>
                    </div>

                    <label for="swal_excel_file" class="form-label fw-bold text-dark fs-7">Pilih File Excel / CSV <span class="text-danger">*</span></label>
                    <input type="file" id="swal_excel_file" class="form-control mb-2" accept=".csv,.txt,.xlsx,.xls" required>
                    <small class="text-muted fs-8">Format didukung: <code>.csv</code>, <code>.xlsx</code>, <code>.xls</code>, <code>.txt</code> (Maksimal 5 MB)</small>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-upload me-1.5"></i> Mulai Validasi & Import',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal2-m3e',
                title: 'swal2-m3e-title',
                confirmButton: 'btn btn-primary rounded-pill px-4 me-2',
                cancelButton: 'btn btn-tonal rounded-pill px-4'
            },
            buttonsStyling: false,
            focusConfirm: false,
            preConfirm: () => {
                const fileInput = document.getElementById('swal_excel_file');
                if (!fileInput || !fileInput.files.length) {
                    Swal.showValidationMessage('Silakan pilih file Excel / CSV terlebih dahulu.');
                    return false;
                }
                return fileInput.files[0];
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const file = result.value;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                Swal.fire({
                    title: 'Menganalisis File...',
                    text: 'Memeriksa validitas baris data dan duplikasi username...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("admin.users.import-upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async res => {
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch(e) {
                        console.error('Server non-JSON response:', text);
                        data = { success: false, message: 'Tanggapan server: ' + text.replace(/<[^>]*>?/gm, '').substring(0, 180) };
                    }

                    if (!res.ok || !data.success) {
                        let errMsg = data.message || 'Gagal memproses file import.';
                        if (data.errors) {
                            if (Array.isArray(data.errors)) {
                                errMsg += '<br><br>' + data.errors.map(e => `• ${e}`).join('<br>');
                            } else if (typeof data.errors === 'object') {
                                errMsg += '<br><br>' + Object.values(data.errors).flat().map(e => `• ${e}`).join('<br>');
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi File Gagal',
                            html: `<div class="text-start fs-8 text-danger">${errMsg}</div>`,
                            confirmButtonText: 'Tutup',
                            customClass: {
                                popup: 'swal2-m3e',
                                confirmButton: 'btn btn-primary rounded-pill px-4'
                            },
                            buttonsStyling: false
                        });
                    } else if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                })

                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan Network',
                        text: 'Gagal mengunggah file. Pastikan koneksi dan ukuran file sesuai.',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: 'swal2-m3e',
                            confirmButton: 'btn btn-primary rounded-pill px-4'
                        },
                        buttonsStyling: false
                    });
                });

            }
        });
    });

    // SweetAlert2 konfirmasi penghapusan user
    document.querySelectorAll('.form-delete-user').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const userName = this.getAttribute('data-user-name');
            const userUsername = this.getAttribute('data-user-username');

            Swal.fire({
                title: 'Hapus User SSO?',
                html: `Apakah Anda yakin ingin menghapus user <strong>${userName}</strong> (@<code>${userUsername}</code>)?<br><small class="text-danger">User yang dihapus tidak dapat mengakses sistem SSO.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash me-1.5"></i> Ya, Hapus User',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal2-m3e',
                    title: 'swal2-m3e-title',
                    confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
                    cancelButton: 'btn btn-tonal rounded-pill px-4'
                },
                buttonsStyling: false,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
