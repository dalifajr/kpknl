<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'jenis_jabatan',
        'level_eselon',
        'standar_grade',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id');
    }
}
