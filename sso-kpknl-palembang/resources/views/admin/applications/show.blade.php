@extends('layouts.app')

@section('title', 'Detail Aplikasi - ' . $application->name)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">{{ $application->name }}</h3>
        <p class="text-muted mb-0">Detail kredensial OAuth2 SSO dan pengelolaan hak akses user.</p>
    </div>
    <a href="{{ route('admin.applications.index') }}" class="btn btn-tonal rounded-pill px-4 py-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Aplikasi
    </a>
</div>

<!-- OAuth Credentials Card M3 Expressive -->
<div class="card-m3 p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary-subtle pb-3">
        <h5 class="fw-bold mb-0 text-primary" style="font-family: var(--font-heading);"><i class="fa-solid fa-key me-2"></i>Kredensial Integrasi OAuth2 SSO</h5>
        <form action="{{ route('admin.applications.regenerate-secret', $application->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuat Client Secret baru? Aplikasi terintegrasi wajib mengupdate client secret baru.');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-bold">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Reset Client Secret
            </button>
        </form>
    </div>

    <div class="row g-3 fs-7">
        <div class="col-md-6">
            <label class="fw-bold text-muted d-block mb-1">Client ID</label>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" value="{{ $application->client_id }}" readonly id="clientId">
                <button class="btn btn-tonal" onclick="navigator.clipboard.writeText('{{ $application->client_id }}'); Swal.fire({icon:'success', title:'Tersalin!', text:'Client ID berhasil disalin', timer:1500, showConfirmButton:false, customClass:{popup:'swal2-m3e'}});"><i class="fa-solid fa-copy me-1"></i> Salin</button>
            </div>
        </div>

        <div class="col-md-6">
            <label class="fw-bold text-muted d-block mb-1">Client Secret</label>
            <div class="input-group">
                <input type="password" class="form-control font-monospace" value="{{ $application->client_secret }}" readonly id="clientSecret">
                <button class="btn btn-tonal" id="toggleSecret"><i class="fa-solid fa-eye" id="secretEye"></i></button>
                <button class="btn btn-tonal" onclick="navigator.clipboard.writeText('{{ $application->client_secret }}'); Swal.fire({icon:'success', title:'Tersalin!', text:'Client Secret berhasil disalin', timer:1500, showConfirmButton:false, customClass:{popup:'swal2-m3e'}});"><i class="fa-solid fa-copy me-1"></i> Salin</button>
            </div>
        </div>

        <div class="col-md-6">
            <label class="fw-bold text-muted d-block mb-1">URL Utama Application</label>
            <input type="text" class="form-control" value="{{ $application->url }}" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-bold text-muted d-block mb-1">OAuth Redirect URI</label>
            <input type="text" class="form-control" value="{{ $application->redirect_uri }}" readonly>
        </div>
    </div>
</div>

