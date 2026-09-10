<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@kpknl.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'login' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::create([
            'username' => 'pelelang_user',
            'email' => 'pelelang@kpknl.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pelelang',
        ]);

        $response = $this->post('/login', [
            'login' => 'pelelang@kpknl.go.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pelelang.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::create([
            'username' => 'user1',
            'email' => 'user1@kpknl.go.id',
            'password' => Hash::make('correct_pass'),
            'role' => 'peminjam',
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'user1',
            'password' => 'wrong_pass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'username' => 'new_peminjam',
            'email' => 'new_peminjam@kpknl.go.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'peminjam',
        ]);

        $response->assertRedirect(route('peminjam.dashboard'));
        $this->assertDatabaseHas('users', [
            'username' => 'new_peminjam',
            'email' => 'new_peminjam@kpknl.go.id',
            'role' => 'peminjam',
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'username' => 'admin_test',
            'email' => 'admin_test@kpknl.go.id',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
