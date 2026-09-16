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

    /**
     * Adapt target URL to current browsing environment (localhost dev vs private server host).
     */
    public function adaptHostToCurrentRequest(?string $targetUrl): string
    {
        if (empty($targetUrl) || !request()) {
            return $targetUrl ?? '';
        }

        $currentHost = request()->getHost(); // e.g. "localhost", "10.66.159.40", "sso.test"
        $parsed = parse_url($targetUrl);
        $targetHost = $parsed['host'] ?? '';

        if (!$targetHost || strtolower($targetHost) === strtolower($currentHost)) {
            return $targetUrl;
        }

        $isCurrentDev = in_array(strtolower($currentHost), ['localhost', '127.0.0.1']) || str_ends_with(strtolower($currentHost), '.test');
        $isTargetDev = in_array(strtolower($targetHost), ['localhost', '127.0.0.1']) || str_ends_with(strtolower($targetHost), '.test');

        // If environment mismatch (dev accessing prod link or vice-versa), adapt host!
        if ($isCurrentDev !== $isTargetDev) {
            $newScheme = request()->getScheme();
            $newPort = request()->getPort() && !in_array(request()->getPort(), [80, 443]) ? ':' . request()->getPort() : '';
            $path = $parsed['path'] ?? '';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return "{$newScheme}://{$currentHost}{$newPort}{$path}{$query}";
        }

        return $targetUrl;
    }

    public function getResolvedUrlAttribute(): string
    {
        return $this->adaptHostToCurrentRequest($this->attributes['url'] ?? '');
    }

    public function getResolvedRedirectUriAttribute(): string
    {
        return $this->adaptHostToCurrentRequest($this->attributes['redirect_uri'] ?? '');
    }
}
