<div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-start justify-content-between" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%); color: #ffffff;">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar-initial fs-3" style="width: 58px; height: 58px; border-radius: 14px; background: rgba(255,255,255,0.18); border: 2px solid rgba(255,255,255,0.35);">
            {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
        </div>
        <div>
            <div class="badge bg-warning text-dark fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                {{ strtoupper($pegawai->tipe_pegawai) }} DEFINITIF
            </div>
            <h5 class="modal-title fw-bold text-white mb-0" id="pegawaiDetailModalLabel">{{ $pegawai->display_name }}</h5>
            <div class="text-white-50 small mt-1">
                NIP: {{ $pegawai->nip ?: '-' }} &bull; NIK: {{ $pegawai->masked_nik }}
            </div>
        </div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4 bg-white">
    <!-- Highlight Cards Bar (From referensi_desain) -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.04em;">Pangkat / Gol.</small>
                    <div class="fw-bold text-primary fs-5 mt-1">{{ $pegawai->pangkatGolongan?->golongan_ruang ?: '-' }}</div>
                    <div class="text-muted text-truncate" style="font-size: 0.72rem;" title="{{ $pegawai->pangkatGolongan?->nama_pangkat }}">{{ $pegawai->pangkatGolongan?->nama_pangkat ?: 'PNS' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.04em;">Kelas Jabatan</small>
                    <div class="fw-bold text-success fs-5 mt-1">Grade {{ $pegawai->job_grade ?: '-' }}</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Remunerasi DJKN</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.04em;">Unit Penempatan</small>
                    <div class="fw-bold text-dark fs-5 mt-1 text-truncate" title="{{ $pegawai->unitKerja?->nama_unit }}">
                        {{ $pegawai->unitKerja?->singkatan ?: ($pegawai->unitKerja?->nama_unit ?: 'KPKNL Palembang') }}
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">Palembang</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            @php $kgbInfo = $pegawai->kgb_status; @endphp
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-{{ $kgbInfo['badge'] }}">
                <div class="card-body p-3">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.04em;">Status KGB</small>
                    <div class="mt-1">
                        <span class="badge bg-{{ $kgbInfo['badge'] }} px-2 py-1" style="font-size: 0.72rem;">
                            {{ $kgbInfo['label'] }}
                        </span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $pegawai->tmt_kgb ? $pegawai->tmt_kgb->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-fill mb-3" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold small py-2" id="tab-profil-tab" data-bs-toggle="tab" data-bs-target="#tab-profil" type="button" role="tab">
                <i class="fas fa-user-tie me-1"></i> Profil & Jabatan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold small py-2" id="tab-masa-tab" data-bs-toggle="tab" data-bs-target="#tab-masa" type="button" role="tab">
                <i class="fas fa-business-time me-1"></i> Masa Kerja & KGB
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold small py-2" id="tab-usia-tab" data-bs-toggle="tab" data-bs-target="#tab-usia" type="button" role="tab">
                <i class="fas fa-hourglass-half me-1"></i> Usia & Pensiun
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold small py-2" id="tab-pendidikan-tab" data-bs-toggle="tab" data-bs-target="#tab-pendidikan" type="button" role="tab">
                <i class="fas fa-graduation-cap me-1"></i> Pendidikan & Gelar
            </button>
        </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
        <!-- Tab 1: Profil & Jabatan -->
        <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 38%;">Nama Lengkap (HRIS)</td>
                            <td class="fw-semibold">: {{ $pegawai->nama_lengkap_gelar ?: $pegawai->nama }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jabatan Definitif</td>
                            <td class="fw-semibold">: {{ $pegawai->nama_jabatan_raw ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Jabatan</td>
                            <td>: <span class="badge bg-primary-subtle text-primary text-uppercase">{{ $pegawai->jabatan?->jenis_jabatan ?: 'Pelaksana' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pangkat / Golongan Ruang</td>
                            <td class="fw-semibold">: {{ $pegawai->pangkat_golongan_raw ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">TMT Golongan Terakhir</td>
                            <td>: {{ $pegawai->tmt_golongan ? $pegawai->tmt_golongan->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">TMT Pengangkatan CPNS/PNS</td>
                            <td>: {{ $pegawai->tmt_nip ? $pegawai->tmt_nip->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Validasi Data Jabatan HRIS</td>
                            <td>: 
                                @if($pegawai->validasi_jabatan)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check me-1"></i> Sesuai HRIS</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fas fa-xmark me-1"></i> Perlu Verifikasi</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Masa Kerja & KGB -->
        <div class="tab-pane fade" id="tab-masa" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 38%;">Total Masa Kerja ASN</td>
                            <td class="fw-semibold text-primary">: {{ $pegawai->masa_kerja_tahun }} Tahun {{ $pegawai->masa_kerja_bulan }} Bulan</td>
                        </tr>
                        <tr>
                            <td class="text-muted">TMT Mulai Tugas di Palembang</td>
                            <td>: {{ $pegawai->tmt_palembang ? $pegawai->tmt_palembang->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lama Penugasan di Palembang</td>
                            <td class="fw-semibold">: {{ $pegawai->lama_palembang_tahun }} Tahun {{ $pegawai->lama_palembang_bulan }} Bulan</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kesiapan Rotasi (Tour of Duty)</td>
                            <td>: 
                                @if($pegawai->is_tour_of_duty_due)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-arrows-split-up-and-left me-1"></i> Siap Rotasi (&gt; 4 Tahun)</span>
                                @else
                                    <span class="badge bg-light text-muted border"><i class="fas fa-check me-1"></i> Normal (&lt; 4 Tahun)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jatuh Tempo KGB Berikutnya</td>
                            <td class="fw-bold text-danger">: {{ $pegawai->tmt_kgb ? $pegawai->tmt_kgb->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status KGB Terkini</td>
                            <td>: <span class="badge bg-{{ $kgbInfo['badge'] }}">{{ $kgbInfo['label'] }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Usia & Pensiun -->
        <div class="tab-pane fade" id="tab-usia" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 38%;">Tempat & Tanggal Lahir</td>
                            <td class="fw-semibold">: {{ $pegawai->tempat_lahir ?: '-' }}, {{ $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Usia Saat Ini</td>
                            <td class="fw-bold text-dark">: {{ $pegawai->usia_tahun }} Tahun {{ $pegawai->usia_bulan }} Bulan</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Kelamin</td>
                            <td>: {{ $pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Batas Usia Pensiun (BUP)</td>
                            <td>: {{ $pegawai->bup_usia }} Tahun</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Proyeksi Tanggal Pensiun</td>
                            <td class="fw-bold text-primary">: {{ $pegawai->tanggal_pensiun ? $pegawai->tanggal_pensiun->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sisa Masa Pengabdian</td>
                            <td>: 
                                @php $sisaBulan = $pegawai->sisa_pensiun_bulan; @endphp
                                @if($sisaBulan !== null)
                                    @php $sisaThn = floor($sisaBulan / 12); $sisaBln = $sisaBulan % 12; @endphp
                                    <span class="fw-bold {{ $sisaThn < 3 ? 'text-danger' : 'text-success' }}">
                                        {{ $sisaThn }} Tahun {{ $sisaBln }} Bulan
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 4: Pendidikan & Gelar -->
        <div class="tab-pane fade" id="tab-pendidikan" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 38%;">Jenjang Pendidikan Terakhir</td>
                            <td>: <span class="badge bg-primary-subtle text-primary">{{ $pegawai->pendidikan_terakhir ?: '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fakultas / Program Studi</td>
                            <td class="fw-semibold">: {{ $pegawai->fakultas ?: '-' }} / {{ $pegawai->jurusan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Perguruan Tinggi / Universitas</td>
                            <td class="fw-semibold">: {{ $pegawai->nama_universitas ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tahun Kelulusan</td>
                            <td>: {{ $pegawai->tahun_lulus ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Pencantuman Gelar</td>
                            <td>: 
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ $pegawai->status_gelar ?: 'Sudah Clear' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
    <div class="small text-muted">
        <i class="fas fa-database text-primary me-1"></i> Data diverifikasi dari Google Spreadsheet SIMPATIK
    </div>
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup Profil</button>
</div>
