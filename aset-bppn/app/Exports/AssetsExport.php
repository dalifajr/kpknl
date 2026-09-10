<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function query()
    {
        $query = Asset::query()->with(['province', 'regency', 'district', 'village']);

        if ($this->request->filled('ids')) {
            $ids = is_array($this->request->ids) ? $this->request->ids : explode(',', $this->request->ids);
            return $query->whereIn('id', $ids);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_aset', 'like', "%{$search}%")
                  ->orWhere('alamat_namajalan', 'like', "%{$search}%")
                  ->orWhere('alamatlama_namajalan', 'like', "%{$search}%")
                  ->orWhere('wakil_kerja', 'like', "%{$search}%")
                  ->orWhere('jenis_aset', 'like', "%{$search}%")
                  ->orWhere('bank_asal', 'like', "%{$search}%")
                  ->orWhere('kondisi_aset', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('jenis_aset')) {
            $query->where('jenis_aset', $this->request->jenis_aset);
        }

        if ($this->request->filled('kondisi_aset')) {
            $query->where('kondisi_aset', $this->request->kondisi_aset);
        }

        if (!$this->request->filled('print_all_data')) {
            if ($this->request->filled('tahun')) {
                $query->where('tahun', $this->request->tahun);
            }

            if ($this->request->filled('bulan')) {
                $query->whereMonth('tanggal_update_kondisi', $this->request->bulan);
            }

            if ($this->request->filled('semester')) {
                $query->where('semester', $this->request->semester);
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Aset',
            'Jenis Aset',
            'Bank Asal',
            'Permasalahan',
            'Alamat',
            'Kelurahan/Desa',
            'Kecamatan',
            'Kabupaten/Kota',
            'Provinsi',
            'Luas Tanah',
            'Luas Bangunan',
            'NJOP',
            'Kondisi Aset',
            'Ada Papan Nama',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->id,
            $asset->kode_aset,
            $asset->jenis_aset,
            $asset->bank_asal ?? '-',
            $asset->permasalahan ?? '-',
            $asset->alamat_namajalan,
            $asset->village->name ?? '-',
            $asset->district->name ?? '-',
            $asset->regency->name ?? '-',
            $asset->province->name ?? '-',
            $asset->luas_tanah,
            $asset->luas_bangunan,
            $asset->njop,
            $asset->kondisi_aset,
            $asset->papan_nama ? 'Ya' : 'Tidak',
        ];
    }
}
