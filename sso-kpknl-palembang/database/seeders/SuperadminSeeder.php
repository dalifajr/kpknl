<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();

        $user = User::updateOrCreate(
            ['username' => 'mardanus'],
            [
                'name' => 'Mardanus',
                'email' => 'mardanus@kpknl.go.id',
                'password' => Hash::make('admin123'),
                'status' => 'active',
            ]
        );

        if ($superadminRole && !$user->roles()->where('role_id', $superadminRole->id)->exists()) {
            $user->roles()->attach($superadminRole->id);
        }
    }
}