<!-- Assign Users Section (div:nth-of-type(3)) -->
<div class="row g-4">
    @php
        $isLelang = str_contains($application->slug, 'lelang') || str_contains($application->slug, 'peminjam');
    @endphp

    <!-- Assigned Users List (div:nth-of-type(3) > div:nth-of-type(1)) -->
    <div class="col-md-8">
        <div class="card-m3 p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-users me-2 text-primary"></i>User Terdaftar di Aplikasi Ini ({{ $assignedUsers->count() }})
                </h5>
                @if($isLelang)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill fs-8">
                        <i class="fa-solid fa-gavel me-1"></i> Peran Khusus: Admin, Pelelang, Peminjam
                    </span>
                @endif
            </div>

            <div class="table-expressive-container">
                <div class="table-responsive">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Nama User</th>
                                <th>Username</th>
                                <th>{{ $isLelang ? 'Role Aplikasi (Lelang)' : 'Role' }}</th>
                                <th>Di-assign Tanggal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedUsers as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark fs-6" style="font-family: var(--font-heading);">{{ $user->name }}</div>
                                    </td>
                                    <td><code class="px-2 py-0.5 bg-light rounded text-primary">{{ $user->username }}</code></td>
                                    <td>
                                        @if($isLelang)
                                            @php
                                                $appRole = $user->pivot->role ?? 'peminjam';
                                            @endphp
                                            <form action="{{ route('admin.applications.update-user-role', ['application' => $application->id, 'user' => $user->id]) }}" method="POST" class="d-inline-flex align-items-center gap-1">
                                                @csrf
                                                @method('PUT')
                                                <select name="role" class="form-select form-select-sm py-1 px-2.5 rounded-pill border-0 fw-bold fs-8 shadow-xs" onchange="this.form.submit()" style="width: auto; cursor: pointer; background-color: {{ $appRole === 'admin' ? '#e0e7ff; color: #1e3a8a;' : ($appRole === 'pelelang' ? '#fef3c7; color: #92400e;' : '#dcfce7; color: #166534;') }}" title="Klik untuk mengubah role pada aplikasi peminjaman">
                                                    <option value="admin" {{ $appRole === 'admin' ? 'selected' : '' }}>🛡️ Admin</option>
                                                    <option value="pelelang" {{ $appRole === 'pelelang' ? 'selected' : '' }}>🔨 Pelelang</option>
                                                    <option value="peminjam" {{ $appRole === 'peminjam' ? 'selected' : '' }}>📋 Peminjam</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="badge badge-role badge-{{ $user->roles->first()?->name ?? 'user' }}">
                                                {{ $user->roles->first()?->display_name ?? 'User' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $user->pivot->created_at ? $user->pivot->created_at->format('d/m/Y') : '-' }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.applications.revoke-user', ['application' => $application->id, 'user' => $user->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Cabut akses user ini dari aplikasi?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-bold" title="Cabut Akses">
                                                <i class="fa-solid fa-user-minus me-1"></i> Cabut
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada user yang di-assign ke aplikasi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Assign Users Form (div:nth-of-type(3) > div:nth-of-type(2)) M3 Expressive -->
    <div class="col-md-4">
        <div class="card-m3 p-4 h-100">
            <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Assign User Baru</h5>

            @if($unassignedUsers->count() > 0)
                <form action="{{ route('admin.applications.assign-users', $application->id) }}" method="POST">
                    @csrf
                    @if($isLelang)
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold mb-1 text-dark">Pilih Role Aplikasi</label>
                            <select name="role" class="form-select rounded-3 py-2 fw-semibold fs-7" required>
                                <option value="admin">🛡️ Admin (Administrator Arsip / Seksi HI)</option>
                                <option value="peminjam" selected>📋 Peminjam (Pegawai / Peminjam Berkas)</option>
                                <option value="pelelang">🔨 Pelelang (Pejabat Lelang)</option>
                            </select>
                            <small class="text-muted fs-8 mt-1 d-block">User yang dipilih akan memiliki peran ini di aplikasi peminjaman.</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold mb-1">Pilih User untuk Di-assign</label>
                        <select name="user_ids[]" class="form-select p-2.5" multiple style="height: 200px; border-radius: var(--md-shape-corner-md);" required>
                            @foreach($unassignedUsers as $unUser)
                                <option value="{{ $unUser->id }}" class="py-1 px-2 mb-1 rounded">
                                    {{ $unUser->name }} ({{ $unUser->username }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted fs-8 mt-1.5 d-block">Tahan tombol <strong>Ctrl</strong> (Windows) / <strong>Cmd</strong> (Mac) untuk memilih beberapa user sekaligus.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2.5 mt-2">
                        <i class="fa-solid fa-plus me-1"></i> ASSIGN USER TERPILIH
                    </button>
                </form>
            @else
                <div class="text-center py-5 text-muted fs-7">
                    <div class="p-3 rounded-circle text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px; background-color: var(--md-sys-color-tertiary-container);">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2">Semua User Telah Di-assign</h6>
                    <p class="mb-0 fs-8">Seluruh user aktif dalam sistem telah memiliki akses ke aplikasi ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('toggleSecret')?.addEventListener('click', function() {
        const input = document.getElementById('clientSecret');
        const eye = document.getElementById('secretEye');
        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.remove('fa-eye');
            eye.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            eye.classList.remove('fa-eye-slash');
            eye.classList.add('fa-eye');
        }
    });
</script>
@endsection

