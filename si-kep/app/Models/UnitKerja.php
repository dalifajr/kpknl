<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'singkatan',
        'kepala_pegawai_id',
        'urutan',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'unit_kerja_id');
    }

    public function kepala(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'kepala_pegawai_id');
    }
}
