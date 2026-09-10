<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Asset extends Model
{
    use SoftDeletes, LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sewa_lelang_tgl_mulai' => 'date',
            'sewa_lelang_tgl_selesai' => 'date',
            'sewa_lelang_nilai' => 'decimal:2',
            'tanggal_update_kondisi' => 'date',
        ];
    }

    // Relasi Alamat Baru
    public function province()
    {
        return $this->belongsTo(Province::class, 'alamat_provinsi_id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'alamat_kota_kab_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'alamat_kecamatan_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'alamat_kelurahan_id');
    }

    // Relasi Alamat Lama
    public function oldProvince()
    {
        return $this->belongsTo(Province::class, 'alamatlama_provinsi_id');
    }

    public function oldRegency()
    {
        return $this->belongsTo(Regency::class, 'alamatlama_kota_kab_id');
    }

    public function oldDistrict()
    {
        return $this->belongsTo(District::class, 'alamatlama_kecamatan_id');
    }

    public function oldVillage()
    {
        return $this->belongsTo(Village::class, 'alamatlama_kelurahan_id');
    }

    public function getDokumenPathsAttribute()
    {
        $val = $this->dokumen_path;
        if (empty($val)) return [];
        if (is_array($val)) return $val;
        $decoded = json_decode($val, true);
        if (is_array($decoded)) return $decoded;
        return [$val];
    }

    public function getFotoPathsAttribute()
    {
        $val = $this->foto_path;
        if (empty($val)) return [];
        if (is_array($val)) return $val;
        $decoded = json_decode($val, true);
        if (is_array($decoded)) return $decoded;
        return [$val];
    }

    // Relasi Riwayat Sewa
    public function leases()
    {
        return $this->hasMany(AssetLease::class, 'asset_id')->orderBy('tgl_mulai', 'asc');
    }

    public function getIsSewaExpiringAttribute(): bool
    {
        if (strtoupper($this->kondisi_aset ?? '') !== 'DISEWAKAN' || empty($this->sewa_lelang_tgl_selesai)) {
            return false;
        }
        $expiry = \Carbon\Carbon::parse($this->sewa_lelang_tgl_selesai)->startOfDay();
        $today = now()->startOfDay();
        $sixMonthsAhead = now()->addMonths(6)->startOfDay();
        return $expiry <= $sixMonthsAhead;
    }

    public function getSewaStatusInfoAttribute(): array
    {
        if (strtoupper($this->kondisi_aset ?? '') !== 'DISEWAKAN' || empty($this->sewa_lelang_tgl_selesai)) {
            return ['status' => 'none', 'badge' => '', 'color' => 'grey darken-1', 'message' => '', 'days_left' => null];
        }

        $today = now()->startOfDay();
        $expiry = \Carbon\Carbon::parse($this->sewa_lelang_tgl_selesai)->startOfDay();
        $diffDays = (int) $today->diffInDays($expiry, false);

        if ($diffDays < 0) {
            $pastDays = abs($diffDays);
            return [
                'status' => 'expired',
                'badge' => 'Kadaluarsa ' . ($pastDays > 30 ? floor($pastDays / 30) . ' bln' : $pastDays . ' hr'),
                'color' => 'red darken-3',
                'message' => 'Masa sewa telah berakhir ' . $pastDays . ' hari yang lalu (' . $expiry->translatedFormat('d F Y') . ')',
                'days_left' => $diffDays
            ];
        } elseif ($diffDays <= 183) { // <= 6 bulan
            $monthsLeft = ceil($diffDays / 30);
            return [
                'status' => 'warning',
                'badge' => 'Sisa ' . ($monthsLeft > 0 ? $monthsLeft . ' bln' : $diffDays . ' hr'),
                'color' => 'orange darken-3',
                'message' => 'Masa sewa akan berakhir dalam ' . $diffDays . ' hari lagi (' . $expiry->translatedFormat('d F Y') . ')',
                'days_left' => $diffDays
            ];
        }

        return [
            'status' => 'active',
            'badge' => 'Aktif s/d ' . $expiry->format('d/m/Y'),
            'color' => 'teal darken-2',
            'message' => 'Masa sewa aktif hingga ' . $expiry->translatedFormat('d F Y'),
            'days_left' => $diffDays
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
