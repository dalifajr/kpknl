<div class="modal-header border-0 pb-3 pt-3 px-4 bg-white d-flex align-items-center justify-content-between border-bottom">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            <i class="fas fa-id-card me-1"></i> PROFIL PEGAWAI
        </span>
        <span class="badge {{ $pegawai->tipe_pegawai === 'pns' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-1" style="font-size: 0.72rem;">
            {{ strtoupper($pegawai->tipe_pegawai) }} DEFINITIF
        </span>
    </div>
    <div class="d-flex align-items-center gap-2">
        @auth
            @if(in_array(auth()->user()->role, ['superadmin', 'maintenance', 'administrator']))
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs" onclick="openPegawaiFormModal({{ $pegawai->id }})" title="Ubah Data Pegawai">
                    <i class="fas fa-user-pen me-1"></i> Edit
                </button>
            @endif
        @endauth
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 text-muted" onclick="window.print()" title="Cetak Ringkasan Profil">
            <i class="fas fa-print me-1"></i> Cetak
        </button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-0 bg-light">
    <div class="row g-0">
        <!-- Left Identity Column: Profile Summary & Key Indicators -->
        <div class="col-lg-4 p-4 border-end bg-white">
            <div class="text-center pb-3 border-bottom mb-3">
                <div class="position-relative d-inline-block mb-3">
                    @if($pegawai->avatar_url)
                        <img src="{{ asset('storage/' . $pegawai->avatar_url) }}" alt="{{ $pegawai->nama }}" class="shadow-sm rounded-4 border border-3 border-white" style="width: 86px; height: 86px; object-fit: cover;">
                    @else
                        <div class="avatar-initial shadow-sm fs-2 fw-bold text-white d-flex align-items-center justify-content-center"
                             style="width: 82px; height: 82px; border-radius: 22px; background: linear-gradient(135deg, #0c306b 0%, #2563eb 100%); border: 3px solid #ffffff;">
                            {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-white p-1" title="Pegawai Aktif">
                        <i class="fas fa-check" style="font-size: 0.65rem;"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $pegawai->display_name }}</h5>
                <div class="text-muted small mb-2">{{ $pegawai->nama_jabatan_raw }}</div>
                <div class="d-inline-flex align-items-center gap-1 bg-light border px-2 py-1 rounded-pill small text-secondary" style="font-size: 0.75rem;">
                    <i class="fas fa-building text-primary"></i>
                    <span>{{ $pegawai->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</span>
                </div>
            </div>

            <!-- Key Identifiers with Copy Button & Sensitive Data Eye Toggle -->
            <div class="p-3 rounded-3 bg-light border mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted" style="font-size: 0.72rem;">NIP Pegawai</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="fw-semibold text-dark small font-monospace">{{ $pegawai->nip ?: '-' }}</span>
                        @if($pegawai->nip)
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary ms-1" onclick="navigator.clipboard.writeText('{{ $pegawai->nip }}'); this.innerHTML='<i class=\'fas fa-check\'></i>'; setTimeout(() => this.innerHTML='<i class=\'far fa-copy\'></i>', 1500);" title="Salin NIP">
                                <i class="far fa-copy"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted" style="font-size: 0.72rem;">NIK Kependudukan</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="fw-semibold text-secondary small font-monospace" id="nikDisplay">{{ $pegawai->masked_nik }}</span>
                        @auth
                            @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'maintenance', 'administrator']))
                                <button type="button" class="btn btn-link btn-sm p-0 text-secondary ms-1" id="btnToggleNik" 
                                        data-masked="{{ $pegawai->masked_nik }}" 
                                        data-unmasked="{{ $pegawai->nik ?: '-' }}" 
                                        onclick="toggleNikVisibility(this)" 
                                        title="Tampilkan / Sembunyikan NIK Lengkap">
                                    <i class="far fa-eye" id="iconEyeNik"></i>
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <!-- 4 Vertical Mini Indicator Cards -->
            <div class="d-flex flex-column gap-2 mb-3">
                <!-- 1. Grade DJKN -->
                <div class="p-2 px-3 rounded-3 border d-flex justify-content-between align-items-center bg-white shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-layer-group" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Kelas Jabatan</div>
                            <div class="fw-bold text-dark small">Grade {{ $pegawai->job_grade ?: '-' }}</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border small" style="font-size: 0.68rem;">Remunerasi</span>
                </div>

                <!-- 2. Pangkat / Gol -->
                <div class="p-2 px-3 rounded-3 border d-flex justify-content-between align-items-center bg-white shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-award" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Pangkat &amp; Gol.</div>
                            <div class="fw-bold text-dark small">{{ $pegawai->pangkatGolongan?->golongan_ruang ?: '-' }} <span class="text-muted fw-normal">({{ $pegawai->pangkatGolongan?->nama_pangkat ?: 'PNS' }})</span></div>
                        </div>
                    </div>
                </div>

                <!-- 3. Status KGB -->
                @php $kgbInfo = $pegawai->kgb_status; @endphp
                <div class="p-2 px-3 rounded-3 border d-flex justify-content-between align-items-center bg-white shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 bg-{{ $kgbInfo['badge'] }}-subtle text-{{ $kgbInfo['badge'] }} d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-clock-rotate-left" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Status KGB</div>
                            <div class="fw-bold text-dark small">{{ $pegawai->tmt_kgb ? $pegawai->tmt_kgb->format('d/m/Y') : '-' }}</div>
                        </div>
                    </div>
                    <span class="badge bg-{{ $kgbInfo['badge'] }} text-white" style="font-size: 0.68rem;">
                        {{ $kgbInfo['label'] }}
                    </span>
                </div>

                <!-- 4. Masa Penugasan di Palembang -->
                <div class="p-2 px-3 rounded-3 border d-flex justify-content-between align-items-center bg-white shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-location-dot" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Masa di Palembang</div>
                            <div class="fw-bold text-dark small">{{ $pegawai->lama_palembang_tahun }} Thn {{ $pegawai->lama_palembang_bulan }} Bln</div>
                        </div>
                    </div>
                    @if($pegawai->lama_palembang_tahun >= 4)
                        <span class="badge bg-danger text-white" style="font-size: 0.68rem;">Tour of Duty</span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">Normal</span>
                    @endif
                </div>
            </div>

            <!-- Footnote Info (Poin 11) -->
            <div class="p-2 px-3 rounded-3 bg-light border text-center small text-muted" style="font-size: 0.72rem;">
                <i class="fas fa-table-cells text-primary me-1"></i> Data diperoleh dari spreadsheet
            </div>
        </div>

        <!-- Right Content Column: Pill Tabs & Rich Information Cards -->
        <div class="col-lg-8 p-4 bg-light">
            <!-- Modern Nav Pills -->
            <ul class="nav nav-pills nav-fill bg-white p-1 rounded-pill border shadow-sm mb-4" id="profileTabPills" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold small py-2" id="tab-karir-tab" data-bs-toggle="pill" data-bs-target="#tab-karir" type="button" role="tab">
                        <i class="fas fa-briefcase me-1"></i> Karir &amp; Jabatan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold small py-2" id="tab-masakerja-tab" data-bs-toggle="pill" data-bs-target="#tab-masakerja" type="button" role="tab">
                        <i class="fas fa-business-time me-1"></i> Masa Kerja &amp; KGB
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold small py-2" id="tab-usia-tab" data-bs-toggle="pill" data-bs-target="#tab-usia" type="button" role="tab">
                        <i class="fas fa-hourglass-half me-1"></i> Usia &amp; Pensiun
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold small py-2" id="tab-pendidikan-tab" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button" role="tab">
                        <i class="fas fa-graduation-cap me-1"></i> Pendidikan &amp; Gelar
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabPillsContent">
                <!-- Tab 1: Karir & Jabatan -->
                <div class="tab-pane fade show active" id="tab-karir" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-user-tag text-primary me-1"></i> Nama Lengkap (Sesuai HRIS)
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $pegawai->nama_lengkap_gelar ?: $pegawai->nama }}</div>
                                <div class="small text-muted mt-1">Nama Panggilan: {{ $pegawai->nama }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-sitemap text-primary me-1"></i> Jabatan Definitif
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $pegawai->nama_jabatan_raw }}</div>
                                <div class="mt-1">
                                    <span class="badge bg-primary-subtle text-primary text-uppercase">{{ $pegawai->jabatan?->nama_jabatan ?: 'Fungsional Umum' }}</span>
                                    @if($pegawai->job_grade)
                                        <span class="badge bg-secondary-subtle text-secondary">Grade {{ $pegawai->job_grade }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-medal text-success me-1"></i> Pangkat &amp; Golongan Ruang
                                </div>
                                <div class="fw-bold text-success fs-6">{{ $pegawai->pangkatGolongan?->nama_pangkat ?: 'PNS' }} ({{ $pegawai->pangkatGolongan?->golongan_ruang ?: '-' }})</div>
                                <div class="small text-muted mt-1">TMT Golongan: <span class="fw-semibold text-dark">{{ $pegawai->tmt_golongan ? $pegawai->tmt_golongan->format('d F Y') : '-' }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-calendar-check text-info me-1"></i> TMT Pengangkatan CPNS / NIP
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $pegawai->tmt_nip ? $pegawai->tmt_nip->format('d F Y') : ($pegawai->tmt_golongan ? $pegawai->tmt_golongan->format('d F Y') : '-') }}</div>
                                <div class="small text-muted mt-1">
                                    Status Kepegawaian: 
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check me-1"></i> {{ strtoupper($pegawai->tipe_pegawai) }} Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Masa Kerja & KGB -->
                <div class="tab-pane fade" id="tab-masakerja" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-hourglass-start text-primary me-1"></i> Total Masa Kerja ASN
                                </div>
                                <div class="fw-bold text-primary fs-5">{{ $pegawai->masa_kerja_tahun }} Tahun {{ $pegawai->masa_kerja_bulan }} Bulan</div>
                                <div class="small text-muted mt-1">Dihitung sejak pengangkatan pertama CPNS.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-arrows-split-up-and-left text-warning me-1"></i> Kesiapan Rotasi (Tour of Duty)
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $pegawai->lama_palembang_tahun }} Tahun {{ $pegawai->lama_palembang_bulan }} Bulan di Palembang</div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 8px;">
                                        @php $pctDuty = min(100, round(($pegawai->lama_palembang_tahun / 4) * 100)); @endphp
                                        <div class="progress-bar {{ $pctDuty >= 100 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $pctDuty }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.68rem;">
                                        <span>Mulai: {{ $pegawai->tmt_palembang ? $pegawai->tmt_palembang->format('d/m/Y') : '-' }}</span>
                                        <span>Batas: 4 Tahun ({{ $pctDuty }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-calendar-day text-danger me-1"></i> Siklus Kenaikan Gaji Berkala (KGB)
                                </div>
                                <div class="d-flex justify-content-between align-items-baseline mt-2">
                                    <span class="small text-muted">TMT Terakhir:</span>
                                    <span class="fw-bold text-dark">{{ $pegawai->tmt_kgb ? $pegawai->tmt_kgb->format('d F Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-baseline mt-1 pt-1 border-top">
                                    <span class="small text-muted">Jatuh Tempo (+2 Thn):</span>
                                    <span class="fw-bold text-danger fs-6">{{ $pegawai->next_tmt_kgb ? $pegawai->next_tmt_kgb->format('d F Y') : '-' }}</span>
                                </div>
                                <div class="small text-muted mt-2" style="font-size: 0.72rem;">Dihitung otomatis 2 tahun ke depan dari TMT KGB terakhir.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-bell text-info me-1"></i> Status Peringatan Dini KGB
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-{{ $kgbInfo['badge'] }} fs-6 px-3 py-2 rounded-pill">
                                        {{ $kgbInfo['label'] }}
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">
                                    @if($kgbInfo['status'] === 'overdue')
                                        Telah melewati tanggal jatuh tempo selama <strong>{{ $kgbInfo['overdue_days'] }} hari</strong>.
                                    @elseif($kgbInfo['status'] === 'warning')
                                        Jatuh tempo dalam waktu <strong>{{ $kgbInfo['days_left'] }} hari</strong> lagi.
                                    @else
                                        Jadwal KGB saat ini berada dalam rentang aman.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Usia & Pensiun (PP No. 17 Tahun 2020) -->
                <div class="tab-pane fade" id="tab-usia" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-cake-candles text-primary me-1"></i> Kelahiran &amp; Usia Saat Ini
                                </div>
                                <div class="fw-bold text-dark fs-5">{{ $pegawai->usia_tahun }} Tahun {{ $pegawai->usia_bulan }} Bulan</div>
                                <div class="small text-muted mt-1">Lahir di {{ $pegawai->tempat_lahir ?: '-' }}, {{ $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->format('d F Y') : '-' }} ({{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }})</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-flag-checkered text-warning me-1"></i> Batas Usia Pensiun (BUP)
                                </div>
                                <div class="fw-bold text-dark fs-5">{{ $pegawai->bup_tahun }} Tahun</div>
                                <div class="small text-muted mt-1">
                                    @if($pegawai->bup_tahun == 60)
                                        Sesuai regulasi PP No. 17/2020: Jabatan Fungsional Madya / Pimpinan Tinggi.
                                    @elseif($pegawai->bup_tahun == 65)
                                        Sesuai regulasi PP No. 17/2020: Jabatan Fungsional Ahli Utama.
                                    @else
                                        Sesuai regulasi PP No. 17/2020: Pejabat Administrasi / Fungsional Pertama & Muda / Pelaksana.
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-calendar-check text-primary me-1"></i> Proyeksi Tanggal Pensiun
                                </div>
                                <div class="fw-bold text-primary fs-5">{{ $pegawai->tgl_pensiun ? $pegawai->tgl_pensiun->format('d F Y') : '-' }}</div>
                                <div class="small text-muted mt-1">TMT Terhitung Mulai Tanggal Pensiun resmi (Awal bulan berikutnya setelah BUP).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-hourglass-end text-success me-1"></i> Sisa Masa Pengabdian
                                </div>
                                <div class="fw-bold text-success fs-5">{{ $pegawai->sisa_dinas_tahun }} Tahun {{ $pegawai->sisa_dinas_bulan }} Bulan</div>
                                <div class="small text-muted mt-1">
                                    @if($pegawai->sisa_dinas_tahun <= 2)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-triangle-exclamation me-1"></i> Mendekati Batas Usia Pensiun</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Masa Pengabdian Produktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Pendidikan & Gelar (Poin 4 Perbaikan Warna Status) -->
                <div class="tab-pane fade" id="tab-pendidikan" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-graduation-cap text-primary me-1"></i> Jenjang Pendidikan Terakhir
                                </div>
                                <div class="fw-bold text-primary fs-5">{{ $pegawai->pendidikan_terakhir ?: '-' }}</div>
                                <div class="small text-muted mt-1">Program Studi: <span class="fw-semibold text-dark">{{ $pegawai->jurusan ?: '-' }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-university text-info me-1"></i> Perguruan Tinggi / Universitas
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $pegawai->nama_universitas ?: '-' }}</div>
                                <div class="small text-muted mt-1">Tahun Kelulusan: <span class="fw-semibold text-dark">{{ $pegawai->tahun_lulus ?: '-' }}</span></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <i class="fas fa-certificate text-primary me-1"></i> Status Pencantuman Gelar &amp; SK Akademik
                                </div>
                                @php
                                    $isGelarInvalid = str_contains(strtolower($pegawai->status_gelar ?? ''), 'tidak sesuai') || str_contains(strtolower($pegawai->status_gelar ?? ''), 'belum');
                                @endphp
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    @if($isGelarInvalid)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill fs-6">
                                            <i class="fas fa-triangle-exclamation me-1"></i> {{ $pegawai->status_gelar }}
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                                            <i class="fas fa-circle-check me-1"></i> {{ $pegawai->status_gelar ?: 'Sudah Clear (sesuai dengan HRIS)' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="small text-muted mt-2">
                                    @if($isGelarInvalid)
                                        <span class="text-danger fw-semibold"><i class="fas fa-info-circle me-1"></i> Catatan:</span> Data gelar belum tersinkronisasi penuh dengan catatan pangkalan data BKN / HRIS Kemenkeu dan perlu verifikasi berkas ijazah.
                                    @else
                                        Gelar akademis resmi diakui dan tercatat pada Keputusan Pengangkatan BKN serta Pangkalan Data Kepegawaian DJKN.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top px-4 py-3 bg-white d-flex justify-content-between align-items-center">
    <div class="small text-muted">
        <i class="fas fa-database text-primary me-1"></i> Database SIMPATIK Kepegawaian KPKNL Palembang
    </div>
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup Profil</button>
</div>
