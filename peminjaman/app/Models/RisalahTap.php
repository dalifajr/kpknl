<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisalahTap extends Model
{
    use HasFactory;

    protected $table = 'risalah_tap';

    public $timestamps = false;

    protected $fillable = [
        'no_risalah',
        'tgl_risalah',
        'tgl_validasi',
        'nama_pelelang',
        'pemohon_lelang',
        'link_erisalah',
        'box',
        'lemari',
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
