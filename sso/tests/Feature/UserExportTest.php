<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class UserExportTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $maintenance;
    private User $admin;
    private User $regularUser;
    private Application $app1;
    private Application $app2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $superadminRole = Role::where('name', 'superadmin')->first();
        $maintRole = Role::where('name', 'maintenance')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        // 1. Superadmin
        $this->superadmin = User::factory()->create([
            'status' => 'active',
            'username' => 'superadmin_test',
            'name' => 'Superadmin User',
            'email' => 'superadmin@kpknl.test',
        ]);
        $this->superadmin->roles()->attach($superadminRole->id);

        // 2. Maintenance
        $this->maintenance = User::factory()->create([
            'status' => 'active',
            'username' => 'maintenance_test',
            'name' => 'Maintenance Tech',
            'email' => 'maint@kpknl.test',
        ]);
        $this->maintenance->roles()->attach($maintRole->id);

        // 3. Admin Kepegawaian
        $this->admin = User::factory()->create([
            'status' => 'active',
            'username' => 'admin_test',
            'name' => 'Admin Kepegawaian',
            'email' => 'admin@kpknl.test',
        ]);
        $this->admin->roles()->attach($adminRole->id);

        // 4. Regular User
        $this->regularUser = User::factory()->create([
            'status' => 'active',
            'username' => 'pegawai_test',
            'name' => 'Budi Santoso',
            'email' => 'budi@kpknl.test',
        ]);
        $this->regularUser->roles()->attach($userRole->id);

        // Applications
        $this->app1 = Application::create([
            'name' => 'SI-KEP Kepegawaian',
            'slug' => 'si-kep',
            'url' => 'http://sikep.test',
            'redirect_uri' => 'http://sikep.test/callback',
            'client_id' => 'client_test_sikep',
            'client_secret' => 'secret_test_sikep',
            'status' => 'active',
        ]);

        $this->app2 = Application::create([
            'name' => 'Peminjaman Berkas Lelang',
            'slug' => 'peminjaman-lelang',
            'url' => 'http://lelang.test',
            'redirect_uri' => 'http://lelang.test/callback',
            'client_id' => 'client_test_lelang',
            'client_secret' => 'secret_test_lelang',
            'status' => 'active',
        ]);

        // Assign applications with specific roles
        $this->regularUser->applications()->attach($this->app1->id, ['role' => 'operator']);
        $this->regularUser->applications()->attach($this->app2->id, ['role' => 'peminjam']);
    }

    public function test_unauthenticated_user_cannot_access_export(): void
    {
        $response = $this->get(route('admin.users.export'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_export(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.users.export'));
        $response->assertForbidden();
    }

    public function test_admin_role_cannot_access_export(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.export'));
        $response->assertForbidden();
    }

    public function test_superadmin_can_export_users_to_xlsx(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.users.export', ['format' => 'xlsx']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('daftar_user_sso_', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition') ?? '');

        // Verify valid zip content
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        if (class_exists('ZipArchive')) {
            $tmp = tempnam(sys_get_temp_dir(), 'test_zip_');
            file_put_contents($tmp, $content);
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($tmp) === true);
            $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $this->assertStringContainsString('pegawai_test', $sheetXml);
            $this->assertStringContainsString('Budi Santoso', $sheetXml);
            $this->assertStringContainsString('SI-KEP Kepegawaian (Operator)', $sheetXml);
            $this->assertStringContainsString('Peminjaman Berkas Lelang (Peminjam)', $sheetXml);
            $zip->close();
            @unlink($tmp);
        }
    }

    public function test_maintenance_can_export_users_to_csv(): void
    {
        $response = $this->actingAs($this->maintenance)->get(route('admin.users.export', ['format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('daftar_user_sso_', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition') ?? '');

        $content = $response->getContent();
        // UTF-8 BOM check
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Header check
        $this->assertStringContainsString('Username', $content);
        $this->assertStringContainsString('Nama Lengkap', $content);
        $this->assertStringContainsString('Role Global SSO', $content);
        $this->assertStringContainsString('Aplikasi & Role Penugasan', $content);

        // Row detail check
        $this->assertStringContainsString('pegawai_test', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('SI-KEP Kepegawaian (Operator)', $content);
        $this->assertStringContainsString('Peminjaman Berkas Lelang (Peminjam)', $content);

        // Maintenance export includes maintenance user
        $this->assertStringContainsString('maintenance_test', $content);
    }

    public function test_superadmin_export_hides_maintenance_users(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.users.export', ['format' => 'csv']));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('pegawai_test', $content);
        $this->assertStringContainsString('superadmin_test', $content);
        // Maintenance account must NOT appear in superadmin export
        $this->assertStringNotContainsString('maintenance_test', $content);
    }

    public function test_export_respects_filter_parameters(): void
    {
        $response = $this->actingAs($this->maintenance)->get(route('admin.users.export', [
            'format' => 'csv',
            'search' => 'pegawai_test',
        ]));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('pegawai_test', $content);
        $this->assertStringNotContainsString('superadmin_test', $content);
        $this->assertStringNotContainsString('admin_test', $content);
    }
}
