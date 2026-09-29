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
        $this->get(route('dashboard'))->assertSee('Tugas Induk')->assertSee('Membuat dan meninjau tugas');
        $this->get(route('calendar.index'))->assertSee('url = "' . url('reviews') . '/" + props.assignment_id;', false);
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
}
