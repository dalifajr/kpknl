<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;
    public function headings(): array
    {
        return [
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
        return [
            [
                'PRK-SAMPLE-01',
                'BANK SUMSEL BABEL',
                'Tanah dan Bangunan',
                'Tidak ada sengketa hukum',
                'SHM',
                'SHM No. 441/Ilir Timur',
                '350',
                '150',
                '250000000',
                'Ya',
                'Jl. Jendral Sudirman No. 12',
                '001/002',
                'SUMATERA SELATAN',
                'KOTA PALEMBANG',
                'ILIR TIMUR I',
                '16 ILIR',
                '30111',
                'Jl. Sudirman Lama No. 12',
                '001/002',
                'SUMATERA SELATAN',
                'KOTA PALEMBANG',
                'ILIR TIMUR I',
                '16 ILIR',
                '30111',
                'Tanah Milik Warga',
                'Jalan Raya Sudirman',
                'Rumah Bapak Anton',
                'Lorong Bahagia',
                '-2.983944',
                '104.756211',
                'DIHUNI',
                '',
                'Rumah Tinggal / Komersil',
                'Budi Santoso',
                'Bangunan fisik baik terawat',
                '2026-08-01',
                '2',
                '2026',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'PRK-SAMPLE-02',
                'BBD',
                'Tanah',
                'Dalam proses optimalisasi aset',
                'SHGB',
                'SHGB No. 102/Jakabaring',
                '800',
                '0',
                '600000000',
                'Ya',
                'Jl. Gubernur H. Bastari',
                '004/001',
                'SUMATERA SELATAN',
                'KOTA PALEMBANG',
                'JAKABARING',
                '15 ULU',
                '30257',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Tanah Kosong Pemda',
                'Jalan Protokol Jakabaring',
                'Ruko Bastari',
                'Saluran Drainase',
                '-3.022060',
                '104.770883',
                'DISEWAKAN',
                '',
                'Komersil / Gudang Terbuka',
                'M. Ridwan',
                'Lahan kering siap guna',
                '2026-08-15',
                '2',
                '2026',
                'PT Sentosa Logistics Palembang',
                'SPK-SEWA/012/2026',
                '2026-08-01',
                '2027-07-31',
                '80000000',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1565C0'],
                ],
            ],
        ];
    }
}
