<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'sso_id',
        'role',
        'subbidang_id',
        'password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function subbidang()
    {
        return $this->belongsTo(Subbidang::class);
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function monlapNotifications()
    {
        return $this->hasMany(MonlapNotification::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return in_array($this->role, $roles, true);
    }

    public function isMaintenance(): bool
    {
        return $this->role === 'maintenance';
    }

    public function isSuperadmin(): bool
    {
        return in_array($this->role, ['superadmin', 'maintenance'], true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin', 'maintenance'], true);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public static function syncFromSso(): void
    {
        try {
            $ssoApp = \Illuminate\Support\Facades\DB::connection('sso_db')
                ->table('applications')
                ->where('slug', 'monitoring')
                ->orWhere('folder_path', 'monlap')
                ->first();
            $appId = $ssoApp ? $ssoApp->id : 4;

            $ssoUsers = \Illuminate\Support\Facades\DB::connection('sso_db')
                ->table('user_application')
                ->join('users', 'user_application.user_id', '=', 'users.id')
                ->where('user_application.application_id', $appId)
                ->whereNull('users.deleted_at')
                ->where('users.status', 'active')
                ->select(
                    'users.id',
                    'users.name',
                    'users.username',
                    'users.email',
                    'user_application.role as app_role'
                )
                ->get();

            // Fetch any privileged global roles from user_role in sso_db
            $userRoles = \Illuminate\Support\Facades\DB::connection('sso_db')
                ->table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->whereIn('roles.name', ['maintenance', 'superadmin', 'admin'])
                ->select('user_role.user_id', 'roles.name as role_name')
                ->get()
                ->groupBy('user_id');

            $syncedSsoIds = [];

            foreach ($ssoUsers as $ssoUser) {
                $syncedSsoIds[] = (string) $ssoUser->id;

                $user = self::where('sso_id', (string) $ssoUser->id)->first();
                if (!$user && !empty($ssoUser->email)) {
                    $user = self::where('email', $ssoUser->email)->first();
                }
                if (!$user && !empty($ssoUser->username)) {
                    $user = self::where('username', $ssoUser->username)->first();
                }
                if (!$user) {
                    $user = new self();
                }

                $user->sso_id = (string) $ssoUser->id;
                $user->name = $ssoUser->name;
                $user->email = $ssoUser->email;
                if (!empty($ssoUser->username)) {
                    $user->username = $ssoUser->username;
                } elseif (empty($user->username)) {
                    $user->username = strstr($ssoUser->email, '@', true) ?: 'user_' . $ssoUser->id;
                }

                // Determine resolved role:
                // If user is admin/superadmin/maintenance in MonLap app_role or in global_roles, they are privileged.
                // Otherwise, they are a regular non-admin user eligible for task assignments.
                $resolvedRole = 'user';
                $appRole = strtolower(trim((string) $ssoUser->app_role));

                if (in_array($appRole, ['maintenance', 'superadmin', 'admin'], true)) {
                    $resolvedRole = $appRole;
                } elseif (isset($userRoles[$ssoUser->id])) {
                    $rolesList = $userRoles[$ssoUser->id]->pluck('role_name')->map(fn($r) => strtolower(trim($r)))->toArray();
                    if (in_array('maintenance', $rolesList, true)) {
                        $resolvedRole = 'maintenance';
                    } elseif (in_array('superadmin', $rolesList, true)) {
                        $resolvedRole = 'superadmin';
                    } elseif (in_array('admin', $rolesList, true)) {
                        $resolvedRole = 'admin';
                    }
                }

                $user->role = $resolvedRole;
                $user->save();
            }

            // Exclude users from being assignable PICs if they are no longer assigned to MonLap in SSO
            if (!empty($syncedSsoIds)) {
                self::where('role', 'user')
                    ->where(function ($q) use ($syncedSsoIds) {
                        $q->whereNotIn('sso_id', $syncedSsoIds)
                          ->orWhereNull('sso_id');
                    })
                    ->update(['role' => 'unassigned']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('syncFromSso error: ' . $e->getMessage());
        }
    }

    /**
     * Get all users eligible to be assigned tasks (PIC) by admin and superadmin.
     */
    public static function getAssignableUsers()
    {
        self::syncFromSso();
        return self::where('role', 'user')->orderBy('name', 'asc')->get();
    }
}
