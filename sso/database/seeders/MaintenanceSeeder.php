<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Application;
use Illuminate\Support\Facades\Hash;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'maintenance'],
            [
                'display_name' => 'Tim Pemeliharaan (Maintenance)',
                'description' => 'Akses eksklusif ke pemeliharaan sistem, manajemen aplikasi, lifecycle orchestrator, backup/restore, dan pengaturan server.',
                'level' => 1,
            ]
        );

        $user = User::updateOrCreate(
            ['username' => 'maintenance'],
            [
                'name' => 'Tim Maintenance KPKNL Palembang',
                'email' => 'maintenance@kpknl.go.id',
                'password' => Hash::make('admin123'),
                'status' => 'active',
                'created_by' => 1,
            ]
        );

        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->sync([$role->id]);
        }

        // Assign all active applications
        $apps = Application::where('status', 'active')->pluck('id')->toArray();
        $syncData = [];
        foreach ($apps as $appId) {
            $syncData[$appId] = ['assigned_by' => 1, 'role' => 'admin'];
        }
        $user->applications()->sync($syncData);
    }
}
