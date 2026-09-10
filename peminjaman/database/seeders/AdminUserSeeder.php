<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@kpknl.go.id',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::firstOrCreate(
            ['username' => 'pelelang1'],
            [
                'email' => 'pelelang1@kpknl.go.id',
                'password' => Hash::make('pelelang123'),
                'role' => User::ROLE_PELELANG,
            ]
        );

        User::firstOrCreate(
            ['username' => 'peminjam1'],
            [
                'email' => 'peminjam1@kpknl.go.id',
                'password' => Hash::make('peminjam123'),
                'role' => User::ROLE_PEMINJAM,
            ]
        );
    }
}
