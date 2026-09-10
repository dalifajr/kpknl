<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
    ];

    public function users(): BelongsToMany
    {
        return $table = $this->belongsToMany(User::class, 'user_role');
    }
}
