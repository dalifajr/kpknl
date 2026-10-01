<?php
// PHP, DropdownSyncTest.php; Laravel/PHPUnit; isolated employee sync regression checks.

namespace Tests\Feature;

use App\Models\{AppSetting, ChangeLog, PangkatGolongan, Pegawai, UnitKerja, User};
use App\Services\GoogleSheetSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DropdownSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        AppSetting::set('google_sheet_webhook_url', 'https://script.google.com/sync-test');
    }

    public function test_edit_sends_selected_dropdown_values_and_original_nip(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $admin = User::factory()->create(['role' => 'superadmin']);
        $pegawai = Pegawai::create(['nama' => 'Pegawai Uji', 'nip' => '198501012010121001', 'is_active' => true]);
        $unit = UnitKerja::create(['kode_unit' => 'HI', 'nama_unit' => 'Seksi Hukum dan Informasi']);
        $pangkat = PangkatGolongan::create(['nama_pangkat' => 'Penata', 'golongan_ruang' => 'III/c', 'hirarki_level' => 3]);

        $this->actingAs($admin)->postJson(route('pegawai.update', $pegawai->id), [
            'nama' => 'Pegawai Uji', 'nip' => '198501012010121002', 'nama_jabatan_raw' => 'Pelaksana',
            'unit_kerja_id' => $unit->id, 'pangkat_golongan_id' => $pangkat->id,
            'jenis_kelamin' => 'p', 'status_gelar' => 'Sudah Clear (sesuai dengan HRIS)',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('pegawai', ['id' => $pegawai->id, 'per_jabatan' => $unit->nama_unit,
            'pangkat_golongan_raw' => 'Penata / III.c', 'jenis_kelamin' => 'P']);
        Http::assertSent(fn ($request) => $request['original_nip'] === '198501012010121001'
            && $request['nip'] === '198501012010121002'
            && $request['row_data']['PANGKAT / GOLONGAN'] === 'Penata / III.c'
            && $request['row_data']['PER.JABATAN'] === $unit->nama_unit
            && $request['row_data']['STATUS PENDIDIKAN DAN PENCANTUMAN GELAR AKADEMIK'] === 'Sudah Clear (sesuai dengan HRIS)');
        $this->assertDatabaseHas('change_logs', ['pegawai_id' => $pegawai->id, 'sync_status' => 'synced']);
    }

    public function test_http_200_without_explicit_confirmation_stays_pending(): void
    {
        foreach (['<html>Sign in to Google</html>', ['message' => 'unknown'], ['status' => 'error', 'message' => 'Dropdown tidak sesuai']] as $body) {
            Http::fake(['*' => Http::response($body)]);
            $log = ChangeLog::create(['nama_pegawai' => 'Uji', 'nip' => '198501012010121001',
                'action' => 'update', 'description' => 'Uji respons', 'payload_after' => ['nama' => 'Uji'], 'sync_status' => 'pending']);
            $result = app(GoogleSheetSyncService::class)->pushRowUpdate($log);
            $this->assertFalse($result['success']);
            $this->assertSame('pending', $log->fresh()->sync_status);
            $this->assertNotEmpty($log->fresh()->sync_error);
        }
    }

    public function test_mock_script_response_keeps_sync_pending_and_warns_admin(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success', 'message' => 'Data berhasil disimpan melalui POST API'])]);
        $log = ChangeLog::create([
            'nama_pegawai' => 'Pegawai Baru',
            'nip' => '199505052020121001',
            'action' => 'create',
            'description' => 'Penambahan personil baru',
            'payload_after' => ['nama' => 'Pegawai Baru', 'nip' => '199505052020121001'],
            'sync_status' => 'pending',
        ]);

        $result = app(GoogleSheetSyncService::class)->pushRowUpdate($log);

        $this->assertFalse($result['success']);
        $this->assertSame('pending', $log->fresh()->sync_status);
        $this->assertStringContainsString('GOOGLE_APPS_SCRIPT_WEBHOOK.js', $log->fresh()->sync_error);
    }

    public function test_create_pegawai_with_mock_webhook_returns_truthful_pending_message(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success', 'message' => 'Data berhasil disimpan melalui POST API'])]);
        $admin = User::factory()->create(['role' => 'admin']);
        $unit = UnitKerja::create(['kode_unit' => 'KI', 'nama_unit' => 'Seksi Kepatuhan Internal']);

        $res = $this->actingAs($admin)->postJson(route('pegawai.store'), [
            'nama' => 'Zaki Tester',
            'nip' => '199901012022031001',
            'unit_kerja_id' => $unit->id,
            'nama_jabatan_raw' => 'Pelaksana',
            'tipe_pegawai' => 'pns',
            'jenis_kelamin' => 'L',
        ]);

        $res->assertOk();
        $res->assertJson(['success' => true]);
        $this->assertStringContainsString('sinkronisasi spreadsheet tertunda', $res->json('message'));
        $this->assertDatabaseHas('pegawai', ['nama' => 'Zaki Tester', 'nip' => '199901012022031001']);
        $this->assertDatabaseHas('change_logs', ['nama_pegawai' => 'Zaki Tester', 'sync_status' => 'pending']);
    }

    public function test_help_matches_employee_edit_permissions(): void
    {
        foreach (['pegawai', 'superadmin', 'maintenance', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $html = view('components.role-help')->render();
            $this->assertStringContainsString('Menelusuri data pegawai', $html);
            if ($role === 'pegawai') {
                $this->assertStringNotContainsString('Mengubah data dan status HRIS', $html);
            } else {
                $this->assertStringContainsString('Mengubah data dan status HRIS', $html);
            }
        }
    }
}
