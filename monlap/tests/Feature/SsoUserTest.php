<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SsoUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_with_username_and_maintenance_role(): void
    {
        $user = User::create([
            'sso_id' => 18,
            'name' => 'Tim Maintenance KPKNL Palembang',
            'email' => 'maintenance@kpknl.go.id',
            'username' => 'maintenance',
            'role' => 'maintenance',
            'last_login_at' => now(),
        ]);

        $this->assertDatabaseHas('users', [
            'sso_id' => 18,
            'username' => 'maintenance',
            'role' => 'maintenance',
            'email' => 'maintenance@kpknl.go.id',
        ]);
    }
}
