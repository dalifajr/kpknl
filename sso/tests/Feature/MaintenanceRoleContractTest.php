<?php
// PHP, MaintenanceRoleContractTest.php; Laravel/PHPUnit; authenticated role contract.
namespace Tests\Feature;

use App\Models\{Application, OAuthToken, Role, User};
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceRoleContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_operator_role_overrides_lower_application_assignment(): void
    {
        $this->seed(RoleSeeder::class);
        foreach (['maintenance', 'superadmin'] as $role) {
            $user = User::factory()->create(['status' => 'active']);
            // The operator role is deliberately attached after a lower role.
            $user->roles()->attach(Role::whereIn('name', ['user', $role])->pluck('id'));
            $app = Application::create(['name' => $role, 'slug' => $role, 'url' => 'https://example.test',
                'redirect_uri' => 'https://example.test/callback', 'client_id' => $role, 'client_secret' => 'test', 'status' => 'active']);
            $user->applications()->attach($app, ['role' => 'user']);
            OAuthToken::create(['user_id' => $user->id, 'application_id' => $app->id,
                'access_token' => $role, 'expires_at' => now()->addHour(), 'revoked' => false]);
            $this->withToken($role)->getJson('/api/user')->assertOk()
                ->assertJsonPath('data.primary_role', $role)->assertJsonPath('data.app_role', $role)
                ->assertJsonPath('data.is_' . $role, true);
        }
    }

    public function test_ordinary_assignment_remains_scoped_to_the_token_application(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['status' => 'active', 'username' => 'maintenance']);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        foreach (['user', 'admin'] as $appRole) {
            $app = Application::create(['name' => $appRole, 'slug' => $appRole, 'url' => 'https://example.test',
                'redirect_uri' => 'https://example.test/callback', 'client_id' => $appRole, 'client_secret' => 'test', 'status' => 'active']);
            $user->applications()->attach($app, ['role' => $appRole]);
            OAuthToken::create(['user_id' => $user->id, 'application_id' => $app->id,
                'access_token' => $appRole, 'expires_at' => now()->addHour(), 'revoked' => false]);
            $this->withToken($appRole)->getJson('/api/user')->assertOk()
                ->assertJsonPath('data.app_role', $appRole)->assertJsonPath('data.is_maintenance', false);
        }
    }
}
