<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::create([
            'username' => 'admin1',
            'email' => 'admin1@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_pelelang_cannot_access_admin_dashboard(): void
    {
        $pelelang = User::create([
            'username' => 'pelelang1',
            'email' => 'pelelang1@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'pelelang',
        ]);

        $response = $this->actingAs($pelelang)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_peminjam_cannot_access_admin_dashboard(): void
    {
        $peminjam = User::create([
            'username' => 'peminjam1',
            'email' => 'peminjam1@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        $response = $this->actingAs($peminjam)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_peminjam_cannot_access_pelelang_routes(): void
    {
        $peminjam = User::create([
            'username' => 'peminjam2',
            'email' => 'peminjam2@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        $response = $this->actingAs($peminjam)->get('/pelelang/dashboard');
        $response->assertStatus(403);
    }
}
