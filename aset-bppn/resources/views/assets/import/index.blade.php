@extends('layouts.app')

@section('title', 'Impor Masal Data Aset')

@section('content')
<div class="row" style="margin-bottom: 20px;">
    <div class="col s12">
        <div class="card-panel blue lighten-5" style="border-left: 5px solid #1565c0; border-radius: 6px; padding: 20px 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h4 class="blue-text text-darken-3" style="margin: 0; font-weight: 700; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
                        <i class="material-icons" style="font-size: 32px;">cloud_upload</i> Impor Masal Data Aset
                    </h4>
                    <p style="font-size: 1rem; margin: 4px 0 0 0;" class="grey-text text-darken-2">
                        Unggah berkas Excel untuk menambahkan banyak data aset secara otomatis dengan validasi data dan pratinjau verifikasi.
                    </p>
                </div>
                <div>
                    <a href="{{ route('assets.import.template') }}" class="btn green darken-2 waves-effect waves-light" style="border-radius: 6px; font-weight: 600;">
                        <i class="material-icons left">file_download</i> Unduh Template Excel (.xlsx)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Statistics --}}
<div class="row">
    <div class="col s12 m4">
        <div class="card-panel blue darken-1 white-text center-align hoverable" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">history</i>
            <h6 style="margin: 4px 0;">Total Sesi Impor</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $totalImports }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Sesi tercatat</p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel green darken-2 white-text center-align hoverable" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">check_circle</i>
            <h6 style="margin: 4px 0;">Total Aset Berhasil</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $totalImportedAssets }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Tersimpan ke database</p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel red darken-2 white-text center-align hoverable" style="border-radius: 8px; padding: 18px;">
            <i class="material-icons" style="font-size: 36px;">error_outline</i>
            <h6 style="margin: 4px 0;">Total Baris Gagal</h6>
            <h3 style="margin: 4px 0; font-weight: 700;">{{ $totalFailedAssets }}</h3>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Dapat diunduh & diperbaiki</p>
        </div>
    </div>
</div>

{{-- Form Upload Excel --}}
<div class="row">
    <div class="col s12">
        <div class="card z-depth-1" style="border-radius: 8px;">
            <div class="card-content" style="padding: 24px;">
                <span class="card-title blue-text text-darken-3" style="font-weight: 700; font-size: 1.25rem; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <i class="material-icons">upload_file</i> Formulir Unggah Berkas Impor
                </span>

                <form action="{{ route('assets.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s12 m8">
                            <div class="file-field input-field" style="margin-top: 5px;">
                                <div class="btn blue darken-2" style="border-radius: 4px;">
                                    <span><i class="material-icons left">folder_open</i> Pilih Berkas</span>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Pilih berkas Excel (.xlsx / .xls) sesuai template resmi...">
                                </div>
                            </div>
                            <span class="helper-text grey-text text-darken-1" style="font-size: 0.85rem; display: block; margin-top: -10px; margin-bottom: 15px;">
                                * Format berkas yang didukung: <strong>.xlsx, .xls, .csv</strong> (Maksimal 10 MB). Gunakan template resmi agar pemetaan kolom terdeteksi otomatis.
                            </span>
                        </div>

                        <div class="col s12 m4" style="margin-top: 5px;">
                            <button type="submit" class="btn-large blue darken-3 waves-effect waves-light full-width" style="width: 100%; border-radius: 6px; font-weight: 600;">
                                <i class="material-icons right">arrow_forward</i> Lanjut ke Pratinjau
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Petunjuk Singkat Pengisian --}}
                <div class="card-panel grey lighten-4" style="border-radius: 6px; padding: 15px 18px; margin-top: 15px; margin-bottom: 0;">
                    <strong class="blue-text text-darken-3" style="font-size: 0.95rem; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                        <i class="material-icons tiny">info</i> Petunjuk Pengisian Template Excel:
                    </strong>
                    <ol class="grey-text text-darken-3" style="margin: 0; padding-left: 20px; font-size: 0.88rem; line-height: 1.6;">
                        <li>Unduh template Excel dengan menekan tombol hijau <strong>"Unduh Template Excel"</strong> di atas.</li>
                        <li>Kolom <code>kode_aset</code> bersifat <strong>wajib dan unik</strong>. Baris tanpa kode aset atau yang sudah ada di database akan ditolak.</li>
                        <li>Format tanggal dapat menggunakan format standar <code>YYYY-MM-DD</code> (contoh: <code>2026-08-01</code>) atau <code>DD/MM/YYYY</code>.</li>
                        <li>Kolom wilayah (<code>alamat_provinsi</code>, <code>alamat_kota_kab</code>, <code>alamat_kecamatan</code>, <code>alamat_kelurahan</code>) diisi dengan nama wilayah resmi (contoh: <i>SUMATERA SELATAN</i>, <i>KOTA PALEMBANG</i>).</li>
                        <li>Sebelum data disimpan permanen, sistem akan menampilkan <strong>halaman pratinjau verifikasi</strong> agar Anda dapat meninjau data dan memilih kolom/baris yang ingin diimpor.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Histori Impor Masal --}}
