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
        'username',
        'email',
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

    public static function syncFromSso()
    {
        $ssoUsers = \Illuminate\Support\Facades\DB::connection('sso_db')
            ->table('user_application')
            ->join('users', 'user_application.user_id', '=', 'users.id')
            ->where('user_application.application_id', 4)
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        foreach ($ssoUsers as $ssoUser) {
            $user = self::firstOrNew(['sso_id' => $ssoUser->id]);
            $user->name = $ssoUser->name;
            $user->email = $ssoUser->email;
            
            if (!$user->exists) {
                $user->role = 'user'; // Default role until they login
            }
            
            $user->save();
        }
    }
}
