<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'url',
        'icon',
        'client_id',
        'client_secret',
        'redirect_uri',
        'status',
        'folder_path',
        'database_name',
        'git_branch',
        'current_version',
        'current_commit',
        'maintenance_mode',
        'maintenance_bypass_token',
        'health_status',
        'last_health_check_at',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'last_health_check_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_application')
                    ->withPivot('assigned_by', 'role')
                    ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function oauthTokens(): HasMany
    {
        return $this->hasMany(OAuthToken::class);
    }

    public function releases(): HasMany
    {
        return $this->hasMany(ApplicationRelease::class)->latest();
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(ApplicationDeployment::class)->latest();
    }

    public function backups(): HasMany
    {
        return $this->hasMany(ApplicationBackup::class)->latest();
    }

    public function maintenanceEvents(): HasMany
    {
        return $this->hasMany(MaintenanceEvent::class)->latest();
    }

    public function getResolvedPathAttribute(): ?string
    {
        if (!$this->folder_path) {
            return null;
        }

        // If folder_path is relative, resolve from d:\laragon\www (parent of sso-kpknl-palembang)
        $parent = dirname(base_path());
        $fullPath = $parent . DIRECTORY_SEPARATOR . $this->folder_path;
        return file_exists($fullPath) ? realpath($fullPath) : $fullPath;
    }
}
