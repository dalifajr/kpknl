<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'browser',
        'browser_version',
        'platform',
        'device_type',
        'device_model',
        'is_active',
        'last_activity_at',
        'login_at',
        'logout_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
