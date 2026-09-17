<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\OAuthToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleSignOutTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestApp(): Application
    {
        $unique = uniqid();
        return Application::create([
            'name' => 'Test App',
            'slug' => 'test-app-' . $unique,
            'url' => 'http://localhost/test-app',
            'client_id' => 'client_test_' . $unique,
            'client_secret' => 'secret_test',
            'redirect_uri' => 'http://localhost/callback',
            'status' => 'active',
        ]);
    }

    public function test_logout_revokes_all_active_oauth_tokens_for_user(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();

        $token1 = OAuthToken::create([
            'access_token' => 'token_client_1',
            'application_id' => $app->id,
            'user_id' => $user->id,
            'expires_at' => now()->addHours(8),
            'revoked' => false,
        ]);

        $token2 = OAuthToken::create([
            'access_token' => 'token_client_2',
            'application_id' => $app->id,
            'user_id' => $user->id,
            'expires_at' => now()->addHours(8),
            'revoked' => false,
        ]);

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $this->assertTrue((bool) $token1->fresh()->revoked);
        $this->assertTrue((bool) $token2->fresh()->revoked);
    }

    public function test_verify_session_endpoint_validates_active_token(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();

        $token = OAuthToken::create([
            'access_token' => 'valid_bearer_token',
            'application_id' => $app->id,
            'user_id' => $user->id,
            'expires_at' => now()->addHours(8),
            'revoked' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid_bearer_token',
        ])->getJson('/api/sso/verify-session');

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'user_id' => $user->id,
        ]);
    }

    public function test_verify_session_endpoint_rejects_revoked_or_missing_token(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();

        OAuthToken::create([
            'access_token' => 'revoked_bearer_token',
            'application_id' => $app->id,
            'user_id' => $user->id,
            'expires_at' => now()->addHours(8),
            'revoked' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer revoked_bearer_token',
        ])->getJson('/api/sso/verify-session');

        $response->assertStatus(401);
        $response->assertJson([
            'valid' => false,
            'error' => 'session_terminated',
        ]);

        $emptyResponse = $this->getJson('/api/sso/verify-session');
        $emptyResponse->assertStatus(401);
    }
}
