<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PeminjamanService
{
    /**
     * Borrow multiple risalah with pessimistic row locks and database transaction.
     *
     * @param User $user
     * @param array $selectedItems Array of strings formatted like "minuta:123", "tap:45", "batal:67"
     * @param string $alasan
     * @return array
     */
    public function borrow(User $user, array $selectedItems, string $alasan): array
    {
        return DB::transaction(function () use ($user, $selectedItems, $alasan) {
            $createdLoans = [];

            foreach ($selectedItems as $itemStr) {
                $parts = explode(':', $itemStr);
                if (count($parts) !== 2) {
                    throw new InvalidArgumentException("Format pilihan risalah tidak valid: {$itemStr}");
                }

                [$type, $id] = $parts;

                $model = match (strtolower($type)) {
                    'minuta' => RisalahMinuta::where('id', $id)->lockForUpdate()->first(),
                    'tap' => RisalahTap::where('id', $id)->lockForUpdate()->first(),
                    'batal' => RisalahBatal::where('id', $id)->lockForUpdate()->first(),
                    default => throw new InvalidArgumentException("Tipe risalah tidak valid: {$type}"),
                };

                if (!$model) {
                    throw new InvalidArgumentException("Data risalah {$type} dengan ID {$id} tidak ditemukan.");
                }

                if (strtolower($model->status) !== 'tersedia') {
                    throw new \Exception("Risalah No. {$model->no_risalah} saat ini sedang tidak tersedia (status: {$model->status}).");
                }

                // Update status of the source risalah
                $model->update(['status' => 'sedang_dipinjam']);

                // Create loan entry
                $loan = Peminjaman::create([
                    'nama_peminjam' => $user->username,
                    'no_risalah' => $model->no_risalah,
                    'tgl_risalah' => $model->tgl_risalah,
                    'nama_pelelang' => $model->nama_pelelang,
                    'pemohon_lelang' => $model->pemohon_lelang,
                    'box' => $model->box,
                    'lemari' => $model->lemari,
                    'tgl_peminjaman' => now()->toDateString(),
                    'tgl_pengembalian' => null,
                    'status' => Peminjaman::STATUS_PROSES,
                    'alasan_peminjaman' => $alasan,
                ]);

                $createdLoans[] = $loan;
            }

            return $createdLoans;
        });
    }

    /**
     * Admin approves the loan request -> moves to "Sedang Dipinjam".
     */
    public function adminApproveLoan(Peminjaman $peminjaman): bool
    {
        return $peminjaman->update([
            'status' => Peminjaman::STATUS_SEDANG_DIPINJAM,
        ]);
    }

    /**
     * Borrower confirms receiving physical document -> moves to "Sedang Dipinjam".
     */
    public function borrowerConfirmLoan(Peminjaman $peminjaman): bool
    {
        return $peminjaman->update([
            'status' => Peminjaman::STATUS_SEDANG_DIPINJAM,
        ]);
    }

    /**
     * Borrower requests return -> moves to "Proses Pengembalian".
     */
    public function requestReturn(Peminjaman $peminjaman): bool
    {
        return $peminjaman->update([
            'status' => Peminjaman::STATUS_PROSES_PENGEMBALIAN,
            'tgl_pengembalian' => now()->toDateString(),
        ]);
    }

    /**
     * Admin validates returned document -> moves to "Sudah Dikembalikan" and resets source risalah to "tersedia".
     */
    public function adminApproveReturn(Peminjaman $peminjaman): bool
    {
        return DB::transaction(function () use ($peminjaman) {
            $lockedLoan = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

            $lockedLoan->update([
                'status' => Peminjaman::STATUS_SUDAH_DIKEMBALIKAN,
                'tgl_pengembalian' => $lockedLoan->tgl_pengembalian ?? now()->toDateString(),
            ]);

            // Restore source risalah status to 'tersedia' across Minuta, TAP, Batal
            if ($lockedLoan->no_risalah) {
                RisalahMinuta::where('no_risalah', $lockedLoan->no_risalah)
                    ->where('status', 'sedang_dipinjam')
                    ->update(['status' => 'tersedia']);

                RisalahTap::where('no_risalah', $lockedLoan->no_risalah)
                    ->where('status', 'sedang_dipinjam')
                    ->update(['status' => 'tersedia']);

                RisalahBatal::where('no_risalah', $lockedLoan->no_risalah)
                    ->where('status', 'sedang_dipinjam')
                    ->update(['status' => 'tersedia']);
            }

            return true;
        });
    }
}
