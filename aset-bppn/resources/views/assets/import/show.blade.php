@extends('layouts.app')

@section('title', 'Detail Sesi Impor #' . $import->id)

@section('content')
<div class="row" style="margin-bottom: 20px;">
    <div class="col s12">
        <div class="card-panel {{ $import->status == 'completed' ? 'green lighten-5' : ($import->status == 'partial' ? 'amber lighten-5' : 'red lighten-5') }}" 
             style="border-left: 5px solid {{ $import->status == 'completed' ? '#2e7d32' : ($import->status == 'partial' ? '#f57c00' : '#d32f2f') }}; border-radius: 6px; padding: 20px 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <a href="{{ route('assets.import.index') }}" class="blue-text text-darken-3" style="font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                        <i class="material-icons tiny">arrow_back</i> Kembali ke Riwayat Impor
                    </a>
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.7rem; color: {{ $import->status == 'completed' ? '#1b5e20' : ($import->status == 'partial' ? '#e65100' : '#b71c1c') }};">
                        Laporan Sesi Impor #{{ $import->id }}: {{ $import->file_name }}
                    </h4>
                    <p style="font-size: 0.95rem; margin: 4px 0 0 0;" class="grey-text text-darken-3">
                        Diproses pada <strong>{{ $import->created_at->translatedFormat('d F Y, H:i:s') }} WIB</strong> oleh <strong>{{ $import->user->name ?? 'Sistem' }}</strong>
                    </p>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    @if($import->failed_rows > 0)
                        <a href="{{ route('assets.import.downloadFailed', $import->id) }}" class="btn red darken-2 waves-effect waves-light" style="border-radius: 6px; font-weight: 600;">
                            <i class="material-icons left">file_download</i> Unduh Data Gagal (.xlsx)
                        </a>
                    @endif
                    <a href="{{ route('assets.index') }}" class="btn blue darken-3 waves-effect waves-light" style="border-radius: 6px; font-weight: 600;">
                        <i class="material-icons left">inventory_2</i> Buka Gudang Aset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Metric Cards --}}
<div class="row">
    <div class="col s12 m3">
        <div class="card-panel blue darken-2 white-text center-align" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">list_alt</i>
            <h6 style="margin: 4px 0;">Total Baris Diproses</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $import->total_rows }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">100% Data</p>
        </div>
    </div>

    <div class="col s12 m3">
        <div class="card-panel green darken-2 white-text center-align" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">check_circle</i>
            <h6 style="margin: 4px 0;">Berhasil Diimpor</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $import->success_rows }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">{{ $import->success_rate }}% Keberhasilan</p>
        </div>
    </div>

    <div class="col s12 m3">
        <div class="card-panel {{ $import->failed_rows > 0 ? 'red darken-2' : 'grey darken-1' }} white-text center-align" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">error_outline</i>
            <h6 style="margin: 4px 0;">Gagal Diimpor</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $import->failed_rows }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">{{ $import->total_rows > 0 ? round(($import->failed_rows / $import->total_rows) * 100) : 0 }}% Gagal</p>
        </div>
    </div>

    <div class="col s12 m3">
        <div class="card-panel {{ $import->status == 'completed' ? 'teal darken-2' : ($import->status == 'partial' ? 'orange darken-3' : 'red darken-3') }} white-text center-align" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">flag</i>
            <h6 style="margin: 4px 0;">Status Akhir</h6>
            <h4 style="margin: 6px 0; font-weight: 700; font-size: 1.5rem;">
                @if($import->status == 'completed')
                    SUKSES PENUH
                @elseif($import->status == 'partial')
                    SEBAGIAN GAGAL
                @elseif($import->status == 'failed')
                    GAGAL TOTAL
                @else
                    {{ strtoupper($import->status) }}
                @endif
            </h4>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Selesai dieksekusi</p>
        </div>
    </div>
</div>

