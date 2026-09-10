@extends('layouts.app')

@section('title', 'Gudang Aset')

@push('styles')
<style>
    .minimal-table tbody tr {
        transition: background-color 0.15s ease-in-out;
        cursor: pointer;
    }
    .minimal-table tbody tr:hover {
        background-color: #f0f7ff !important;
    }
    .minimal-table th {
        font-weight: 600;
        color: #37474f;
        background-color: #fafafa;
        border-bottom: 2px solid #e0e0e0;
        padding: 14px 10px;
    }
    .minimal-table td {
        padding: 12px 10px;
        font-size: 0.95rem;
    }
    .red-dot-pulsing {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #e53935;
        border-radius: 50%;
        margin-right: 6px;
        box-shadow: 0 0 0 rgba(229, 57, 53, 0.4);
        animation: pulse-red 1.6s infinite;
        vertical-align: middle;
    }
    @keyframes pulse-red {
        0% {
            box-shadow: 0 0 0 0 rgba(229, 57, 53, 0.7);
        }
        70% {
            box-shadow: 0 0 0 7px rgba(229, 57, 53, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(229, 57, 53, 0);
        }
    }
</style>
@endpush

@section('content')
<div class="card" style="border-radius: 8px;">
    <div class="card-content">
        
        <!-- Filters & Actions Bar -->
        <div class="row" style="margin-bottom: 15px;">
            <form action="{{ route('assets.index') }}" method="GET" id="filter-form" class="col s12 xl8">
                <div class="row" style="margin-bottom: 0;">
                    <div class="input-field col s12 m4 l3">
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari kode/jalan/waker...">
                        <label for="search" class="active">Pencarian</label>
                    </div>
                    
                    <div class="input-field col s12 m4 l2">
                        <select name="jenis_aset" onchange="document.getElementById('filter-form').submit();">
                            <option value="">Semua Jenis</option>
                            <option value="Tanah" {{ request('jenis_aset') == 'Tanah' ? 'selected' : '' }}>Tanah</option>
                            <option value="Bangunan" {{ request('jenis_aset') == 'Bangunan' ? 'selected' : '' }}>Bangunan</option>
                            <option value="Tanah dan Bangunan" {{ request('jenis_aset') == 'Tanah dan Bangunan' ? 'selected' : '' }}>Tanah & Bangunan</option>
                        </select>
                        <label>Jenis Aset</label>
                    </div>

                    <div class="input-field col s12 m4 l3">
                        <select name="kondisi_aset" onchange="document.getElementById('filter-form').submit();">
                            <option value="">Semua Kondisi</option>
                            <option value="DIHUNI" {{ request('kondisi_aset') == 'DIHUNI' ? 'selected' : '' }}>DIHUNI</option>
                            <option value="DIGUNAKAN PIHAK KETIGA" {{ request('kondisi_aset') == 'DIGUNAKAN PIHAK KETIGA' ? 'selected' : '' }}>PIHAK KETIGA</option>
                            <option value="KOSONG" {{ request('kondisi_aset') == 'KOSONG' ? 'selected' : '' }}>KOSONG</option>
                            <option value="DISEWAKAN" {{ request('kondisi_aset') == 'DISEWAKAN' ? 'selected' : '' }}>DISEWAKAN</option>
                            <option value="DILELANG" {{ request('kondisi_aset') == 'DILELANG' ? 'selected' : '' }}>DILELANG</option>
                            <option value="LAIN-LAIN" {{ request('kondisi_aset') == 'LAIN-LAIN' ? 'selected' : '' }}>LAIN-LAIN</option>
                        </select>
                        <label>Kondisi Aset</label>
                    </div>

                    <div class="input-field col s6 m2 l2">
                        <select name="semester" onchange="document.getElementById('filter-form').submit();">
                            <option value="">Semua Sem</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Semester I</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Semester II</option>
                        </select>
                        <label>Semester</label>
                    </div>

                    <div class="input-field col s6 m2 l2">
                        <select name="tahun" onchange="document.getElementById('filter-form').submit();">
                            <option value="">Semua Thn</option>
                            @php $currentYear = date('Y'); @endphp
                            @for($i = $currentYear; $i >= $currentYear - 5; $i--)
                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <label>Tahun</label>
                    </div>
                </div>
                
                @if(request('search') || request('jenis_aset') || request('kondisi_aset') || request('semester') || request('tahun'))
                    <div style="margin-top: -10px; margin-bottom: 10px;">
                        <a href="{{ route('assets.index') }}" class="red-text" style="font-size: 0.85rem;"><i class="material-icons tiny">clear</i> Hapus Semua Filter</a>
                    </div>
                @endif
            </form>

            <div class="col s12 xl4 right-align" style="margin-top: 15px;">
                <a class="dropdown-trigger btn green darken-1 waves-effect" href="#" data-target="dropdown-export">
                    <i class="material-icons left">download</i> Cetak / Export
                </a>
                
                <ul id='dropdown-export' class='dropdown-content'>
                    <li><a href="#modalExportSemester" class="modal-trigger black-text"><i class="material-icons red-text">picture_as_pdf</i> Lap. Semester (PDF)</a></li>
                    <li><a href="#!" onclick="triggerExport('{{ route('assets.exportBukuProfilPdf') }}', event, 'Buku Profil Aset (PDF)', 'Laporan_Buku_Profil_Aset.pdf')" class="black-text"><i class="material-icons blue-text">book</i> Buku Profil (PDF)</a></li>
                    <li><a href="#!" onclick="triggerExport('{{ route('assets.exportResumePdf') }}', event, 'Resume Profil Aset (PDF)', 'Resume_Profil_Aset.pdf')" class="black-text"><i class="material-icons green-text text-darken-1">article</i> Resume Profil (PDF)</a></li>
                </ul>

                <a href="{{ route('assets.create') }}" class="btn blue darken-3 waves-effect waves-light" style="margin-left: 8px;">
                    <i class="material-icons left">add</i> Tambah Aset
                </a>
            </div>
        </div>

        <!-- Tabel Minimalis Gudang Aset -->
        <form action="{{ route('assets.bulkDestroy') }}" method="POST" id="bulk-action-form">
            @csrf
            
            <div style="margin-bottom: 10px; display: none;" id="bulk-delete-container">
                <button type="button" class="btn waves-effect waves-light red btn-small" onclick="confirmBulkDelete()">
                    <i class="material-icons left">delete_sweep</i> Hapus Terpilih (<span id="selected-count">0</span>)
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="highlight minimal-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <label>
                                    <input type="checkbox" id="checkAll" class="filled-in" />
                                    <span></span>
                                </label>
                            </th>
                            <th>Kode Aset</th>
                            <th>Tahun</th>
                            <th>Jenis</th>
                            <th>Alamat Saat Ini</th>
                            <th>Kota/Kab</th>
                            <th>Wakil Kerja</th>
                            <th>Kondisi</th>
                            <th>Papan Nama</th>
                            <th>Keterangan</th>
                            <th class="center-align" width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        <tr onclick="window.location='{{ route('assets.show', $asset->id) }}'">
                            <td onclick="event.stopPropagation()">
                                <label>
                                    <input type="checkbox" name="ids[]" value="{{ $asset->id }}" class="asset-checkbox filled-in" />
                                    <span></span>
                                </label>
                            </td>
                            <td>
                                @if(strtoupper($asset->kondisi_aset ?? '') === 'DISEWAKAN' && $asset->is_sewa_expiring)
                                    <span class="red-dot-pulsing tooltipped" data-position="top" data-tooltip="{{ $asset->sewa_status_info['message'] }} (Perlu Perpanjangan)"></span>
                                @endif
                                <strong class="blue-text text-darken-3">{{ $asset->kode_aset }}</strong>
                            </td>
                            <td><span class="chip blue-text text-darken-3 grey lighten-4" style="font-weight: 600; margin: 0; font-size: 11px;">{{ $asset->tahun ? ($asset->tahun . ($asset->semester ? ' (S' . $asset->semester . ')' : '')) : '-' }}</span></td>
                            <td>{{ $asset->jenis_aset }}</td>
                            <td>{{ $asset->alamat_namajalan ? Str::limit($asset->alamat_namajalan, 30) : '-' }}</td>
                            <td>{{ $asset->regency->name ?? '-' }}</td>
                            <td>{{ $asset->wakil_kerja ?? '-' }}</td>
                            <td>
                                @php
                                    $kondisiUpper = strtoupper($asset->kondisi_aset ?? '');
                                    $badgeColor = 'grey darken-1';
                                    if ($kondisiUpper == 'DIHUNI') $badgeColor = 'blue darken-2';
                                    elseif ($kondisiUpper == 'DIGUNAKAN PIHAK KETIGA') $badgeColor = 'green darken-2';
                                    elseif ($kondisiUpper == 'KOSONG') $badgeColor = 'red darken-2';
                                    elseif ($kondisiUpper == 'DISEWAKAN') $badgeColor = $asset->sewa_status_info['color'] ?? 'teal darken-2';
                                    elseif ($kondisiUpper == 'DILELANG') $badgeColor = 'orange darken-3';
                                @endphp
                                <span class="new badge {{ $badgeColor }}" data-badge-caption="" style="float: left; margin-left: 0;">
                                    {{ $asset->kondisi_aset ?? 'N/A' }}
                                    @if($kondisiUpper == 'DISEWAKAN' && !empty($asset->sewa_status_info['badge']))
                                        ({{ $asset->sewa_status_info['badge'] }})
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($asset->papan_nama)
                                    <span class="new badge green" data-badge-caption="Ada" style="float: left; margin-left: 0;"></span>
                                @else
                                    <span class="new badge red" data-badge-caption="Tidak" style="float: left; margin-left: 0;"></span>
                                @endif
                            </td>
                            <td>{{ Str::limit($asset->keterangan_kondisi_aset ?? '-', 25) }}</td>
                            <td class="center-align" style="white-space: nowrap;" onclick="event.stopPropagation()">
                                <a class="dropdown-trigger btn-floating btn-small waves-effect waves-light blue darken-1" href="#" data-target="action-{{ $asset->id }}">
                                    <i class="material-icons">more_vert</i>
                                </a>
                                <ul id="action-{{ $asset->id }}" class="dropdown-content">
                                    <li><a href="{{ route('assets.show', $asset->id) }}" class="black-text"><i class="material-icons blue-text">visibility</i> Detail</a></li>
                                    <li><a href="{{ route('assets.singleBukuProfilPdf', $asset->id) }}" target="_blank" class="black-text"><i class="material-icons light-blue-text">book</i> Buku Profil</a></li>
                                    <li><a href="{{ route('assets.edit', $asset->id) }}" class="black-text"><i class="material-icons amber-text text-darken-2">edit</i> Edit</a></li>
                                    <li class="divider" tabindex="-1"></li>
                                    <li><a href="#!" onclick="confirmDeleteAsset({{ $asset->id }})" class="black-text"><i class="material-icons red-text">delete</i> Hapus</a></li>
                                </ul>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="center-align grey-text" style="padding: 40px 0;">Belum ada data aset.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div style="margin-top: 25px;">
            {{ $assets->links() }}
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Hapus Tunggal -->
<div id="modalDeleteSingle" class="modal" style="width: 400px; border-radius: 8px;">
    <div class="modal-content">
        <h4 style="font-size: 1.5rem; font-weight: 600;">Konfirmasi Hapus</h4>
        <p>Apakah Anda yakin ingin menghapus data aset ini secara permanen?</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
        <a href="#!" id="btn-confirm-delete-single" class="waves-effect waves-light btn red darken-1">Hapus</a>
    </div>
</div>

<!-- Modal Hapus Massal -->
<div id="modalDeleteBulk" class="modal" style="width: 450px; border-radius: 8px;">
    <div class="modal-content">
        <h4 style="font-size: 1.5rem; font-weight: 600;">Hapus Massal</h4>
        <p>Anda akan menghapus <b id="bulk-count-display"></b> data terpilih secara bersamaan. Lanjutkan?</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
        <a href="#!" id="btn-confirm-delete-bulk" class="waves-effect waves-light btn red darken-1">Hapus Semua</a>
    </div>
</div>

<!-- Modal Pilih Semester Laporan PDF -->
<div id="modalExportSemester" class="modal" style="width: 450px; border-radius: 8px;">
    <div class="modal-content">
        <h4 style="font-size: 1.3rem; font-weight: 600;" class="blue-text text-darken-2"><i class="material-icons left">picture_as_pdf</i> Pilih Semester Laporan</h4>
        <p class="grey-text" style="font-size: 0.9rem;">Pilih periode semester dan tahun data aset yang ingin dicetak:</p>
        
        <div style="margin-top: 20px;">
            <label style="font-size: 0.9rem; font-weight: 600; color: #333; display: block; margin-bottom: 10px;">Periode Semester *</label>
            <p style="margin-bottom: 10px;">
                <label>
                    <input name="export_semester_choice" type="radio" value="1" class="with-gap" {{ request('semester', '1') == '1' ? 'checked' : '' }} />
                    <span style="font-weight: 500; color: #212121;">Semester I (Januari - Juni)</span>
                </label>
            </p>
            <p style="margin-bottom: 15px;">
                <label>
                    <input name="export_semester_choice" type="radio" value="2" class="with-gap" {{ request('semester') == '2' ? 'checked' : '' }} />
                    <span style="font-weight: 500; color: #212121;">Semester II (Juli - Desember)</span>
                </label>
            </p>

            <div class="input-field" style="margin-top: 20px;">
                <input type="number" id="export_tahun_choice" value="{{ request('tahun', date('Y')) }}" min="2000" max="2099">
                <label for="export_tahun_choice" class="active">Tahun Laporan</label>
            </div>

            <p style="margin-top: 25px;">
                <label>
                    <input type="checkbox" id="export_print_all_data" class="filled-in" />
                    <span style="font-weight: 600; color: #d32f2f;">Print Semua Semester & Tahun</span>
                </label>
                <br>
                <small class="grey-text" style="margin-left: 35px;">(Abaikan pilihan periode, ambil seluruh data aset)</small>
            </p>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
        <button type="button" onclick="executeExportSemesterPdf()" class="waves-effect waves-light btn red darken-1"><i class="material-icons left">picture_as_pdf</i> Cetak Laporan PDF</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let selectedAssetId = null;

    document.addEventListener('DOMContentLoaded', function() {
        var elemsDropdown = document.querySelectorAll('.dropdown-trigger');
        M.Dropdown.init(elemsDropdown, { constrainWidth: false, coverTrigger: false });

        var elemsModal = document.querySelectorAll('.modal');
        M.Modal.init(elemsModal);

        var elemsSelect = document.querySelectorAll('select');
        M.FormSelect.init(elemsSelect);
    });

    function updateBulkDeleteBtn() {
        let checkboxes = document.querySelectorAll('.asset-checkbox:checked');
        let count = checkboxes.length;
        let container = document.getElementById('bulk-delete-container');
        if (container) {
            if (count > 0) {
                container.style.display = 'block';
                document.getElementById('selected-count').innerText = count;
            } else {
                container.style.display = 'none';
            }
        }
    }

    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.asset-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkDeleteBtn();
    });

    document.querySelectorAll('.asset-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteBtn);
    });

    function confirmDeleteAsset(id) {
        selectedAssetId = id;
        let instance = M.Modal.getInstance(document.getElementById('modalDeleteSingle'));
        instance.open();
    }

    document.getElementById('btn-confirm-delete-single').addEventListener('click', function() {
        if(selectedAssetId) {
            let form = document.getElementById('delete-form');
            form.action = '{{ url('assets') }}/' + selectedAssetId;
            form.submit();
        }
    });

    function confirmBulkDelete() {
        let count = document.querySelectorAll('.asset-checkbox:checked').length;
        document.getElementById('bulk-count-display').innerText = count;
        let instance = M.Modal.getInstance(document.getElementById('modalDeleteBulk'));
        instance.open();
    }

    document.getElementById('btn-confirm-delete-bulk').addEventListener('click', function() {
        let form = document.getElementById('bulk-action-form');
        let methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        form.submit();
    });

    function executeExportSemesterPdf() {
        let baseUrl = '{{ route('assets.exportPdf') }}';
        let form = document.getElementById('filter-form');
        let params = new URLSearchParams(new FormData(form));
        
        let selectedSemester = document.querySelector('input[name="export_semester_choice"]:checked').value;
        params.set('semester', selectedSemester);

        let selectedTahun = document.getElementById('export_tahun_choice').value || '{{ date('Y') }}';
        if (selectedTahun) {
            params.set('tahun', selectedTahun);
        }

        let printAll = document.getElementById('export_print_all_data').checked;
        if (printAll) {
            params.set('print_all_data', '1');
        }

        let checkedIds = Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => cb.value);
        if (checkedIds.length > 0) {
            params.set('ids', checkedIds.join(','));
        }

        let instance = M.Modal.getInstance(document.getElementById('modalExportSemester'));
        if (instance) instance.close();

        let fullUrl = baseUrl + '?' + params.toString();
        let semName = selectedSemester === '2' ? 'Sem_II' : 'Sem_I';
        startExportWithProgress(fullUrl, 'Laporan Semester ' + (selectedSemester === '2' ? 'II' : 'I') + ' (' + selectedTahun + ')', 'Laporan_Rekapitulasi_' + semName + '_' + selectedTahun + '.pdf');
    }

    function triggerExport(baseUrl, event, title, filename) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        let dropTrigger = document.querySelector('.dropdown-trigger[data-target="dropdown-export"]');
        if (dropTrigger) {
            let dropInstance = M.Dropdown.getInstance(dropTrigger);
            if (dropInstance) dropInstance.close();
        }

        let form = document.getElementById('filter-form');
        let params = new URLSearchParams(new FormData(form));
        
        let checkedIds = Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => cb.value);
        if (checkedIds.length > 0) {
            params.set('ids', checkedIds.join(','));
        }

        let fullUrl = baseUrl + '?' + params.toString();
        startExportWithProgress(fullUrl, title || 'Mengekspor Dokumen PDF...', filename || 'Dokumen_Aset.pdf');
    }

</script>
@endpush

