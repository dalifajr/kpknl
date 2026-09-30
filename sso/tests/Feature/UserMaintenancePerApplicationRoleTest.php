<?php
// PHP, UserMaintenancePerApplicationRoleTest.php; Laravel/PHPUnit; test maintenance per-application role management.

namespace Tests\Feature;

use App\Models\Application;
use App\Models\OAuthToken;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMaintenancePerApplicationRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $maintenanceUser;
    private Application $app1;
    private Application $app2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $maintenanceRole = Role::where('name', 'maintenance')->first();
        $this->maintenanceUser = User::factory()->create([
            'status' => 'active',
            'username' => 'maint_test_user',
        ]);
        $this->maintenanceUser->roles()->attach($maintenanceRole->id);

        $this->app1 = Application::create([
            'name' => 'SI-KEP Kepegawaian',
            'slug' => 'si-kep',
            'url' => 'https://sikep.test',
            'redirect_uri' => 'https://sikep.test/callback',
            'client_id' => 'client_sikep',
            'client_secret' => 'secret_sikep',
            'status' => 'active',
        ]);

        $this->app2 = Application::create([
            'name' => 'Peminjaman Berkas Lelang',
            'slug' => 'peminjaman-lelang',
            'url' => 'https://lelang.test',
            'redirect_uri' => 'https://lelang.test/callback',
            'client_id' => 'client_lelang',
            'client_secret' => 'secret_lelang',
            'status' => 'active',
        ]);
    }

    public function test_maintenance_can_create_user_with_per_app_roles(): void
    {
        $userRole = Role::where('name', 'user')->first();

        $response = $this->actingAs($this->maintenanceUser)
            ->post(route('admin.users.store'), [
                'name' => 'Budi Pratama',
                'username' => 'budi_pratama',
                'email' => 'budi@kpknl.test',
                'password' => 'Password!123',
                'role_id' => $userRole->id,
                'status' => 'active',
                'applications' => [$this->app1->id, $this->app2->id],
                'app_roles' => [
                    $this->app1->id => 'operator',
                    $this->app2->id => 'pelelang',
                ],
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('username', 'budi_pratama')->first();
        $this->assertNotNull($createdUser);

        $this->assertDatabaseHas('user_application', [
            'user_id' => $createdUser->id,
            'application_id' => $this->app1->id,
            'role' => 'operator',
        ]);

        $this->assertDatabaseHas('user_application', [
            'user_id' => $createdUser->id,
            'application_id' => $this->app2->id,
            'role' => 'pelelang',
        ]);
    }

    public function test_maintenance_can_update_user_per_app_roles(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $user = User::create([
            'name' => 'User Update Test',
            'username' => 'user_update_test',
            'email' => 'user_update@kpknl.test',
            'password' => bcrypt('Password!123'),
            'status' => 'active',
        ]);
        $user->roles()->attach($userRole->id);
        $user->applications()->attach($this->app1->id, ['role' => 'user']);

        $response = $this->actingAs($this->maintenanceUser)
            ->put(route('admin.users.update', $user->id), [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role_id' => $userRole->id,
                'status' => 'active',
                'applications' => [$this->app1->id],
                'app_roles' => [
                    $this->app1->id => 'admin',
                ],
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('user_application', [
            'user_id' => $user->id,
            'application_id' => $this->app1->id,
            'role' => 'admin',
        ]);
    }

    public function test_maintenance_can_update_user_role_from_application_show_page(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->applications()->attach($this->app1->id, ['role' => 'user']);

        $response = $this->actingAs($this->maintenanceUser)
            ->put(route('admin.applications.update-user-role', [
                'application' => $this->app1->id,
                'user' => $user->id,
            ]), [
                'role' => 'operator',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_application', [
            'user_id' => $user->id,
            'application_id' => $this->app1->id,
            'role' => 'operator',
        ]);
    }

    public function test_maintenance_can_assign_unassigned_user_with_role_from_application_show_page(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->maintenanceUser)
            ->post(route('admin.applications.assign-users', $this->app2->id), [
                'user_ids' => [$user->id],
                'role' => 'peminjam',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_application', [
            'user_id' => $user->id,
            'application_id' => $this->app2->id,
            'role' => 'peminjam',
        ]);
    }

    public function test_oauth_user_info_reflects_app_role_per_application_token(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->roles()->attach($userRole->id);

        $targetUser->applications()->attach($this->app1->id, ['role' => 'operator']);
        $targetUser->applications()->attach($this->app2->id, ['role' => 'pelelang']);

        // Token 1 for app 1
        OAuthToken::create([
            'user_id' => $targetUser->id,
            'application_id' => $this->app1->id,
            'access_token' => 'token_app1',
            'expires_at' => now()->addHour(),
            'revoked' => false,
        ]);

        // Token 2 for app 2
        OAuthToken::create([
            'user_id' => $targetUser->id,
            'application_id' => $this->app2->id,
            'access_token' => 'token_app2',
            'expires_at' => now()->addHour(),
            'revoked' => false,
        ]);

        // Verify API user info for app 1 returns operator
        $this->withToken('token_app1')->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.app_role', 'operator');

        // Verify API user info for app 2 returns pelelang
        $this->withToken('token_app2')->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.app_role', 'pelelang');
    }
}
