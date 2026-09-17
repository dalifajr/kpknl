<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ChangeLog;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\GoogleSheetSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RollbackAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        AppSetting::set('google_sheet_url', GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL);
    }

    /**
     * Test superadmin can rollback an update change log
     */
    public function test_superadmin_can_rollback_update_change_log(): void
    {
        $admin = User::factory()->create([
            'username' => 'superadmin_test',
            'role' => 'superadmin',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 1,
            'nip' => '198501012010121001',
            'nama' => 'Budi Santoso',
            'nama_jabatan_raw' => 'Kepala Seksi Pelayanan Lelang',
            'is_active' => true,
        ]);

        $payloadBefore = $pegawai->toArray();

        // Simulate update
        $pegawai->nama_jabatan_raw = 'Pelelang Ahli Pertama';
        $pegawai->save();
        $payloadAfter = $pegawai->toArray();

        $log = ChangeLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'pegawai_id' => $pegawai->id,
            'nama_pegawai' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'action' => 'update',
            'description' => 'Pembaruan jabatan pegawai',
            'changes' => [
                'nama_jabatan_raw' => [
                    'before' => 'Kepala Seksi Pelayanan Lelang',
                    'after' => 'Pelelang Ahli Pertama',
                ],
            ],
            'payload_before' => $payloadBefore,
            'payload_after' => $payloadAfter,
            'sync_status' => 'pending',
        ]);

        // Rollback request
        $response = $this->actingAs($admin)
            ->postJson(route('change_log.rollback', $log->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Verify pegawai data restored
        $pegawai->refresh();
        $this->assertEquals('Kepala Seksi Pelayanan Lelang', $pegawai->nama_jabatan_raw);

        // Verify new rollback log created
        $this->assertDatabaseHas('change_logs', [
            'action' => 'rollback',
            'nama_pegawai' => 'Budi Santoso',
        ]);
    }

    /**
     * Test superadmin can rollback a create change log (deletes created pegawai)
     */
    public function test_superadmin_can_rollback_create_change_log(): void
    {
        $admin = User::factory()->create([
            'username' => 'superadmin_create_test',
            'role' => 'superadmin',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 2,
            'nip' => '199001012015011002',
            'nama' => 'Pegawai Baru Ditambah',
            'nama_jabatan_raw' => 'Pelaksana',
            'is_active' => true,
        ]);

        $log = ChangeLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'pegawai_id' => $pegawai->id,
            'nama_pegawai' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'action' => 'create',
            'description' => 'Penambahan personil baru',
            'payload_after' => $pegawai->toArray(),
            'sync_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('change_log.rollback', $log->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Verify pegawai deleted from database
        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
            'nip' => '199001012015011002',
        ]);

        // Verify rollback log created
        $this->assertDatabaseHas('change_logs', [
            'action' => 'rollback',
            'nama_pegawai' => 'Pegawai Baru Ditambah',
        ]);
    }

    /**
     * Test regular user cannot rollback change logs
     */
    public function test_regular_user_cannot_rollback_change_log(): void
    {
        $user = User::factory()->create([
            'username' => 'pegawai_biasa',
            'role' => 'pegawai',
        ]);

        $log = ChangeLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'nama_pegawai' => 'Target Pegawai',
            'nip' => '198501012010121002',
            'action' => 'update',
            'description' => 'Test log',
            'sync_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('change_log.rollback', $log->id));

        $response->assertStatus(403);
    }

    /**
     * Test permission detection for Read Only (valid CSV, no webhook)
     */
    public function test_spreadsheet_permission_detection_read_only(): void
    {
        $sampleCsv = "NO,NAMA,NIP,JABATAN\n1,Ahmad Yani,198001012005011001,Pelelang";
        Http::fake([
            '*export?format=csv*' => Http::response($sampleCsv, 200, ['Content-Type' => 'text/csv']),
        ]);

        $syncService = app(GoogleSheetSyncService::class);
        $result = $syncService->checkPermissions(null, '');

        $this->assertTrue($result['can_read']);
        $this->assertFalse($result['can_write']);
        $this->assertEquals('read_only', $result['permission']);
    }

    /**
     * Test permission detection for Read & Write (valid CSV and responding webhook)
     */
    public function test_spreadsheet_permission_detection_read_and_write(): void
    {
        $sampleCsv = "NO,NAMA,NIP,JABATAN\n1,Ahmad Yani,198001012005011001,Pelelang";
        Http::fake([
            '*export?format=csv*' => Http::response($sampleCsv, 200, ['Content-Type' => 'text/csv']),
            'https://script.google.com/test' => Http::response(['status' => 'ok'], 200),
        ]);

        $syncService = app(GoogleSheetSyncService::class);
        $result = $syncService->checkPermissions(null, 'https://script.google.com/test');

        $this->assertTrue($result['can_read']);
        $this->assertTrue($result['can_write']);
        $this->assertEquals('read_and_write', $result['permission']);
    }

    /**
     * Test permission detection for No Access (private / Google redirect to login)
     */
    public function test_spreadsheet_permission_detection_no_access(): void
    {
        Http::fake([
            '*export?format=csv*' => Http::response('<html><body>ServiceLogin accounts.google.com</body></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $syncService = app(GoogleSheetSyncService::class);
        $result = $syncService->checkPermissions(null, '');

        $this->assertFalse($result['can_read']);
        $this->assertEquals('no_access', $result['permission']);
    }

    /**
     * Test Smart Upsert preserves avatar_url and local IDs
     */
    public function test_smart_upsert_preserves_avatar_and_local_data(): void
    {
        $existing = Pegawai::create([
            'no_urut' => 1,
            'nip' => '198001012005011001',
            'nama' => 'Ahmad Yani Lama',
            'nama_jabatan_raw' => 'Pelelang Ahli Pertama',
            'avatar_url' => 'avatars/avatar_1_custom.jpg',
            'is_active' => true,
        ]);

        $existingId = $existing->id;

        // Mock CSV response from Google Sheet with updated name
        $csvData = "NO,NAMA,NIP,JABATAN,PER.JABATAN,PANGKAT / GOLONGAN,GRADING\n";
        $csvData .= "1,Ahmad Yani Diperbarui,198001012005011001,Pelelang Ahli Muda,Pelelang,Penata (III/c),9\n";

        Http::fake([
            '*export?format=csv*' => Http::response($csvData, 200, ['Content-Type' => 'text/csv']),
        ]);

        $syncService = app(GoogleSheetSyncService::class);
        $syncResult = $syncService->sync();

        $this->assertTrue($syncResult['success']);

        // Assert record updated in place, avatar NOT lost!
        $existing->refresh();
        $this->assertEquals($existingId, $existing->id);
        $this->assertEquals('Ahmad Yani Diperbarui', $existing->nama);
        $this->assertEquals('avatars/avatar_1_custom.jpg', $existing->avatar_url);
    }

    /**
     * Test check-spreadsheet-permission AJAX route endpoint
     */
    public function test_check_spreadsheet_permission_endpoint(): void
    {
        $user = User::factory()->create();

        $sampleCsv = "NO,NAMA,NIP,JABATAN\n1,Ahmad Yani,198001012005011001,Pelelang";
        Http::fake([
            '*export?format=csv*' => Http::response($sampleCsv, 200, ['Content-Type' => 'text/csv']),
        ]);

        $response = $this->actingAs($user)->postJson(route('settings.check_permission'), [
            'sheet_url' => GoogleSheetSyncService::DEFAULT_SPREADSHEET_URL,
            'webhook_url' => '',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'permission' => 'read_only',
                'can_read' => true,
                'can_write' => false,
            ]);
    }
}