<div class="row">
    <div class="col s12">
        <div class="card z-depth-1" style="border-radius: 8px;">
            <div class="card-content" style="padding: 24px;">
                <span class="card-title blue-text text-darken-3" style="font-weight: 700; font-size: 1.25rem; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <i class="material-icons">history</i> Histori Sesi Impor Data Aset
                </span>

                <div style="overflow-x: auto;">
                    <table class="striped highlight" style="font-size: 0.92rem;">
                        <thead class="grey lighten-4">
                            <tr>
                                <th width="60">ID Batch</th>
                                <th>Tanggal & Waktu</th>
                                <th>Nama Berkas</th>
                                <th>Pengunggah</th>
                                <th class="center-align">Total Data</th>
                                <th class="center-align">Berhasil</th>
                                <th class="center-align">Gagal</th>
                                <th>Status</th>
                                <th class="center-align" width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($imports as $item)
                            <tr>
                                <td><strong>#{{ $item->id }}</strong></td>
                                <td>{{ $item->created_at->translatedFormat('d M Y, H:i') }} WIB</td>
                                <td><span class="blue-text text-darken-2 font-weight-500">{{ $item->file_name }}</span></td>
                                <td>{{ $item->user->name ?? 'Sistem' }}</td>
                                <td class="center-align font-weight-600">{{ $item->total_rows }}</td>
                                <td class="center-align">
                                    <span class="chip green lighten-5 green-text text-darken-3" style="font-weight: 600; margin: 0;">
                                        {{ $item->success_rows }}
                                    </span>
                                </td>
                                <td class="center-align">
                                    @if($item->failed_rows > 0)
                                        <span class="chip red lighten-5 red-text text-darken-3" style="font-weight: 600; margin: 0;">
                                            {{ $item->failed_rows }}
                                        </span>
                                    @else
                                        <span class="grey-text">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status == 'completed')
                                        <span class="new badge green darken-1" data-badge-caption="Sukses Penuh" style="float: left; margin: 0;"></span>
                                    @elseif($item->status == 'partial')
                                        <span class="new badge orange darken-3" data-badge-caption="Sebagian Gagal" style="float: left; margin: 0;"></span>
                                    @elseif($item->status == 'failed')
                                        <span class="new badge red darken-2" data-badge-caption="Gagal Total" style="float: left; margin: 0;"></span>
                                    @else
                                        <span class="new badge grey" data-badge-caption="{{ $item->status }}" style="float: left; margin: 0;"></span>
                                    @endif
                                </td>
                                <td class="center-align" style="white-space: nowrap;">
                                    <a href="{{ route('assets.import.show', $item->id) }}" class="btn-small blue darken-2 waves-effect waves-light" style="border-radius: 4px;" title="Lihat Detail Sesi">
                                        <i class="material-icons left">visibility</i> Detail
                                    </a>
                                    @if($item->failed_rows > 0)
                                        <a href="{{ route('assets.import.downloadFailed', $item->id) }}" class="btn-small red darken-2 waves-effect waves-light" style="border-radius: 4px; margin-left: 4px;" title="Unduh Data Gagal (Excel)">
                                            <i class="material-icons left">file_download</i> Gagal
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="center-align grey-text" style="padding: 40px 0;">
                                    <i class="material-icons" style="font-size: 48px; opacity: 0.5;">cloud_off</i>
                                    <p style="margin-top: 8px;">Belum ada riwayat sesi impor data aset yang tercatat.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($imports->hasPages())
                <div style="margin-top: 20px;">
                    {{ $imports->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
