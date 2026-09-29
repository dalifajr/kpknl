<?php
// PHP, MaintenanceAccessTest.php; Laravel/PHPUnit; operator and ordinary access.
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
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

    public function test_maintenance_callback_opens_activity_log_but_regular_user_is_denied(): void
    {
        foreach ([true, false] as $maintenance) {
            $profile = (new SocialiteUser)->setRaw(['roles' => $maintenance ? ['user', 'maintenance'] : ['user'],
                'primary_role' => 'user', 'app_role' => 'user', 'username' => 'maintenance'])
                ->map(['id' => '18', 'name' => 'Maintenance', 'email' => 'maintenance@example.test'])->setToken('test-token');
            Socialite::shouldReceive('driver->user')->once()->andReturn($profile);
            $this->get(route('auth.callback'))->assertRedirect(route('dashboard'));
            $this->assertSame($maintenance ? 'maintenance' : 'user', auth()->user()->role);
            $this->get(route('activity-logs.index'))->assertStatus($maintenance ? 200 : 403);
        }
    }
}