{{-- Banner Penjelasan Data Gagal jika ada --}}
@if($import->failed_rows > 0)
<div class="row">
    <div class="col s12">
        <div class="card-panel amber lighten-5 orange-text text-darken-4" style="border-left: 5px solid #f57c00; border-radius: 6px; padding: 16px 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="material-icons orange-text text-darken-3" style="font-size: 32px;">info</i>
                    <div>
                        <strong style="font-size: 1.05rem;">Terdapat {{ $import->failed_rows }} baris data yang gagal diimpor.</strong>
                        <div style="font-size: 0.9rem; margin-top: 2px;">
                            Anda dapat mengunduh berkas Excel khusus data gagal dengan mengklik tombol di sebelah kanan. Berkas tersebut memuat hanya baris yang gagal dengan keterangan kesalahan di kolom paling kanan untuk diperbaiki dan diimpor ulang.
                        </div>
                    </div>
                </div>
                <a href="{{ route('assets.import.downloadFailed', $import->id) }}" class="btn orange darken-3 waves-effect waves-light" style="border-radius: 4px; font-weight: 600;">
                    <i class="material-icons left">file_download</i> Unduh Berkas Excel Data Gagal
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Tabel Rincian Data Impor --}}
<div class="row">
    <div class="col s12">
        <div class="card z-depth-1" style="border-radius: 8px;">
            <div class="card-content" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <span class="card-title blue-text text-darken-3" style="font-weight: 700; font-size: 1.2rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons">receipt_long</i> Rincian Setiap Baris Data Impor
                    </span>

                    {{-- Status Filter Tabs --}}
                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('assets.import.show', $import->id) }}" class="btn-small {{ empty($filterStatus) ? 'blue darken-3' : 'grey lighten-3 grey-text text-darken-3' }} waves-effect" style="border-radius: 4px; font-weight: 600;">
                            Semua ({{ $import->total_rows }})
                        </a>
                        <a href="{{ route('assets.import.show', [$import->id, 'status' => 'success']) }}" class="btn-small {{ $filterStatus === 'success' ? 'green darken-2' : 'grey lighten-3 grey-text text-darken-3' }} waves-effect" style="border-radius: 4px; font-weight: 600;">
                            Berhasil ({{ $import->success_rows }})
                        </a>
                        <a href="{{ route('assets.import.show', [$import->id, 'status' => 'failed']) }}" class="btn-small {{ $filterStatus === 'failed' ? 'red darken-2' : 'grey lighten-3 grey-text text-darken-3' }} waves-effect" style="border-radius: 4px; font-weight: 600;">
                            Gagal ({{ $import->failed_rows }})
                        </a>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="striped highlight" style="font-size: 0.92rem;">
                        <thead class="grey lighten-4">
                            <tr>
                                <th width="70">Baris #</th>
                                <th>Kode Aset</th>
                                <th>Jenis & Alamat</th>
                                <th>Kondisi Aset</th>
                                <th>Status Impor</th>
                                <th>Keterangan / Hasil</th>
                                <th class="center-align" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            @php
                                $d = $row->raw_data ?? [];
                                $isSuccess = $row->status === 'success';
                            @endphp
                            <tr style="{{ !$isSuccess ? 'background-color: #ffebee;' : '' }}">
                                <td><strong>#{{ $row->row_number }}</strong></td>
                                <td>
                                    <strong class="blue-text text-darken-3">{{ $row->kode_aset ?? ($d['kode_aset'] ?? '-') }}</strong>
                                    @if(!empty($d['bank_asal']))
                                        <div style="font-size: 0.8rem; color: #546e7a;">{{ $d['bank_asal'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $d['jenis_aset'] ?? '-' }}</div>
                                    <div style="font-size: 0.8rem; color: #546e7a;">
                                        {{ $d['alamat_namajalan'] ?? '-' }} ({{ $d['alamat_kota_kab'] ?? '' }})
                                    </div>
                                </td>
                                <td>
                                    <span class="chip grey lighten-3 grey-text text-darken-3" style="font-size: 11px; height: 22px; line-height: 22px; margin: 0;">
                                        {{ $d['kondisi_aset'] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($isSuccess)
                                        <span class="chip green lighten-5 green-text text-darken-3" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0;">
                                            <i class="material-icons tiny left" style="line-height: 24px;">check_circle</i> Berhasil
                                        </span>
                                    @else
                                        <span class="chip red lighten-5 red-text text-darken-3" style="font-weight: 600; font-size: 11px; height: 24px; line-height: 24px; margin: 0;">
                                            <i class="material-icons tiny left" style="line-height: 24px;">error</i> Gagal
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($isSuccess)
                                        <span class="green-text text-darken-3" style="font-size: 0.88rem; font-weight: 500;">
                                            Tersimpan ke database
                                            @if($row->created_asset_id)
                                                (ID #{{ $row->created_asset_id }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="red-text text-darken-3" style="font-size: 0.85rem; font-weight: 600;">
                                            {{ $row->error_message ?? 'Validasi gagal' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="center-align">
                                    @if($isSuccess && $row->created_asset_id)
                                        <a href="{{ route('assets.show', $row->created_asset_id) }}" class="btn-small blue darken-2 waves-effect waves-light" style="border-radius: 4px;" title="Buka Detail Aset">
                                            <i class="material-icons">open_in_new</i>
                                        </a>
                                    @else
                                        <button type="button" class="btn-small red darken-2 waves-effect waves-light modal-trigger" data-target="modal-detail-{{ $row->id }}" style="border-radius: 4px;" title="Lihat Penjelasan Error">
                                            <i class="material-icons">help_outline</i>
                                        </button>

                                        {{-- Modal Penjelasan Error --}}
                                        <div id="modal-detail-{{ $row->id }}" class="modal" style="max-width: 650px; border-radius: 8px;">
                                            <div class="modal-content left-align" style="padding: 24px;">
                                                <h5 class="red-text text-darken-3" style="margin-top: 0; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                                    <i class="material-icons">error</i> Detail Kesalahan Baris #{{ $row->row_number }}
                                                </h5>
                                                
                                                <div class="card-panel red lighten-5 red-text text-darken-4" style="border-left: 4px solid #d32f2f; margin-bottom: 15px;">
                                                    <strong>Penyebab Kegagalan:</strong>
                                                    <p style="margin: 4px 0 0 0; font-size: 0.95rem;">{{ $row->error_message }}</p>
                                                </div>

                                                <h6 style="font-weight: 700; color: #37474f; margin-top: 15px;">Data Mentah yang Diunggah:</h6>
                                                <div style="background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 4px; padding: 10px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                                                    @foreach($d as $k => $v)
                                                        @if(!empty($v))
                                                            <div><strong>{{ $k }}:</strong> {{ $v }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="padding: 10px 24px; background: #fafafa;">
                                                <button type="button" class="modal-close btn-flat waves-effect">Tutup</button>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="center-align grey-text" style="padding: 30px 0;">
                                    Tidak ada data untuk status yang dipilih.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                <div style="margin-top: 20px;">
                    {{ $items->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elemsModal = document.querySelectorAll('.modal');
        M.Modal.init(elemsModal);
    });
</script>
@endpush
