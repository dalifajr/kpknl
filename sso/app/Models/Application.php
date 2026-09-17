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

    /**
     * Get accessible URL for application icon (custom upload or default illustration SVG).
     */
    public function getIconUrlAttribute(): ?string
    {
        $icon = $this->attributes['icon'] ?? null;

        // 1. If custom upload file exists (starts with applications/ or contains extension)
        if (!empty($icon) && (str_contains($icon, '/') || str_contains($icon, '.'))) {
            $basename = basename($icon);

            // If file exists in public/images/apps/
            if (file_exists(public_path('images/apps/' . $basename))) {
                return asset('images/apps/' . $basename);
            }

            // If public/storage exists
            if (file_exists(public_path('storage/' . $icon))) {
                return asset('storage/' . $icon);
            }

            // If storage/app/public exists
            if (file_exists(storage_path('app/public/' . $icon)) || file_exists(storage_path('app/public/applications/' . $basename))) {
                return route('application.icon', ['path' => $icon]);
            }

            return asset('storage/' . $icon);
        }

        // 2. Map known application slug / name to dedicated official SVG illustration
        $slug = strtolower($this->slug ?? '');
        $name = strtolower($this->name ?? '');

        if (str_contains($slug, 'monlap') || str_contains($name, 'monitoring') || str_contains($slug, 'monitoring')) {
            return asset('images/apps/monlap.svg');
        }
        if (str_contains($slug, 'aset') || str_contains($name, 'aset') || str_contains($name, 'bppn')) {
            return asset('images/apps/aset-bppn.svg');
        }
        if (str_contains($slug, 'bmn') || str_contains($name, 'bmn') || str_contains($slug, 'dashboard')) {
            return asset('images/apps/dashboard-bmn.svg');
        }
        if (str_contains($slug, 'lelang') || str_contains($name, 'lelang') || str_contains($name, 'peminjaman')) {
            return asset('images/apps/peminjaman-lelang.svg');
        }
        if (str_contains($slug, 'kep') || str_contains($name, 'kepegawaian') || str_contains($slug, 'simpatik')) {
            return asset('images/apps/si-kep.svg');
        }

        return null;
    }

    /**
     * Determine if the application has an image/SVG icon or FontAwesome icon.
     */
    public function getHasImageIconAttribute(): bool
    {
        return !empty($this->icon_url);
    }
    /**
     * Determine if the application is in maintenance mode (503).
     */
    public function getIsMaintenanceAttribute(): bool
    {
        if ($this->maintenance_mode) {
            return true;
        }

        if (($this->health_status ?? '') === 'maintenance') {
            return true;
        }

        if (($this->status ?? '') === 'maintenance') {
            return true;
        }

        $appPath = $this->resolved_path;
        if ($appPath && file_exists($appPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'down')) {
            return true;
        }

        return false;
    }

    /**
     * Get user-friendly status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_maintenance) {
            return 'PEMELIHARAAN (503)';
        }

        if ($this->status === 'inactive') {
            return 'NONAKTIF';
        }

        return 'TERSEDIA';
    }

    /**
     * Get badge CSS classes for application status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_maintenance) {
            return 'bg-warning-subtle text-warning border border-warning-subtle';
        }

        if ($this->status === 'inactive') {
            return 'bg-secondary-subtle text-secondary';
        }

        return 'bg-success-subtle text-success';
    }
}
