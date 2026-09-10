@extends('layouts.app')

@section('title', 'Pratinjau & Verifikasi Impor Aset')

@push('styles')
<style>
    .subinfo-card {
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 14px;
        margin-bottom: 12px;
    }
    .subinfo-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .preview-detail-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #78909c;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .preview-detail-value {
        font-size: 0.92rem;
        font-weight: 500;
        color: #263238;
        margin: 2px 0 8px 0;
    }
    .column-group-box {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 12px;
        background: #fafafa;
        margin-bottom: 10px;
    }
    .column-group-header {
        font-weight: 700;
        font-size: 0.9rem;
        color: #1565c0;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endpush

@section('content')
<form action="{{ route('assets.import.execute') }}" method="POST" id="form-import-execute">
    @csrf
    <input type="hidden" name="preview_token" value="{{ $previewToken }}">

    {{-- Header & Summary Bar --}}
    <div class="row" style="margin-bottom: 20px;">
        <div class="col s12">
            <div class="card-panel blue lighten-5" style="border-left: 5px solid #1565c0; border-radius: 6px; padding: 20px 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <a href="{{ route('assets.import.index') }}" class="blue-text text-darken-3" style="font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                            <i class="material-icons tiny">arrow_back</i> Kembali ke Upload
                        </a>
                        <h4 class="blue-text text-darken-3" style="margin: 0; font-weight: 700; font-size: 1.7rem;">
                            Pratinjau & Verifikasi Impor Data Aset
                        </h4>
                        <p style="font-size: 0.95rem; margin: 4px 0 0 0;" class="grey-text text-darken-2">
                            Berkas: <strong>{{ $fileName }}</strong> (Total {{ $totalRows }} baris terdeteksi)
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <span class="chip green darken-1 white-text" style="font-weight: 600; height: 32px; line-height: 32px; padding: 0 14px;">
                            <i class="material-icons left tiny" style="line-height: 32px;">check_circle</i> {{ $totalValid }} Valid
                        </span>
                        @if($totalWarning > 0)
                        <span class="chip orange darken-2 white-text" style="font-weight: 600; height: 32px; line-height: 32px; padding: 0 14px;">
                            <i class="material-icons left tiny" style="line-height: 32px;">warning</i> {{ $totalWarning }} Peringatan
                        </span>
                        @endif
                        @if($totalError > 0)
                        <span class="chip red darken-2 white-text" style="font-weight: 600; height: 32px; line-height: 32px; padding: 0 14px;">
                            <i class="material-icons left tiny" style="line-height: 32px;">error</i> {{ $totalError }} Error
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Pemilihan Kolom (Checkbox Kolom) --}}
    <div class="row">
        <div class="col s12">
            <div class="card z-depth-1" style="border-radius: 8px;">
                <div class="card-content" style="padding: 20px 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                        <span class="card-title blue-text text-darken-3" style="font-weight: 700; font-size: 1.15rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="material-icons">view_column</i> Pilih Kolom yang Ingin Diimpor
                        </span>
                        <div>
                            <button type="button" class="btn-small blue-grey lighten-4 blue-grey-text text-darken-4 waves-effect" onclick="toggleAllColumns(true)" style="border-radius: 4px; margin-right: 6px; font-weight: 600;">
                                <i class="material-icons left tiny">select_all</i> Pilih Semua Kolom
                            </button>
                            <button type="button" class="btn-small grey lighten-3 grey-text text-darken-3 waves-effect" onclick="toggleAllColumns(false)" style="border-radius: 4px; font-weight: 600;">
                                <i class="material-icons left tiny">clear</i> Hapus Pilihan (Kecuali Wajib)
                            </button>
                        </div>
                    </div>

                    <p class="grey-text text-darken-1" style="font-size: 0.88rem; margin-top: 0; margin-bottom: 15px;">
                        Centang kolom-kolom yang ingin Anda masukkan ke dalam database. Kolom yang tidak dicentang akan dilewati dan dibiarkan kosong pada data aset yang terbentuk.
                    </p>

                    <div class="row" style="margin-bottom: 0;">
                        <!-- Group 1: Ringkasan & Legalitas -->
                        <div class="col s12 m6 l3">
                            <div class="column-group-box">
                                <div class="column-group-header">
                                    <i class="material-icons tiny">gavel</i> Legalitas & Fisik
                                </div>
                                <label style="display: block; margin-bottom: 6px;">
                                    <input type="checkbox" name="selected_columns[]" value="kode_aset" checked disabled class="filled-in column-cb mandatory-cb" />
                                    <span style="font-size: 0.85rem; color: #0d47a1; font-weight: 600;">Kode Aset (Wajib)</span>
                                </label>
                                <input type="hidden" name="selected_columns[]" value="kode_aset">
                                
                                @foreach(['bank_asal' => 'Bank Asal', 'jenis_aset' => 'Jenis Aset', 'permasalahan' => 'Permasalahan', 'jenis_bukti_kepemilikan' => 'Bukti Hak', 'nomor_bukti_kepemilikan' => 'Nomor Bukti', 'luas_tanah' => 'Luas Tanah (m²)', 'luas_bangunan' => 'Luas Bangunan (m²)', 'njop' => 'NJOP (Rp)', 'papan_nama' => 'Papan Nama'] as $col => $lbl)
                                <label style="display: block; margin-bottom: 6px;">
                                    <input type="checkbox" name="selected_columns[]" value="{{ $col }}" checked class="filled-in column-cb" />
                                    <span style="font-size: 0.85rem; color: #37474f;">{{ $lbl }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group 2: Alamat Baru -->
                        <div class="col s12 m6 l3">
                            <div class="column-group-box">
                                <div class="column-group-header">
                                    <i class="material-icons tiny">navigation</i> Alamat Baru
                                </div>
                                @foreach(['alamat_namajalan' => 'Jalan / Detail', 'alamat_rt_rw' => 'RT / RW', 'alamat_provinsi' => 'Provinsi', 'alamat_kota_kab' => 'Kota / Kabupaten', 'alamat_kecamatan' => 'Kecamatan', 'alamat_kelurahan' => 'Kelurahan / Desa', 'kodepos' => 'Kode Pos'] as $col => $lbl)
                                <label style="display: block; margin-bottom: 6px;">
                                    <input type="checkbox" name="selected_columns[]" value="{{ $col }}" checked class="filled-in column-cb" />
                                    <span style="font-size: 0.85rem; color: #37474f;">{{ $lbl }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group 3: Alamat Lama & Batas -->
                        <div class="col s12 m6 l3">
                            <div class="column-group-box">
                                <div class="column-group-header">
                                    <i class="material-icons tiny">history</i> Alamat Lama & Batas
                                </div>
                                @foreach(['alamatlama_namajalan' => 'Alamat Lama', 'alamatlama_rt_rw' => 'RT/RW Lama', 'alamatlama_provinsi' => 'Provinsi Lama', 'alamatlama_kota_kab' => 'Kota/Kab Lama', 'alamatlama_kecamatan' => 'Kecamatan Lama', 'alamatlama_kelurahan' => 'Kelurahan Lama', 'alamatlama_kodepos' => 'Kodepos Lama', 'batas_utara' => 'Batas Utara', 'batas_timur' => 'Batas Timur', 'batas_selatan' => 'Batas Selatan', 'batas_barat' => 'Batas Barat'] as $col => $lbl)
                                <label style="display: block; margin-bottom: 6px;">
                                    <input type="checkbox" name="selected_columns[]" value="{{ $col }}" checked class="filled-in column-cb" />
                                    <span style="font-size: 0.85rem; color: #37474f;">{{ $lbl }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group 4: Koordinat, Kondisi & Sewa/Lelang -->
                        <div class="col s12 m6 l3">
                            <div class="column-group-box">
                                <div class="column-group-header">
                                    <i class="material-icons tiny">health_and_safety</i> Kondisi & Sewa/Lelang
                                </div>
                                @foreach(['koordinat_latitude' => 'Latitude', 'koordinat_longitude' => 'Longitude', 'kondisi_aset' => 'Kondisi Aset', 'kondisi_aset_lainlain' => 'Kondisi Lain-lain', 'potensi_aset' => 'Potensi Aset', 'wakil_kerja' => 'Wakil Kerja (Waker)', 'keterangan_kondisi_aset' => 'Keterangan Fisik', 'tanggal_update_kondisi' => 'Tgl Update Kondisi', 'semester' => 'Semester', 'tahun' => 'Tahun', 'sewa_lelang_nama_pihak' => 'Nama Penyewa/Lelang', 'sewa_lelang_no_surat' => 'No. Surat/Risalah', 'sewa_lelang_tgl_mulai' => 'Tgl Mulai Sewa', 'sewa_lelang_tgl_selesai' => 'Tgl Selesai Sewa', 'sewa_lelang_nilai' => 'Nilai Sewa/Lelang'] as $col => $lbl)
                                <label style="display: block; margin-bottom: 6px;">
                                    <input type="checkbox" name="selected_columns[]" value="{{ $col }}" checked class="filled-in column-cb" />
                                    <span style="font-size: 0.85rem; color: #37474f;">{{ $lbl }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Verifikasi Baris Data --}}
    <div class="row">
        <div class="col s12">
            <div class="card z-depth-1" style="border-radius: 8px;">
                <div class="card-content" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                        <span class="card-title blue-text text-darken-3" style="font-weight: 700; font-size: 1.2rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="material-icons">table_chart</i> Daftar Baris Data untuk Diverifikasi & Diimpor
                        </span>
                        <div>
                            <button type="button" class="btn-small green darken-2 waves-effect waves-light" onclick="selectValidOnly()" style="border-radius: 4px; margin-right: 6px;">
                                <i class="material-icons left tiny">check</i> Pilih Hanya Baris Valid ({{ $totalValid + $totalWarning }})
                            </button>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="highlight" style="font-size: 0.9rem;">
                            <thead class="grey lighten-4">
                                <tr>
                                    <th width="40">
                                        <label>
                                            <input type="checkbox" id="checkAllRows" class="filled-in" checked onchange="toggleAllRows(this.checked)" />
                                            <span></span>
                                        </label>
                                    </th>
                                    <th width="80">Baris #</th>
                                    <th>Kode Aset</th>
                                    <th>Jenis & Legalitas</th>
                                    <th>Alamat & Kota</th>
                                    <th>Kondisi & Status</th>
                                    <th>Status Validasi</th>
                                    <th class="center-align" width="120">Rincian Sub-Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $idx => $r)
                                @php
                                    $d = $r['data'];
                                    $isError = $r['status'] === 'error';
                                    $isWarning = $r['status'] === 'warning';
                                    $isValid = $r['status'] === 'valid';
                                @endphp
                                <tr style="{{ $isError ? 'background-color: #ffebee;' : ($isWarning ? 'background-color: #fffde7;' : '') }}">
                                    <td>
                                        <label>
                                            <input type="checkbox" name="selected_rows[]" value="{{ $r['row_number'] }}" class="row-checkbox filled-in" {{ $isError ? '' : 'checked' }} onchange="updateSelectedCount()" />
                                            <span></span>
                                        </label>
                                    </td>
                                    <td><strong>{{ $r['row_number'] }}</strong></td>
                                    <td>
                                        <strong class="blue-text text-darken-3">{{ $d['kode_aset'] ?? '-' }}</strong>
                                        @if(!empty($d['bank_asal']))
                                            <div style="font-size: 0.8rem; color: #546e7a;">{{ $d['bank_asal'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $d['jenis_aset'] ?? '-' }}</div>
                                        <div style="font-size: 0.8rem; color: #546e7a;">{{ $d['jenis_bukti_kepemilikan'] ?? '' }} {{ $d['nomor_bukti_kepemilikan'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $d['alamat_namajalan'] ?? '-' }}</div>
                                        <div style="font-size: 0.8rem; color: #546e7a;">{{ $d['alamat_kota_kab'] ?? '' }}, {{ $d['alamat_provinsi'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="chip grey lighten-3 grey-text text-darken-3" style="font-size: 11px; height: 22px; line-height: 22px; margin: 0;">
                                            {{ $d['kondisi_aset'] ?? 'N/A' }}
                                        </span>
                                        @if(strtoupper($d['kondisi_aset'] ?? '') === 'DISEWAKAN' && !empty($d['sewa_lelang_nama_pihak']))
                                            <div style="font-size: 0.8rem; color: #00695c; font-weight: 500;">Penyewa: {{ $d['sewa_lelang_nama_pihak'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isValid)
                                            <span class="chip green lighten-5 green-text text-darken-3" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0;">
                                                <i class="material-icons tiny left" style="line-height: 24px;">check</i> Siap Impor
                                            </span>
                                        @elseif($isWarning)
                                            <span class="chip amber lighten-5 orange-text text-darken-4" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0;" title="{{ implode('; ', $r['warnings']) }}">
                                                <i class="material-icons tiny left" style="line-height: 24px;">warning</i> Peringatan
                                            </span>
                                            <div style="font-size: 0.78rem; color: #e65100; margin-top: 2px;">{{ implode('; ', $r['warnings']) }}</div>
                                        @else
                                            <span class="chip red lighten-5 red-text text-darken-3" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0;" title="{{ implode('; ', $r['errors']) }}">
                                                <i class="material-icons tiny left" style="line-height: 24px;">error</i> Error
                                            </span>
                                            <div style="font-size: 0.78rem; color: #c62828; margin-top: 2px; font-weight: 500;">{{ implode('; ', $r['errors']) }}</div>
                                        @endif
                                    </td>
                                    <td class="center-align">
                                        <button type="button" class="btn-small blue-grey lighten-4 blue-grey-text text-darken-4 waves-effect modal-trigger" data-target="modal-row-{{ $idx }}" style="border-radius: 4px;" title="Lihat Sub-Info Lengkap">
                                            <i class="material-icons">visibility</i>
                                        </button>

                                        {{-- Modal Sub-Info Detail Row (Sesuai Layout Detail Aset) --}}
                                        <div id="modal-row-{{ $idx }}" class="modal modal-fixed-footer" style="max-width: 850px; border-radius: 8px;">
                                            <div class="modal-content left-align" style="padding: 24px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e0e0e0; padding-bottom: 12px; margin-bottom: 15px;">
                                                    <div>
                                                        <h5 class="blue-text text-darken-3" style="margin: 0; font-weight: 700;">
                                                            Pratinjau Sub-Info: {{ $d['kode_aset'] ?? 'Baris #' . $r['row_number'] }}
                                                        </h5>
                                                        <span class="grey-text" style="font-size: 0.85rem;">Baris ke-{{ $r['row_number'] }} pada berkas Excel</span>
                                                    </div>
                                                    <div>
                                                        @if($isValid)
                                                            <span class="chip green white-text font-weight-600">VALID</span>
                                                        @elseif($isWarning)
                                                            <span class="chip orange darken-2 white-text font-weight-600">PERINGATAN</span>
                                                        @else
                                                            <span class="chip red darken-2 white-text font-weight-600">ERROR</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(!empty($r['errors']))
                                                <div class="card-panel red lighten-5 red-text text-darken-4" style="border-left: 4px solid #d32f2f; padding: 10px 14px; margin-bottom: 15px; font-size: 0.88rem;">
                                                    <strong>Catatan Error:</strong>
                                                    <ul style="margin: 4px 0 0 16px; padding: 0;">
                                                        @foreach($r['errors'] as $err)
                                                            <li>{{ $err }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif

                                                <!-- Sub-info 1: Legalitas -->
                                                <div class="subinfo-card">
                                                    <div class="subinfo-title blue-text text-darken-3">
                                                        <i class="material-icons tiny">gavel</i> 1. Ringkasan & Legalitas Aset
                                                    </div>
                                                    <div class="row" style="margin-bottom: 0;">
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Bank Asal</p>
                                                            <p class="preview-detail-value">{{ $d['bank_asal'] ?? '-' }}</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Jenis Aset</p>
                                                            <p class="preview-detail-value">{{ $d['jenis_aset'] ?? '-' }}</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Bukti Kepemilikan</p>
                                                            <p class="preview-detail-value">{{ $d['jenis_bukti_kepemilikan'] ?? '-' }} - {{ $d['nomor_bukti_kepemilikan'] ?? '-' }}</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Luas Tanah / Bangunan</p>
                                                            <p class="preview-detail-value">{{ $d['luas_tanah'] ?? '-' }} m² / {{ $d['luas_bangunan'] ?? '-' }} m²</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">NJOP (Rp)</p>
                                                            <p class="preview-detail-value" style="color: #2e7d32; font-weight: 600;">{{ !empty($d['njop']) ? 'Rp ' . number_format((float)$d['njop'], 0, ',', '.') : '-' }}</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Papan Nama</p>
                                                            <p class="preview-detail-value">{{ $d['papan_nama'] ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Sub-info 2: Alamat & Batas -->
                                                <div class="subinfo-card">
                                                    <div class="subinfo-title blue-text text-darken-3">
                                                        <i class="material-icons tiny">location_on</i> 2. Alamat & Batas Wilayah
                                                    </div>
                                                    <div class="row" style="margin-bottom: 0;">
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Alamat Baru</p>
                                                            <p class="preview-detail-value">{{ $d['alamat_namajalan'] ?? '-' }} (RT/RW: {{ $d['alamat_rt_rw'] ?? '-' }})</p>
                                                            <p class="preview-detail-label">Wilayah Baru</p>
                                                            <p class="preview-detail-value">{{ $d['alamat_kelurahan'] ?? '-' }}, {{ $d['alamat_kecamatan'] ?? '-' }}, {{ $d['alamat_kota_kab'] ?? '-' }}, {{ $d['alamat_provinsi'] ?? '-' }} ({{ $d['kodepos'] ?? '-' }})</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Alamat Lama</p>
                                                            <p class="preview-detail-value">{{ $d['alamatlama_namajalan'] ?? '-' }} (RT/RW: {{ $d['alamatlama_rt_rw'] ?? '-' }})</p>
                                                            <p class="preview-detail-label">Wilayah Lama</p>
                                                            <p class="preview-detail-value">{{ $d['alamatlama_kelurahan'] ?? '-' }}, {{ $d['alamatlama_kecamatan'] ?? '-' }}, {{ $d['alamatlama_kota_kab'] ?? '-' }}, {{ $d['alamatlama_provinsi'] ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="margin-top: 10px; margin-bottom: 0;">
                                                        <div class="col s6 m3"><p class="preview-detail-label">Utara</p><p class="preview-detail-value">{{ $d['batas_utara'] ?? '-' }}</p></div>
                                                        <div class="col s6 m3"><p class="preview-detail-label">Timur</p><p class="preview-detail-value">{{ $d['batas_timur'] ?? '-' }}</p></div>
                                                        <div class="col s6 m3"><p class="preview-detail-label">Selatan</p><p class="preview-detail-value">{{ $d['batas_selatan'] ?? '-' }}</p></div>
                                                        <div class="col s6 m3"><p class="preview-detail-label">Barat</p><p class="preview-detail-value">{{ $d['batas_barat'] ?? '-' }}</p></div>
                                                    </div>
                                                </div>

                                                <!-- Sub-info 3: Kondisi & Sewa/Lelang -->
                                                <div class="subinfo-card">
                                                    <div class="subinfo-title blue-text text-darken-3">
                                                        <i class="material-icons tiny">real_estate_agent</i> 3. Kondisi & Transaksi Sewa / Lelang
                                                    </div>
                                                    <div class="row" style="margin-bottom: 0;">
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Kondisi Aset</p>
                                                            <p class="preview-detail-value"><strong>{{ $d['kondisi_aset'] ?? '-' }}</strong> {{ !empty($d['kondisi_aset_lainlain']) ? '(' . $d['kondisi_aset_lainlain'] . ')' : '' }}</p>
                                                            <p class="preview-detail-label">Waker & Potensi</p>
                                                            <p class="preview-detail-value">{{ $d['wakil_kerja'] ?? '-' }} / {{ $d['potensi_aset'] ?? '-' }}</p>
                                                        </div>
                                                        <div class="col s12 m6">
                                                            <p class="preview-detail-label">Nama Pihak Penyewa/Lelang</p>
                                                            <p class="preview-detail-value" style="color: #0d47a1; font-weight: 600;">{{ $d['sewa_lelang_nama_pihak'] ?? '-' }}</p>
                                                            <p class="preview-detail-label">No. Surat & Periode</p>
                                                            <p class="preview-detail-value">{{ $d['sewa_lelang_no_surat'] ?? '-' }} ({{ $d['sewa_lelang_tgl_mulai'] ?? '-' }} s/d {{ $d['sewa_lelang_tgl_selesai'] ?? '-' }})</p>
                                                            <p class="preview-detail-label">Nilai Transaksi (Rp)</p>
                                                            <p class="preview-detail-value" style="color: #1b5e20; font-weight: 700;">{{ !empty($d['sewa_lelang_nilai']) ? 'Rp ' . number_format((float)$d['sewa_lelang_nilai'], 0, ',', '.') : '-' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="padding: 10px 24px; background: #f5f5f5;">
                                                <button type="button" class="modal-close btn-flat waves-effect">Tutup Pratinjau</button>
                                            </div>
                                        </div>
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

    {{-- Bottom Sticky Execution Bar --}}
    <div class="row" style="margin-top: 25px; margin-bottom: 40px;">
        <div class="col s12">
            <div class="card-panel white z-depth-2" style="border-radius: 8px; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <span style="font-size: 1.05rem; font-weight: 600; color: #37474f;">
                        Total Baris Dipilih untuk Diimpor: <span id="selected-rows-count" class="blue-text text-darken-3" style="font-size: 1.3rem; font-weight: 700;">0</span> baris
                    </span>
                    <div class="grey-text" style="font-size: 0.85rem;">Pastikan kolom dan baris yang dipilih sudah sesuai dengan kebutuhan data Anda.</div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('assets.import.index') }}" class="btn-large grey lighten-2 grey-text text-darken-3 waves-effect" style="border-radius: 6px; font-weight: 600;">
                        <i class="material-icons left">cancel</i> Batal
                    </a>
                    <button type="submit" class="btn-large blue darken-3 waves-effect waves-light" id="btn-submit-import" style="border-radius: 6px; font-weight: 700;">
                        <i class="material-icons left">save</i> Eksekusi Impor Data Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Modals
        var elemsModal = document.querySelectorAll('.modal');
        M.Modal.init(elemsModal);

        updateSelectedCount();
    });

    function updateSelectedCount() {
        let count = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('selected-rows-count').innerText = count;
        let submitBtn = document.getElementById('btn-submit-import');
        if (count === 0) {
            submitBtn.classList.add('disabled');
        } else {
            submitBtn.classList.remove('disabled');
        }
    }

    function toggleAllRows(isChecked) {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = isChecked;
        });
        updateSelectedCount();
    }

    function selectValidOnly() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(function(cb) {
            let tr = cb.closest('tr');
            let isError = tr.style.backgroundColor.indexOf('255, 235, 238') !== -1 || tr.style.backgroundColor.indexOf('#ffebee') !== -1;
            cb.checked = !isError;
        });
        document.getElementById('checkAllRows').checked = false;
        updateSelectedCount();
        M.toast({html: 'Hanya baris valid dan peringatan yang dipilih.', classes: 'green darken-2'});
    }

    function toggleAllColumns(isChecked) {
        let columnCheckboxes = document.querySelectorAll('.column-cb');
        columnCheckboxes.forEach(function(cb) {
            if (!cb.classList.contains('mandatory-cb')) {
                cb.checked = isChecked;
            }
        });
        M.toast({html: isChecked ? 'Semua kolom dipilih.' : 'Pilihan kolom dinonaktifkan (kecuali wajib).', classes: 'blue darken-2'});
    }
</script>
@endpush
