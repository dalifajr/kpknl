@extends('layouts.bmn_master')

@section('title', 'Daftar Koleksi Berkas Risalah Lelang — KPKNL Palembang')
@section('page_title', 'Daftar Koleksi Berkas Risalah Lelang')
@section('page_subtitle', 'Ditemukan ' . number_format($totalItems, 0, ',', '.') . ' berkas fisik akta risalah lelang yang sesuai kriteria pencarian — KPKNL Palembang')

@section('page_actions')
    <button class="btn btn-warning btn-sm d-flex align-items-center gap-2 fw-semibold text-dark shadow-sm" onclick="openBorrowModal()">
        <i class="fas fa-cart-shopping"></i> Pinjam (<span class="cart-action-count">0</span> Berkas)
    </button>
    <a href="{{ route('katalog.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2 text-primary fw-semibold shadow-sm">
        <i class="fas fa-arrows-rotate"></i> Reset Filter
    </a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <!-- FILTER & SEARCH BAR -->
        <form method="GET" action="{{ route('katalog.index') }}" class="row g-2 mb-3">
            <!-- Jenis Filter Buttons -->
            <div class="col-12 col-md-5">
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('katalog.index', array_merge(request()->except('type', 'page'), ['type' => 'all'])) }}" class="btn btn-sm {{ $type === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Semua (10k+)
                    </a>
                    <a href="{{ route('katalog.index', array_merge(request()->except('type', 'page'), ['type' => 'minuta'])) }}" class="btn btn-sm {{ $type === 'minuta' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Minuta (Laku)
                    </a>
                    <a href="{{ route('katalog.index', array_merge(request()->except('type', 'page'), ['type' => 'tap'])) }}" class="btn btn-sm {{ $type === 'tap' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        TAP
                    </a>
                    <a href="{{ route('katalog.index', array_merge(request()->except('type', 'page'), ['type' => 'batal'])) }}" class="btn btn-sm {{ $type === 'batal' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Batal
                    </a>
                </div>
                <input type="hidden" name="type" value="{{ $type }}">
            </div>

            <!-- Status Filter Dropdown -->
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status Fisik</option>
                    <option value="tersedia" {{ $status === 'tersedia' ? 'selected' : '' }}>Tersedia di Lemari</option>
                    <option value="dipinjam" {{ $status === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="col-6 col-md-4">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari No Risalah / Pelelang / Pemohon..." value="{{ $search }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(!empty($search))
                        <a href="{{ route('katalog.index', ['type' => $type, 'status' => $status]) }}" class="btn btn-outline-secondary" title="Hapus Pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- TABLE OF CATALOG ITEMS -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px; text-align: center;">Pilih</th>
                        <th>Jenis</th>
                        <th>Nomor Risalah</th>
                        <th>Tanggal</th>
                        <th>Pejabat Lelang</th>
                        <th>Pemohon Lelang</th>
                        <th>Lokasi Lemari & Box</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($risalahList as $item)
                        @php
                            $isAvailable = ($item->status === 'tersedia' || empty($item->status));
                        @endphp
                        <tr id="row-{{ $item->jenis }}-{{ $item->id }}">
                            <td style="text-align: center;">
                                <input
                                    type="checkbox"
                                    class="form-check-input item-cart-checkbox"
                                    data-id="{{ $item->id }}"
                                    data-jenis="{{ $item->jenis }}"
                                    data-no="{{ $item->no_risalah }}"
                                    data-pelelang="{{ $item->nama_pelelang }}"
                                    {{ !$isAvailable ? 'disabled' : '' }}
                                    onchange="toggleCart(this)"
                                    title="{{ $isAvailable ? 'Centang untuk pinjam' : 'Berkas sedang dipinjam' }}"
                                >
                            </td>
                            <td>
                                <span class="badge {{ $item->jenis === 'minuta' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($item->jenis === 'tap' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }} text-uppercase" style="font-size: 0.68rem;">
                                    {{ $item->jenis_label }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark">{{ $item->no_risalah ?? '-' }}</td>
                            <td class="text-muted">{{ $item->tgl_risalah ?? '-' }}</td>
                            <td>
                                <div class="fw-medium text-dark">{{ $item->nama_pelelang ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="text-muted text-truncate" style="max-width: 220px;" title="{{ $item->pemohon_lelang }}">
                                    {{ $item->pemohon_lelang ?? '-' }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                @php
                                    $rawLemari = trim((string)($item->lemari ?? ''));
                                    $rawBox = trim((string)($item->box ?? ''));

                                    $hasLemari = ($rawLemari !== '' && $rawLemari !== '-' && strtolower($rawLemari) !== 'null');
                                    $hasBox = ($rawBox !== '' && $rawBox !== '-' && strtolower($rawBox) !== 'null');
                                @endphp

                                @if(!$hasLemari && !$hasBox)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-nowrap" title="Berkas fisik belum dialokasikan nomor lemari dan box di ruang arsip Seksi HI">
                                        <i class="fas fa-inbox me-1"></i> Belum Dialokasikan
                                    </span>
                                @elseif($hasLemari && $hasBox)
                                    @php
                                        $lemariFormatted = (stripos($rawLemari, 'Lemari') !== false) ? $rawLemari : 'Lemari ' . $rawLemari;
                                        $boxFormatted = (stripos($rawBox, 'Box') !== false) ? $rawBox : 'Box ' . $rawBox;
                                    @endphp
                                    <span class="badge bg-light text-dark border text-nowrap shadow-xs">
                                        <i class="fas fa-box-archive me-1 text-primary"></i>
                                        <strong>{{ $lemariFormatted }}</strong> &bull; <span class="text-secondary">{{ $boxFormatted }}</span>
                                    </span>
                                @elseif($hasLemari && !$hasBox)
                                    @php
                                        $lemariFormatted = (stripos($rawLemari, 'Lemari') !== false) ? $rawLemari : 'Lemari ' . $rawLemari;
                                    @endphp
                                    <span class="badge bg-light text-dark border text-nowrap shadow-xs">
                                        <i class="fas fa-box-archive me-1 text-primary"></i>
                                        <strong>{{ $lemariFormatted }}</strong> &bull; <span class="text-muted">Box -</span>
                                    </span>
                                @else
                                    @php
                                        $boxFormatted = (stripos($rawBox, 'Box') !== false) ? $rawBox : 'Box ' . $rawBox;
                                    @endphp
                                    <span class="badge bg-light text-dark border text-nowrap shadow-xs">
                                        <i class="fas fa-box-archive me-1 text-primary"></i>
                                        <span class="text-muted">Lemari -</span> &bull; <span class="text-secondary">{{ $boxFormatted }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isAvailable)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-circle-check me-1"></i> Tersedia
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        <i class="fas fa-clock me-1"></i> Dipinjam
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-magnifying-glass fa-2x mb-2 text-muted d-block"></i>
                                Tidak ditemukan berkas risalah lelang yang sesuai dengan filter atau kata kunci pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between pt-3 mt-3 border-top gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $risalahList->firstItem() ?? 0 }}</strong> sampai <strong>{{ $risalahList->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($totalItems, 0, ',', '.') }}</strong> berkas
            </small>
            <div>
                {{ $risalahList->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: AJUKAN PEMINJAMAN BERKAS (MULTI-ITEM CART)                        -->
<!-- ========================================================================= -->
<div class="modal fade" id="borrowModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-paper-plane me-2 text-warning"></i> Formulir Permohonan Peminjaman Risalah Lelang
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('peminjaman.store') }}" method="POST" id="borrowForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-primary py-2 px-3 mb-3 d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fas fa-circle-info fs-5 text-primary"></i>
                        <div>
                            Anda sedang mengajukan peminjaman sebanyak <strong id="modalCartCount">0</strong> berkas risalah fisik. Pastikan berkas dijaga keutuhannya selama masa peminjaman.
                        </div>
                    </div>

                    <!-- Selected Items Container -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 0.8rem;">
                            Daftar Berkas Fisik yang Dipilih (Maks. 5 Berkas):
                        </label>
                        <div class="border rounded-3 p-2 bg-light overflow-y-auto" style="max-height: 140px;" id="selectedItemsContainer">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Nama Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="nama_peminjam" class="form-control form-control-sm" required value="{{ Auth::user()->name ?? '' }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Tanggal Peminjaman <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_peminjaman" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Rencana Pengembalian <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_pengembalian" class="form-control form-control-sm" required value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">
                                Alasan / Keperluan Peminjaman Berkas <span class="text-danger">*</span>
                            </label>
                            <textarea name="keperluan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Pemeriksaan berkas perkara sengketa lelang di Pengadilan Negeri..." required></textarea>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Items -->
                    <div id="hiddenItemInputs"></div>
                </div>
                <div class="modal-footer py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3" id="btnSubmitBorrow">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Permohonan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleCart(checkbox) {
        const id = parseInt(checkbox.dataset.id);
        const jenis = checkbox.dataset.jenis;
        const no = checkbox.dataset.no;
        const pelelang = checkbox.dataset.pelelang;

        const existsIdx = borrowCart.findIndex(item => item.id === id && item.jenis === jenis);

        if (checkbox.checked) {
            if (existsIdx === -1) {
                if (borrowCart.length >= 5) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Maksimum',
                        text: 'Maksimum peminjaman sekaligus adalah 5 berkas risalah.',
                        confirmButtonColor: '#0c306b'
                    });
                    checkbox.checked = false;
                    return;
                }
                borrowCart.push({ id, jenis, no, pelelang });
            }
        } else {
            if (existsIdx !== -1) {
                borrowCart.splice(existsIdx, 1);
            }
        }

        updateCartUI();
        updateKatalogCheckboxes();
    }

    function updateKatalogCheckboxes() {
        document.querySelectorAll('.item-cart-checkbox').forEach(cb => {
            const id = parseInt(cb.dataset.id);
            const jenis = cb.dataset.jenis;
            const isChecked = borrowCart.some(item => item.id === id && item.jenis === jenis);
            cb.checked = isChecked;

            const tr = document.getElementById(`row-${jenis}-${id}`);
            if (tr) {
                if (isChecked) {
                    tr.classList.add('table-warning');
                } else {
                    tr.classList.remove('table-warning');
                }
            }
        });

        document.querySelectorAll('.cart-action-count').forEach(span => {
            span.textContent = borrowCart.length;
        });
    }

    function openBorrowModal() {
        if (borrowCart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Centang minimal satu berkas risalah lelang yang ingin Anda pinjam.',
                confirmButtonColor: '#0c306b'
            });
            return;
        }

        const container = document.getElementById('selectedItemsContainer');
        const hiddenInputs = document.getElementById('hiddenItemInputs');
        const countSpan = document.getElementById('modalCartCount');

        container.innerHTML = '';
        hiddenInputs.innerHTML = '';
        countSpan.textContent = borrowCart.length;

        borrowCart.forEach((item, idx) => {
            // Display row
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between py-1 border-bottom last:border-0';
            div.style.fontSize = '0.78rem';
            div.innerHTML = `
                <div>
                    <strong class="text-dark me-2">${item.no}</strong>
                    <span class="badge bg-secondary-subtle text-secondary text-uppercase me-2">${item.jenis}</span>
                    <span class="text-muted">Pelelang: ${item.pelelang}</span>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeItemFromCart(${item.id}, '${item.jenis}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);

            // Hidden inputs for form submit
            hiddenInputs.innerHTML += `
                <input type="hidden" name="items[${idx}][id]" value="${item.id}">
                <input type="hidden" name="items[${idx}][jenis]" value="${item.jenis}">
            `;
        });

        const modalEl = document.getElementById('borrowModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function removeItemFromCart(id, jenis) {
        const idx = borrowCart.findIndex(item => item.id === id && item.jenis === jenis);
        if (idx !== -1) {
            borrowCart.splice(idx, 1);
            updateCartUI();
            updateKatalogCheckboxes();
            if (borrowCart.length === 0) {
                const modalEl = document.getElementById('borrowModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else {
                openBorrowModal();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateKatalogCheckboxes();
    });
</script>
@endpush
