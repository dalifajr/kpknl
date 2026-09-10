<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Application;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperadminSeeder;

class SSOTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(SuperadminSeeder::class);
    }

    public function test_superadmin_can_login_with_mardanus_credentials()
    {
        $response = $this->post('/login', [
            'login' => 'mardanus',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertTrue($user->isSuperadmin());
    }

    public function test_password_policy_nist_validation()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        // Weak password should fail
        $response = $this->actingAs($superadmin)->post('/admin/users', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@kpknl.go.id',
            'password' => 'weakpass',
            'role_id' => 3,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_oauth_authorization_and_token_exchange()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        $app = Application::create([
            'name' => 'App Test',
            'slug' => 'app-test',
            'url' => 'http://localhost/app-test',
            'redirect_uri' => 'http://localhost/app-test/callback',
            'client_id' => 'client_test123',
            'client_secret' => 'secret_test123',
            'status' => 'active',
        ]);

        // OAuth Authorize endpoint redirect
        $response = $this->actingAs($superadmin)->get("/oauth/authorize?client_id={$app->client_id}");
        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('code=', $redirectUrl);

        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $query);
        $code = $query['code'];

        // Token Exchange endpoint
        $tokenResponse = $this->postJson('/oauth/token', [
            'code' => $code,
            'client_id' => $app->client_id,
            'client_secret' => $app->client_secret,
        ]);

        $tokenResponse->assertStatus(200);
        $tokenResponse->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in']);

        $accessToken = $tokenResponse->json('access_token');

        // UserInfo API endpoint
        $userInfoResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->getJson('/api/user');

        $userInfoResponse->assertStatus(200);
        $userInfoResponse->assertJsonPath('data.username', 'mardanus');
    }

    public function test_session_termination_logs_out_user()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        $loginSession = \App\Models\LoginSession::create([
            'user_id' => $superadmin->id,
            'session_id' => 'fake_session_123',
            'ip_address' => '127.0.0.1',
            'browser' => 'Chrome',
            'browser_version' => '120.0',
            'platform' => 'Windows',
            'device_type' => 'desktop',
            'device_model' => 'Desktop PC',
            'is_active' => true,
            'last_activity_at' => now(),
            'login_at' => now(),
        ]);

        // Superadmin terminates the session
        $response = $this->actingAs($superadmin)->delete("/login-sessions/{$loginSession->id}");
        $response->assertRedirect();


        $this->assertDatabaseHas('login_sessions', [
            'id' => $loginSession->id,
            'is_active' => false,
        ]);
    }

    public function test_superadmin_cannot_change_own_role()
    {
        $superadmin = User::where('username', 'mardanus')->first();
        $userRoleId = Role::where('name', 'user')->first()->id;

        // Try to update own role to 'user'
        $response = $this->actingAs($superadmin)->put("/admin/users/{$superadmin->id}", [
            'name' => $superadmin->name,
            'username' => $superadmin->username,
            'email' => $superadmin->email,
            'role_id' => $userRoleId,
            'status' => 'active',
        ]);

        $response->assertSessionHas('error');
        $this->assertTrue($superadmin->fresh()->isSuperadmin());
    }

    public function test_maintenance_mode_blocks_normal_users_allows_superadmin()
    {
        // Activate maintenance mode
        \App\Models\Setting::set('maintenance_mode', 'active');
        \App\Models\Setting::set('maintenance_message', 'Sedang maintenance');

        // Superadmin can log in
        $response = $this->post('/login', [
            'login' => 'mardanus',
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/dashboard');

        // Normal user attempt to login gets blocked
        $normalUser = User::create([
            'name' => 'Normal User',
            'username' => 'normaluser',
            'email' => 'normal@kpknl.go.id',
            'password' => bcrypt('StrongPass123!'),
            'status' => 'active',
        ]);
        $normalUser->roles()->attach(Role::where('name', 'user')->first()->id);

        $this->post('/logout');

        $userResponse = $this->post('/login', [
            'login' => 'normaluser',
            'password' => 'StrongPass123!',
        ]);
        $userResponse->assertSessionHasErrors(['login']);
    }

    public function test_notifications_page_and_mark_all_read()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        // Access notifications page
        $response = $this->actingAs($superadmin)->get('/notifications');
        $response->assertStatus(200);
        $response->assertSee('Pusat Notifikasi System');

        // Mark all read
        $markResponse = $this->actingAs($superadmin)->post('/notifications/mark-all-read');
        $markResponse->assertSessionHas('success');
    }

    public function test_user_import_flow()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        // 1. Download Template (.xlsx)
        $templateResponse = $this->actingAs($superadmin)->get('/admin/users/import-template');
        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 1b. Verify Uploading the Downloaded .xlsx Template Works Without 500 Error / Path empty ValueError
        $xlsxFile = \Illuminate\Http\UploadedFile::fake()->createWithContent('template_import_user_sso.xlsx', $templateResponse->getContent());
        $xlsxUploadResponse = $this->actingAs($superadmin)->postJson('/admin/users/import-upload', [
            'file' => $xlsxFile,
        ]);
        $xlsxUploadResponse->assertStatus(200);
        $xlsxUploadResponse->assertJsonStructure(['success', 'job_id', 'redirect_url']);

        // 2. Upload CSV File
        $csvContent = "name,username,email,role,status,password\n" .
                      "User Test One,test.one,test1@kpknl.go.id,user,active,Password123!\n" .
                      "User Test Two,test.two,test2@kpknl.go.id,admin,active,Password123!\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('import_test.csv', $csvContent);

        $uploadResponse = $this->actingAs($superadmin)->postJson('/admin/users/import-upload', [
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(200);
        $uploadResponse->assertJsonStructure(['success', 'job_id', 'redirect_url']);

        $jobId = $uploadResponse->json('job_id');

        // 3. Access Progress Page
        $progressResponse = $this->actingAs($superadmin)->get("/admin/users/import-progress/{$jobId}");
        $progressResponse->assertStatus(200);
        $progressResponse->assertSee('Progress Import User Excel');

        // 4. Run Batch Step
        $stepResponse = $this->actingAs($superadmin)->postJson("/admin/users/import-step/{$jobId}", [
            'batch_size' => 5,
        ]);
        $stepResponse->assertStatus(200);
        $this->assertEquals('completed', $stepResponse->json('status'));
        $this->assertEquals(2, $stepResponse->json('success_count'));

        // Verify Users in DB
        $this->assertDatabaseHas('users', ['username' => 'test.one']);
        $this->assertDatabaseHas('users', ['username' => 'test.two']);
    }

    public function test_user_import_duplicate_resolution_suffix()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        // Upload CSV with existing username & email 'mardanus'
        $csvContent = "name,username,email,role,status,password\n" .
                      "Mardanus Duplicate,mardanus,mardanus@kpknl.go.id,admin,active,Password123!\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('duplicate_test.csv', $csvContent);

        $uploadResponse = $this->actingAs($superadmin)->postJson('/admin/users/import-upload', [
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(200);
        $this->assertTrue($uploadResponse->json('has_duplicates'));
        $jobId = $uploadResponse->json('job_id');

        // Choose 'suffix' resolution
        $resolveResponse = $this->actingAs($superadmin)->postJson("/admin/users/import-resolve/{$jobId}", [
            'action' => 'suffix',
        ]);
        $resolveResponse->assertStatus(200);

        // Execute batch step
        $stepResponse = $this->actingAs($superadmin)->postJson("/admin/users/import-step/{$jobId}", [
            'batch_size' => 5,
        ]);
        $stepResponse->assertStatus(200);
        $this->assertEquals('completed', $stepResponse->json('status'));
        $this->assertEquals(1, $stepResponse->json('success_count'));

        // Verify suffixed username & email created in DB
        $this->assertDatabaseHas('users', [
            'username' => 'mardanus_1',
            'email' => 'mardanus_1@kpknl.go.id',
            'name' => 'Mardanus Duplicate',
        ]);
    }

    public function test_user_import_soft_deleted_duplicate()
    {
        $superadmin = User::where('username', 'mardanus')->first();

        // Create and soft-delete a test user
        $user = User::create([
            'name' => 'Soft Deleted User',
            'username' => 'soft.user',
            'email' => 'soft.user@kpknl.go.id',
            'password' => \Illuminate\Support\Facades\Hash::make('Password123!'),
            'status' => 'inactive',
        ]);
        $user->delete();

        // Try importing soft.user
        $csvContent = "name,username,email,role,status,password\n" .
                      "New Soft User,soft.user,soft.user@kpknl.go.id,user,active,Password123!\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('soft_duplicate.csv', $csvContent);

        $uploadResponse = $this->actingAs($superadmin)->postJson('/admin/users/import-upload', [
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(200);
        // Ensure soft-deleted user is NOT flagged as an active duplicate
        $this->assertFalse($uploadResponse->json('has_duplicates'));

        $jobId = $uploadResponse->json('job_id');

        // Execute step directly (no resolution prompt required)
        $stepResponse = $this->actingAs($superadmin)->postJson("/admin/users/import-step/{$jobId}", [
            'batch_size' => 5,
        ]);
        $stepResponse->assertStatus(200);
        $this->assertEquals('completed', $stepResponse->json('status'));
        $this->assertEquals(1, $stepResponse->json('success_count'));

        // Verify soft-deleted user was restored and updated in DB
        $this->assertDatabaseHas('users', [
            'username' => 'soft.user',
            'email' => 'soft.user@kpknl.go.id',
            'name' => 'New Soft User',
            'deleted_at' => null,
        ]);
    }
}





