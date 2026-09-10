@extends('layouts.bmn_master')

@section('title', 'Daftar Alur Kerja Peminjaman Berkas Fisik — KPKNL Palembang')
@section('page_title', 'Daftar Alur Kerja Peminjaman Berkas Fisik')
@section('page_subtitle', 'Monitoring dan eksekusi 5 tahap alur permohonan, serah terima fisik, hingga pengembalian berkas — KPKNL Palembang')

@section('page_actions')
    <a href="{{ route('katalog.index') }}" class="btn btn-warning btn-sm d-flex align-items-center gap-2 fw-semibold text-dark shadow-sm">
        <i class="fas fa-cart-shopping"></i> Pinjam Berkas Baru
    </a>
    <a href="{{ route('peminjaman.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-arrows-rotate"></i> Refresh
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <!-- STATUS WORKFLOW TABS -->
        <div class="d-flex gap-1 overflow-x-auto pb-2 mb-3">
            @php
                $statusTabs = [
                    'all' => ['label' => 'Semua Status', 'icon' => 'fa-layer-group', 'count' => $counts['all']],
                    'Proses Peminjaman' => ['label' => '1. Proses Permohonan', 'icon' => 'fa-file-signature', 'count' => $counts['Proses Peminjaman']],
                    'Menunggu Konfirmasi' => ['label' => '2. Menunggu Ambil Fisik', 'icon' => 'fa-handshake', 'count' => $counts['Menunggu Konfirmasi']],
                    'Sedang Dipinjam' => ['label' => '3. Sedang Dipinjam', 'icon' => 'fa-box-archive', 'count' => $counts['Sedang Dipinjam']],
                    'Proses Pengembalian' => ['label' => '4. Proses Kembali', 'icon' => 'fa-arrow-rotate-left', 'count' => $counts['Proses Pengembalian']],
                    'Sudah Dikembalikan' => ['label' => '5. Selesai Kembali', 'icon' => 'fa-check-double', 'count' => $counts['Sudah Dikembalikan']],
                ];
            @endphp

            @foreach($statusTabs as $sKey => $sVal)
                <a
                    href="{{ route('peminjaman.index', array_merge(request()->except('status', 'page'), ['status' => $sKey])) }}"
                    class="btn btn-sm text-nowrap {{ ($status === $sKey || ($sKey === 'all' && empty($status))) ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}"
                    style="font-size: 0.78rem;"
                >
                    <i class="fas {{ $sVal['icon'] }} me-1"></i> {{ $sVal['label'] }}
                    <span class="badge bg-white text-dark ms-1 rounded-pill" style="font-size: 0.65rem;">{{ $sVal['count'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- SEARCH BAR -->
        <form method="GET" action="{{ route('peminjaman.index') }}" class="row g-2 mb-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama peminjam / alasan / nomor risalah..." value="{{ $search }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(!empty($search))
                        <a href="{{ route('peminjaman.index', ['status' => $status]) }}" class="btn btn-outline-secondary" title="Hapus Pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- PEMINJAMAN TABLE -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead class="table-light">
                    <tr>
                        <th>No. Pinjam</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Total Berkas</th>
                        <th>Status Alur</th>
                        <th style="text-align: right;">Aksi Alur Kerja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamanList as $loan)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $loan->id }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $loan->nama_peminjam }}</div>
                                <small class="text-muted text-truncate d-block" style="max-width: 240px;">
                                    {{ $loan->alasan_peminjaman ?? ($loan->keperluan ?? 'Keperluan dinas') }}
                                </small>
                            </td>
                            <td class="text-muted">{{ $loan->tgl_peminjaman ?? '-' }}</td>
                            <td class="text-muted">{{ $loan->tgl_pengembalian ?? '-' }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light border py-0 px-2 text-dark"
                                    style="font-size: 0.75rem;"
                                    onclick="showLoanDetail({{ json_encode($loan) }})"
                                >
                                    <i class="fas fa-list-check me-1 text-primary"></i>
                                    1 Berkas
                                </button>
                            </td>
                            <td>
                                <span class="badge {{ $loan->getStatusBadgeClass() }} px-2 py-1">
                                    {{ $loan->status }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <!-- STAGE 1: Proses Peminjaman -> Admin Approve -->
                                @if($loan->status === \App\Models\Peminjaman::STATUS_PROSES && Auth::user()->role === 'admin')
                                    <form action="{{ route('peminjaman.approve', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'Setujui Permohonan Pinjam?', 'Berkas fisik akan disiapkan untuk diambil oleh peminjam.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary py-1 px-2.5" style="font-size: 0.75rem;">
                                            <i class="fas fa-check me-1"></i> Setujui
                                        </button>
                                    </form>
                                @endif

                                <!-- STAGE 2: Menunggu Konfirmasi -> Borrower/Admin Confirms Fisik Diambil -->
                                @if($loan->status === \App\Models\Peminjaman::STATUS_MENUNGGU_KONFIRMASI)
                                    <form action="{{ route('peminjaman.confirm-receive', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'Konfirmasi Penerimaan Fisik?', 'Pastikan berkas fisik risalah telah Anda pegang dan periksa keutuhannya.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2.5" style="font-size: 0.75rem;">
                                            <i class="fas fa-handshake me-1"></i> Konfirmasi Ambil
                                        </button>
                                    </form>
                                @endif

                                <!-- STAGE 3: Sedang Dipinjam -> Borrower/Admin requests return -->
                                @if($loan->status === \App\Models\Peminjaman::STATUS_SEDANG_DIPINJAM)
                                    <form action="{{ route('peminjaman.return-request', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'Ajukan Pengembalian Fisik?', 'Serahkan berkas fisik ke Admin Seksi Hukum dan Informasi untuk diverifikasi.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning text-dark py-1 px-2.5 fw-semibold" style="font-size: 0.75rem;">
                                            <i class="fas fa-arrow-rotate-left me-1"></i> Ajukan Kembali
                                        </button>
                                    </form>
                                @endif

                                <!-- STAGE 4: Proses Pengembalian -> Admin verifies physical return -->
                                @if($loan->status === \App\Models\Peminjaman::STATUS_PROSES_PENGEMBALIAN && Auth::user()->role === 'admin')
                                    <form action="{{ route('peminjaman.verify-return', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'Verifikasi Fisik Pengembalian?', 'Berkas fisik akan dikembalikan ke lemari arsip dan status risalah dalam katalog akan pulih menjadi Tersedia.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2.5" style="font-size: 0.75rem;">
                                            <i class="fas fa-boxes-packing me-1"></i> Verifikasi Selesai
                                        </button>
                                    </form>
                                @endif

                                <!-- STAGE 5: Sudah Dikembalikan -->
                                @if($loan->status === \App\Models\Peminjaman::STATUS_SUDAH_DIKEMBALIKAN)
                                    <span class="text-muted small">
                                        <i class="fas fa-circle-check text-success me-1"></i> Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 text-muted d-block"></i>
                                Tidak ada data permohonan peminjaman dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 mt-3 border-top gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $peminjamanList->firstItem() ?? 0 }}</strong> sampai <strong>{{ $peminjamanList->lastItem() ?? 0 }}</strong> dari <strong>{{ $peminjamanList->total() }}</strong> transaksi
            </small>
            <div>
                {{ $peminjamanList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL PEMINJAMAN BERKAS FISIK                                     -->
<!-- ========================================================================= -->
<div class="modal fade" id="loanDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-file-lines me-2 text-warning"></i> Detail Peminjaman <span id="detailModalTitleId">#</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 mb-3 bg-light p-3 rounded-3 border" style="font-size: 0.82rem;">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Peminjam:</span>
                        <strong id="detailPeminjam">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Tanggal Pinjam:</span>
                        <strong id="detailTglPinjam">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Batas Kembali:</span>
                        <strong id="detailTglKembali">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Status Alur:</span>
                        <div id="detailStatusBadge">-</div>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <span class="text-muted d-block">Alasan / Keperluan Kedinasan:</span>
                        <div id="detailAlasan">-</div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">
                    Daftar Berkas Fisik Terkait:
                </h6>
                <div class="table-responsive border rounded-3">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis</th>
                                <th>Nomor Risalah</th>
                                <th>Tanggal Risalah</th>
                                <th>Pejabat Lelang</th>
                                <th>Lokasi Lemari & Box</th>
                            </tr>
                        </thead>
                        <tbody id="detailItemsTableBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2.5 px-4 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function confirmAction(e, title, text) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0c306b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    function showLoanDetail(loan) {
        document.getElementById('detailModalTitleId').textContent = '#' + loan.id;
        document.getElementById('detailPeminjam').textContent = loan.nama_peminjam || '-';
        document.getElementById('detailTglPinjam').textContent = loan.tgl_peminjaman || '-';
        document.getElementById('detailTglKembali').textContent = loan.tgl_pengembalian || '-';
        document.getElementById('detailAlasan').textContent = loan.alasan_peminjaman || loan.keperluan || 'Keperluan kedinasan';
        document.getElementById('detailStatusBadge').innerHTML = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">${loan.status}</span>`;

        const tbody = document.getElementById('detailItemsTableBody');
        tbody.innerHTML = '';

        const formatLemariBox = (lemari, box) => {
            const l = (lemari || '').toString().trim();
            const b = (box || '').toString().trim();
            const isLEmpty = !l || l === '-' || l.toLowerCase() === 'null';
            const isBEmpty = !b || b === '-' || b.toLowerCase() === 'null';
            if (isLEmpty && isBEmpty) return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-nowrap"><i class="fas fa-inbox me-1"></i> Belum Dialokasikan</span>';
            const lStr = !isLEmpty ? (l.toLowerCase().includes('lemari') ? l : 'Lemari ' + l) : '<span class="text-muted">Lemari -</span>';
            const bStr = !isBEmpty ? (b.toLowerCase().includes('box') ? b : 'Box ' + b) : '<span class="text-muted">Box -</span>';
            return `<span class="badge bg-light text-dark border text-nowrap"><i class="fas fa-box-archive me-1 text-primary"></i> ${lStr} &bull; ${bStr}</span>`;
        };

        if (loan.items && loan.items.length > 0) {
            loan.items.forEach(it => {
                const tr = document.createElement('tr');
                const itLemari = it.risalah ? it.risalah.lemari : '';
                const itBox = it.risalah ? it.risalah.box : '';
                tr.innerHTML = `
                    <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">${it.jenis_risalah}</span></td>
                    <td class="fw-bold">${it.risalah ? it.risalah.no_risalah : ('#' + it.risalah_id)}</td>
                    <td class="text-muted">${it.risalah ? (it.risalah.tgl_risalah || '-') : '-'}</td>
                    <td>${it.risalah ? (it.risalah.nama_pelelang || '-') : '-'}</td>
                    <td>${formatLemariBox(itLemari, itBox)}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="badge bg-primary-subtle text-primary">RISALAH</span></td>
                <td class="fw-bold">${loan.no_risalah || '-'}</td>
                <td class="text-muted">${loan.tgl_risalah || '-'}</td>
                <td>${loan.nama_pelelang || '-'}</td>
                <td>${formatLemariBox(loan.lemari, loan.box)}</td>
            `;
            tbody.appendChild(tr);
        }

        const modalEl = document.getElementById('loanDetailModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
