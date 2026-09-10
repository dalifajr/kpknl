@extends('layouts.bmn_master')

@section('title', 'Berkas Risalah yang Perlu Diperbaiki — KPKNL Palembang')
@section('page_title', 'Berkas Risalah yang Perlu Diperbaiki')
@section('page_subtitle', 'Koreksi dan kirim ulang berkas risalah lelang yang dikembalikan oleh Admin Seksi HI — KPKNL Palembang')

@section('page_actions')
    <a href="{{ route('revisi.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-arrows-rotate"></i> Refresh
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead class="table-light">
                    <tr>
                        <th>Nomor Risalah</th>
                        <th>Jenis</th>
                        <th>Pejabat Lelang</th>
                        <th>Pemohon Lelang</th>
                        <th>Catatan Revisi dari Seksi HI</th>
                        <th style="text-align: right;">Aksi Koreksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revisiList as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item->no_risalah }}</td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-uppercase" style="font-size: 0.68rem;">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $item->nama_pelelang }}</td>
                            <td class="text-muted">{{ $item->pemohon_lelang ?? '-' }}</td>
                            <td>
                                <div class="p-2 rounded-3 bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.78rem;">
                                    <i class="fas fa-triangle-exclamation me-1"></i>
                                    {{ $item->catatan ?? 'Koreksi penulisan dan kelengkapan berkas fisik.' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary py-1 px-2.5"
                                    style="font-size: 0.75rem;"
                                    onclick="openEditRevisiModal({{ json_encode($item) }})"
                                >
                                    <i class="fas fa-pen-to-square me-1"></i> Koreksi & Kirim Ulang
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-circle-check fa-2x mb-2 text-success d-block"></i>
                                Tidak ada risalah yang memerlukan revisi. Seluruh berkas telah terverifikasi dengan baik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 mt-3 border-top gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $revisiList->firstItem() ?? 0 }}</strong> sampai <strong>{{ $revisiList->lastItem() ?? 0 }}</strong> dari <strong>{{ $revisiList->total() }}</strong> berkas
            </small>
            <div>
                {{ $revisiList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: KOREKSI & RESUBMIT REVISI (PELELANG)                               -->
<!-- ========================================================================= -->
<div class="modal fade" id="editRevisiModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-pen-to-square me-2"></i> Perbaiki Risalah <span id="revisiModalNo"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="revisiForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size: 0.78rem;">
                        <strong>Catatan Admin Seksi HI:</strong>
                        <div id="revisiModalCatatan" class="mt-1">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Nomor Risalah Lelang <span class="text-danger">*</span></label>
                        <input type="text" name="no_risalah" id="revisiInputNo" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Tanggal Risalah <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_risalah" id="revisiInputTgl" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Pemohon Lelang / Penjual <span class="text-danger">*</span></label>
                        <input type="text" name="pemohon_lelang" id="revisiInputPemohon" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Ulang ke Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditRevisiModal(item) {
        document.getElementById('revisiModalNo').textContent = '#' + item.no_risalah;
        document.getElementById('revisiModalCatatan').textContent = item.catatan || 'Koreksi penulisan dan kelengkapan dokumen.';
        document.getElementById('revisiInputNo').value = item.no_risalah;
        document.getElementById('revisiInputTgl').value = item.tgl_risalah || '';
        document.getElementById('revisiInputPemohon').value = item.pemohon_lelang || '';

        document.getElementById('revisiForm').action = "{{ url('/revisi') }}/" + item.id + "/resubmit";
        const modal = new bootstrap.Modal(document.getElementById('editRevisiModal'));
        modal.show();
    }
</script>
@endpush
