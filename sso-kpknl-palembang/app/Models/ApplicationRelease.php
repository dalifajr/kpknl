<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationRelease extends Model
{
    protected $fillable = [
        'application_id',
        'version_tag',
        'commit_hash',
        'release_title',
        'release_notes',
        'is_stable',
        'created_by',
    ];

    protected $casts = [
        'is_stable' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(ApplicationDeployment::class, 'release_id');
    }
}
