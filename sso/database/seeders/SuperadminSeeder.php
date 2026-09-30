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

        $accounts = [
            [
                'username' => 'kepala_kantor',
                'name' => 'Kepala Kantor',
                'email' => 'kepala_kantor@kpknl.go.id',
            ],
            [
                'username' => 'mardanus',
                'name' => 'Mardanus',
                'email' => 'mardanus@kpknl.go.id',
            ],
        ];

        foreach ($accounts as $acc) {
            $user = User::updateOrCreate(
                ['username' => $acc['username']],
                [
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'password' => Hash::make('admin123'),
                    'status' => 'active',
                ]
            );

            if ($superadminRole && !$user->roles()->where('role_id', $superadminRole->id)->exists()) {
                $user->roles()->attach($superadminRole->id);
            }
        }
    }
}
