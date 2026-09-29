<?php
// PHP, MaintenanceAccessTest.php; Laravel/PHPUnit; operator role precedence and safe fallback.
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

    public function test_maintenance_overrides_peminjam_assignment_and_displays_identity(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/oauth/token' => Http::response(['access_token' => 'test-token', 'user' => [
            'id' => 18, 'name' => 'Maintenance', 'username' => 'maintenance', 'email' => 'maintenance@example.test',
            'primary_role' => 'maintenance', 'app_role' => 'peminjam',
        ]])]);
        $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));
        $this->assertTrue(auth()->user()->isAdmin());
        $this->assertSame('maintenance', session('sso_role'));
        $this->get(route('dashboard'))->assertOk()->assertSee('Maintenance · Administrator Arsip')->assertSee('Maintenance SSO aktif.');
        $this->get(route('validasi.index'))->assertOk();
    }

    public function test_explicit_peminjam_assignment_beats_global_admin_and_username(): void
    {
        Http::fake(['*/oauth/token' => Http::response(['access_token' => 'test-token', 'user' => [
            'id' => 19, 'name' => 'Ordinary', 'username' => 'maintenance', 'email' => 'ordinary@example.test',
            'primary_role' => 'admin', 'app_role' => 'peminjam',
        ]])]);
        $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));
        $this->assertSame('peminjam', auth()->user()->role);
        $this->assertFalse(auth()->user()->isAdmin());
        $this->get(route('dashboard'))->assertDontSee('Maintenance SSO aktif.');
    }

    public function test_profile_without_access_token_cannot_create_a_maintenance_session(): void
    {
        Http::fake(['*/oauth/token' => Http::response(['user' => [
            'id' => 18, 'name' => 'Maintenance', 'is_maintenance' => true,
        ]])]);
        $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect(route('sso.redirect'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
