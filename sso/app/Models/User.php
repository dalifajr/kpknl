<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'status',
        'created_by',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'user_application')
                    ->withPivot('assigned_by', 'role')
                    ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function loginSessions(): HasMany
    {
        return $this->hasMany(LoginSession::class);
    }

    public function oauthTokens(): HasMany
    {
        return $this->hasMany(OAuthToken::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Role Helper Methods
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->pluck('name')->contains($roles);
        }

        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isMaintenance(): bool
    {
        return $this->hasRole('maintenance');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    public function hasApplicationAccess(int|Application $application): bool
    {
        if ($this->isSuperadmin() || $this->isMaintenance()) {
            return true;
        }

        $appId = $application instanceof Application ? $application->id : $application;
        return $this->applications->pluck('id')->contains($appId);
    }
}
