<?php
// PHP, MaintenanceAccessTest.php; Laravel/PHPUnit; operator access without direct SSO database reads.
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MaintenanceAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(['*/api/sso/verify-session' => Http::response(['valid' => true])]);
    }

    public function test_maintenance_without_assignment_can_login_and_role_is_visible(): void
    {
        Http::preventStrayRequests();
        foreach ([true, false] as $maintenance) {
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['*/api/sso/verify-session' => Http::response(['valid' => true]),'*/oauth/token' => Http::response(['access_token' => 'test-token', 'user' => [
                'id' => 18, 'name' => 'Maintenance', 'username' => 'maintenance', 'email' => 'maintenance@example.test',
                'is_maintenance' => $maintenance, 'primary_role' => 'user', 'app_role' => 'user',
            ]])]);
            $this->get(route('auth.sso.callback', ['code' => 'test-code']))->assertRedirect();
            $user = auth()->user();
            $this->assertSame($maintenance ? 'maintenance' : 'pegawai', $user->role);
            $this->assertSame($maintenance, $user->isAdmin());
            $this->assertSame($maintenance, $user->isSuperadmin());
            if ($maintenance) $this->assertSame('Maintenance', $user->getRoleLabel());
        }
    }
}
