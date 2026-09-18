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
     * Batas Usia Pensiun (BUP) berdasarkan PP No. 17 Tahun 2020:
     * - 58 Tahun: Pejabat Administrasi (Kepala Seksi, Kasubbag, Kepala KPKNL), Pejabat Fungsional Ahli Pertama & Muda, Pejabat Pelaksana
     * - 60 Tahun: Pejabat Pimpinan Tinggi & Pejabat Fungsional Ahli Madya
     * - 65 Tahun: Pejabat Fungsional Ahli Utama
     */
    public function getBupUsiaAttribute(): int
    {
        if ($this->tipe_pegawai === 'ppnpn') {
            return 58;
        }

        $jabatan = strtolower($this->nama_jabatan_raw ?? '');

        // 65 Tahun: Fungsional Ahli Utama
        if (str_contains($jabatan, 'ahli utama')) {
            return 65;
        }

        // 60 Tahun: Pimpinan Tinggi (JPT) dan Fungsional Ahli Madya
        if (str_contains($jabatan, 'madya') || str_contains($jabatan, 'pimpinan tinggi') || str_contains($jabatan, 'eselon i') || str_contains($jabatan, 'eselon ii')) {
            return 60;
        }

        // 58 Tahun: Pejabat Administrasi (Kepala Kantor/KPKNL, Kasi, Kasubbag), Fungsional Pertama/Muda, dan Pelaksana
        return 58;
    }

    /**
     * Alias bup_tahun untuk view blade
     */
    public function getBupTahunAttribute(): int
    {
        return $this->bup_usia;
    }

    /**
     * Tanggal Pensiun (Tanggal 1 bulan berikutnya setelah mencapai BUP)
     */
    public function getTanggalPensiunAttribute(): ?Carbon
    {
        if (!$this->tanggal_lahir) {
            return null;
        }

        $bupDate = $this->tanggal_lahir->copy()->addYears($this->bup_usia);
        return $bupDate->copy()->startOfMonth()->addMonth();
    }

    /**
     * Alias tgl_pensiun untuk view blade
     */
    public function getTglPensiunAttribute(): ?Carbon
    {
        return $this->tanggal_pensiun;
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

        $now = Carbon::now()->startOfDay();
        if ($now->greaterThan($tglPensiun)) {
            return 0;
        }
        return (int) $now->diffInMonths($tglPensiun);
    }

    /**
     * Sisa Masa Dinas Tahun
     */
    public function getSisaDinasTahunAttribute(): int
    {
        $sisaBulan = $this->sisa_pensiun_bulan;
        if ($sisaBulan === null) return 0;
        return (int) floor($sisaBulan / 12);
    }

    /**
     * Sisa Masa Dinas Bulan
     */
    public function getSisaDinasBulanAttribute(): int
    {
        $sisaBulan = $this->sisa_pensiun_bulan;
        if ($sisaBulan === null) return 0;
        return (int) ($sisaBulan % 12);
    }

    /**
     * Relasi ke ChangeLog
     */
    public function changeLogs()
    {
        return $this->hasMany(ChangeLog::class, 'pegawai_id')->latest();
    }

    /**
     * TMT KGB Berikutnya (Jatuh Tempo 2 Tahun setelah TMT KGB Terakhir)
     */
    public function getNextTmtKgbAttribute(): ?Carbon
    {
        return $this->tmt_kgb ? $this->tmt_kgb->copy()->addYears(2) : null;
    }

    /**
     * Status Early Warning System KGB (Dihitung 2 tahun dari TMT KGB Terakhir di Spreadsheet)
     */
    public function getKgbStatusAttribute(): array
    {
        if (!$this->tmt_kgb) {
            return [
                'status' => 'unknown',
                'label' => 'Belum Ada Data',
                'badge' => 'secondary',
                'days_diff' => null,
                'overdue_days' => 0,
                'days_left' => 0,
                'next_tmt' => null,
            ];
        }

        $now = Carbon::now()->startOfDay();
        // TMT KGB di spreadsheet adalah TMT terakhir, maka jatuh tempo berikutnya adalah +2 tahun
        $nextKgb = $this->tmt_kgb->copy()->addYears(2)->startOfDay();
        $days = (int) $now->diffInDays($nextKgb, false);

        if ($days < 0) {
            $absDays = abs($days);
            return [
                'status' => 'overdue',
                'label' => 'Lewat Tempo (' . $absDays . ' hari)',
                'badge' => 'danger',
                'days_diff' => $days,
                'overdue_days' => $absDays,
                'days_left' => 0,
                'next_tmt' => $nextKgb,
            ];
        } elseif ($days <= 90) {
            return [
                'status' => 'warning',
                'label' => 'Jatuh Tempo (' . $days . ' hari lagi)',
                'badge' => 'warning',
                'days_diff' => $days,
                'overdue_days' => 0,
                'days_left' => $days,
                'next_tmt' => $nextKgb,
            ];
        } else {
            return [
                'status' => 'safe',
                'label' => 'Aman (' . round($days / 30) . ' bln)',
                'badge' => 'success',
                'days_diff' => $days,
                'overdue_days' => 0,
                'days_left' => $days,
                'next_tmt' => $nextKgb,
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

    /**
     * Total Lama Bertugas di Unit Eselon IV (dalam hitungan Bulan)
     */
    public function getLamaUeIvBulanAttribute(): int
    {
        if (!empty($this->lama_bertugas_ue_iv) && preg_match('/(\d+)\s*thn\s*(\d+)\s*bln/i', $this->lama_bertugas_ue_iv, $matches)) {
            return ((int)$matches[1] * 12) + (int)$matches[2];
        }

        if (!empty($this->tmt_ue_iv) && $this->tmt_ue_iv !== '-') {
            try {
                $parsed = Carbon::hasFormat($this->tmt_ue_iv, 'd/m/Y')
                    ? Carbon::createFromFormat('d/m/Y', $this->tmt_ue_iv)->startOfDay()
                    : Carbon::parse($this->tmt_ue_iv)->startOfDay();
                return max(0, (int)$parsed->diffInMonths(Carbon::now()));
            } catch (\Exception $e) {
                // fallback below
            }
        }

        // Jika tidak ada tmt_ue_iv tetapi ada tmt_eselon (misal Kepala Seksi / Kasubbag Eselon IV)
        if ($this->tmt_eselon) {
            return max(0, (int)$this->tmt_eselon->diffInMonths(Carbon::now()));
        }

        // Atau jika memiliki tmt_palembang dan berada di unit non-pimpinan (Seksi/Subbagian Eselon IV)
        if ($this->tmt_palembang && $this->unit_kerja_id != 8) {
            return max(0, (int)$this->tmt_palembang->diffInMonths(Carbon::now()));
        }

        return 0;
    }

    /**
     * TMT Efektif di Unit Eselon IV (TMT UE IV / TMT Eselon / TMT Palembang)
     */
    public function getEffectiveTmtUeIvAttribute(): string
    {
        if (!empty($this->tmt_ue_iv) && $this->tmt_ue_iv !== '-') {
            return $this->tmt_ue_iv;
        }

        if ($this->tmt_eselon) {
            return $this->tmt_eselon->format('d/m/Y');
        }

        if ($this->tmt_palembang) {
            return $this->tmt_palembang->format('d/m/Y');
        }

        return '-';
    }

    /**
     * Format teks lama bertugas UE IV (misal: "4 Thn 2 Bln")
     */
    public function getLamaUeIvFormattedAttribute(): string
    {
        if (!empty($this->lama_bertugas_ue_iv) && $this->lama_bertugas_ue_iv !== '-') {
            return ucwords(trim($this->lama_bertugas_ue_iv));
        }

        $bulan = $this->lama_ue_iv_bulan;
        if ($bulan <= 0) return '-';

        $thn = floor($bulan / 12);
        $bln = $bulan % 12;

        if ($thn > 0 && $bln > 0) return "{$thn} Thn {$bln} Bln";
        if ($thn > 0) return "{$thn} Thn";
        return "{$bln} Bln";
    }
}
