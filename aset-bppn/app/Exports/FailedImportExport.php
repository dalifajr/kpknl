<?php

namespace App\Exports;

use App\Models\AssetImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedImportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;
    protected $assetImport;

    public function __construct(AssetImport $assetImport)
    {
        $this->assetImport = $assetImport;
    }

    public function headings(): array
    {
        return [
            'baris_ke',
            'keterangan_error_gagal',
            'kode_aset',
            'bank_asal',
            'jenis_aset',
            'permasalahan',
            'jenis_bukti_kepemilikan',
            'nomor_bukti_kepemilikan',
            'luas_tanah',
            'luas_bangunan',
            'njop',
            'papan_nama',
            'alamat_namajalan',
            'alamat_rt_rw',
            'alamat_provinsi',
            'alamat_kota_kab',
            'alamat_kecamatan',
            'alamat_kelurahan',
            'kodepos',
            'alamatlama_namajalan',
            'alamatlama_rt_rw',
            'alamatlama_provinsi',
            'alamatlama_kota_kab',
            'alamatlama_kecamatan',
            'alamatlama_kelurahan',
            'alamatlama_kodepos',
            'batas_utara',
            'batas_timur',
            'batas_selatan',
            'batas_barat',
            'koordinat_latitude',
            'koordinat_longitude',
            'kondisi_aset',
            'kondisi_aset_lainlain',
            'potensi_aset',
            'wakil_kerja',
            'keterangan_kondisi_aset',
            'tanggal_update_kondisi',
            'semester',
            'tahun',
            'sewa_lelang_nama_pihak',
            'sewa_lelang_no_surat',
            'sewa_lelang_tgl_mulai',
            'sewa_lelang_tgl_selesai',
            'sewa_lelang_nilai',
        ];
    }

    public function array(): array
    {
        $failedItems = $this->assetImport->failedItems()->get();
        $rows = [];

        foreach ($failedItems as $item) {
            $data = $item->raw_data ?? [];
            $rows[] = [
                $item->row_number,
                $item->error_message ?? 'Kesalahan validasi data',
                $data['kode_aset'] ?? ($item->kode_aset ?? ''),
                $data['bank_asal'] ?? '',
                $data['jenis_aset'] ?? '',
                $data['permasalahan'] ?? '',
                $data['jenis_bukti_kepemilikan'] ?? '',
                $data['nomor_bukti_kepemilikan'] ?? '',
                $data['luas_tanah'] ?? '',
                $data['luas_bangunan'] ?? '',
                $data['njop'] ?? '',
                $data['papan_nama'] ?? '',
                $data['alamat_namajalan'] ?? '',
                $data['alamat_rt_rw'] ?? '',
                $data['alamat_provinsi'] ?? '',
                $data['alamat_kota_kab'] ?? '',
                $data['alamat_kecamatan'] ?? '',
                $data['alamat_kelurahan'] ?? '',
                $data['kodepos'] ?? '',
                $data['alamatlama_namajalan'] ?? '',
                $data['alamatlama_rt_rw'] ?? '',
                $data['alamatlama_provinsi'] ?? '',
                $data['alamatlama_kota_kab'] ?? '',
                $data['alamatlama_kecamatan'] ?? '',
                $data['alamatlama_kelurahan'] ?? '',
                $data['alamatlama_kodepos'] ?? '',
                $data['batas_utara'] ?? '',
                $data['batas_timur'] ?? '',
                $data['batas_selatan'] ?? '',
                $data['batas_barat'] ?? '',
                $data['koordinat_latitude'] ?? '',
                $data['koordinat_longitude'] ?? '',
                $data['kondisi_aset'] ?? '',
                $data['kondisi_aset_lainlain'] ?? '',
                $data['potensi_aset'] ?? '',
                $data['wakil_kerja'] ?? '',
                $data['keterangan_kondisi_aset'] ?? '',
                $data['tanggal_update_kondisi'] ?? '',
                $data['semester'] ?? '',
                $data['tahun'] ?? '',
                $data['sewa_lelang_nama_pihak'] ?? '',
                $data['sewa_lelang_no_surat'] ?? '',
                $data['sewa_lelang_tgl_mulai'] ?? '',
                $data['sewa_lelang_tgl_selesai'] ?? '',
                $data['sewa_lelang_nilai'] ?? '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFC62828'], // Red for failed data
                ],
            ],
        ];
    }
}
