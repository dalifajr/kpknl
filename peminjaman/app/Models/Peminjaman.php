<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    public $timestamps = false;

    // Status Constants for Peminjaman
    const STATUS_PROSES = 'Proses Peminjaman';
    const STATUS_MENUNGGU_KONFIRMASI = 'Menunggu Konfirmasi Peminjam';
    const STATUS_SEDANG_DIPINJAM = 'Sedang Dipinjam';
    const STATUS_PROSES_PENGEMBALIAN = 'Proses Pengembalian';
    const STATUS_SUDAH_DIKEMBALIKAN = 'Sudah Dikembalikan';

    protected $fillable = [
        'nama_peminjam',
        'no_risalah',
        'tgl_risalah',
        'nama_pelelang',
        'pemohon_lelang',
        'box',
        'lemari',
        'tgl_peminjaman',
        'tgl_pengembalian',
        'status',
        'alasan_peminjaman',
    ];

    protected function casts(): array
    {
        return [
            'tgl_risalah' => 'date',
            'tgl_peminjaman' => 'date',
            'tgl_pengembalian' => 'date',
        ];
    }

    /**
     * Get CSS badge class for loan status
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PROSES => 'bg-info-subtle text-info border border-info-subtle',
            self::STATUS_MENUNGGU_KONFIRMASI => 'bg-warning-subtle text-warning border border-warning-subtle',
            self::STATUS_SEDANG_DIPINJAM => 'bg-primary-subtle text-primary border border-primary-subtle',
            self::STATUS_PROSES_PENGEMBALIAN => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::STATUS_SUDAH_DIKEMBALIKAN => 'bg-success-subtle text-success border border-success-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}
