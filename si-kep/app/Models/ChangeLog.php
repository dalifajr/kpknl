<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeLog extends Model
{
    use HasFactory;

    protected $table = 'change_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'pegawai_id',
        'nama_pegawai',
        'nip',
        'action',
        'description',
        'changes',
        'payload_before',
        'payload_after',
        'sync_status',
        'sync_error',
        'synced_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'payload_before' => 'array',
        'payload_after' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
