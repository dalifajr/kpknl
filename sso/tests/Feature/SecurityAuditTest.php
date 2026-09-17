<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestApp(array $overrides = []): Application
    {
        $unique = uniqid();
        return Application::create(array_merge([
            'name' => 'Aplikasi Aman Test',
            'slug' => 'app-aman-' . $unique,
            'url' => 'http://localhost/app-aman',
            'client_id' => 'client_aman_' . $unique,
            'client_secret' => 'secret_aman_123',
            'redirect_uri' => 'http://localhost/app-aman/callback',
            'status' => 'active',
        ], $overrides));
    }

    public function test_oauth_authorization_rejects_mismatched_redirect_uri(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();
        $user->applications()->attach($app->id, ['assigned_by' => 1]);

        // Attempt open redirect to evil attacker site
        $response = $this->actingAs($user)->getJson('/oauth/authorize?' . http_build_query([
            'client_id' => $app->client_id,
            'redirect_uri' => 'https://evil-attacker.com/steal-code',
            'response_type' => 'code',
        ]));

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'redirect_uri_mismatch',
        ]);
    }

    public function test_oauth_authorization_allows_legitimate_matching_redirect_uri(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();
        $user->applications()->attach($app->id, ['assigned_by' => 1]);

        $response = $this->actingAs($user)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $app->client_id,
            'redirect_uri' => 'http://localhost/app-aman/callback',
            'response_type' => 'code',
            'state' => 'secret_state_123',
        ]));

        $response->assertStatus(302);
        $this->assertStringContainsString('http://localhost/app-aman/callback', $response->headers->get('Location'));
        $this->assertStringContainsString('code=', $response->headers->get('Location'));
        $this->assertStringContainsString('state=secret_state_123', $response->headers->get('Location'));
    }

    public function test_maintenance_user_cannot_login_with_hardcoded_backdoor_password(): void
    {
        $maintenanceUser = User::factory()->create([
            'username' => 'maintenance',
            'password' => Hash::make('SuperSecureOfficialPassword2026!'),
            'status' => 'active',
        ]);

        // Attempt backdoor with 'password'
        $response1 = $this->post('/login', [
            'login' => 'maintenance',
            'password' => 'password',
        ]);
        $response1->assertSessionHasErrors('login');
        $this->assertGuest();

        // Attempt backdoor with 'maintenance'
        $response2 = $this->post('/login', [
            'login' => 'maintenance',
            'password' => 'maintenance',
        ]);
        $response2->assertSessionHasErrors('login');
        $this->assertGuest();

        // Legitimate password must succeed
        $response3 = $this->post('/login', [
            'login' => 'maintenance',
            'password' => 'SuperSecureOfficialPassword2026!',
        ]);
        $response3->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($maintenanceUser);
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_app_icon_route_rejects_path_traversal_and_disallowed_extensions(): void
    {
        // Disallowed file extension (.env or .php)
        $resp1 = $this->get('/app-icon/....//....//.env');
        $resp1->assertStatus(404);

        $resp2 = $this->get('/app-icon/test.php');
        $resp2->assertStatus(404);
    }

    public function test_notification_hub_api_requires_valid_authentication(): void
    {
        $user = User::factory()->create();

        // Unauthenticated request must return 401
        $unauthResp = $this->postJson('/api/hub/notifications', [
            'user_id' => $user->id,
            'title' => 'Test Phishing Alert',
            'message' => 'Please visit evil link',
        ]);
        $unauthResp->assertStatus(401);
        $unauthResp->assertJson(['error' => 'unauthorized']);

        // Authenticated request with client credentials must succeed
        $app = $this->createTestApp();
        $authResp = $this->withHeaders([
            'X-Client-Id' => $app->client_id,
            'X-Client-Secret' => $app->client_secret,
        ])->postJson('/api/hub/notifications', [
            'user_id' => $user->id,
            'title' => 'Official App Notification',
            'message' => 'Your task has been approved.',
            'link' => 'http://localhost/app-aman/tasks/1',
        ]);

        $authResp->assertStatus(200);
        $authResp->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('sso_notifications', [
            'user_id' => $user->id,
            'title' => 'Official App Notification',
            'app_name' => $app->name,
        ]);
    }
}
