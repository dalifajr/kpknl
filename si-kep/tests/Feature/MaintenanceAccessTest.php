<?php
// PHP, MaintenanceAccessTest.php; Laravel/PHPUnit; preserve maintenance through callback.
namespace Tests\Feature;

use App\Models\User;
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

    public function test_callback_recovers_privileges_and_later_demotion_is_respected(): void
    {
        Http::preventStrayRequests();
        $user = User::factory()->create(['sso_user_id' => 18, 'role' => 'user']);
        foreach ([true, false] as $maintenance) {
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['*/api/sso/verify-session' => Http::response(['valid' => true]),
                '*/oauth/token' => Http::response(['access_token' => 'test-token']),
                '*/api/user' => Http::response(['data' => ['id' => 18, 'name' => $user->name, 'username' => 'maintenance',
                    'email' => $user->email, 'is_maintenance' => $maintenance, 'primary_role' => 'user', 'app_role' => 'user']]),
            ]);
            $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect();
            $this->assertSame($maintenance ? 'maintenance' : 'user', $user->fresh()->role);
            $this->get(route('change_log.index'))->assertStatus($maintenance ? 200 : 403);
            $this->get(route('pegawai.form_data'))->assertStatus($maintenance ? 200 : 403);
        }
    }
}
