<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ChangeLog;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AppSetting::set('google_sheet_webhook_url', 'https://script.google.com/macros/s/test_exec/exec');
    }

    public function test_superadmin_can_delete_pegawai_and_triggers_sheet_webhook(): void
    {
        $superadmin = User::factory()->create([
            'username' => 'superadmin_del',
            'role' => 'superadmin',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 1,
            'nip' => '198501012010121099',
            'nama' => 'Bambang Sudarsono',
            'nama_jabatan_raw' => 'Pelelang Ahli Pertama',
            'is_active' => true,
        ]);

        Http::fake([
            'https://script.google.com/macros/s/test_exec/exec' => Http::response([
                'status' => 'success',
                'message' => 'Data pegawai Bambang Sudarsono berhasil dihapus dari baris 10 Google Spreadsheet.',
                'row' => 10,
                'nip' => '198501012010121099',
                'action' => 'delete',
            ], 200),
        ]);

        $response = $this->actingAs($superadmin)
            ->deleteJson(route('pegawai.destroy', $pegawai->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Pegawai should be deleted from DB
        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
        ]);

        // ChangeLog should be recorded with action delete and synced status
        $this->assertDatabaseHas('change_logs', [
            'action' => 'delete',
            'nama_pegawai' => 'Bambang Sudarsono',
            'nip' => '198501012010121099',
            'sync_status' => 'synced',
        ]);

        // Verify webhook was sent with action delete
        Http::assertSent(function ($request) {
            return $request['action'] === 'delete'
                && $request['nip'] === '198501012010121099';
        });
    }

    public function test_admin_can_delete_pegawai_and_triggers_sheet_webhook(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_del',
            'role' => 'admin',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 2,
            'nip' => '199002022015031002',
            'nama' => 'Siti Aminah',
            'nama_jabatan_raw' => 'Penilai Pemerintah Ahli Muda',
            'is_active' => true,
        ]);

        Http::fake([
            'https://script.google.com/macros/s/test_exec/exec' => Http::response([
                'status' => 'success',
                'message' => 'Data pegawai Siti Aminah berhasil dihapus.',
                'action' => 'delete',
            ], 200),
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('pegawai.destroy', $pegawai->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
        ]);

        $this->assertDatabaseHas('change_logs', [
            'action' => 'delete',
            'nama_pegawai' => 'Siti Aminah',
            'nip' => '199002022015031002',
            'sync_status' => 'synced',
        ]);
    }

    public function test_unauthorized_user_cannot_delete_pegawai(): void
    {
        $user = User::factory()->create([
            'username' => 'ordinary_user',
            'role' => 'user',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 3,
            'nip' => '199203032018011003',
            'nama' => 'Rahmat Hidayat',
            'nama_jabatan_raw' => 'Pengadministrasi Umum',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('pegawai.destroy', $pegawai->id));

        $response->assertStatus(403);

        // Pegawai must still exist
        $this->assertDatabaseHas('pegawai', [
            'id' => $pegawai->id,
        ]);
    }

    public function test_delete_pegawai_handles_webhook_failure_gracefully(): void
    {
        $superadmin = User::factory()->create([
            'username' => 'superadmin_err',
            'role' => 'superadmin',
        ]);

        $pegawai = Pegawai::create([
            'no_urut' => 4,
            'nip' => '199504042020021004',
            'nama' => 'Joko Widodo KW',
            'nama_jabatan_raw' => 'Pelaksana Seksi HI',
            'is_active' => true,
        ]);

        Http::fake([
            'https://script.google.com/macros/s/test_exec/exec' => Http::response('Server Error', 500),
        ]);

        $response = $this->actingAs($superadmin)
            ->deleteJson(route('pegawai.destroy', $pegawai->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Pegawai is deleted locally
        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
        ]);

        // ChangeLog has pending status for spreadsheet retry
        $this->assertDatabaseHas('change_logs', [
            'action' => 'delete',
            'nama_pegawai' => 'Joko Widodo KW',
            'sync_status' => 'pending',
        ]);
    }

    public function test_delete_button_visible_for_superadmin_and_admin_in_pegawai_index(): void
    {
        $pegawai = Pegawai::create([
            'no_urut' => 1,
            'nip' => '198001012000011001',
            'nama' => 'Ahmad Test',
            'nama_jabatan_raw' => 'Kepala Subbagian Umum',
            'is_active' => true,
        ]);

        // Test Superadmin
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $responseSuper = $this->actingAs($superadmin)->get(route('pegawai.index'));
        $responseSuper->assertOk();
        $responseSuper->assertSee('title="Hapus Data Pegawai"', false);

        // Test Admin
        $admin = User::factory()->create(['role' => 'admin']);
        $responseAdmin = $this->actingAs($admin)->get(route('pegawai.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('title="Hapus Data Pegawai"', false);

        // Test Ordinary User
        $user = User::factory()->create(['role' => 'user']);
        $responseUser = $this->actingAs($user)->get(route('pegawai.index'));
        $responseUser->assertOk();
        $responseUser->assertDontSee('title="Hapus Data Pegawai"', false);
    }
}
