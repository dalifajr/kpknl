<?php

namespace App\Services;

use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Models\RisalahTap;
use Illuminate\Support\Facades\DB;

class RisalahValidationService
{
    /**
     * Approve pending risalah and move to minuta, tap, or batal.
     */
    public function approve(RisalahPending $pending, array $data): mixed
    {
        return DB::transaction(function () use ($pending, $data) {
            // Lock row
            $lockedPending = RisalahPending::where('id', $pending->id)->lockForUpdate()->firstOrFail();

            $commonAttributes = [
                'no_risalah' => $lockedPending->no_risalah,
                'tgl_risalah' => $lockedPending->tgl_risalah,
                'tgl_validasi' => $data['tgl_validasi'] ?? now()->toDateString(),
                'nama_pelelang' => $lockedPending->nama_pelelang,
                'pemohon_lelang' => $lockedPending->pemohon_lelang,
                'link_erisalah' => $data['link_erisalah'] ?? $lockedPending->link_erisalah,
                'box' => $data['box'] ?? $lockedPending->box,
                'lemari' => $data['lemari'] ?? $lockedPending->lemari,
                'status' => 'tersedia',
            ];

            $created = match ($lockedPending->jenis) {
                'minuta' => RisalahMinuta::create($commonAttributes),
                'tap' => RisalahTap::create($commonAttributes),
                'batal' => RisalahBatal::create($commonAttributes),
                default => throw new \InvalidArgumentException("Jenis risalah tidak dikenali: {$lockedPending->jenis}"),
            };

            $lockedPending->delete();

            return $created;
        });
    }

    /**
     * Move pending risalah to revision table.
     */
    public function requestRevision(RisalahPending $pending, array $notes): RisalahRevisi
    {
        return DB::transaction(function () use ($pending, $notes) {
            $lockedPending = RisalahPending::where('id', $pending->id)->lockForUpdate()->firstOrFail();

            $revisi = RisalahRevisi::create([
                'no_risalah' => $lockedPending->no_risalah,
                'jenis' => $lockedPending->jenis,
                'tgl_risalah' => $lockedPending->tgl_risalah,
                'tgl_revisi' => now()->toDateString(),
                'nama_pelelang' => $lockedPending->nama_pelelang,
                'pemohon_lelang' => $lockedPending->pemohon_lelang,
                'catatan' => $notes['catatan'] ?? null,
                'status' => 'revisi',
                'catatan_no' => $notes['catatan_no'] ?? null,
                'catatan_jenis' => $notes['catatan_jenis'] ?? null,
                'catatan_tgl' => $notes['catatan_tgl'] ?? null,
                'catatan_pelelang' => $notes['catatan_pelelang'] ?? null,
                'catatan_pemohon' => $notes['catatan_pemohon'] ?? null,
            ]);

            $lockedPending->delete();

            return $revisi;
        });
    }

    /**
     * Resubmit revised risalah back to pending.
     */
    public function resubmitRevision(RisalahRevisi $revisi, array $data): RisalahPending
    {
        return DB::transaction(function () use ($revisi, $data) {
            $lockedRevisi = RisalahRevisi::where('id', $revisi->id)->lockForUpdate()->firstOrFail();

            $pending = RisalahPending::create([
                'no_risalah' => $data['no_risalah'] ?? $lockedRevisi->no_risalah,
                'jenis' => $data['jenis'] ?? $lockedRevisi->jenis,
                'tgl_risalah' => $data['tgl_risalah'] ?? $lockedRevisi->tgl_risalah,
                'tgl_validasi' => null,
                'nama_pelelang' => $lockedRevisi->nama_pelelang,
                'pemohon_lelang' => $data['pemohon_lelang'] ?? $lockedRevisi->pemohon_lelang,
                'link_erisalah' => $data['link_erisalah'] ?? null,
                'box' => $data['box'] ?? null,
                'lemari' => $data['lemari'] ?? null,
                'keterangan' => $data['keterangan'] ?? 'Perbaikan dari revisi',
                'catatan' => null,
                'status' => 'belum_validasi',
            ]);

            $lockedRevisi->delete();

            return $pending;
        });
    }
}
