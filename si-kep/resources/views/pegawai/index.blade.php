@extends('layouts.app')

@section('title', 'Data Kepegawaian — SIMPATIK KPKNL Palembang')
@section('hero-title', 'Direktori Data Kepegawaian')
@section('hero-subtitle', 'Pangkalan data personil resmi ASN KPKNL Palembang terintegrasi langsung dengan pangkalan data Google Spreadsheet.')

@section('content')

<!-- Header Filter & Search Box -->
<div class="card card-custom mb-4">
    <div class="card-body p-4">
        <form action="{{ route('pegawai.index') }}" method="GET" class="row g-3 align-items-end">
            <!-- Search Keyword -->
            <div class="col-lg-4">
                <label class="form-label small fw-bold text-muted text-uppercase">Pencarian Data Personil</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama, NIP, NIK, atau Jabatan..." value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" title="Reset pencarian"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </div>

            <!-- Filter Unit Kerja -->
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Unit Kerja / Seksi</label>
                <select name="unit_kerja_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Unit / Seksi --</option>
                    @foreach($unitKerjas as $u)
                        <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Golongan -->
            <div class="col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Pangkat / Golongan</label>
                <select name="pangkat_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Gol. --</option>
                    @foreach($pangkats as $pkg)
                        <option value="{{ $pkg->id }}" {{ request('pangkat_id') == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->golongan_ruang }} ({{ $pkg->nama_pangkat }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Gender -->
            <div class="col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Jenis Kelamin</label>
                <select name="gender" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Gender --</option>
                    <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-sm-6 col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary rounded-3" style="background: var(--primary-color);">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table Container Card -->
<div class="card card-custom">
    <div class="card-header-clean">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-address-book text-primary"></i>
            <span>Daftar Personil Definitif KPKNL Palembang</span>
            <span class="badge bg-primary rounded-pill px-2 ms-2">{{ $pegawais->total() }} Pegawai</span>
        </div>
        <div class="small text-muted">
            <i class="fa-solid fa-hand-pointer me-1 text-primary"></i> Klik pada baris tabel untuk melihat detail profil lengkap
        </div>
    </div>
    <div class="card-body p-0">
        @if($pegawais->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-user-slash fs-1 text-secondary mb-3"></i>
                <h5>Tidak Ada Data Pegawai</h5>
                <p class="text-muted">Data tidak ditemukan dengan kriteria pencarian atau filter yang dipilih.</p>
                <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">Reset Filter</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-custom mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="min-width: 250px;">Nama Pegawai &amp; NIP</th>
                            <th style="min-width: 200px;">Jabatan</th>
                            <th style="min-width: 170px;">Seksi / Unit Kerja</th>
                            <th style="min-width: 120px;">Gol. / Grade</th>
                            <th style="min-width: 130px;">Usia &amp; Masa Kerja</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawais as $p)
                            <tr onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat detail profil lengkap {{ $p->nama }}">
                                <td class="text-center fw-bold text-muted">{{ $p->no_urut ?: $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-initial" style="background: linear-gradient(135deg, {{ $p->jenis_kelamin == 'P' ? '#e11d48, #f43f5e' : '#0c306b, #1e40af' }});">
                                            {{ strtoupper(substr($p->nama, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $p->display_name }}</div>
                                            <div class="small text-muted font-monospace">
                                                NIP: {{ $p->nip ?: '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->nama_jabatan_raw ?: '-' }}</div>
                                    <span class="badge badge-subtle badge-navy text-uppercase" style="font-size: 0.68rem;">
                                        {{ $p->jabatan?->jenis_jabatan ?: 'PNS' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-subtle badge-slate fw-bold">
                                        {{ $p->unitKerja?->singkatan ?: ($p->unitKerja?->nama_unit ?: 'KPKNL Palembang') }}
                                    </span>
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                        {{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln di Palembang
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                            {{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}
                                        </span>
                                    </div>
                                    @if($p->job_grade)
                                        <div class="small text-muted mt-1">Grade {{ $p->job_grade }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->usia_tahun }} Thn {{ $p->usia_bulan }} Bln</div>
                                    <div class="small text-muted" style="font-size: 0.74rem;">MK: {{ $p->masa_kerja_tahun }} Thn</div>
                                </td>
                                <td class="text-center" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;" title="Lihat Profil Lengkap">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="p-3 border-top d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $pegawais->firstItem() ?? 0 }}</strong> sampai <strong>{{ $pegawais->lastItem() ?? 0 }}</strong> dari <strong>{{ $pegawais->total() }}</strong> personil
                </div>
                <div>
                    {{ $pegawais->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
