<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SingleSignOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class);
    }

    public function test_authenticated_user_with_valid_sso_token_can_access_dashboard(): void
    {
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => true, 'user_id' => 1], 200),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'sso_id' => 1,
        ]);

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

        $user = User::factory()->create([
            'role' => 'user',
            'sso_id' => 1,
        ]);

        $response = $this->actingAs($user)

            ->withSession([
                'sso_access_token' => 'revoked_token_123',
                'sso_last_checked_at' => null,
            ])
            ->get('/dashboard');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
