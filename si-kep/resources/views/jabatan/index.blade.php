@extends('layouts.app')

@section('title', 'Struktur & Jabatan — SI-KEP KPKNL Palembang')
@section('hero-title', 'Struktur & Formasi Jabatan')
@section('hero-subtitle', 'Pemetaan aparatur berdasarkan jenjang struktural, fungsional tertentu (Pelelang & Penilai), pelaksana, serta hierarki kelas jabatan (grading).')

@section('content')

<!-- 4 Main KPI Cards in 1 Horizontal Row (Struktural, Fungsional, Pelaksana, Grading) -->
<div class="row g-3 mb-4">
    <!-- 1. Pejabat Struktural -->
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary card-clickable transition-all" onclick="showAggregateModal('kategori_jabatan', 'struktural', 'Pejabat Struktural Definitif')" title="Klik untuk melihat daftar pejabat struktural">
            <div class="card-body p-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pejabat Struktural</small>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-user-tie" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-dark mb-0">{{ $struktural->count() }}</h3>
                    <span class="text-muted small">Pejabat Definitif</span>
                </div>
                <small class="text-muted mt-1" style="font-size: 0.72rem;">Kepala Kantor &amp; Kepala Seksi/Kasubbag</small>
            </div>
        </div>
    </div>

    <!-- 2. Fungsional Tertentu -->
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info card-clickable transition-all" onclick="showAggregateModal('kategori_jabatan', 'fungsional', 'Pejabat Fungsional Tertentu')" title="Klik untuk melihat daftar fungsional tertentu">
            <div class="card-body p-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Fungsional Tertentu</small>
                    <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-gavel" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-info mb-0">{{ $fungsional->count() }}</h3>
                    <span class="text-muted small">Pejabat Fungsional</span>
                </div>
                <small class="text-muted mt-1" style="font-size: 0.72rem;">Pelelang Ahli &amp; Penilai Pemerintah Ahli</small>
            </div>
        </div>
    </div>

    <!-- 3. Staf Pelaksana -->
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success card-clickable transition-all" onclick="showAggregateModal('kategori_jabatan', 'pelaksana', 'Aparatur Staf Pelaksana')" title="Klik untuk melihat daftar staf pelaksana">
            <div class="card-body p-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Staf Pelaksana</small>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-users-gear" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-success mb-0">{{ $pelaksana->count() }}</h3>
                    <span class="text-muted small">Aparatur Pelaksana</span>
                </div>
                <small class="text-muted mt-1" style="font-size: 0.72rem;">Pengolah Data, Bendahara, &amp; Staf Teknis</small>
            </div>
        </div>
    </div>

    <!-- 4. Sebaran Grading -->
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning card-clickable transition-all" onclick="showAggregateModal('total_pegawai', '', 'Hierarki Kelas Jabatan Pegawai')" title="Klik untuk melihat sebaran personil">
            <div class="card-body p-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Hierarki Grading</small>
                    <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-layer-group" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-warning mb-0">{{ $grades->count() }} Tingkat</h3>
                    <span class="text-muted small">Rentang Grade</span>
                </div>
                <small class="text-muted mt-1" style="font-size: 0.72rem;">Terendah Grade 7 s.d. Tertinggi Grade 18</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Personnel List by Category (From referensi_desain) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-id-badge text-primary"></i> Personil Berdasarkan Kategori Jabatan
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                Total {{ $struktural->count() + $fungsional->count() + $pelaksana->count() }} Personil
            </span>
        </div>
        <ul class="nav nav-pills gap-1 p-1 bg-light rounded-3 border" id="jabatanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-semibold small px-4 py-2" id="tab-struktural-tab" data-bs-toggle="pill" data-bs-target="#tab-struktural" type="button" role="tab">
                    <i class="fas fa-user-tie me-1"></i> Struktural <span class="badge bg-white text-dark rounded-pill ms-1">{{ $struktural->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold small px-4 py-2" id="tab-fungsional-tab" data-bs-toggle="pill" data-bs-target="#tab-fungsional" type="button" role="tab">
                    <i class="fas fa-gavel me-1"></i> Fungsional <span class="badge bg-white text-dark rounded-pill ms-1">{{ $fungsional->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold small px-4 py-2" id="tab-pelaksana-tab" data-bs-toggle="pill" data-bs-target="#tab-pelaksana" type="button" role="tab">
                    <i class="fas fa-users-gear me-1"></i> Pelaksana <span class="badge bg-white text-dark rounded-pill ms-1">{{ $pelaksana->count() }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-3">
        <div class="tab-content" id="jabatanTabContent">
            <!-- 1. Struktural -->
            <div class="tab-pane fade show active" id="tab-struktural" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle table-jabatan w-100" id="tableStruktural" style="width: 100% !important;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Pejabat &amp; NIP</th>
                                <th>Jabatan Struktural</th>
                                <th>Seksi / Unit</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-3" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($struktural as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-3">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                            {{ str_contains(strtolower($p->nama_jabatan_raw), 'kepala kpknl') ? 'Eselon III.a' : 'Eselon IV.a' }}
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade ?: '-' }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Fungsional -->
            <div class="tab-pane fade" id="tab-fungsional" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle table-jabatan w-100" id="tableFungsional" style="width: 100% !important;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Pejabat Fungsional &amp; NIP</th>
                                <th>Jabatan Fungsional</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-3" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fungsional as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-3">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #1e40af, #3b82f6);">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.68rem;">
                                            Fungsional Tertentu
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade ?: '-' }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Pelaksana -->
            <div class="tab-pane fade" id="tab-pelaksana" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle table-jabatan w-100" id="tablePelaksana" style="width: 100% !important;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Pelaksana &amp; NIP</th>
                                <th>Jabatan Pelaksana</th>
                                <th>Seksi Penempatan</th>
                                <th>Gol. / Grade</th>
                                <th>Masa Tugas Palembang</th>
                                <th class="text-end pe-3" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelaksana as $p)
                                <tr onclick="showPegawaiDetail({{ $p->id }})" role="button" title="Klik untuk melihat profil">
                                    <td class="text-center fw-bold text-muted ps-3">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-initial" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #059669, #10b981);">
                                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->display_name }}</div>
                                                <div class="small text-muted font-monospace">{{ $p->nip ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->nama_jabatan_raw }}</div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                            Staf Pelaksana
                                        </span>
                                    </td>
                                    <td>{{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                        <span class="small text-muted ms-1">Grade {{ $p->job_grade ?: '-' }}</span>
                                    </td>
                                    <td>{{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln</td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation(); showPegawaiDetail({{ $p->id }});">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; padding: 0;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Paginasi Tabel DataTables (Maks 10 data per halaman)
        $('.table-jabatan').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50],
                    autoWidth: false,
                    language: {
                        search: "Cari Pegawai:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ s.d. _END_ dari _TOTAL_ pegawai",
                        infoEmpty: "Tidak ada data personil",
                        paginate: {
                            previous: "<i class='fas fa-chevron-left'></i>",
                            next: "<i class='fas fa-chevron-right'></i>"
                        }
                    }
                });
            }
        });

        // Re-adjust columns when tabs switch
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
            setTimeout(function() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }, 150);
        });
    });
</script>
@endpush
