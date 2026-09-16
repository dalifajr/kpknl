<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Superadmin',
                'description' => 'Akses penuh ke seluruh sistem SSO, manajemen user, role, aplikasi, dan backup/restore.',
                'level' => 1,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin Aplikasi',
                'description' => 'Akses ke aplikasi yang di-assign dan melihat log aktivitas user pada aplikasi tersebut.',
                'level' => 2,
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Akses ke aplikasi yang di-assign dan melihat aktivitas login sendiri.',
                'level' => 3,
            ],
            [
                'name' => 'maintenance',
                'display_name' => 'Tim Pemeliharaan (Maintenance)',
                'description' => 'Akses eksklusif ke pemeliharaan sistem, manajemen aplikasi, lifecycle orchestrator, backup/restore, dan pengaturan server.',
                'level' => 1,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}
