<div class="modal-header border-0 pb-3 pt-3 px-4 bg-white d-flex align-items-center justify-content-between border-bottom">
    <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
            <i class="fas fa-users-viewfinder fs-6"></i>
        </div>
        <div>
            <h6 class="modal-title fw-bold text-dark mb-0">{{ $title ?: 'Daftar Personil Pegawai' }}</h6>
            <small class="text-muted" style="font-size: 0.72rem;">KPKNL Palembang &bull; SI-KEP Kepegawaian</small>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-3 py-1.5" style="font-size: 0.75rem;">
            {{ $pegawais->count() }} Personil
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-4 bg-light">
    <!-- Quick Search Bar inside Modal -->
    <div class="mb-3">
        <div class="input-group shadow-xs">
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="fas fa-magnifying-glass"></i>
            </span>
            <input type="text" class="form-control border-start-0" id="filterModalSearchInput" placeholder="Cari nama, NIP, atau jabatan di dalam daftar ini..." onkeyup="filterModalTableLive(this)">
        </div>
    </div>

    <!-- Table of Filtered Employees -->
    <div class="table-responsive bg-white rounded-3 border shadow-xs" style="max-height: 480px; overflow-y: auto;">
        <table class="table table-hover mb-0 align-middle" id="tableFilteredPegawai">
            <thead class="bg-light sticky-top" style="z-index: 2;">
                <tr>
                    <th class="ps-3" style="width: 50px;">No</th>
                    <th>Pegawai &amp; NIP</th>
                    <th>Jabatan &amp; Unit</th>
                    <th>Gol. / Grade</th>
                    <th>Keterangan</th>
                    <th class="text-end pe-3" style="width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pegawais as $p)
                    @php 
                        $kgb = $p->kgb_status; 
                    @endphp
                    <tr class="modal-pegawai-row" role="button" onclick="showPegawaiDetail({{ $p->id }})" title="Klik untuk melihat profil lengkap">
                        <td class="text-center text-muted fw-semibold ps-3">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($p->avatar_url)
                                    <img src="{{ asset('storage/' . $p->avatar_url) }}" alt="{{ $p->nama }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                @else
                                    <div class="avatar-initial shadow-xs" style="width: 36px; height: 36px; font-size: 0.78rem;">
                                        {{ strtoupper(substr($p->nama, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark search-target-name">{{ $p->display_name }}</div>
                                    <div class="small text-muted font-monospace search-target-nip">{{ $p->nip ?: '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-primary search-target-jabatan" style="font-size: 0.85rem;">{{ $p->nama_jabatan_raw }}</div>
                            <div class="small text-muted">{{ $p->unitKerja?->nama_unit ?: 'KPKNL Palembang' }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-light text-secondary border">{{ $p->pangkatGolongan?->golongan_ruang ?: '-' }}</span>
                                @if($p->job_grade)
                                    <span class="badge bg-primary-subtle text-primary">G{{ $p->job_grade }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(isset($context) && $context === 'kgb')
                                <span class="badge bg-{{ $kgb['badge'] }} px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $kgb['label'] }}
                                </span>
                            @elseif(isset($context) && $context === 'pensiun')
                                <div class="small">
                                    <span class="fw-bold text-dark">{{ $p->usia_tahun }} Thn</span>
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">Pensiun: {{ $p->tgl_pensiun ? $p->tgl_pensiun->format('d/m/Y') : '-' }}</span>
                                </div>
                            @elseif(isset($context) && $context === 'duty')
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $p->lama_palembang_tahun }} Thn {{ $p->lama_palembang_bulan }} Bln
                                </span>
                            @elseif(isset($context) && $context === 'generasi')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    Usia {{ $p->usia_tahun }} Thn ({{ $p->jenis_kelamin == 'L' ? 'L' : 'P' }})
                                </span>
                            @elseif(isset($context) && $context === 'pendidikan')
                                <span class="small fw-semibold text-dark">{{ $p->pendidikan_terakhir ?: '-' }}</span>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $p->nama_universitas ?: '-' }}</small>
                            @elseif(isset($context) && $context === 'ue_iv')
                                <div>
                                    <span class="badge {{ $p->lama_ue_iv_bulan >= 48 ? 'bg-danger text-white' : ($p->lama_ue_iv_bulan >= 24 ? 'bg-warning text-dark' : 'bg-success text-white') }} fw-bold px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-business-time me-1"></i> {{ $p->lama_ue_iv_formatted }}
                                    </span>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 0.68rem;">TMT: {{ $p->effective_tmt_ue_iv }}</small>
                                </div>
                            @elseif(isset($context) && $context === 'pns_definitif')
                                @if(str_contains(strtolower($p->status_gelar ?? ''), 'tidak sesuai'))
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-triangle-exclamation me-1"></i> Beda HRIS
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-check-circle me-1"></i> Sesuai HRIS
                                    </span>
                                @endif
                            @else
                                <span class="badge {{ $p->tipe_pegawai === 'pns' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill" style="font-size: 0.7rem;">
                                    {{ strtoupper($p->tipe_pegawai) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3" onclick="event.stopPropagation();">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick="showPegawaiDetail({{ $p->id }})" title="Lihat Profil Pegawai">
                                <i class="fas fa-eye me-1"></i> Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 opacity-50"></i>
                            <p class="mb-0">Tidak ada pegawai yang sesuai dengan kategori ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer border-top px-4 py-3 bg-white d-flex justify-content-between align-items-center">
    <div class="small text-muted">
        <i class="fas fa-circle-info text-primary me-1"></i> Klik pada baris pegawai untuk membuka profil lengkap.
    </div>
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
</div>

<script>
    function filterModalTableLive(input) {
        const val = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#tableFilteredPegawai tbody tr.modal-pegawai-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    }
</script>
