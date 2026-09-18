<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SingleSignOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_valid_sso_token_can_access_dashboard(): void
    {
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => true, 'user_id' => 1], 200),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'sso_user_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['sso_access_token' => 'valid_token_123'])
            ->get('/');

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_is_logged_out_when_sso_session_is_terminated_or_revoked(): void
    {
        // Simulate SSO returning 401 Unauthorized because user logged out on SSO
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => false, 'error' => 'session_terminated'], 401),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'sso_user_id' => 1,
        ]);

        // When user refreshes or requests any page after SSO logout
        $response = $this->actingAs($user)
            ->withSession([
                'sso_access_token' => 'revoked_token_123',
                'sso_last_checked_at' => null,
            ])
            ->get('/');

        // Assert user was logged out and redirected to login with notice
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('warning', 'Sesi SSO Anda telah berakhir atau Anda telah logout dari Portal SSO.');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_sso_token_is_immediately_logged_out(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'sso_user_id' => 1,
        ]);

        // Request with auth but NO sso_access_token in session
        $response = $this->actingAs($user)
            ->withSession(['_enforce_sso_check' => true])
            ->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
