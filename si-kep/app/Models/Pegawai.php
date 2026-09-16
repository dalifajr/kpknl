<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'no_urut',
        'nip',
        'nik',
        'nama',
        'nama_lengkap_gelar',
        'tipe_pegawai',
        'unit_kerja_id',
        'jabatan_id',
        'pangkat_golongan_id',
        'nama_jabatan_raw',
        'per_jabatan',
        'job_grade',
        'tmt_nip',
        'tmt_eselon',
        'tmt_palembang',
        'masa_kerja_raw',
        'masa_kerja_tahun',
        'masa_kerja_bulan',
        'lama_palembang_raw',
        'lama_palembang_tahun',
        'lama_palembang_bulan',
        'pangkat_golongan_raw',
        'tmt_golongan',
        'tmt_kgb',
        'tmt_grading',
        'tempat_lahir',
        'tanggal_lahir',
        'usia_raw',
        'usia_tahun',
        'usia_bulan',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'fakultas',
        'jurusan',
        'tahun_lulus',
        'nama_universitas',
        'tmt_ue_iv',
        'lama_bertugas_ue_iv',
        'status_gelar',
        'validasi_jabatan',
        'validasi_pangkat',
        'validasi_pendidikan',
        'avatar_url',
        'is_active',
    ];

    protected $casts = [
        'tmt_nip' => 'date',
        'tmt_eselon' => 'date',
        'tmt_palembang' => 'date',
        'tmt_golongan' => 'date',
        'tmt_kgb' => 'date',
        'tmt_grading' => 'date',
        'tanggal_lahir' => 'date',
        'validasi_jabatan' => 'boolean',
        'validasi_pangkat' => 'boolean',
        'validasi_pendidikan' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function pangkatGolongan(): BelongsTo
    {
        return $this->belongsTo(PangkatGolongan::class, 'pangkat_golongan_id');
    }

    /**
     * Display Name with Academic Degrees
     */
    public function getDisplayNameAttribute(): string
    {
        return !empty($this->nama_lengkap_gelar) ? trim($this->nama_lengkap_gelar) : $this->nama;
    }

    /**
     * Masked NIK for privacy protection
     */
    public function getMaskedNikAttribute(): string
    {
        if (empty($this->nik) || strlen($this->nik) < 8) {
            return $this->nik ?: '-';
        }
        $start = substr($this->nik, 0, 6);
        $end = substr($this->nik, -4);
        return $start . '******' . $end;
    }

    /**
     * Batas Usia Pensiun (BUP): 60 tahun untuk Struktural Eselon II/III & Madya, 58 tahun untuk Pelaksana/Eselon IV
     */
    public function getBupUsiaAttribute(): int
    {
        if ($this->tipe_pegawai === 'ppnpn') {
            return 58;
        }

        $jabatan = strtolower($this->nama_jabatan_raw ?? '');
        if (str_contains($jabatan, 'kepala kpknl') || str_contains($jabatan, 'madya')) {
            return 60;
        }
        return 58;
    }

    /**
     * Tanggal Pensiun
     */
    public function getTanggalPensiunAttribute(): ?Carbon
    {
        if (!$this->tanggal_lahir) {
            return null;
        }

        // Pensiun pada tanggal 1 bulan berikutnya setelah mencapai BUP
        $bupDate = $this->tanggal_lahir->copy()->addYears($this->bup_usia);
        return $bupDate->copy()->startOfMonth()->addMonth();
    }

    /**
     * Sisa Waktu Menuju Pensiun (dalam Bulan)
     */
    public function getSisaPensiunBulanAttribute(): ?int
    {
        $tglPensiun = $this->tanggal_pensiun;
        if (!$tglPensiun) {
            return null;
        }

        $now = Carbon::now();
        if ($now->greaterThan($tglPensiun)) {
            return 0;
        }
        return (int) $now->diffInMonths($tglPensiun);
    }

    /**
     * Status Early Warning System KGB
     */
    public function getKgbStatusAttribute(): array
    {
        if (!$this->tmt_kgb) {
            return ['status' => 'unknown', 'label' => 'Belum Ada Data', 'badge' => 'secondary', 'days_diff' => null];
        }

        $now = Carbon::now()->startOfDay();
        $kgb = $this->tmt_kgb->copy()->startOfDay();
        $days = (int) $now->diffInDays($kgb, false);

        if ($days < 0) {
            return [
                'status' => 'overdue',
                'label' => 'Lewat Tempo (' . abs($days) . ' hari)',
                'badge' => 'danger',
                'days_diff' => $days,
            ];
        } elseif ($days <= 90) {
            return [
                'status' => 'warning',
                'label' => 'Jatuh Tempo (' . $days . ' hari lagi)',
                'badge' => 'warning',
                'days_diff' => $days,
            ];
        } else {
            return [
                'status' => 'safe',
                'label' => 'Masih Aman (' . round($days / 30) . ' bln)',
                'badge' => 'success',
                'days_diff' => $days,
            ];
        }
    }

    /**
     * Status Tour of Duty (> 4 tahun di Palembang)
     */
    public function getIsTourOfDutyDueAttribute(): bool
    {
        return $this->lama_palembang_tahun >= 4;
    }
}
