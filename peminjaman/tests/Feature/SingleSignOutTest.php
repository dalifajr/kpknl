<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SingleSignOutTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin Peminjaman',
            'username' => 'admin.peminjaman.' . uniqid(),
            'email' => 'admin.' . uniqid() . '@kpknl.go.id',
            'role' => 'admin',
            'sso_id' => 1,
            'password' => bcrypt('password'),
        ], $attributes));
    }

    public function test_authenticated_user_with_valid_sso_token_can_access_dashboard(): void
    {
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => true, 'user_id' => 1], 200),
        ]);

        $user = $this->createTestUser();

        $response = $this->actingAs($user)
            ->withSession(['sso_access_token' => 'valid_token_123'])
            ->get('/dashboard');

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_is_logged_out_when_sso_session_is_terminated_or_revoked(): void
    {
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => false, 'error' => 'session_terminated'], 401),
        ]);

        $user = $this->createTestUser();

        $response = $this->actingAs($user)
            ->withSession([
                'sso_access_token' => 'revoked_token_123',
            ])
            ->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_user_without_sso_token_is_immediately_logged_out(): void
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)
            ->withSession(['_enforce_sso_check' => true])
            ->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
