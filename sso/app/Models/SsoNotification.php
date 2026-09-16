<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SsoNotification extends Model
{
    protected $table = 'sso_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'icon',
        'link',
        'app_name',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
