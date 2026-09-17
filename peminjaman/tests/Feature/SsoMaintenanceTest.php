<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_user_can_authenticate_via_sso_callback(): void
    {
        // Mock SSO Token exchange response
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'mock_access_token_maintenance_123',
                'user' => [
                    'id' => 999,
                    'name' => 'Tim Maintenance Server',
                    'username' => 'maintenance',
                    'email' => 'maintenance@kpknl.go.id',
                    'is_superadmin' => false,
                    'is_admin' => false,
                    'is_maintenance' => true,
                    'primary_role' => 'maintenance',
                    'roles' => ['maintenance'],
                    'app_role' => 'admin',
                ]
            ], 200),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'mock_code_123', 'state' => 'xyz']));
        if ($response->status() === 404) {
            dump($response->getContent());
            dump(route('sso.callback'));
        }

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('maintenance', $user->username);
        $this->assertEquals('admin', $user->role);
    }
}
