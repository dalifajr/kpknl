@extends('layouts.app')

@section('title', isset($asset) ? 'Edit Aset' : 'Tambah Aset Baru')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 350px; border-radius: 4px; border: 1px solid #ccc; margin-top: 10px; z-index: 1; }
    .error-text { color: #f44336; font-size: 12px; margin-top: -10px; margin-bottom: 15px; display: block; }
    .collapsible-header { font-weight: 500; font-size: 1.1rem; }
    .collapsible-header i { color: #1565c0; }
    .step-badge { float: right; margin-left: auto; display: flex; align-items: center; }
    .btn-next-container { margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 15px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col s12">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h5 style="margin: 0; font-weight: 600;">{{ isset($asset) ? 'Edit Data Aset' : 'Tambah Data Aset' }}</h5>
            <a href="{{ isset($asset) ? route('assets.show', $asset->id) : route('assets.index') }}" class="btn waves-effect waves-light grey darken-1">
                <i class="material-icons left">arrow_back</i> Batal
            </a>
        </div>
        
        <form id="asset-form" action="{{ isset($asset) ? route('assets.update', $asset->id) : route('assets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($asset))
                @method('PUT')
            @endif

            <ul class="collapsible" id="form-accordion">
                
                <!-- STEP 1: Informasi Dasar -->
                <li class="active">
                    <div class="collapsible-header">
                        <i class="material-icons">info</i> 1. Informasi Dasar 
                        <span class="step-badge" id="badge-step-0"></span>
                    </div>
                    <div class="collapsible-body white">
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input type="text" id="kode_aset" name="kode_aset" value="{{ old('kode_aset', $asset->kode_aset ?? '') }}" required class="validate">
                                <label for="kode_aset">Kode Aset *</label>
                                @error('kode_aset')<span class="error-text">{{ $message }}</span>@enderror
                            </div>

                            <div class="input-field col s12 m6">
                                <select name="jenis_aset" id="jenis_aset" required>
                                    <option value="" disabled selected>Pilih Jenis</option>
                                    @foreach(['Tanah', 'Bangunan', 'Tanah dan Bangunan'] as $jenis)
                                        <option value="{{ $jenis }}" {{ old('jenis_aset', $asset->jenis_aset ?? '') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                    @endforeach
                                </select>
                                <label for="jenis_aset">Jenis Aset *</label>
                                @error('jenis_aset')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input type="text" id="bank_asal" name="bank_asal" value="{{ old('bank_asal', $asset->bank_asal ?? '') }}">
                                <label for="bank_asal">Bank Asal</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <textarea id="permasalahan" name="permasalahan" class="materialize-textarea">{{ old('permasalahan', $asset->permasalahan ?? '') }}</textarea>
                                <label for="permasalahan">Permasalahan</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select name="semester" id="semester">
                                    <option value="" disabled selected>Pilih Semester</option>
                                    <option value="1" {{ old('semester', $asset->semester ?? '') == '1' ? 'selected' : '' }}>Semester 1</option>
                                    <option value="2" {{ old('semester', $asset->semester ?? '') == '2' ? 'selected' : '' }}>Semester 2</option>
                                </select>
                                <label for="semester">Semester</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="number" id="tahun" name="tahun" value="{{ old('tahun', $asset->tahun ?? date('Y')) }}">
                                <label for="tahun">Tahun</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input type="number" step="0.01" id="njop" name="njop" value="{{ old('njop', $asset->njop ?? '') }}">
                                <label for="njop">NJOP (Rp) <span class="grey-text">(Opsional)</span></label>
                            </div>

                            <div class="input-field col s12 m6">
                                <select name="jenis_bukti_kepemilikan" id="jenis_bukti_kepemilikan">
                                    <option value="" disabled selected>Pilih Jenis Bukti</option>
                                    @foreach([
                                        'Sertifikat Hak Milik (SHM)',
                                        'Hak Guna Bangunan (HGB)',
                                        'Surat Hak Milik Atas Satuan Rumah Susun (SHMSRBUN)',
                                        'Tanah Negara',
                                        'Lain-lain (surat keterangan dari kelurahan/kecamatan/kota/kab)'
                                    ] as $bukti)
                                        <option value="{{ $bukti }}" {{ old('jenis_bukti_kepemilikan', $asset->jenis_bukti_kepemilikan ?? '') == $bukti ? 'selected' : '' }}>{{ $bukti }}</option>
                                    @endforeach
                                </select>
                                <label for="jenis_bukti_kepemilikan">Jenis Bukti Kepemilikan</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12">
                                <input type="text" id="nomor_bukti_kepemilikan" name="nomor_bukti_kepemilikan" value="{{ old('nomor_bukti_kepemilikan', $asset->nomor_bukti_kepemilikan ?? '') }}">
                                <label for="nomor_bukti_kepemilikan">Nomor Bukti Kepemilikan</label>
                            </div>
                        </div>
                        <div class="btn-next-container">
                            <button type="button" class="btn blue darken-2 waves-effect btn-next" data-step="0">Selanjutnya <i class="material-icons right">arrow_forward</i></button>
                        </div>
                    </div>
                </li>

                <!-- STEP 2: Lokasi & Alamat -->
                <li>
                    <div class="collapsible-header">
                        <i class="material-icons">place</i> 2. Lokasi & Alamat 
                        <span class="step-badge" id="badge-step-1"></span>
                    </div>
                    <div class="collapsible-body white">
                        
                        <h6 class="grey-text text-darken-2" style="font-weight: 500;">Alamat Lama</h6>
                        <div class="row">
                            <div class="input-field col s12">
                                <input type="text" id="alamatlama_namajalan" name="alamatlama_namajalan" value="{{ old('alamatlama_namajalan', $asset->alamatlama_namajalan ?? '') }}">
                                <label for="alamatlama_namajalan">Nama Jalan / Detail Alamat Lama</label>
                            </div>
                            <div class="col s12 m6">
                                @php $oldRt = explode('/', $asset->alamatlama_rt_rw ?? ''); @endphp
                                <div style="display: flex; align-items: baseline;">
                                    <div class="input-field" style="flex: 1;">
                                        <input type="text" id="alamatlama_rt" name="alamatlama_rt" value="{{ old('alamatlama_rt', $oldRt[0] ?? '') }}">
                                        <label for="alamatlama_rt">RT Lama</label>
                                    </div>
                                    <span style="margin: 0 15px; font-size: 1.5rem; color: #9e9e9e;">/</span>
                                    <div class="input-field" style="flex: 1;">
                                        <input type="text" id="alamatlama_rw" name="alamatlama_rw" value="{{ old('alamatlama_rw', $oldRt[1] ?? '') }}">
                                        <label for="alamatlama_rw">RW Lama</label>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field col s12 m6">
                                <select name="alamatlama_provinsi_id" id="old_provinsi"></select>
                                <label for="old_provinsi">Provinsi (Lama)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select name="alamatlama_kota_kab_id" id="old_kota"></select>
                                <label for="old_kota">Kabupaten / Kota (Lama)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select name="alamatlama_kecamatan_id" id="old_kecamatan"></select>
                                <label for="old_kecamatan">Kecamatan (Lama)</label>
                            </div>
                            <div class="input-field col s12 m3">
                                <select name="alamatlama_kelurahan_id" id="old_kelurahan"></select>
                                <label for="old_kelurahan">Kelurahan / Desa (Lama)</label>
                            </div>
                            <div class="input-field col s12 m3">
                                <input type="text" id="alamatlama_kodepos" name="alamatlama_kodepos" value="{{ old('alamatlama_kodepos', $asset->alamatlama_kodepos ?? '') }}">
                                <label for="alamatlama_kodepos">Kode Pos (Lama)</label>
                            </div>
                        </div>

                        <h6 class="blue-text text-darken-2" style="font-weight: 500; margin-top: 30px;">Alamat Baru</h6>
                        <div class="row" style="margin-top: 10px; margin-bottom: 20px;">
                            <div class="col s12">
                                <label>
                                    <input type="checkbox" id="copy_alamat" class="filled-in" />
                                    <span style="font-weight: 600; color: #1565c0;">Salin dari Alamat Lama</span>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input type="text" id="alamat_namajalan" name="alamat_namajalan" value="{{ old('alamat_namajalan', $asset->alamat_namajalan ?? '') }}">
                                <label for="alamat_namajalan">Nama Jalan / Detail Alamat Baru</label>
                            </div>
                            <div class="col s12 m6">
                                @php $newRt = explode('/', $asset->alamat_rt_rw ?? ''); @endphp
                                <div style="display: flex; align-items: baseline;">
                                    <div class="input-field" style="flex: 1;">
                                        <input type="text" id="alamat_rt" name="alamat_rt" value="{{ old('alamat_rt', $newRt[0] ?? '') }}">
                                        <label for="alamat_rt">RT</label>
                                    </div>
                                    <span style="margin: 0 15px; font-size: 1.5rem; color: #9e9e9e;">/</span>
                                    <div class="input-field" style="flex: 1;">
                                        <input type="text" id="alamat_rw" name="alamat_rw" value="{{ old('alamat_rw', $newRt[1] ?? '') }}">
                                        <label for="alamat_rw">RW</label>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field col s12 m6">
                                <select name="alamat_provinsi_id" id="provinsi"></select>
                                <label for="provinsi">Provinsi</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select name="alamat_kota_kab_id" id="kota"></select>
                                <label for="kota">Kabupaten / Kota</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select name="alamat_kecamatan_id" id="kecamatan"></select>
                                <label for="kecamatan">Kecamatan</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select name="alamat_kelurahan_id" id="kelurahan"></select>
                                <label for="kelurahan">Kelurahan / Desa</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <input type="text" id="kodepos" name="kodepos" value="{{ old('kodepos', $asset->kodepos ?? '') }}">
                                <label for="kodepos">Kode Pos</label>
                            </div>
                        </div>

                        <h6 class="blue-text text-darken-2" style="font-weight: 500; margin-top: 30px;">Peta Koordinat</h6>
                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12 m5">
                                <input type="text" name="koordinat_latitude" id="lat" value="{{ old('koordinat_latitude', $asset->koordinat_latitude ?? '-2.990934') }}">
                                <label for="lat" class="active">Latitude</label>
                            </div>
                            <div class="input-field col s12 m5">
                                <input type="text" name="koordinat_longitude" id="lng" value="{{ old('koordinat_longitude', $asset->koordinat_longitude ?? '104.756554') }}">
                                <label for="lng" class="active">Longitude</label>
                            </div>
                            <div class="col s12 m2" style="margin-top: 20px;">
                                <button type="button" class="btn blue darken-1 w-100" id="btn_apply_map" style="padding: 0 10px;">Terapkan</button>
                            </div>
                        </div>
                        <div id="map"></div>

                        <div class="btn-next-container">
                            <button type="button" class="btn blue darken-2 waves-effect btn-next" data-step="1">Selanjutnya <i class="material-icons right">arrow_forward</i></button>
                        </div>
                    </div>
                </li>

                <!-- STEP 3: Dimensi & Batas -->
                <li>
                    <div class="collapsible-header">
                        <i class="material-icons">straighten</i> 3. Dimensi & Batas 
                        <span class="step-badge" id="badge-step-2"></span>
                    </div>
                    <div class="collapsible-body white">
                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12 m6">
                                <input type="number" step="0.01" id="luas_tanah" name="luas_tanah" value="{{ old('luas_tanah', $asset->luas_tanah ?? '') }}">
                                <label for="luas_tanah">Luas Tanah (m²)</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input type="number" step="0.01" id="luas_bangunan" name="luas_bangunan" value="{{ old('luas_bangunan', $asset->luas_bangunan ?? '') }}">
                                <label for="luas_bangunan">Luas Bangunan (m²)</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input type="text" id="batas_utara" name="batas_utara" value="{{ old('batas_utara', $asset->batas_utara ?? '') }}">
                                <label for="batas_utara">Batas Utara</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input type="text" id="batas_selatan" name="batas_selatan" value="{{ old('batas_selatan', $asset->batas_selatan ?? '') }}">
                                <label for="batas_selatan">Batas Selatan</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input type="text" id="batas_timur" name="batas_timur" value="{{ old('batas_timur', $asset->batas_timur ?? '') }}">
                                <label for="batas_timur">Batas Timur</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input type="text" id="batas_barat" name="batas_barat" value="{{ old('batas_barat', $asset->batas_barat ?? '') }}">
                                <label for="batas_barat">Batas Barat</label>
                            </div>
                        </div>
                        <div class="btn-next-container">
                            <button type="button" class="btn blue darken-2 waves-effect btn-next" data-step="2">Selanjutnya <i class="material-icons right">arrow_forward</i></button>
                        </div>
                    </div>
                </li>

                <!-- STEP 4: Kondisi & Lampiran -->
                <li>
                    <div class="collapsible-header">
                        <i class="material-icons">description</i> 4. Kondisi & Lampiran 
                        <span class="step-badge" id="badge-step-3"></span>
                    </div>
                    <div class="collapsible-body white">
                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12 m6">
                                <select name="kondisi_aset" id="kondisi_aset" onchange="toggleKondisiFields()">
                                    <option value="" disabled selected>Pilih Kondisi</option>
                                    @foreach(['DIHUNI', 'DIGUNAKAN PIHAK KETIGA', 'KOSONG', 'DISEWAKAN', 'DILELANG', 'LAIN-LAIN'] as $kondisi)
                                        <option value="{{ $kondisi }}" {{ old('kondisi_aset', $asset->kondisi_aset ?? '') == $kondisi ? 'selected' : '' }}>{{ $kondisi }}</option>
                                    @endforeach
                                </select>
                                <label for="kondisi_aset">Kondisi Aset</label>
                            </div>

                            <div class="input-field col s12 m6" id="kondisi_lainlain_container" style="display: none;">
                                <input type="text" id="kondisi_aset_lainlain" name="kondisi_aset_lainlain" value="{{ old('kondisi_aset_lainlain', $asset->kondisi_aset_lainlain ?? '') }}">
                                <label for="kondisi_aset_lainlain">Kondisi Lain-lain (Detail)</label>
                            </div>

                            <!-- Container Khusus Disewakan / Dilelang -->
                            <div class="col s12" id="sewa_lelang_container" style="display: none; margin-top: 5px; margin-bottom: 15px;">
                                <div class="card-panel blue lighten-5" style="border-left: 4px solid #1565c0; border-radius: 6px; padding: 18px 20px;">
                                    <h6 id="sewa_lelang_header_title" class="blue-text text-darken-3" style="margin-top: 0; margin-bottom: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                        <i class="material-icons">info</i> Keterangan Tambahan
                                    </h6>
                                    <div class="row" style="margin-bottom: 0;">
                                        <div class="input-field col s12 m6">
                                            <input type="text" id="sewa_lelang_nama_pihak" name="sewa_lelang_nama_pihak" value="{{ old('sewa_lelang_nama_pihak', $asset->sewa_lelang_nama_pihak ?? '') }}">
                                            <label for="sewa_lelang_nama_pihak" id="label_nama_pihak">Nama Penyewa / Pelelang</label>
                                        </div>
                                        <div class="input-field col s12 m6">
                                            <input type="text" id="sewa_lelang_no_surat" name="sewa_lelang_no_surat" value="{{ old('sewa_lelang_no_surat', $asset->sewa_lelang_no_surat ?? '') }}">
                                            <label for="sewa_lelang_no_surat" id="label_no_surat">Nomor Surat / Risalah Lelang</label>
                                        </div>
                                        <div class="input-field col s12 m4">
                                            <input type="date" id="sewa_lelang_tgl_mulai" name="sewa_lelang_tgl_mulai" value="{{ old('sewa_lelang_tgl_mulai', isset($asset->sewa_lelang_tgl_mulai) ? (is_string($asset->sewa_lelang_tgl_mulai) ? $asset->sewa_lelang_tgl_mulai : $asset->sewa_lelang_tgl_mulai->format('Y-m-d')) : '') }}">
                                            <label for="sewa_lelang_tgl_mulai" class="active" id="label_tgl_mulai">Tanggal Mulai</label>
                                        </div>
                                        <div class="input-field col s12 m4">
                                            <input type="date" id="sewa_lelang_tgl_selesai" name="sewa_lelang_tgl_selesai" value="{{ old('sewa_lelang_tgl_selesai', isset($asset->sewa_lelang_tgl_selesai) ? (is_string($asset->sewa_lelang_tgl_selesai) ? $asset->sewa_lelang_tgl_selesai : $asset->sewa_lelang_tgl_selesai->format('Y-m-d')) : '') }}">
                                            <label for="sewa_lelang_tgl_selesai" class="active" id="label_tgl_selesai">Tanggal Selesai</label>
                                        </div>
                                        <div class="input-field col s12 m4">
                                            <input type="number" step="0.01" id="sewa_lelang_nilai" name="sewa_lelang_nilai" value="{{ old('sewa_lelang_nilai', $asset->sewa_lelang_nilai ?? '') }}" placeholder="Contoh: 50000000">
                                            <label for="sewa_lelang_nilai" id="label_nilai">Nilai Transaksi (Rp)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="input-field col s12 m6">
                                <input type="text" id="wakil_kerja" name="wakil_kerja" value="{{ old('wakil_kerja', $asset->wakil_kerja ?? '') }}">
                                <label for="wakil_kerja">Wakil Kerja (Waker)</label>
                            </div>
                            
                            <div class="input-field col s12 m6">
                                <input type="date" id="tanggal_update_kondisi" name="tanggal_update_kondisi" value="{{ old('tanggal_update_kondisi', $asset->tanggal_update_kondisi ?? '') }}">
                                <label for="tanggal_update_kondisi" class="active">Tanggal Update Kondisi</label>
                            </div>

                            <div class="input-field col s12">
                                <textarea id="potensi_aset" name="potensi_aset" class="materialize-textarea">{{ old('potensi_aset', $asset->potensi_aset ?? '') }}</textarea>
                                <label for="potensi_aset">Potensi Aset <span class="grey-text">(Opsional)</span></label>
                            </div>

                            <div class="input-field col s12">
                                <textarea id="keterangan_kondisi_aset" name="keterangan_kondisi_aset" class="materialize-textarea">{{ old('keterangan_kondisi_aset', $asset->keterangan_kondisi_aset ?? '') }}</textarea>
                                <label for="keterangan_kondisi_aset">Keterangan Kondisi</label>
                            </div>

                            <div class="col s12" style="margin-bottom: 25px;">
                                <label>
                                    <input type="checkbox" name="papan_nama" value="1" class="filled-in" {{ old('papan_nama', $asset->papan_nama ?? 0) ? 'checked' : '' }} />
                                    <span>Ada Papan Nama Terpasang</span>
                                </label>
                            </div>

                            <div class="col s12 m6" style="margin-bottom: 20px;">
                                <div class="file-field input-field">
                                    <div class="btn blue darken-1">
                                        <span>Foto Aset</span>
                                        <input type="file" name="foto[]" id="foto_input" accept="image/*" multiple>
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text" placeholder="Upload foto (bisa lebih dari 1 file)">
                                    </div>
                                </div>
                                @if(isset($asset) && count($asset->foto_paths) > 0)
                                    <div style="margin-top: 10px; padding: 10px; background: #fafafa; border: 1px solid #e0e0e0; border-radius: 4px;">
                                        <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px;" class="grey-text text-darken-3">Foto Terunggah Saat Ini:</div>
                                        @foreach($asset->foto_paths as $fPath)
                                            <div id="existing-foto-{{ md5($fPath) }}" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; padding: 4px 0; border-bottom: 1px dashed #eee;">
                                                <a href="{{ asset('storage/' . $fPath) }}" target="_blank" style="font-size: 0.85rem;"><i class="material-icons tiny blue-text" style="vertical-align: middle;">image</i> {{ basename($fPath) }}</a>
                                                <a href="#!" onclick="confirmDeleteExistingFile('foto', '{{ $fPath }}', 'existing-foto-{{ md5($fPath) }}')" class="red-text" style="font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                                    <i class="material-icons tiny">delete</i> Hapus
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div id="foto_preview_list" style="margin-top: 10px;"></div>
                            </div>

                            <div class="col s12 m6" style="margin-bottom: 20px;">
                                <div class="file-field input-field">
                                    <div class="btn blue darken-1">
                                        <span>Dokumen</span>
                                        <input type="file" name="dokumen[]" id="dokumen_input" accept=".pdf,.doc,.docx" multiple>
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text" placeholder="Upload PDF/DOC (bisa lebih dari 1 file)">
                                    </div>
                                </div>
                                @if(isset($asset) && count($asset->dokumen_paths) > 0)
                                    <div style="margin-top: 10px; padding: 10px; background: #fafafa; border: 1px solid #e0e0e0; border-radius: 4px;">
                                        <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px;" class="grey-text text-darken-3">Dokumen Terunggah Saat Ini:</div>
                                        @foreach($asset->dokumen_paths as $dPath)
                                            <div id="existing-dokumen-{{ md5($dPath) }}" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; padding: 4px 0; border-bottom: 1px dashed #eee;">
                                                <a href="{{ asset('storage/' . $dPath) }}" target="_blank" style="font-size: 0.85rem;"><i class="material-icons tiny red-text" style="vertical-align: middle;">description</i> {{ basename($dPath) }}</a>
                                                <a href="#!" onclick="confirmDeleteExistingFile('dokumen', '{{ $dPath }}', 'existing-dokumen-{{ md5($dPath) }}')" class="red-text" style="font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                                    <i class="material-icons tiny">delete</i> Hapus
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div id="dokumen_preview_list" style="margin-top: 10px;"></div>
                            </div>
                        </div>
                        <div class="btn-next-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <span class="grey-text text-darken-1"><i class="material-icons left" style="font-size: 16px;">check_circle</i> Pastikan semua data sudah benar</span>
                            <div>
                                <a href="{{ isset($asset) ? route('assets.show', $asset->id) : route('assets.index') }}" class="btn waves-effect waves-light grey darken-1 btn-large" style="margin-right: 10px;"><i class="material-icons left">cancel</i> Batal</a>
                                <button type="submit" class="btn waves-effect waves-light green darken-2 btn-large"><i class="material-icons left">save</i> Simpan Data Aset</button>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Berkas Terunggah -->
<div id="modalDeleteExistingFile" class="modal" style="width: 420px; border-radius: 8px;">
    <div class="modal-content">
        <h4 style="font-size: 1.4rem; font-weight: 600;" class="red-text text-darken-2">Hapus Berkas</h4>
        <p>Apakah Anda yakin ingin menghapus berkas <b id="delete-file-name" class="blue-text"></b> dari aset ini?</p>
        <p class="grey-text" style="font-size: 0.85rem;">Berkas akan dihapus secara permanen dari server setelah Anda menekan tombol <b>Simpan Data Aset</b>.</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
        <button type="button" onclick="executeDeleteExistingFile()" class="waves-effect waves-light btn red darken-1">Hapus Berkas</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var mapInstance;
    var mapMarker;
    var accordionInstance;

    function toggleKondisiLainlain() {
        let val = document.getElementById('kondisi_aset').value;
        let container = document.getElementById('kondisi_lainlain_container');
        container.style.display = (val === 'LAIN-LAIN') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Materialize Components
        M.FormSelect.init(document.querySelectorAll('select'));
        M.updateTextFields();

        var elems = document.querySelectorAll('.collapsible');
        var instances = M.Collapsible.init(elems, {
            accordion: true,
            onOpenEnd: function(el) {
                // Leaflet fix for rendering inside hidden div (accordion)
                if (el.querySelector('#map') && mapInstance) {
                    mapInstance.invalidateSize();
                }
            }
        });
        accordionInstance = instances[0];
        
        toggleKondisiLainlain();

        // Step Validation Logic
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', function() {
                let step = parseInt(this.getAttribute('data-step'));
                let currentLi = this.closest('li');
                let inputs = currentLi.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('invalid');
                    } else {
                        input.classList.remove('invalid');
                    }
                });
                
                if (isValid) {
                    document.getElementById('badge-step-' + step).innerHTML = '<i class="material-icons green-text">check_circle</i>';
                    accordionInstance.open(step + 1); // Opens the NEXT step
                } else {
                    M.toast({html: 'Mohon lengkapi semua data wajib (bertanda *)', classes: 'red rounded'});
                }
            });
        });

        // Initialize Map
        let latInput = document.getElementById('lat');
        let lngInput = document.getElementById('lng');
        mapInstance = L.map('map').setView([latInput.value, lngInput.value], 13);
        
        let osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        });
        let satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        });

        osmLayer.addTo(mapInstance);
        L.control.layers({
            "Peta Normal": osmLayer,
            "Satelit": satelliteLayer
        }).addTo(mapInstance);

        mapMarker = L.marker([latInput.value, lngInput.value], {draggable: true}).addTo(mapInstance);

        mapMarker.on('dragend', function(e) {
            let pos = mapMarker.getLatLng();
            latInput.value = pos.lat.toFixed(8);
            lngInput.value = pos.lng.toFixed(8);
        });

        mapInstance.on('click', function(e) {
            mapMarker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(8);
            lngInput.value = e.latlng.lng.toFixed(8);
        });

        // Btn Apply Map
        document.getElementById('btn_apply_map').addEventListener('click', function() {
            let nLat = parseFloat(latInput.value.replace(',', '.'));
            let nLng = parseFloat(lngInput.value.replace(',', '.'));
            if(!isNaN(nLat) && !isNaN(nLng)) {
                let newLatLng = new L.LatLng(nLat, nLng);
                mapMarker.setLatLng(newLatLng);
                mapInstance.setView(newLatLng, 13);
                mapInstance.invalidateSize();
                M.toast({html: 'Pin lokasi berhasil diperbarui', classes: 'green rounded'});
            } else {
                M.toast({html: 'Koordinat tidak valid', classes: 'red rounded'});
            }
        });

        // Copy Alamat Baru = Alamat Lama
        document.getElementById('copy_alamat').addEventListener('change', async function() {
            if(this.checked) {
                document.getElementById('alamat_namajalan').value = document.getElementById('alamatlama_namajalan').value;
                document.getElementById('alamat_rt').value = document.getElementById('alamatlama_rt').value;
                document.getElementById('alamat_rw').value = document.getElementById('alamatlama_rw').value;
                
                M.updateTextFields();

                // Sync API Dropdowns with async sequence
                let provLama = document.getElementById('old_provinsi').value;
                let kotaLama = document.getElementById('old_kota').value;
                let kecLama = document.getElementById('old_kecamatan').value;
                let kelLama = document.getElementById('old_kelurahan').value;

                if (provLama) {
                    await fetchRegions(`${apiUrl}/regencies/${provLama}`, 'kota', 'Pilih Kota', kotaLama);
                    if (kotaLama) {
                        await fetchRegions(`${apiUrl}/districts/${kotaLama}`, 'kecamatan', 'Pilih Kecamatan', kecLama);
                        if (kecLama) {
                            await fetchRegions(`${apiUrl}/villages/${kecLama}`, 'kelurahan', 'Pilih Kelurahan', kelLama);
                        }
                    }
                    document.getElementById('provinsi').value = provLama;
                    M.FormSelect.init(document.getElementById('provinsi'));
                }
            }
        });
    });

    // Wilayah API Dropdowns
    const apiUrl = '{{ url('api') }}';
    
    // Alamat Baru
    const oldProv = '{{ old('alamat_provinsi_id', $asset->alamat_provinsi_id ?? '') }}';
    const oldKota = '{{ old('alamat_kota_kab_id', $asset->alamat_kota_kab_id ?? '') }}';
    const oldKec = '{{ old('alamat_kecamatan_id', $asset->alamat_kecamatan_id ?? '') }}';
    const oldKel = '{{ old('alamat_kelurahan_id', $asset->alamat_kelurahan_id ?? '') }}';

    // Alamat Lama
    const oldLamaProv = '{{ old('alamatlama_provinsi_id', $asset->alamatlama_provinsi_id ?? '') }}';
    const oldLamaKota = '{{ old('alamatlama_kota_kab_id', $asset->alamatlama_kota_kab_id ?? '') }}';
    const oldLamaKec = '{{ old('alamatlama_kecamatan_id', $asset->alamatlama_kecamatan_id ?? '') }}';
    const oldLamaKel = '{{ old('alamatlama_kelurahan_id', $asset->alamatlama_kelurahan_id ?? '') }}';

    async function fetchRegions(endpoint, selectId, placeholder, selectedVal) {
        let select = document.getElementById(selectId);
        select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        if(!endpoint) {
            M.FormSelect.init(select);
            return;
        }
        let res = await fetch(endpoint);
        let data = await res.json();
        data.forEach(item => {
            let opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            if(item.id == selectedVal) opt.selected = true;
            select.appendChild(opt);
        });
        M.FormSelect.init(select);
    }

    // Event Listeners Alamat Baru
    document.getElementById('provinsi').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/regencies/${this.value}` : null, 'kota', 'Pilih Kota', oldKota);
        fetchRegions(null, 'kecamatan', 'Pilih Kecamatan', null);
        fetchRegions(null, 'kelurahan', 'Pilih Kelurahan', null);
    });
    document.getElementById('kota').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/districts/${this.value}` : null, 'kecamatan', 'Pilih Kecamatan', oldKec);
        fetchRegions(null, 'kelurahan', 'Pilih Kelurahan', null);
    });
    document.getElementById('kecamatan').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/villages/${this.value}` : null, 'kelurahan', 'Pilih Kelurahan', oldKel);
    });

    // Event Listeners Alamat Lama
    document.getElementById('old_provinsi').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/regencies/${this.value}` : null, 'old_kota', 'Pilih Kota Lama', oldLamaKota);
        fetchRegions(null, 'old_kecamatan', 'Pilih Kecamatan Lama', null);
        fetchRegions(null, 'old_kelurahan', 'Pilih Kelurahan Lama', null);
    });
    document.getElementById('old_kota').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/districts/${this.value}` : null, 'old_kecamatan', 'Pilih Kecamatan Lama', oldLamaKec);
        fetchRegions(null, 'old_kelurahan', 'Pilih Kelurahan Lama', null);
    });
    document.getElementById('old_kecamatan').addEventListener('change', function() {
        fetchRegions(this.value ? `${apiUrl}/villages/${this.value}` : null, 'old_kelurahan', 'Pilih Kelurahan Lama', oldLamaKel);
    });

    // Init All Data on Load
    fetchRegions(`${apiUrl}/provinces`, 'provinsi', 'Pilih Provinsi', oldProv).then(() => {
        if(oldProv) {
            document.getElementById('provinsi').dispatchEvent(new Event('change'));
            setTimeout(() => {
                if(oldKota) {
                    document.getElementById('kota').dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        if(oldKec) document.getElementById('kecamatan').dispatchEvent(new Event('change'));
                    }, 300);
                }
            }, 300);
        }
    });

    fetchRegions(`${apiUrl}/provinces`, 'old_provinsi', 'Pilih Provinsi (Lama)', oldLamaProv).then(() => {
        if(oldLamaProv) {
            document.getElementById('old_provinsi').dispatchEvent(new Event('change'));
            setTimeout(() => {
                if(oldLamaKota) {
                    document.getElementById('old_kota').dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        if(oldLamaKec) document.getElementById('old_kecamatan').dispatchEvent(new Event('change'));
                    }, 300);
                }
            }, 300);
        }
    });
        // Copy Alamat Baru = Alamat Lama
        document.getElementById('copy_alamat').addEventListener('change', async function() {
            if(this.checked) {
                document.getElementById('alamat_namajalan').value = document.getElementById('alamatlama_namajalan').value;
                document.getElementById('alamat_rt').value = document.getElementById('alamatlama_rt').value;
                document.getElementById('alamat_rw').value = document.getElementById('alamatlama_rw').value;
                document.getElementById('kodepos').value = document.getElementById('alamatlama_kodepos').value;
                
                M.updateTextFields();

                // Sync API Dropdowns with async sequence
                let provLama = document.getElementById('old_provinsi').value;
                let kotaLama = document.getElementById('old_kota').value;
                let kecLama = document.getElementById('old_kecamatan').value;
                let kelLama = document.getElementById('old_kelurahan').value;

                if (provLama) {
                    await fetchRegions(`${apiUrl}/regencies/${provLama}`, 'kota', 'Pilih Kota', kotaLama);
                    if (kotaLama) {
                        await fetchRegions(`${apiUrl}/districts/${kotaLama}`, 'kecamatan', 'Pilih Kecamatan', kecLama);
                        if (kecLama) {
                            await fetchRegions(`${apiUrl}/villages/${kecLama}`, 'kelurahan', 'Pilih Kelurahan', kelLama);
                        }
                    }
                    document.getElementById('provinsi').value = provLama;
                    M.FormSelect.init(document.getElementById('provinsi'));
                }
            }
        });

    // Foto Preview & Item Removal Manager (DataTransfer)
    const fotoInput = document.getElementById('foto_input');
    const fotoListContainer = document.getElementById('foto_preview_list');
    let fotoDataTransfer = new DataTransfer();

    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            for (let i = 0; i < this.files.length; i++) {
                fotoDataTransfer.items.add(this.files[i]);
            }
            this.files = fotoDataTransfer.files;
            renderFotoPreviews();
        });
    }

    function renderFotoPreviews() {
        if (!fotoListContainer) return;
        fotoListContainer.innerHTML = '';
        for (let i = 0; i < fotoDataTransfer.files.length; i++) {
            let file = fotoDataTransfer.files[i];
            let index = i;
            let div = document.createElement('div');
            div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; padding:8px 12px; margin-top:6px; background:#f5f5f5; border-radius:4px; border:1px solid #e0e0e0; font-size:0.85rem;';
            
            let nameSpan = document.createElement('span');
            nameSpan.innerHTML = `<i class="material-icons tiny blue-text" style="vertical-align:middle; margin-right:4px;">image</i> <b>${file.name}</b> (${(file.size/1024).toFixed(1)} KB)`;
            
            let actionBox = document.createElement('div');
            actionBox.style.cssText = 'display:flex; gap:10px; align-items:center;';

            let viewBtn = document.createElement('a');
            viewBtn.href = '#!';
            viewBtn.className = 'blue-text';
            viewBtn.style.cssText = 'font-weight:500; display:flex; align-items:center; gap:2px;';
            viewBtn.innerHTML = '<i class="material-icons tiny">open_in_new</i> Lihat';
            viewBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                let fileUrl = URL.createObjectURL(file);
                window.open(fileUrl, '_blank');
            };

            let removeBtn = document.createElement('a');
            removeBtn.href = '#!';
            removeBtn.className = 'red-text';
            removeBtn.style.cssText = 'font-weight:bold; display:flex; align-items:center; gap:2px;';
            removeBtn.innerHTML = '<i class="material-icons tiny">delete</i> Hapus';
            removeBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                fotoDataTransfer.items.remove(index);
                fotoInput.files = fotoDataTransfer.files;
                renderFotoPreviews();
            };
            
            actionBox.appendChild(viewBtn);
            actionBox.appendChild(removeBtn);
            div.appendChild(nameSpan);
            div.appendChild(actionBox);
            fotoListContainer.appendChild(div);
        }
    }

    // Dokumen Preview & Item Removal Manager (DataTransfer)
    const dokumenInput = document.getElementById('dokumen_input');
    const dokumenListContainer = document.getElementById('dokumen_preview_list');
    let dokumenDataTransfer = new DataTransfer();

    if (dokumenInput) {
        dokumenInput.addEventListener('change', function() {
            for (let i = 0; i < this.files.length; i++) {
                dokumenDataTransfer.items.add(this.files[i]);
            }
            this.files = dokumenDataTransfer.files;
            renderDokumenPreviews();
        });
    }

    function renderDokumenPreviews() {
        if (!dokumenListContainer) return;
        dokumenListContainer.innerHTML = '';
        for (let i = 0; i < dokumenDataTransfer.files.length; i++) {
            let file = dokumenDataTransfer.files[i];
            let index = i;
            let div = document.createElement('div');
            div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; padding:8px 12px; margin-top:6px; background:#f5f5f5; border-radius:4px; border:1px solid #e0e0e0; font-size:0.85rem;';
            
            let nameSpan = document.createElement('span');
            nameSpan.innerHTML = `<i class="material-icons tiny red-text" style="vertical-align:middle; margin-right:4px;">description</i> <b>${file.name}</b> (${(file.size/1024).toFixed(1)} KB)`;
            
            let actionBox = document.createElement('div');
            actionBox.style.cssText = 'display:flex; gap:10px; align-items:center;';

            let viewBtn = document.createElement('a');
            viewBtn.href = '#!';
            viewBtn.className = 'blue-text';
            viewBtn.style.cssText = 'font-weight:500; display:flex; align-items:center; gap:2px;';
            viewBtn.innerHTML = '<i class="material-icons tiny">open_in_new</i> Lihat';
            viewBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                let fileUrl = URL.createObjectURL(file);
                window.open(fileUrl, '_blank');
            };

            let removeBtn = document.createElement('a');
            removeBtn.href = '#!';
            removeBtn.className = 'red-text';
            removeBtn.style.cssText = 'font-weight:bold; display:flex; align-items:center; gap:2px;';
            removeBtn.innerHTML = '<i class="material-icons tiny">delete</i> Hapus';
            removeBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                dokumenDataTransfer.items.remove(index);
                dokumenInput.files = dokumenDataTransfer.files;
                renderDokumenPreviews();
            };
            
            actionBox.appendChild(viewBtn);
            actionBox.appendChild(removeBtn);
            div.appendChild(nameSpan);
            div.appendChild(actionBox);
            dokumenListContainer.appendChild(div);
        }
    }

    // Modal Confirmation for Existing File Deletion
    let pendingDeleteType = null;
    let pendingDeletePath = null;
    let pendingDeleteElementId = null;

    function confirmDeleteExistingFile(type, path, elementId) {
        pendingDeleteType = type;
        pendingDeletePath = path;
        pendingDeleteElementId = elementId;
        
        let filename = path.split('/').pop();
        let nameEl = document.getElementById('delete-file-name');
        if (nameEl) nameEl.innerText = filename;
        
        let modalEl = document.getElementById('modalDeleteExistingFile');
        let instance = M.Modal.getInstance(modalEl);
        if (!instance) {
            instance = M.Modal.init(modalEl);
        }
        instance.open();
    }

    function executeDeleteExistingFile() {
        if (pendingDeletePath && pendingDeleteType) {
            let form = document.getElementById('asset-form');
            let hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = pendingDeleteType === 'foto' ? 'delete_foto[]' : 'delete_dokumen[]';
            hiddenInput.value = pendingDeletePath;
            form.appendChild(hiddenInput);
            
            let el = document.getElementById(pendingDeleteElementId);
            if (el) {
                el.style.display = 'none';
            }
            
            let instance = M.Modal.getInstance(document.getElementById('modalDeleteExistingFile'));
            if (instance) instance.close();
            
            M.toast({html: 'Berkas ditandai untuk dihapus saat Anda menyimpan aset', classes: 'amber darken-2 white-text'});
        }
    }

    // Toggle Kondisi Aset Dynamic Fields (Disewakan / Dilelang / Lain-lain)
    function toggleKondisiFields() {
        let selectEl = document.getElementById('kondisi_aset');
        if (!selectEl) return;
        let val = selectEl.value;
        let lainlainContainer = document.getElementById('kondisi_lainlain_container');
        let sewaLelangContainer = document.getElementById('sewa_lelang_container');
        let headerTitle = document.getElementById('sewa_lelang_header_title');
        let labelNama = document.getElementById('label_nama_pihak');
        let labelNoSurat = document.getElementById('label_no_surat');
        let labelTglMulai = document.getElementById('label_tgl_mulai');
        let labelTglSelesai = document.getElementById('label_tgl_selesai');
        let labelNilai = document.getElementById('label_nilai');

        // Toggle Lain-lain
        if (lainlainContainer) {
            lainlainContainer.style.display = (val === 'LAIN-LAIN') ? 'block' : 'none';
        }

        // Toggle Sewa / Lelang
        if (sewaLelangContainer) {
            if (val === 'DISEWAKAN') {
                sewaLelangContainer.style.display = 'block';
                if (headerTitle) headerTitle.innerHTML = '<i class="material-icons">real_estate_agent</i> Keterangan Penyewaan Aset';
                if (labelNama) labelNama.innerText = 'Nama Penyewa';
                if (labelNoSurat) labelNoSurat.innerText = 'Nomor Surat Perjanjian Sewa';
                if (labelTglMulai) labelTglMulai.innerText = 'Tanggal Mulai Sewa';
                if (labelTglSelesai) labelTglSelesai.innerText = 'Tanggal Selesai Sewa';
                if (labelNilai) labelNilai.innerText = 'Nilai Sewa (Rp)';
            } else if (val === 'DILELANG') {
                sewaLelangContainer.style.display = 'block';
                if (headerTitle) headerTitle.innerHTML = '<i class="material-icons">gavel</i> Keterangan Pelelangan Aset';
                if (labelNama) labelNama.innerText = 'Nama Pemenang Lelang / Pembeli';
                if (labelNoSurat) labelNoSurat.innerText = 'Nomor Risalah Lelang / Penetapan';
                if (labelTglMulai) labelTglMulai.innerText = 'Tanggal Pelaksanaan Lelang';
                if (labelTglSelesai) labelTglSelesai.innerText = 'Batas Waktu Pelunasan Lelang';
                if (labelNilai) labelNilai.innerText = 'Nilai Pokok Lelang (Rp)';
            } else {
                sewaLelangContainer.style.display = 'none';
            }
        }

        M.updateTextFields();
    }

    // Trigger on initial load
    document.addEventListener('DOMContentLoaded', function() {
        toggleKondisiFields();
    });
</script>
@endpush
