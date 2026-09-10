@extends('layouts.bmn_master')

@section('title', 'Daftar Risalah Menunggu Validasi & Penataan Fisik — KPKNL Palembang')
@section('page_title', 'Daftar Risalah Menunggu Validasi & Penataan Fisik')
@section('page_subtitle', 'Penetapan lokasi lemari dan box arsip fisik untuk risalah baru — Seksi Hukum dan Informasi KPKNL Palembang')

@section('page_actions')
    <a href="{{ route('validasi.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-arrows-rotate"></i> Refresh Antrean
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Risalah</th>
                        <th>Jenis</th>
                        <th>Tanggal Risalah</th>
                        <th>Pejabat Lelang</th>
                        <th>Pemohon Lelang</th>
                        <th style="text-align: right;">Aksi Validasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingList as $idx => $item)
                        <tr>
                            <td>{{ $pendingList->firstItem() + $idx }}</td>
                            <td class="fw-bold text-dark">{{ $item->no_risalah }}</td>
                            <td>
                                <span class="badge {{ $item->jenis === 'minuta' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($item->jenis === 'tap' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }} text-uppercase" style="font-size: 0.68rem;">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $item->tgl_risalah ?? '-' }}</td>
                            <td class="fw-semibold text-dark">{{ $item->nama_pelelang }}</td>
                            <td class="text-muted">{{ $item->pemohon_lelang ?? '-' }}</td>
                            <td style="text-align: right;">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success py-1 px-2.5 me-1"
                                    style="font-size: 0.75rem;"
                                    onclick="openValidateModal({{ $item->id }}, '{{ addslashes($item->no_risalah) }}')"
                                >
                                    <i class="fas fa-check-circle me-1"></i> Validasi & Arsipkan
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger py-1 px-2"
                                    style="font-size: 0.75rem;"
                                    onclick="openRejectModal({{ $item->id }}, '{{ addslashes($item->no_risalah) }}')"
                                >
                                    <i class="fas fa-triangle-exclamation me-1"></i> Minta Revisi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-circle-check fa-2x mb-2 text-success d-block"></i>
                                Tidak ada risalah baru yang menunggu validasi. Seluruh arsip tertata rapi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 mt-3 border-top gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $pendingList->firstItem() ?? 0 }}</strong> sampai <strong>{{ $pendingList->lastItem() ?? 0 }}</strong> dari <strong>{{ $pendingList->total() }}</strong> berkas
            </small>
            <div>
                {{ $pendingList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VALIDASI & ASSIGN LEMARI/BOX (ADMIN)                               -->
<!-- ========================================================================= -->
<div class="modal fade" id="validateModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3 px-4">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-check-double me-2"></i> Validasi & Simpan Risalah <span id="validateModalNo"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="validateForm">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size: 0.82rem;">
                        Tentukan lokasi fisik penyimpanan berkas risalah di ruang arsip Seksi Hukum dan Informasi KPKNL Palembang:
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Lokasi Lemari Arsip <span class="text-danger">*</span></label>
                        <input type="text" name="lemari" class="form-control form-control-sm" required value="Lemari Arsip Utama 01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Nomor Box Arsip <span class="text-danger">*</span></label>
                        <input type="text" name="box" class="form-control form-control-sm" required value="Box Lelang 01">
                    </div>
                </div>
                <div class="modal-footer py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                        <i class="fas fa-vault me-1"></i> Simpan ke Katalog Utama
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REJECT & CATATAN REVISI (ADMIN)                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-triangle-exclamation me-2"></i> Minta Revisi Risalah <span id="rejectModalNo"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="rejectForm">
                @csrf
                <div class="modal-body p-4">
                    <label class="form-label fw-semibold" style="font-size: 0.8rem;">
                        Catatan Revisi / Poin Kekurangan <span class="text-danger">*</span>
                    </label>
                    <textarea name="catatan" class="form-control form-control-sm" rows="3" placeholder="Jelaskan bagian berkas atau data yang perlu dikoreksi oleh Pejabat Lelang..." required></textarea>
                </div>
                <div class="modal-footer py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Catatan Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openValidateModal(id, noRisalah) {
        document.getElementById('validateModalNo').textContent = '#' + noRisalah;
        document.getElementById('validateForm').action = "{{ url('/validasi') }}/" + id + "/approve";
        const modal = new bootstrap.Modal(document.getElementById('validateModal'));
        modal.show();
    }

    function openRejectModal(id, noRisalah) {
        document.getElementById('rejectModalNo').textContent = '#' + noRisalah;
        document.getElementById('rejectForm').action = "{{ url('/validasi') }}/" + id + "/reject";
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }
</script>
@endpush
