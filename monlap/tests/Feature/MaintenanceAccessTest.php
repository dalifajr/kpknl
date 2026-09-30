<?php
// PHP, MaintenanceAccessTest.php; Laravel/PHPUnit; callback and privileged task operations.
namespace Tests\Feature;

use App\Models\{Task, TaskAssignment, User};
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

    public function test_callback_restores_maintenance_and_management_access(): void
    {
        $user = User::create(['sso_id' => '18', 'name' => 'Maintenance', 'email' => 'maintenance@example.test', 'role' => 'user']);
        $profile = (new SocialiteUser)->setRaw(['is_maintenance' => true, 'app_role' => 'user', 'username' => 'maintenance'])
            ->map(['id' => '18', 'name' => 'Maintenance', 'email' => $user->email])->setToken('test-token');
        Socialite::shouldReceive('driver->user')->once()->andReturn($profile);
        $this->get(route('auth.callback'))->assertRedirect(route('dashboard'));
        $this->assertSame('maintenance', $user->fresh()->role);
        foreach (['tasks.index', 'tasks.create', 'reviews.index', 'calendar.index', 'dashboard'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('calendar.index'))
            ->assertSee("['admin', 'superadmin', 'maintenance'].includes(userRole)", false)
            ->assertSee('url = "' . url('reviews') . '/" + props.assignment_id;', false);
        $this->get(route('dashboard'))
            ->assertSee('Maintenance')
            ->assertSee('build');

        $freshUser = $user->fresh();
        $this->assertTrue($freshUser->isMaintenance());
        $this->assertTrue($freshUser->isSuperadmin());
        $this->assertTrue($freshUser->isAdmin());
        $this->assertFalse($freshUser->isUser());
        $this->assertTrue($freshUser->hasRole('maintenance'));
        $this->assertTrue($freshUser->hasRole(['admin', 'maintenance']));

        $task = Task::create(['title' => 'Uji', 'period_type' => 'bulanan']);
        $assignment = TaskAssignment::create(['task_id' => $task->id, 'user_id' => $user->id, 'status' => 'acc']);
        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson(route('reviews.process', $assignment), ['action' => 'batal_acc'])->assertOk();
        $this->assertSame('submitted', $assignment->fresh()->status);
    }

    public function test_user_named_maintenance_does_not_gain_operator_rights(): void
    {
        $profile = (new SocialiteUser)->setRaw(['primary_role' => 'user', 'app_role' => 'user', 'username' => 'maintenance'])
            ->map(['id' => '19', 'name' => 'Ordinary', 'email' => 'ordinary@example.test'])->setToken('test-token');
        Socialite::shouldReceive('driver->user')->once()->andReturn($profile);
        $this->get(route('auth.callback'))->assertRedirect(route('dashboard'));
        $this->assertSame('user', auth()->user()->role);
        $this->get(route('tasks.index'))->assertForbidden();
        $this->get(route('reviews.index'))->assertForbidden();
    }

    public function test_callback_handles_existing_email_with_different_or_colliding_sso_id(): void
    {
        // Kondisi bentrok: user target sudah ada by email, tetapi ada record lain yang memegang sso_id 18
        $collidingUser = User::create(['sso_id' => '18', 'name' => 'Wrong Owner', 'email' => 'wrong@example.test', 'role' => 'user']);
        $legitMaintenance = User::create(['sso_id' => null, 'name' => 'Tim Maintenance', 'email' => 'maintenance@kpknl.go.id', 'role' => 'user']);

        $profile = (new SocialiteUser)->setRaw(['is_maintenance' => true, 'app_role' => 'maintenance', 'username' => 'maintenance'])
            ->map(['id' => '18', 'name' => 'Tim Maintenance KPKNL Palembang', 'email' => 'maintenance@kpknl.go.id'])->setToken('test-token');
        Socialite::shouldReceive('driver->user')->once()->andReturn($profile);

        $this->get(route('auth.callback'))->assertRedirect(route('dashboard'));

        // Harus berhasil login sebagai legitMaintenance dengan sso_id 18 dan role maintenance
        $this->assertSame($legitMaintenance->id, auth()->id());
        $this->assertSame('18', $legitMaintenance->fresh()->sso_id);
        $this->assertSame('maintenance', $legitMaintenance->fresh()->role);
        $this->assertNull($collidingUser->fresh()->sso_id);
    }
}
