<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $maintenanceUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $userRole = Role::where('name', 'user')->first();
        $this->user = User::factory()->create([
            'status' => 'active',
            'username' => 'pegawai_test',
        ]);
        $this->user->roles()->attach($userRole->id);

        $maintRole = Role::where('name', 'maintenance')->first();
        $this->maintenanceUser = User::factory()->create([
            'status' => 'active',
            'username' => 'maint_test',
        ]);
        $this->maintenanceUser->roles()->attach($maintRole->id);

        Application::create([
            'name' => 'SI-KEP Kepegawaian',
            'slug' => 'si-kep',
            'url' => 'http://sikep.test',
            'redirect_uri' => 'http://sikep.test/callback',
            'client_id' => 'client_test_sikep',
            'client_secret' => 'secret_test_sikep',
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_documentation(): void
    {
        $response = $this->get('/documentation');
        $response->assertRedirect(route('login'));

        $pdfResponse = $this->get('/documentation/download-pdf');
        $pdfResponse->assertRedirect(route('login'));
    }

    public function test_authenticated_user_only_sees_their_own_role_documentation(): void
    {
        $response = $this->actingAs($this->user)->get('/documentation');

        $response->assertOk();
        $response->assertViewIs('documentation.index');
        $response->assertSee('Buku Panduan &amp; Dokumentasi Fitur SSO', false);
        $response->assertSee('Panduan Pegawai');
        $response->assertSee('SI-KEP Kepegawaian');

        // Must NOT see guides for other roles
        $response->assertDontSee('section-admin', false);
        $response->assertDontSee('section-maintenance', false);
        $response->assertDontSee('section-superadmin', false);
        $response->assertDontSee('Maintenance Orchestrator');
    }

    public function test_user_cannot_view_other_roles_via_query_parameter(): void
    {
        // Regular user attempts to access maintenance guide via URL parameter
        $response = $this->actingAs($this->user)->get('/documentation?role=maintenance');

        $response->assertOk();
        // Still locked to Pegawai/User only
        $response->assertSee('Panduan Pegawai');
        $response->assertDontSee('section-maintenance', false);
        $response->assertDontSee('Maintenance Orchestrator');
    }

    public function test_maintenance_user_only_sees_maintenance_documentation(): void
    {
        $response = $this->actingAs($this->maintenanceUser)->get('/documentation');

        $response->assertOk();
        $response->assertSee('section-maintenance', false);
        $response->assertSee('Maintenance Orchestrator');

        // Must NOT see ordinary user guide section
        $response->assertDontSee('section-user', false);
        $response->assertDontSee('section-admin', false);
        $response->assertDontSee('section-superadmin', false);
    }

    public function test_user_downloads_only_their_own_role_pdf(): void
    {
        // Even if user specifies role=maintenance, it must strictly generate user PDF
        $response = $this->actingAs($this->user)->get('/documentation/download-pdf?role=maintenance');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('Panduan_SSO_KPKNL_Palembang_Pegawai_User_', $response->headers->get('Content-Disposition') ?? '');

        // Verify PDF signature (%PDF-)
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }

    public function test_maintenance_user_downloads_maintenance_pdf(): void
    {
        $response = $this->actingAs($this->maintenanceUser)->get('/documentation/download-pdf');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Panduan_SSO_KPKNL_Palembang_Tim_Maintenance_', $response->headers->get('Content-Disposition') ?? '');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }
}
