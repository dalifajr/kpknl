<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'sso_id',
        'avatar_url',
        'nip',
        'jabatan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        ];
    }

    /**
     * Helper to check role
     */
    public function isSuperadmin(): bool
    {
        return strtolower($this->role) === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array(strtolower($this->role), ['admin', 'superadmin']);
    }

    public function isPegawai(): bool
    {
        return in_array(strtolower($this->role), ['pegawai', 'user', 'operator']);
    }

    public function getRoleBadgeClass(): string
    {
        return match (strtolower($this->role)) {
            'superadmin' => 'bg-danger text-white',
            'admin' => 'bg-primary text-white',
            'eksekutif', 'kepala kantor' => 'bg-warning text-dark',
            default => 'bg-secondary text-white',
        };
    }

    public function getRoleLabel(): string
    {
        return match (strtolower($this->role)) {
            'superadmin' => 'Superadmin KPKNL',
            'admin' => 'Admin Seksi PKN',
            'eksekutif', 'kepala kantor' => 'Kepala Kantor',
            default => 'Pegawai / Operator',
        };
    }
}
