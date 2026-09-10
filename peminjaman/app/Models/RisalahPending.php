<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisalahPending extends Model
{
    use HasFactory;

    protected $table = 'risalah_pending';

    public $timestamps = false;

    protected $fillable = [
        'no_risalah',
        'jenis',
        'tgl_risalah',
        'tgl_validasi',
        'nama_pelelang',
        'pemohon_lelang',
        'link_erisalah',
        'box',
        'lemari',
        'keterangan',
        'catatan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_risalah' => 'date',
            'tgl_validasi' => 'date',
        ];
    }
}
