<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisalahRevisi extends Model
{
    use HasFactory;

    protected $table = 'risalah_revisi';

    public $timestamps = false;

    protected $fillable = [
        'no_risalah',
        'jenis',
        'tgl_risalah',
        'tgl_revisi',
        'nama_pelelang',
        'pemohon_lelang',
        'catatan',
        'status',
        'catatan_no',
        'catatan_jenis',
        'catatan_tgl',
        'catatan_pelelang',
        'catatan_pemohon',
    ];

    protected function casts(): array
    {
        return [
            'tgl_risalah' => 'date',
            'tgl_revisi' => 'date',
        ];
    }
}
