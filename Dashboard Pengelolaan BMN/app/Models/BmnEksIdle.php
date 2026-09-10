<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BmnEksIdle extends Model
{
    use HasFactory;

    protected $table = 'bmn_eks_idle';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_surat' => 'date',
        'luas' => 'decimal:2',
        'nilai_perolehan' => 'decimal:2',
        'bobot_nilai' => 'decimal:2',
        'nilai' => 'decimal:2',
    ];

    /**
     * Scope filter Palembang
     */
    public function scopePalembang($query)
    {
        return $query->where('nama_kpknl', 'LIKE', '%PALEMBANG%');
    }
}
