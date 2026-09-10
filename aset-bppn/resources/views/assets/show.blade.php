@extends('layouts.app')

@section('title', 'Detail Aset - ' . $asset->kode_aset)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .detail-card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .detail-label {
        color: #757575;
        font-size: 0.85rem;
        margin-bottom: 2px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-value {
        font-weight: 600;
        font-size: 1.05rem;
        margin-top: 0;
        margin-bottom: 18px;
        color: #212121;
    }
    .section-title {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.15rem;
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tabs {
        background-color: #ffffff;
        border-radius: 8px 8px 0 0;
        border-bottom: 2px solid #e0e0e0;
    }
    .tabs .tab a {
        color: #546e7a;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .tabs .tab a:hover, .tabs .tab a.active {
        color: #1565c0 !important;
    }
    .tabs .indicator {
        background-color: #1565c0;
        height: 3px;
    }
    .tab-content {
        background: #ffffff;
        padding: 24px;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        min-height: 400px;
    }
    #map {
        height: 380px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    .badge-status {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<div class="row" style="margin-top: 15px; margin-bottom: 10px;">
    <div class="col s12">
        <a href="{{ route('assets.index') }}" class="btn-flat waves-effect" style="padding-left: 0; color: #546e7a;">
            <i class="material-icons left">arrow_back</i> Kembali ke Gudang Aset
        </a>
    </div>
</div>

{{-- Banner Status Trashed --}}
@if($asset->trashed())
<div class="row">
    <div class="col s12">
        <div class="card-panel red lighten-5" style="border-left: 5px solid #d32f2f; border-radius: 6px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="material-icons red-text text-darken-2" style="font-size: 30px;">delete_forever</i>
                <div>
                    <h6 class="red-text text-darken-3" style="margin: 0; font-weight: 700; font-size: 1.1rem;">ASET INI TELAH DIHAPUS (STATUS: TERHAPUS)</h6>
                    <p style="margin: 2px 0 0 0; color: #5d4037; font-size: 0.9rem;">
                        Dihapus pada {{ $asset->deleted_at->translatedFormat('d F Y, H:i') }} WIB. Anda hanya dapat melihat rincian data (Read-Only) atau memulihkan aset ini.
                    </p>
                </div>
            </div>
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <div>
                <form action="{{ route('assets.restore', $asset->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan aset ini?')">
                    @csrf
                    <button type="submit" class="btn green darken-2 waves-effect waves-light" style="border-radius: 6px;">
                        <i class="material-icons left">restore_from_trash</i> Pulihkan Aset
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Header Hero Card --}}
<div class="card detail-card">
    <div class="card-content" style="padding: 20px 24px;">
        <div class="row" style="margin-bottom: 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div class="col s12 m7" style="padding-left: 0;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.6rem; color: #0d47a1;">
                        {{ $asset->kode_aset }}
                    </h4>
                    
                    @php
                        $kondisiUpper = strtoupper($asset->kondisi_aset ?? '');
                        $badgeClass = 'grey darken-1';
                        $badgeIcon = 'info';
                        if ($kondisiUpper == 'DIHUNI') {
                            $badgeClass = 'blue darken-2'; $badgeIcon = 'home';
                        } elseif ($kondisiUpper == 'DIGUNAKAN PIHAK KETIGA') {
                            $badgeClass = 'green darken-2'; $badgeIcon = 'people';
                        } elseif ($kondisiUpper == 'KOSONG') {
                            $badgeClass = 'red darken-2'; $badgeIcon = 'domain_disabled';
                        } elseif ($kondisiUpper == 'DISEWAKAN') {
                            $badgeClass = 'teal darken-2'; $badgeIcon = 'real_estate_agent';
                        } elseif ($kondisiUpper == 'DILELANG') {
                            $badgeClass = 'orange darken-3'; $badgeIcon = 'gavel';
                        }
                    @endphp
                    
                    <span class="badge-status white-text {{ $badgeClass }}">
                        <i class="material-icons" style="font-size: 16px;">{{ $badgeIcon }}</i> {{ $asset->kondisi_aset ?? 'N/A' }}
                    </span>

                    @if($asset->trashed())
                        <span class="badge-status white-text red darken-3">
                            <i class="material-icons" style="font-size: 16px;">delete</i> TERHAPUS
                        </span>
                    @endif
                </div>
                <p style="margin: 0; color: #546e7a; font-size: 0.95rem;">
                    <i class="material-icons tiny" style="vertical-align: middle;">category</i> <strong>{{ $asset->jenis_aset }}</strong>
                    @if($asset->regency)
                        &bull; <i class="material-icons tiny" style="vertical-align: middle;">location_on</i> {{ $asset->regency->name }}
                    @endif
                </p>
            </div>

            <div class="col s12 m5 right-align" style="padding-right: 0; display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;">
                <a href="{{ route('assets.singleBukuProfilPdf', $asset->id) }}" onclick="event.preventDefault(); startExportWithProgress('{{ route('assets.singleBukuProfilPdf', $asset->id) }}', 'Buku Profil Aset - {{ $asset->kode_aset }}', 'Buku_Profil_{{ $asset->kode_aset }}.pdf');" class="btn blue darken-3 waves-effect waves-light" style="border-radius: 6px;">
                    <i class="material-icons left">book</i> Buku Profil (PDF)
                </a>
                @if(!$asset->trashed())
                    <a href="#modalUploadAttachment" class="modal-trigger btn green darken-2 waves-effect waves-light" style="border-radius: 6px;">
                        <i class="material-icons left">add_photo_alternate</i> Tambah Foto / Dokumen
                    </a>
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn amber darken-3 waves-effect waves-light" style="border-radius: 6px;">
                        <i class="material-icons left">edit</i> Edit Aset
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Main Tabbed Section --}}
<div class="row">
    <div class="col s12">
        <!-- Tabs Header -->
        <ul class="tabs z-depth-1" id="asset-tabs">
            <li class="tab col s2"><a class="active" href="#tab-ringkasan"><i class="material-icons left">info</i> Ringkasan & Legalitas</a></li>
            <li class="tab col s2"><a href="#tab-alamat"><i class="material-icons left">place</i> Alamat & Batas</a></li>
            <li class="tab col s3"><a href="#tab-kondisi"><i class="material-icons left">fact_check</i> Kondisi & Transaksi</a></li>
            <li class="tab col s2"><a href="#tab-peta"><i class="material-icons left">map</i> Peta Lokasi</a></li>
            <li class="tab col s3"><a href="#tab-lampiran"><i class="material-icons left">attachment</i> Foto & Dokumen ({{ count($asset->foto_paths) + count($asset->dokumen_paths) }})</a></li>
        </ul>

        <!-- TAB 1: Ringkasan & Legalitas -->
        <div id="tab-ringkasan" class="tab-content">
            <div class="row" style="margin-bottom: 0;">
                <div class="col s12 l6">
                    <h5 class="section-title blue-text text-darken-3">
                        <i class="material-icons">account_balance</i> Informasi Legalitas & Asal Usul
                    </h5>
                    
                    <p class="detail-label">Bank Asal Eks BPPN</p>
                    <p class="detail-value">{{ $asset->bank_asal ?? '-' }}</p>

                    <p class="detail-label">Permasalahan / Sengketa Hukum</p>
                    <p class="detail-value" style="color: {{ !empty($asset->permasalahan) && $asset->permasalahan != '-' ? '#c62828' : '#212121' }};">
                        {{ $asset->permasalahan ?? '-' }}
                    </p>

                    <p class="detail-label">Jenis Bukti Kepemilikan</p>
                    <p class="detail-value">{{ $asset->jenis_bukti_kepemilikan ?? '-' }}</p>

                    <p class="detail-label">Nomor Bukti Kepemilikan</p>
                    <p class="detail-value">{{ $asset->nomor_bukti_kepemilikan ?? '-' }}</p>

                    <p class="detail-label">Wakil Kerja (Waker)</p>
                    <p class="detail-value">{{ $asset->wakil_kerja ?? '-' }}</p>
                </div>

                <div class="col s12 l6">
                    <h5 class="section-title blue-text text-darken-3">
                        <i class="material-icons">straighten</i> Dimensi & Nilai Finansial
                    </h5>
                    
                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s6">
                            <p class="detail-label">Luas Tanah</p>
                            <p class="detail-value">{{ $asset->luas_tanah ? number_format($asset->luas_tanah, 2, ',', '.') . ' m²' : '-' }}</p>
                        </div>
                        <div class="col s6">
                            <p class="detail-label">Luas Bangunan</p>
                            <p class="detail-value">{{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 2, ',', '.') . ' m²' : '-' }}</p>
                        </div>
                    </div>

                    <p class="detail-label">Nilai Jual Objek Pajak (NJOP)</p>
                    <p class="detail-value" style="font-size: 1.2rem; color: #2e7d32;">
                        {{ $asset->njop ? 'Rp ' . number_format($asset->njop, 2, ',', '.') : '-' }}
                    </p>

                    <p class="detail-label">Jenis Aset Properti</p>
                    <p class="detail-value">{{ $asset->jenis_aset }}</p>

                    <p class="detail-label">Status Papan Nama</p>
                    <p class="detail-value">
                        @if($asset->papan_nama)
                            <span class="badge-status green white-text"><i class="material-icons" style="font-size: 14px;">check</i> Terpasang</span>
                        @else
                            <span class="badge-status grey lighten-2 grey-text text-darken-3">Belum Terpasang</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- TAB 2: Alamat & Batas Wilayah -->
        <div id="tab-alamat" class="tab-content">
            <div class="row">
                <!-- Alamat Baru -->
                <div class="col s12 m6">
                    <div class="card-panel z-depth-0" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 18px;">
                        <h5 class="section-title blue-text text-darken-3" style="font-size: 1.05rem;">
                            <i class="material-icons">navigation</i> Alamat Baru (Sesuai Wilayah)
                        </h5>
                        
                        <p class="detail-label">Nama Jalan / Detail</p>
                        <p class="detail-value">{{ $asset->alamat_namajalan ?? '-' }}</p>

                        <p class="detail-label">RT / RW</p>
                        <p class="detail-value">{{ $asset->alamat_rt_rw ?? '-' }}</p>

                        <p class="detail-label">Kelurahan / Desa</p>
                        <p class="detail-value">{{ $asset->village->name ?? '-' }}</p>

                        <p class="detail-label">Kecamatan</p>
                        <p class="detail-value">{{ $asset->district->name ?? '-' }}</p>

                        <p class="detail-label">Kabupaten / Kota & Provinsi</p>
                        <p class="detail-value">{{ $asset->regency->name ?? '-' }}, {{ $asset->province->name ?? '-' }}</p>

                        <p class="detail-label">Kode Pos</p>
                        <p class="detail-value">{{ $asset->kodepos ?? '-' }}</p>
                    </div>
                </div>

                <!-- Alamat Lama -->
                <div class="col s12 m6">
                    <div class="card-panel z-depth-0" style="background: #fdfdfe; border: 1px solid #e9ecef; border-radius: 6px; padding: 18px;">
                        <h5 class="section-title grey-text text-darken-3" style="font-size: 1.05rem;">
                            <i class="material-icons">history</i> Alamat Lama (Sebelum Pemekaran)
                        </h5>
                        
                        <p class="detail-label">Nama Jalan / Detail Lama</p>
                        <p class="detail-value">{{ $asset->alamatlama_namajalan ?? '-' }}</p>

                        <p class="detail-label">RT / RW Lama</p>
                        <p class="detail-value">{{ $asset->alamatlama_rt_rw ?? '-' }}</p>

                        <p class="detail-label">Kelurahan / Desa Lama</p>
                        <p class="detail-value">{{ $asset->oldVillage->name ?? '-' }}</p>

                        <p class="detail-label">Kecamatan Lama</p>
                        <p class="detail-value">{{ $asset->oldDistrict->name ?? '-' }}</p>

                        <p class="detail-label">Kabupaten / Kota & Provinsi Lama</p>
                        <p class="detail-value">{{ $asset->oldRegency->name ?? '-' }}, {{ $asset->oldProvince->name ?? '-' }}</p>

                        <p class="detail-label">Kode Pos Lama</p>
                        <p class="detail-value">{{ $asset->alamatlama_kodepos ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Batas-Batas Wilayah -->
            <h5 class="section-title blue-text text-darken-3" style="margin-top: 15px;">
                <i class="material-icons">explore</i> Batas-Batas Wilayah Geografis
            </h5>
            <div class="row" style="margin-bottom: 0;">
                <div class="col s12 m3">
                    <div class="card-panel center-align z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 14px;">
                        <div style="font-weight: 700; color: #1565c0; margin-bottom: 4px;">UTARA</div>
                        <div style="font-size: 0.95rem; color: #424242;">{{ $asset->batas_utara ?? '-' }}</div>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center-align z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 14px;">
                        <div style="font-weight: 700; color: #1565c0; margin-bottom: 4px;">TIMUR</div>
                        <div style="font-size: 0.95rem; color: #424242;">{{ $asset->batas_timur ?? '-' }}</div>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center-align z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 14px;">
                        <div style="font-weight: 700; color: #1565c0; margin-bottom: 4px;">SELATAN</div>
                        <div style="font-size: 0.95rem; color: #424242;">{{ $asset->batas_selatan ?? '-' }}</div>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center-align z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 14px;">
                        <div style="font-weight: 700; color: #1565c0; margin-bottom: 4px;">BARAT</div>
                        <div style="font-size: 0.95rem; color: #424242;">{{ $asset->batas_barat ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Kondisi & Transaksi -->
        <div id="tab-kondisi" class="tab-content">
            {{-- Alert Reminder Perpanjangan Sewa --}}
            @if($kondisiUpper === 'DISEWAKAN' && $asset->is_sewa_expiring)
                @if($asset->sewa_status_info['status'] === 'expired')
                    <div class="card-panel red lighten-5 red-text text-darken-4" style="border-left: 5px solid #d32f2f; border-radius: 6px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="material-icons red-text text-darken-2" style="font-size: 32px;">warning</i>
                            <div>
                                <strong style="font-size: 1.05rem;">PERINGATAN: Masa Kontrak Sewa Telah Berakhir!</strong>
                                <div style="font-size: 0.9rem; margin-top: 3px; color: #b71c1c;">
                                    Kontrak sewa dengan <strong>{{ $asset->sewa_lelang_nama_pihak }}</strong> telah berakhir pada {{ $asset->sewa_lelang_tgl_selesai ? $asset->sewa_lelang_tgl_selesai->translatedFormat('d F Y') : '-' }}. Segera lakukan perpanjangan kontrak.
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn red darken-2 waves-effect waves-light modal-trigger" data-target="modalPerpanjangSewa" style="border-radius: 6px;">
                            <i class="material-icons left">update</i> Perpanjang Kontrak Sewa
                        </button>
                    </div>
                @elseif($asset->sewa_status_info['status'] === 'warning')
                    <div class="card-panel amber lighten-5 orange-text text-darken-4" style="border-left: 5px solid #f57c00; border-radius: 6px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="material-icons orange-text text-darken-3" style="font-size: 32px;">notification_important</i>
                            <div>
                                <strong style="font-size: 1.05rem;">PERINGATAN: Masa Kontrak Sewa Segera Berakhir (Sisa &le; 6 Bulan)!</strong>
                                <div style="font-size: 0.9rem; margin-top: 3px; color: #e65100;">
                                    Masa sewa untuk aset ini tersisa <strong>{{ $asset->sewa_status_info['badge'] }}</strong> (Jatuh tempo: {{ $asset->sewa_lelang_tgl_selesai ? $asset->sewa_lelang_tgl_selesai->translatedFormat('d F Y') : '-' }}). Silakan siapkan perpanjangan kontrak.
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn orange darken-3 waves-effect waves-light modal-trigger" data-target="modalPerpanjangSewa" style="border-radius: 6px;">
                            <i class="material-icons left">update</i> Perpanjang Kontrak Sewa
                        </button>
                    </div>
                @endif
            @endif

            <div class="row">
                <div class="col s12 {{ ($kondisiUpper == 'DISEWAKAN' || $kondisiUpper == 'DILELANG' || !empty($asset->sewa_lelang_nama_pihak)) ? 'l6' : 'l12' }}">
                    <h5 class="section-title blue-text text-darken-3">
                        <i class="material-icons">health_and_safety</i> Kondisi Fisik & Monitoring
                    </h5>

                    <p class="detail-label">Status Kondisi Aset</p>
                    <p class="detail-value">
                        <span class="badge-status white-text {{ $badgeClass }}">
                            <i class="material-icons" style="font-size: 16px;">{{ $badgeIcon }}</i> {{ $asset->kondisi_aset }}
                        </span>
                        @if($asset->kondisi_aset == 'LAIN-LAIN' && $asset->kondisi_aset_lainlain)
                            <span class="grey-text text-darken-1" style="margin-left: 8px;">({{ $asset->kondisi_aset_lainlain }})</span>
                        @endif
                    </p>

                    <p class="detail-label">Keterangan Kondisi Fisik</p>
                    <p class="detail-value">{{ $asset->keterangan_kondisi_aset ?? '-' }}</p>

                    <p class="detail-label">Potensi Pemanfaatan Aset</p>
                    <p class="detail-value">{{ $asset->potensi_aset ?? '-' }}</p>

                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s12 m6">
                            <p class="detail-label">Tanggal Update Kondisi Terakhir</p>
                            <p class="detail-value">{{ $asset->tanggal_update_kondisi ? $asset->tanggal_update_kondisi->translatedFormat('d F Y') : '-' }}</p>
                        </div>
                        <div class="col s12 m6">
                            <p class="detail-label">Periode Semester & Tahun</p>
                            <p class="detail-value">Semester {{ $asset->semester ?? '-' }} / {{ $asset->tahun ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Panel Khusus Disewakan / Dilelang --}}
                @if($kondisiUpper == 'DISEWAKAN' || $kondisiUpper == 'DILELANG' || !empty($asset->sewa_lelang_nama_pihak))
                <div class="col s12 l6">
                    <div class="card-panel blue lighten-5" style="border-left: 5px solid #1565c0; border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                            <h5 class="blue-text text-darken-3" style="margin: 0; font-weight: 700; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                                <i class="material-icons">{{ $kondisiUpper == 'DILELANG' ? 'gavel' : 'real_estate_agent' }}</i>
                                {{ $kondisiUpper == 'DILELANG' ? 'Informasi Pelelangan Aset' : 'Informasi Kontrak Sewa Aktif' }}
                            </h5>
                            @if($kondisiUpper == 'DISEWAKAN')
                                <button type="button" class="btn-small teal darken-2 waves-effect waves-light modal-trigger" data-target="modalPerpanjangSewa" style="border-radius: 4px;">
                                    <i class="material-icons left">update</i> Perpanjang Sewa
                                </button>
                            @endif
                        </div>

                        <p class="detail-label">{{ $kondisiUpper == 'DILELANG' ? 'Nama Pemenang Lelang / Pembeli' : 'Nama Penyewa Saat Ini' }}</p>
                        <p class="detail-value" style="color: #0d47a1;">{{ $asset->sewa_lelang_nama_pihak ?? '-' }}</p>

                        <p class="detail-label">{{ $kondisiUpper == 'DILELANG' ? 'Nomor Risalah Lelang / Surat Penetapan' : 'Nomor Surat Perjanjian Sewa' }}</p>
                        <p class="detail-value">{{ $asset->sewa_lelang_no_surat ?? '-' }}</p>

                        <div class="row" style="margin-bottom: 0;">
                            <div class="col s6">
                                <p class="detail-label">{{ $kondisiUpper == 'DILELANG' ? 'Tanggal Pelaksanaan Lelang' : 'Tanggal Mulai Sewa' }}</p>
                                <p class="detail-value">{{ $asset->sewa_lelang_tgl_mulai ? $asset->sewa_lelang_tgl_mulai->translatedFormat('d F Y') : '-' }}</p>
                            </div>
                            <div class="col s6">
                                <p class="detail-label">{{ $kondisiUpper == 'DILELANG' ? 'Batas Waktu Pelunasan' : 'Tanggal Selesai Sewa' }}</p>
                                <p class="detail-value">{{ $asset->sewa_lelang_tgl_selesai ? $asset->sewa_lelang_tgl_selesai->translatedFormat('d F Y') : '-' }}</p>
                            </div>
                        </div>

                        <p class="detail-label">{{ $kondisiUpper == 'DILELANG' ? 'Nilai Pokok Lelang (Rp)' : 'Nilai Sewa Kontrak (Rp)' }}</p>
                        <p class="detail-value" style="font-size: 1.25rem; color: #1b5e20;">
                            {{ $asset->sewa_lelang_nilai ? 'Rp ' . number_format($asset->sewa_lelang_nilai, 2, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Riwayat & Grafik Tren Nilai Sewa --}}
            @if($kondisiUpper == 'DISEWAKAN' || count($asset->leases) > 0)
            <div class="row" style="margin-top: 25px;">
                <div class="col s12">
                    <div class="card-panel z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
                        <h5 class="section-title blue-text text-darken-3" style="border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                            <i class="material-icons">trending_up</i> Grafik Tren Nilai Sewa Aset
                        </h5>
                        <div style="position: relative; height: 260px; width: 100%;">
                            <canvas id="assetLeaseChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col s12">
                    <div class="card-panel z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                            <h5 class="section-title blue-text text-darken-3" style="margin: 0; border: none; padding: 0;">
                                <i class="material-icons">history</i> Riwayat Kontrak Sewa ({{ count($asset->leases) }} Periode)
                            </h5>
                            <button type="button" class="btn-small blue darken-3 waves-effect waves-light modal-trigger" data-target="modalPerpanjangSewa" style="border-radius: 4px;">
                                <i class="material-icons left">add</i> Tambah / Perpanjang Sewa
                            </button>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="striped highlight" style="font-size: 0.9rem;">
                                <thead class="grey lighten-4">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Penyewa</th>
                                        <th>No. Surat / Dokumen</th>
                                        <th>Periode Sewa</th>
                                        <th>Durasi</th>
                                        <th class="right-align">Nilai Sewa (Rp)</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($asset->leases as $lIdx => $lease)
                                    <tr>
                                        <td>{{ $lIdx + 1 }}</td>
                                        <td><strong>{{ $lease->nama_penyewa }}</strong></td>
                                        <td>{{ $lease->no_surat ?? '-' }}</td>
                                        <td>
                                            {{ $lease->tgl_mulai->translatedFormat('d M Y') }} s/d {{ $lease->tgl_selesai->translatedFormat('d M Y') }}
                                        </td>
                                        <td>
                                            {{ $lease->tgl_mulai->diffInDays($lease->tgl_selesai) }} hari (~{{ round($lease->tgl_mulai->diffInMonths($lease->tgl_selesai)) }} bulan)
                                        </td>
                                        <td class="right-align" style="font-weight: 600; color: #1b5e20;">
                                            Rp {{ number_format($lease->nilai_sewa, 2, ',', '.') }}
                                        </td>
                                        <td>{{ $lease->keterangan ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="center-align grey-text" style="padding: 20px;">
                                            Belum ada catatan riwayat sewa untuk aset ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- TAB 4: Peta Lokasi -->
        <div id="tab-peta" class="tab-content">
            <div class="row" style="margin-bottom: 15px;">
                <div class="col s12 m8">
                    <p style="margin: 0; color: #546e7a; font-size: 0.95rem;">
                        <strong>Titik Koordinat:</strong> 
                        Latitude: <code style="background: #eceff1; padding: 2px 6px; border-radius: 4px;">{{ $asset->koordinat_latitude ?? '-' }}</code> &bull; 
                        Longitude: <code style="background: #eceff1; padding: 2px 6px; border-radius: 4px;">{{ $asset->koordinat_longitude ?? '-' }}</code>
                    </p>
                </div>
                <div class="col s12 m4 right-align">
                    @if($asset->koordinat_latitude && $asset->koordinat_longitude)
                        <a href="https://maps.google.com/?q={{ $asset->koordinat_latitude }},{{ $asset->koordinat_longitude }}" target="_blank" class="btn-small blue darken-3 waves-effect waves-light" style="border-radius: 4px;">
                            <i class="material-icons left">open_in_new</i> Buka di Google Maps
                        </a>
                    @endif
                </div>
            </div>

            <div id="map"></div>
        </div>

        <!-- TAB 5: Lampiran Foto & Dokumen -->
        <div id="tab-lampiran" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; border-bottom: 2px solid #e0e0e0; padding-bottom: 8px;">
                <h5 class="section-title blue-text text-darken-3" style="margin: 0; border-bottom: none; padding-bottom: 0;">
                    <i class="material-icons">photo_library</i> Galeri Foto Dokumentasi Fisik ({{ count($asset->foto_paths) }})
                </h5>
                @if(!$asset->trashed())
                <a href="#modalUploadAttachment" onclick="switchAttachmentTab('foto')" class="modal-trigger btn-small green darken-2 waves-effect waves-light" style="border-radius: 4px;">
                    <i class="material-icons left">add_a_photo</i> Tambah Foto
                </a>
                @endif
            </div>
            
            @php $fotos = $asset->foto_paths; @endphp
            @if(count($fotos) > 0)
                <div class="row">
                    @foreach($fotos as $fIndex => $fotoPath)
                        <div class="col s12 m6 l4" style="margin-bottom: 20px;">
                            <div class="card z-depth-1" style="border-radius: 6px; overflow: hidden; margin: 0;">
                                <div style="height: 200px; overflow: hidden; background: #f5f5f5;">
                                    <a href="{{ asset('storage/' . $fotoPath) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto Aset {{ $fIndex + 1 }}" style="width: 100%; height: 200px; object-fit: cover; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                </div>
                                <div class="card-content" style="padding: 10px 14px; background: #fafafa; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600; font-size: 0.85rem; color: #424242;">Foto #{{ $fIndex + 1 }}</span>
                                    <a href="{{ asset('storage/' . $fotoPath) }}" target="_blank" class="blue-text text-darken-2" style="font-size: 0.8rem; font-weight: 500;">
                                        <i class="material-icons tiny" style="vertical-align: middle;">open_in_new</i> Perbesar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="center-align" style="padding: 30px 0; color: #9e9e9e;">
                    <i class="material-icons" style="font-size: 48px;">no_photography</i>
                    <p style="margin-top: 6px;">Belum ada foto fisik yang diunggah untuk aset ini.</p>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #e0e0e0; padding-bottom: 8px;">
                <h5 class="section-title blue-text text-darken-3" style="margin: 0; border-bottom: none; padding-bottom: 0;">
                    <i class="material-icons">folder</i> Dokumen Legalitas & Berkas Lampiran ({{ count($asset->dokumen_paths) }})
                </h5>
                @if(!$asset->trashed())
                <a href="#modalUploadAttachment" onclick="switchAttachmentTab('dokumen')" class="modal-trigger btn-small teal darken-2 waves-effect waves-light" style="border-radius: 4px;">
                    <i class="material-icons left">note_add</i> Tambah Dokumen
                </a>
                @endif
            </div>

            @php $docs = $asset->dokumen_paths; @endphp
            @if(count($docs) > 0)
                <ul class="collapsible z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px;">
                    @foreach($docs as $dIndex => $docPath)
                        @php $ext = strtolower(pathinfo($docPath, PATHINFO_EXTENSION)); @endphp
                        <li>
                            <div class="collapsible-header" style="font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <i class="material-icons blue-text text-darken-2" style="margin-right: 8px;">description</i> 
                                    <span>Dokumen #{{ $dIndex + 1 }}: {{ basename($docPath) }}</span>
                                </div>
                                <span class="chip blue lighten-5 blue-text text-darken-3" style="font-size: 11px; height: 22px; line-height: 22px; margin: 0;">
                                    {{ strtoupper($ext) }}
                                </span>
                            </div>
                            <div class="collapsible-body white">
                                <div style="margin-bottom: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="{{ asset('storage/' . $docPath) }}" target="_blank" class="btn-small blue darken-2 waves-effect waves-light"><i class="material-icons left">open_in_new</i> Buka di Tab Baru</a>
                                    <a href="{{ asset('storage/' . $docPath) }}" download class="btn-small grey darken-2 waves-effect waves-light"><i class="material-icons left">file_download</i> Unduh Berkas</a>
                                </div>
                                @if($ext == 'pdf')
                                    <div style="margin-top: 10px;">
                                        <iframe src="{{ asset('storage/' . $docPath) }}" style="width: 100%; height: 420px; border: 1px solid #e0e0e0; border-radius: 4px;"></iframe>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="center-align" style="padding: 30px 0; color: #9e9e9e;">
                    <i class="material-icons" style="font-size: 48px;">folder_off</i>
                    <p style="margin-top: 6px;">Belum ada dokumen legalitas yang diunggah.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Tambah Foto & Dokumen Cepat --}}
<div id="modalUploadAttachment" class="modal modal-fixed-footer" style="max-width: 650px; max-height: 85%; border-radius: 8px;">
    <form action="{{ route('assets.uploadAttachment', $asset->id) }}" method="POST" enctype="multipart/form-data" id="formUploadAttachment">
        @csrf
        <div class="modal-content" style="padding: 24px;">
            <h5 class="blue-text text-darken-3" style="margin-top: 0; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="material-icons">cloud_upload</i> Tambah Lampiran Foto / Dokumen
            </h5>
            <p class="grey-text text-darken-1" style="font-size: 0.9rem; margin-bottom: 15px;">
                Unggah berkas foto dokumentasi fisik atau dokumen legalitas baru untuk aset <strong>{{ $asset->kode_aset }}</strong> secara cepat tanpa perlu membuka form edit.
            </p>

            <div class="row" style="margin-bottom: 15px;">
                <div class="col s12">
                    <ul class="tabs z-depth-0" id="upload-modal-tabs" style="border-bottom: 1px solid #e0e0e0;">
                        <li class="tab col s6"><a href="#upload-tab-foto" class="active"><i class="material-icons left tiny">photo_camera</i> Foto Fisik</a></li>
                        <li class="tab col s6"><a href="#upload-tab-dokumen"><i class="material-icons left tiny">description</i> Dokumen Legalitas</a></li>
                    </ul>
                </div>
            </div>

            <!-- Tab Upload Foto -->
            <div id="upload-tab-foto" style="padding-top: 10px;">
                <div class="file-field input-field" style="margin-top: 0;">
                    <div class="btn blue darken-2 waves-effect waves-light">
                        <span><i class="material-icons left">add_a_photo</i> Pilih Foto</span>
                        <input type="file" name="foto[]" id="inputUploadFoto" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Pilih satu atau beberapa file foto (JPG, PNG, max 10MB)">
                    </div>
                </div>
                <div id="previewFotoList" style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;"></div>
            </div>

            <!-- Tab Upload Dokumen -->
            <div id="upload-tab-dokumen" style="padding-top: 10px;">
                <div class="file-field input-field" style="margin-top: 0;">
                    <div class="btn teal darken-2 waves-effect waves-light">
                        <span><i class="material-icons left">note_add</i> Pilih Dokumen</span>
                        <input type="file" name="dokumen[]" id="inputUploadDokumen" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.zip">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Pilih berkas PDF, Word, Excel, Gambar, atau ZIP (max 25MB)">
                    </div>
                </div>
                <div id="previewDokumenList" style="margin-top: 10px;"></div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 12px 24px; background: #fafafa; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="modal-close btn-flat waves-effect">Batal</button>
            <button type="submit" id="btnSubmitUploadAttachment" class="btn green darken-2 waves-effect waves-light" style="border-radius: 4px;">
                <i class="material-icons left">cloud_upload</i> Simpan & Unggah Berkas
            </button>
        </div>
    </form>
</div>

{{-- Modal Perpanjang Kontrak Sewa --}}
<div id="modalPerpanjangSewa" class="modal" style="max-width: 600px; border-radius: 8px;">
    <form action="{{ route('assets.perpanjangSewa', $asset->id) }}" method="POST">
        @csrf
        <div class="modal-content" style="padding: 24px;">
            <h5 class="blue-text text-darken-3" style="margin-top: 0; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="material-icons">update</i> Perpanjang Kontrak Sewa
            </h5>
            <p class="grey-text text-darken-1" style="font-size: 0.9rem; margin-bottom: 20px;">
                Perbarui data kontrak sewa untuk aset <strong>{{ $asset->kode_aset }}</strong>. Riwayat kontrak sebelumnya akan otomatis tersimpan dalam sistem.
            </p>

            <div class="row" style="margin-bottom: 0;">
                <div class="input-field col s12">
                    <input type="text" id="modal_nama_penyewa" name="nama_penyewa" value="{{ $asset->sewa_lelang_nama_pihak ?? '' }}" required>
                    <label for="modal_nama_penyewa" class="active">Nama Penyewa</label>
                </div>
                <div class="input-field col s12">
                    <input type="text" id="modal_no_surat" name="no_surat" value="" placeholder="Contoh: SPK-SEWA/099/2027">
                    <label for="modal_no_surat" class="active">Nomor Surat Perjanjian / Adendum Baru</label>
                </div>
                <div class="input-field col s12 m6">
                    @php
                        $nextStartDate = $asset->sewa_lelang_tgl_selesai ? \Carbon\Carbon::parse($asset->sewa_lelang_tgl_selesai)->addDay()->format('Y-m-d') : date('Y-m-d');
                    @endphp
                    <input type="date" id="modal_tgl_mulai" name="tgl_mulai" value="{{ $nextStartDate }}" required>
                    <label for="modal_tgl_mulai" class="active">Tanggal Mulai Sewa Baru</label>
                </div>
                <div class="input-field col s12 m6">
                    @php
                        $nextEndDate = $asset->sewa_lelang_tgl_selesai ? \Carbon\Carbon::parse($asset->sewa_lelang_tgl_selesai)->addYear()->format('Y-m-d') : date('Y-m-d', strtotime('+1 year'));
                    @endphp
                    <input type="date" id="modal_tgl_selesai" name="tgl_selesai" value="{{ $nextEndDate }}" required>
                    <label for="modal_tgl_selesai" class="active">Tanggal Selesai Sewa Baru</label>
                </div>
                <div class="input-field col s12">
                    <input type="number" step="0.01" id="modal_nilai_sewa" name="nilai_sewa" value="{{ $asset->sewa_lelang_nilai ?? '' }}" required placeholder="Contoh: 85000000">
                    <label for="modal_nilai_sewa" class="active">Nilai Kontrak Sewa Baru (Rp)</label>
                </div>
                <div class="input-field col s12">
                    <textarea id="modal_keterangan" name="keterangan" class="materialize-textarea" placeholder="Contoh: Perpanjangan periode sewa ke-2"></textarea>
                    <label for="modal_keterangan" class="active">Keterangan / Catatan Perpanjangan</label>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 12px 24px; background: #fafafa; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="modal-close btn-flat waves-effect">Batal</button>
            <button type="submit" class="btn blue darken-2 waves-effect waves-light" style="border-radius: 4px;">
                <i class="material-icons left">save</i> Simpan Perpanjangan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Collapsibles
        var elemsCollapsible = document.querySelectorAll('.collapsible');
        M.Collapsible.init(elemsCollapsible);

        // Init Modals
        var elemsModal = document.querySelectorAll('.modal');
        M.Modal.init(elemsModal);

        // Map Setup
        let lat = {{ $asset->koordinat_latitude ?? -2.990934 }};
        let lng = {{ $asset->koordinat_longitude ?? 104.756554 }};
        let map = L.map('map').setView([lat, lng], 15);
        window.assetMap = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>{{ $asset->kode_aset }}</b><br>{{ $asset->alamat_namajalan ?? "Lokasi Aset" }}')
            .openPopup();

        // Init Tabs with Map InvalidateSize Listener
        var elemsTabs = document.querySelectorAll('.tabs');
        M.Tabs.init(elemsTabs, {
            onShow: function(tab) {
                if (tab && tab.id === 'tab-peta') {
                    setTimeout(function() {
                        if (window.assetMap) {
                            window.assetMap.invalidateSize();
                        }
                    }, 150);
                }
            }
        });

        // Render Asset Lease Trend Chart
        @if(count($asset->leases) > 0)
            let leaseLabels = {!! json_encode($asset->leases->map(function($l) { return $l->tgl_mulai->format('d/m/Y') . ' - ' . $l->tgl_selesai->format('d/m/Y'); })) !!};
            let leaseValues = {!! json_encode($asset->leases->pluck('nilai_sewa')) !!};
            
            let ctx = document.getElementById('assetLeaseChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: leaseLabels,
                        datasets: [{
                            label: 'Nilai Kontrak Sewa (Rp)',
                            data: leaseValues,
                            borderColor: '#1565c0',
                            backgroundColor: 'rgba(21, 101, 192, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#0d47a1',
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 15,
                                top: 5,
                                bottom: 5
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Nilai Sewa: Rp ' + Number(context.raw).toLocaleString('id-ID', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                                        }
                                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Tab switcher helper for modal upload
        window.switchAttachmentTab = function(tabName) {
            var uploadTabsElem = document.getElementById('upload-modal-tabs');
            if (uploadTabsElem) {
                var instance = M.Tabs.getInstance(uploadTabsElem);
                if (instance) {
                    instance.select(tabName === 'dokumen' ? 'upload-tab-dokumen' : 'upload-tab-foto');
                }
            }
        };

        // File previews
        const inputFoto = document.getElementById('inputUploadFoto');
        const previewFotoList = document.getElementById('previewFotoList');
        if (inputFoto && previewFotoList) {
            inputFoto.addEventListener('change', function() {
                previewFotoList.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '80px';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '6px';
                        img.style.border = '1px solid #ddd';
                        previewFotoList.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        const inputDok = document.getElementById('inputUploadDokumen');
        const previewDokList = document.getElementById('previewDokumenList');
        if (inputDok && previewDokList) {
            inputDok.addEventListener('change', function() {
                previewDokList.innerHTML = '';
                Array.from(this.files).forEach((file, idx) => {
                    const item = document.createElement('div');
                    item.className = 'chip';
                    item.innerHTML = `<i class="material-icons tiny left">description</i> ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    previewDokList.appendChild(item);
                });
            });
        }
    });
</script>
@endpush
