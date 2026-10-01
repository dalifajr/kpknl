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

    public function test_callback_safely_updates_user_when_sso_id_changes_preventing_duplicate_email_error(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Maintenance & Sistem IT',
            'username' => 'maintenance',
            'email' => 'maintenance@kpknl.go.id',
            'sso_user_id' => 18,
            'role' => 'user',
        ]);

        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => true]),
            '*/oauth/token' => Http::response(['access_token' => 'test-token']),
            '*/api/user' => Http::response([
                'data' => [
                    'id' => 2, // SSO ID changed from 18 to 2
                    'name' => 'Tim Maintenance KPKNL Palembang',
                    'username' => 'maintenance',
                    'email' => 'maintenance@kpknl.go.id',
                    'is_maintenance' => true,
                    'primary_role' => 'maintenance',
                ],
            ]),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));
        $response->assertRedirect(route('dashboard'));

        $this->assertSame(1, User::count());
        $freshUser = $user->fresh();
        $this->assertSame(2, $freshUser->sso_user_id);
        $this->assertSame('Tim Maintenance KPKNL Palembang', $freshUser->name);
        $this->assertSame('maintenance', $freshUser->role);
    }

    public function test_callback_safely_clears_stale_sso_id_collision(): void
    {
        $staleUser = User::factory()->create([
            'name' => 'User Lama',
            'username' => 'user_lama',
            'email' => 'lama@kpknl.go.id',
            'sso_user_id' => 2,
        ]);

        $maintenanceUser = User::factory()->create([
            'name' => 'Tim Maintenance',
            'username' => 'maintenance',
            'email' => 'maintenance@kpknl.go.id',
            'sso_user_id' => 18,
        ]);

        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake([
            '*/api/sso/verify-session' => Http::response(['valid' => true]),
            '*/oauth/token' => Http::response(['access_token' => 'test-token']),
            '*/api/user' => Http::response([
                'data' => [
                    'id' => 2,
                    'name' => 'Tim Maintenance KPKNL Palembang',
                    'username' => 'maintenance',
                    'email' => 'maintenance@kpknl.go.id',
                    'is_maintenance' => true,
                    'primary_role' => 'maintenance',
                ],
            ]),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));
        $response->assertRedirect(route('dashboard'));

        $this->assertNull($staleUser->fresh()->sso_user_id);
        $this->assertSame(2, $maintenanceUser->fresh()->sso_user_id);
    }
}
