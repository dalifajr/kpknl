<?php
// PHP, PortalHelpTest.php; Laravel/PHPUnit; portal visibility by actual permissions.

namespace Tests\Feature;

use App\Models\{Application, Role, User};
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_shows_only_assigned_apps_and_handles_maintenance(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', 'user')->first());
        $assigned = Application::create(['name' => 'SI-KEP Uji', 'slug' => 'si-kep-uji', 'url' => 'https://example.test',
            'redirect_uri' => 'https://example.test/callback', 'client_id' => 'assigned', 'client_secret' => 'secret', 'status' => 'active']);
        $other = Application::create(['name' => 'Aplikasi Tanpa Akses', 'slug' => 'other', 'url' => 'https://other.test',
            'redirect_uri' => 'https://other.test/callback', 'client_id' => 'other', 'client_secret' => 'secret', 'status' => 'active']);
        $user->applications()->attach($assigned, ['role' => 'user']);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Cari aplikasi')
            ->assertSee('SI-KEP Uji')->assertDontSee($other->name)->assertSee('Sesi Login')->assertSee('Panduan Portal');
        $assigned->update(['maintenance_mode' => true]);
        $this->get(route('dashboard'))->assertOk()->assertSee('Sedang dalam pemeliharaan.')
            ->assertDontSee(route('oauth.authorize', ['client_id' => 'assigned']), false);
    }

    public function test_technical_management_links_are_only_shown_to_maintenance(): void
    {
        $this->seed(RoleSeeder::class);
        foreach (['user', 'admin', 'superadmin', 'maintenance'] as $role) {
            $user = User::factory()->create(['status' => 'active']);
            $user->roles()->attach(Role::where('name', $role)->first());
            $response = $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Panduan Portal');
            if ($role === 'maintenance') {
                $response->assertSee('Kelola aplikasi')->assertSee('Kelola akses aplikasi');
            } else {
                $response->assertDontSee('Kelola aplikasi')->assertDontSee('Kelola akses aplikasi');
            }
        }
    }
}
