@extends('layouts.app')

@section('title', 'Backup & Restore Database | SSO KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Backup & Restore Database</h3>
        <p class="text-muted mb-0">Kelola cadangan data sistem SSO KPKNL Palembang secara manual.</p>
    </div>
    <form action="{{ route('admin.backup.create') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
            <i class="fa-solid fa-database me-1"></i> Buat Backup Baru Sekarang
        </button>
    </form>
</div>

<div class="row g-4 mb-4">
    <!-- Backup History List -->
    <div class="col-md-8">
        <div class="card-m3 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i>Daftar File Backup Database</h5>

            <div class="table-expressive-container">
                <div class="table-responsive">
                    <table class="table align-middle fs-7 mb-0">
                        <thead>
                            <tr>
                                <th>Nama File Backup</th>
                                <th>Tanggal Pembuatan</th>
                                <th>Ukuran</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td><code class="px-2 py-1 bg-light rounded text-primary fw-bold">{{ $backup['filename'] }}</code></td>
                                    <td class="text-muted">{{ $backup['date'] }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold">{{ round($backup['size'] / 1024, 2) }} KB</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.backup.download', $backup['filename']) }}" class="btn btn-tonal btn-sm rounded-pill px-3 me-1" title="Download SQL">
                                            <i class="fa-solid fa-download me-1"></i> Download
                                        </a>
                                        <form action="{{ route('admin.backup.destroy', $backup['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus file backup ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus File Backup">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada file backup database. Klik tombol "Buat Backup Baru Sekarang".</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Form -->
    <div class="col-md-4">
        <div class="card-m3 p-4 h-100" style="background-color: var(--md-sys-color-tertiary-container); color: var(--md-sys-color-on-tertiary-container);">
            <h5 class="fw-bold mb-2 text-dark" style="font-family: var(--font-heading);"><i class="fa-solid fa-rotate-left me-2 text-warning"></i>Restore Database</h5>
            <p class="fs-7 mb-3" style="opacity: 0.85;">Upload file backup berformat <code>.sql</code> untuk memulihkan data sistem.</p>

            <div class="alert alert-expressive alert-expressive-danger p-3 fs-8 mb-3 shadow-none border-0">
                <i class="fa-solid fa-triangle-exclamation me-1 fs-6"></i> 
                <div><strong>PERINGATAN:</strong> Proses restore akan menimpa seluruh data saat ini.</div>
            </div>

            <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN: Restore akan menimpa seluruh data database saat ini. Apakah Anda yakin ingin melanjutkan?');">
                @csrf
                <div class="mb-3">
                    <label for="backup_file" class="form-label fs-7 fw-bold mb-1">Pilih File SQL Backup</label>
                    <input type="file" name="backup_file" id="backup_file" class="form-control" accept=".sql,.txt" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2.5">
                    <i class="fa-solid fa-upload me-1"></i> RESTORE SEKARANG
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

