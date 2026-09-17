<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Prevent SingleSignOut middleware from failing in test environment
        $this->withoutMiddleware(\App\Http\Middleware\EnsureSsoSessionIsValid::class);
    }

    public function test_sso_callback_rejects_missing_or_mismatched_oauth_state(): void
    {
        // 1. Missing state
        $response1 = $this->withSession(['sso_state' => 'expected_state_abc'])
            ->get('/auth/sso/callback?code=some_code');

        $response1->assertRedirect(route('login'));
        $response1->assertSessionHas('error');

        // 2. Mismatched state (CSRF attempt)
        $response2 = $this->withSession(['sso_state' => 'expected_state_abc'])
            ->get('/auth/sso/callback?code=some_code&state=forged_state_xyz');

        $response2->assertRedirect(route('login'));
        $response2->assertSessionHas('error');
    }

    public function test_sso_callback_accepts_valid_matching_oauth_state(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'valid_mock_token_123'], 200),
            '*/api/user' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 999,
                    'name' => 'Pegawai Uji Aman',
                    'username' => 'pegawai.uji',
                    'email' => 'pegawai.uji@kpknl.go.id',
                    'primary_role' => 'user',
                    'is_superadmin' => false,
                ]
            ], 200),
        ]);

        $response = $this->withSession(['sso_state' => 'secret_valid_state'])
            ->get('/auth/sso/callback?code=valid_code_123&state=secret_valid_state');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_check_spreadsheet_permission_rejects_unauthorized_roles(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->postJson('/settings/check-spreadsheet-permission', [
                'sheet_url' => 'https://docs.google.com/spreadsheets/d/test',
            ]);

        $response->assertStatus(403);
    }

    public function test_check_spreadsheet_permission_blocks_ssrf_to_internal_or_foreign_hosts(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        // Attempt SSRF to local loopback port
        $response1 = $this->actingAs($superadmin)
            ->postJson('/settings/check-spreadsheet-permission', [
                'sheet_url' => 'http://127.0.0.1:3306/probe',
            ]);

        $response1->assertStatus(422);
        $response1->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('SSRF Alert', $response1->json('message'));

        // Attempt SSRF to untrusted external host
        $response2 = $this->actingAs($superadmin)
            ->postJson('/settings/check-spreadsheet-permission', [
                'sheet_url' => 'http://evil-attacker.com/exploit',
            ]);

        $response2->assertStatus(422);
        $this->assertStringContainsString('SSRF Alert', $response2->json('message'));
    }

    public function test_security_headers_present_on_sikep_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
