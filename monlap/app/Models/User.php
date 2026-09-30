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

    public static function syncFromSso()
    {
        try {
            $ssoUsers = \Illuminate\Support\Facades\DB::connection('sso_db')
                ->table('user_application')
                ->join('users', 'user_application.user_id', '=', 'users.id')
                ->where('user_application.application_id', 4)
                ->select('users.id', 'users.name', 'users.email', 'user_application.role as app_role')
                ->get();

            // Fetch any privileged global roles from user_role in sso_db
            $userRoles = \Illuminate\Support\Facades\DB::connection('sso_db')
                ->table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->whereIn('roles.name', ['maintenance', 'superadmin', 'admin'])
                ->select('user_role.user_id', 'roles.name as role_name')
                ->get()
                ->groupBy('user_id');

            foreach ($ssoUsers as $ssoUser) {
                $user = self::firstOrNew(['sso_id' => $ssoUser->id]);
                $user->name = $ssoUser->name;
                $user->email = $ssoUser->email;
                
                // Determine resolved role
                $resolvedRole = 'user';
                if (!empty($ssoUser->app_role) && in_array($ssoUser->app_role, ['maintenance', 'superadmin', 'admin'], true)) {
                    $resolvedRole = $ssoUser->app_role;
                } elseif (isset($userRoles[$ssoUser->id])) {
                    $rolesList = $userRoles[$ssoUser->id]->pluck('role_name')->toArray();
                    if (in_array('maintenance', $rolesList, true)) {
                        $resolvedRole = 'maintenance';
                    } elseif (in_array('superadmin', $rolesList, true)) {
                        $resolvedRole = 'superadmin';
                    } elseif (in_array('admin', $rolesList, true)) {
                        $resolvedRole = 'admin';
                    }
                }
                
                // If user is new, or if resolvedRole is a privileged role (maintenance, superadmin, admin)
                if (!$user->exists || in_array($resolvedRole, ['maintenance', 'superadmin', 'admin'], true)) {
                    $user->role = $resolvedRole;
                }
                
                $user->save();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('syncFromSso error: ' . $e->getMessage());
        }
    }
}
