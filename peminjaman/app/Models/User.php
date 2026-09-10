<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    public $timestamps = false;

    const ROLE_ADMIN = 'admin';
    const ROLE_PELELANG = 'pelelang';
    const ROLE_PEMINJAM = 'peminjam';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'sso_id',
        'avatar_url',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->role === 'superadmin';
    }

    public function isPelelang(): bool
    {
        return $this->role === self::ROLE_PELELANG;
    }

    public function isPeminjam(): bool
    {
        return $this->role === self::ROLE_PEMINJAM;
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin', 'superadmin' => 'Administrator Arsip',
            'pelelang' => 'Pejabat Lelang',
            default => 'Peminjam Berkas',
        };
    }

    public function getRoleBadgeClass(): string
    {
        return match ($this->role) {
            'admin', 'superadmin' => 'badge-role-admin',
            'pelelang' => 'badge-role-pelelang',
            default => 'badge-role-peminjam',
        };
    }
}
