@extends('layouts.app')

@section('title', 'Data Mentah Spreadsheet — SI-KEP SIMPATIK KPKNL Palembang')
@section('hero-title', 'Data Mentah Google Spreadsheet')
@section('hero-subtitle', 'Tampilan matriks terlengkap 35+ atribut personil langsung dari sumber Google Spreadsheet SIMPATIK Kepegawaian')

@section('content')

<!-- Quick Info Cards (From referensi_desain) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Total Baris Data</small>
                <h3 class="fw-bold text-dark mb-0">{{ $pegawais->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Personil Terdata</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">PNS Definitif</small>
                <h3 class="fw-bold text-success mb-0">{{ $pegawais->where('tipe_pegawai', 'pns')->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Aparatur Sipil Negara</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning">
            <div class="card-body p-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">PPNPN</small>
                <h3 class="fw-bold text-warning mb-0">{{ $pegawais->where('tipe_pegawai', 'ppnpn')->count() }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Pegawai Pemerintah Non-PNS</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Status Sumber Data</small>
                <div class="fw-bold text-info fs-6 mt-1 text-truncate" title="{{ $sheetUrl }}">Google Sheets Aktif</div>
                <small class="text-muted" style="font-size: 0.72rem;">
                    {{ $lastSync ? $lastSync->synced_at->locale('id')->diffForHumans() : 'Belum sync' }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Control & Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-semibold">
                <i class="fas fa-filter me-1"></i> Filter Data Mentah
            </span>
            <small class="text-muted d-none d-md-inline">Saring baris spreadsheet atau cari berdasarkan nama, NIP, atau NIK</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $sheetUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-file-excel"></i> <span class="d-none d-sm-inline">Buka Dokumen Google Sheet</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" onclick="exportTableToCSV('data-mentah-spreadsheet.csv')">
                <i class="fas fa-file-arrow-down"></i> <span>Ekspor CSV</span>
            </button>
        </div>
    </div>
    <div class="card-body p-4 bg-light bg-opacity-50">
        <form action="{{ route('spreadsheet.raw') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase">Pencarian Cepat</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Ketik nama, NIP, NIK, jabatan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Unit Kerja</label>
                <select name="unit_id" class="form-select">
                    <option value="">-- Semua Unit Kerja --</option>
                    @foreach($unitKerjas as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Tipe Pegawai</label>
                <select name="tipe" class="form-select">
                    <option value="">-- Semua Tipe --</option>
                    <option value="pns" {{ request('tipe') == 'pns' ? 'selected' : '' }}>PNS Definitif</option>
                    <option value="ppnpn" {{ request('tipe') == 'ppnpn' ? 'selected' : '' }}>PPNPN</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold">
                    <i class="fas fa-magnifying-glass me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'unit_id', 'tipe']))
                    <a href="{{ route('spreadsheet.raw') }}" class="btn btn-outline-secondary rounded-pill" title="Reset filter">
                        <i class="fas fa-arrows-rotate"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Raw Spreadsheet Table Matrix Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-table text-primary"></i>
            Matriks Lengkap Spreadsheet Pegawai ({{ $pegawais->count() }} Baris)
        </h6>
        <span class="badge bg-light text-secondary border">Akses Terbatas: Superadmin &bull; Admin &bull; Maintenance</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
            <table class="table table-hover table-striped table-bordered align-middle mb-0 text-nowrap" id="rawSpreadsheetTable" style="font-size: 0.82rem;">
                <thead class="table-light sticky-top" style="z-index: 5;">
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>NIP</th>
                        <th>NIK</th>
                        <th>Nama (HRIS)</th>
                        <th>Nama Lengkap &amp; Gelar</th>
                        <th>Tipe</th>
                        <th>Jabatan (Raw)</th>
                        <th>Per. Jabatan</th>
                        <th>Unit Kerja</th>
                        <th>Grade</th>
                        <th>Pangkat / Gol.</th>
                        <th>TMT Golongan</th>
                        <th>TMT CPNS/NIP</th>
                        <th>TMT Palembang</th>
                        <th>Masa Kerja</th>
                        <th>Lama di Palembang</th>
                        <th>TMT KGB</th>
                        <th>Status KGB</th>
                        <th>TMT Grading</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Usia</th>
                        <th>Gender</th>
                        <th>BUP</th>
                        <th>Tanggal Pensiun</th>
                        <th>Pendidikan</th>
                        <th>Fakultas</th>
                        <th>Jurusan</th>
                        <th>Tahun Lulus</th>
                        <th>Universitas</th>
                        <th>Status Gelar</th>
                        <th>TMT UE IV</th>
                        <th>Lama UE IV</th>
                        <th>Validasi Jabatan</th>
                        <th>Validasi Pangkat</th>
                        <th>Validasi Pendidikan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $p)
                        @php
                            $kgb = $p->kgb_status;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $p->no_urut }}</td>
                            <td class="font-monospace text-primary">{{ $p->nip ?: '-' }}</td>
                            <td class="font-monospace text-muted">{{ $p->masked_nik }}</td>
                            <td class="fw-semibold text-dark">{{ $p->nama }}</td>
                            <td>{{ $p->nama_lengkap_gelar ?: '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $p->tipe_pegawai === 'pns' ? 'success' : 'warning text-dark' }} text-uppercase">
                                    {{ $p->tipe_pegawai }}
                                </span>
                            </td>
                            <td>{{ $p->nama_jabatan_raw }}</td>
                            <td class="text-muted small">{{ $p->per_jabatan ?: '-' }}</td>
                            <td>{{ $p->unitKerja?->nama_unit ?: '-' }}</td>
                            <td class="fw-bold text-center">
                                @if($p->job_grade)
                                    <span class="badge bg-primary-subtle text-primary">Grade {{ $p->job_grade }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $p->pangkat_golongan_raw ?: '-' }}</td>
                            <td>{{ $p->tmt_golongan ? $p->tmt_golongan->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->tmt_nip ? $p->tmt_nip->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->tmt_palembang ? $p->tmt_palembang->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->masa_kerja_raw ?: ($p->masa_kerja_tahun . ' thn ' . $p->masa_kerja_bulan . ' bln') }}</td>
                            <td>{{ $p->lama_palembang_raw ?: ($p->lama_palembang_tahun . ' thn ' . $p->lama_palembang_bulan . ' bln') }}</td>
                            <td class="font-monospace">{{ $p->tmt_kgb ? $p->tmt_kgb->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $kgb['badge'] }} py-1 px-2" style="font-size: 0.72rem;">
                                    {{ $kgb['label'] }}
                                </span>
                            </td>
                            <td>{{ $p->tmt_grading ? $p->tmt_grading->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->tempat_lahir ?: '-' }}</td>
                            <td>{{ $p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->usia_raw ?: ($p->usia_tahun . ' Thn ' . $p->usia_bulan . ' Bln') }}</td>
                            <td class="text-center">{{ $p->jenis_kelamin }}</td>
                            <td class="text-center">{{ $p->bup_usia }} Thn</td>
                            <td>{{ $p->tanggal_pensiun ? $p->tanggal_pensiun->format('d/m/Y') : '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->pendidikan_terakhir ?: '-' }}</span></td>
                            <td>{{ $p->fakultas ?: '-' }}</td>
                            <td>{{ $p->jurusan ?: '-' }}</td>
                            <td class="text-center">{{ $p->tahun_lulus ?: '-' }}</td>
                            <td>{{ $p->nama_universitas ?: '-' }}</td>
                            <td>{{ $p->status_gelar ?: '-' }}</td>
                            <td>{{ $p->tmt_ue_iv ?: '-' }}</td>
                            <td>{{ $p->lama_bertugas_ue_iv ?: '-' }}</td>
                            <td class="text-center">
                                @if($p->validasi_jabatan)
                                    <span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> Valid</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger"><i class="fas fa-xmark"></i> Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->validasi_pangkat)
                                    <span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> Valid</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger"><i class="fas fa-xmark"></i> Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->validasi_pendidikan)
                                    <span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> Valid</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger"><i class="fas fa-xmark"></i> Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="showPegawaiDetail({{ $p->id }})" title="Lihat Profil Detail">
                                    <i class="fas fa-eye" style="font-size: 0.75rem;"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="37" class="text-center py-5 text-muted">
                                Tidak ada data personil yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <small class="text-muted">
            Menampilkan {{ $pegawais->count() }} baris data &bull; Format kolom disinkronkan langsung dari Google Spreadsheet SIMPATIK
        </small>
        <div class="small text-muted font-monospace">
            KPKNL Palembang &bull; Seksi Kepatuhan Internal &bull; Subbagian Umum
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function exportTableToCSV(filename) {
        let csv = [];
        const rows = document.querySelectorAll("#rawSpreadsheetTable tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            // Exclude action column (last column)
            for (let j = 0; j < cols.length - 1; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }

        const csvFile = new Blob([csv.join("\n")], {type: "text/csv;charset=utf-8;"});
        const downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endpush
