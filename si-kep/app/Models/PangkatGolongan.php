<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PangkatGolongan extends Model
{
    use HasFactory;

    protected $table = 'pangkat_golongan';

    protected $fillable = [
        'nama_pangkat',
        'golongan_ruang',
        'hirarki_level',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'pangkat_golongan_id');
    }
}
