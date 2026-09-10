<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BmnPotensiIdle extends Model
{
    use HasFactory;

    protected $table = 'bmn_potensi_idle';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_klarifikasi' => 'date',
        'tanggal_jawaban' => 'date',
        'is_kemenkeu' => 'boolean',
        'luas' => 'decimal:2',
        'nilai_perolehan' => 'decimal:2',
        'bobot_nilai' => 'decimal:2',
    ];

    /**
     * Scope filter Kemenkeu
     */
    public function scopeKemenkeu($query)
    {
        return $query->where('is_kemenkeu', true);
    }

    /**
     * Scope filter Palembang
     */
    public function scopePalembang($query)
    {
        return $query->where('kpknl', 'LIKE', '%Palembang%');
    }
}
