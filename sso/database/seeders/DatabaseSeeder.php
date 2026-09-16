<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperadminSeeder::class,
            MaintenanceSeeder::class,
        ]);

        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        // Sample Admin Account
        $adminUser = User::firstOrCreate(
            ['username' => 'admin_aplikasi'],
            [
                'name' => 'Admin Aplikasi Inventaris',
                'email' => 'admin.inv@kpknl.go.id',
                'password' => Hash::make('Admin123!@#'),
                'status' => 'active',
            ]
        );
        if (!$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }

        // Sample Standard User Account
        $sampleUser = User::firstOrCreate(
            ['username' => 'pegawai1'],
            [
                'name' => 'Ahmad Pegawai',
                'email' => 'ahmad@kpknl.go.id',
                'password' => Hash::make('User123!@#'),
                'status' => 'active',
            ]
        );
        if (!$sampleUser->roles()->where('role_id', $userRole->id)->exists()) {
            $sampleUser->roles()->attach($userRole->id);
        }

        // Sample Integrated Applications
        $app1 = Application::firstOrCreate(
            ['slug' => 'sistem-inventaris'],
            [
                'name' => 'Sistem Inventaris BMN',
                'description' => 'Aplikasi Pengelolaan Inventaris Barang Milik Negara KPKNL Palembang.',
                'url' => 'http://localhost/inventaris',
                'icon' => 'box',
                'client_id' => 'client_' . Str::random(16),
                'client_secret' => Str::random(32),
                'redirect_uri' => 'http://localhost/inventaris/callback',
                'status' => 'active',
            ]
        );

        $app2 = Application::firstOrCreate(
            ['slug' => 'sistem-persuratan'],
            [
                'name' => 'Sistem Persuratan Internal',
                'description' => 'Aplikasi Manajemen Surat Masuk & Surat Keluar KPKNL Palembang.',
                'url' => 'http://localhost/persuratan',
                'icon' => 'mail',
                'client_id' => 'client_' . Str::random(16),
                'client_secret' => Str::random(32),
                'redirect_uri' => 'http://localhost/persuratan/callback',
                'status' => 'active',
            ]
        );

        // Assign Applications
        $mardanus = User::where('username', 'mardanus')->first();
        if ($mardanus) {
            $mardanus->applications()->syncWithoutDetaching([$app1->id => ['assigned_by' => $mardanus->id], $app2->id => ['assigned_by' => $mardanus->id]]);
        }
        if ($adminUser) {
            $adminUser->applications()->syncWithoutDetaching([$app1->id => ['assigned_by' => $mardanus->id ?? 1]]);
        }
        if ($sampleUser) {
            $sampleUser->applications()->syncWithoutDetaching([$app1->id => ['assigned_by' => $mardanus->id ?? 1]]);
        }
    }
}
