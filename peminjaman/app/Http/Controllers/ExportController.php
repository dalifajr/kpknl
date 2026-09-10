<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use App\Services\RisalahExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    protected RisalahExportService $exportService;

    public function __construct(RisalahExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function exportMinuta(Request $request): StreamedResponse
    {
        $query = RisalahMinuta::query();
        $this->applyFilters($query, $request);

        $filename = 'risalah_minuta_' . date('Ymd_His') . '.csv';
        $headers = ['No', 'Nomor Risalah', 'Tanggal Risalah', 'Tanggal Validasi', 'Nama Pelelang', 'Pemohon Lelang', 'Box', 'Lemari', 'Status', 'Link E-Risalah'];

        $i = 0;
        return $this->exportService->exportCsv($filename, $headers, $query, function ($row) use (&$i) {
            $i++;
            return [
                $i,
                $row->no_risalah,
                $row->tgl_risalah?->format('Y-m-d') ?? $row->tgl_risalah,
                $row->tgl_validasi?->format('Y-m-d') ?? $row->tgl_validasi,
                $row->nama_pelelang,
                $row->pemohon_lelang,
                $row->box,
                $row->lemari,
                $row->status,
                $row->link_erisalah,
            ];
        });
    }

    public function exportTap(Request $request): StreamedResponse
    {
        $query = RisalahTap::query();
        $this->applyFilters($query, $request);

        $filename = 'risalah_tap_' . date('Ymd_His') . '.csv';
        $headers = ['No', 'Nomor Risalah', 'Tanggal Risalah', 'Tanggal Validasi', 'Nama Pelelang', 'Pemohon Lelang', 'Box', 'Lemari', 'Status', 'Link E-Risalah'];

        $i = 0;
        return $this->exportService->exportCsv($filename, $headers, $query, function ($row) use (&$i) {
            $i++;
            return [
                $i,
                $row->no_risalah,
                $row->tgl_risalah?->format('Y-m-d') ?? $row->tgl_risalah,
                $row->tgl_validasi?->format('Y-m-d') ?? $row->tgl_validasi,
                $row->nama_pelelang,
                $row->pemohon_lelang,
                $row->box,
                $row->lemari,
                $row->status,
                $row->link_erisalah,
            ];
        });
    }

    public function exportBatal(Request $request): StreamedResponse
    {
        $query = RisalahBatal::query();
        $this->applyFilters($query, $request);

        $filename = 'risalah_batal_' . date('Ymd_His') . '.csv';
        $headers = ['No', 'Nomor Risalah', 'Tanggal Risalah', 'Tanggal Validasi', 'Nama Pelelang', 'Pemohon Lelang', 'Box', 'Lemari', 'Status', 'Link E-Risalah'];

        $i = 0;
        return $this->exportService->exportCsv($filename, $headers, $query, function ($row) use (&$i) {
            $i++;
            return [
                $i,
                $row->no_risalah,
                $row->tgl_risalah?->format('Y-m-d') ?? $row->tgl_risalah,
                $row->tgl_validasi?->format('Y-m-d') ?? $row->tgl_validasi,
                $row->nama_pelelang,
                $row->pemohon_lelang,
                $row->box,
                $row->lemari,
                $row->status,
                $row->link_erisalah,
            ];
        });
    }

    public function exportPeminjaman(Request $request): StreamedResponse
    {
        $query = Peminjaman::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_peminjam', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'data_peminjaman_' . date('Ymd_His') . '.csv';
        $headers = ['No', 'Nama Peminjam', 'Nomor Risalah', 'Tanggal Risalah', 'Nama Pelelang', 'Pemohon Lelang', 'Box', 'Lemari', 'Tanggal Peminjaman', 'Tanggal Pengembalian', 'Status', 'Alasan Peminjaman'];

        $i = 0;
        return $this->exportService->exportCsv($filename, $headers, $query, function ($row) use (&$i) {
            $i++;
            return [
                $i,
                $row->nama_peminjam,
                $row->no_risalah,
                $row->tgl_risalah?->format('Y-m-d') ?? $row->tgl_risalah,
                $row->nama_pelelang,
                $row->pemohon_lelang,
                $row->box,
                $row->lemari,
                $row->tgl_peminjaman?->format('Y-m-d') ?? $row->tgl_peminjaman,
                $row->tgl_pengembalian?->format('Y-m-d') ?? $row->tgl_pengembalian,
                $row->status,
                $row->alasan_peminjaman,
            ];
        });
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_risalah', 'like', "%{$s}%")
                    ->orWhere('nama_pelelang', 'like', "%{$s}%")
                    ->orWhere('pemohon_lelang', 'like', "%{$s}%");
            });
        }

        if ($request->filled('box')) {
            $query->where('box', $request->box);
        }

        if ($request->filled('lemari')) {
            $query->where('lemari', $request->lemari);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tgl_risalah', $request->tahun);
        }

        if ($request->filled('bulan_awal') && $request->filled('bulan_akhir')) {
            $query->whereMonth('tgl_risalah', '>=', $request->bulan_awal)
                ->whereMonth('tgl_risalah', '<=', $request->bulan_akhir);
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tgl_risalah', $request->bulan);
        }
    }
}
