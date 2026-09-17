<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KepegawaianViewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_authenticated_user_can_access_dashboard_with_new_design(): void
    {
        $user = User::factory()->create([
            'name' => 'Kepala Kantor',
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('SI-KEP');
        $response->assertSee('Dashboard');
        $response->assertDontSee('Distribusi Formasi Personil per Seksi & Subbagian');
        $response->assertDontSee('onclick="openPegawaiFormModal()"', false);
        $response->assertSee('Total Personil');
        $response->assertSee('admin-app.css');
        $response->assertSee('Outfit');
    }

    public function test_authenticated_user_can_access_pegawai_directory(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($user)->get('/pegawai');

        $response->assertStatus(200);
        $response->assertSee('Direktori Data Kepegawaian');
        $response->assertSee('Daftar Personil Definitif KPKNL Palembang');
        $response->assertSee('Tambah Pegawai');
    }

    public function test_authenticated_user_can_access_jabatan_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/jabatan');

        $response->assertStatus(200);
        $response->assertSee('Struktur & Formasi Jabatan');
        $response->assertSee('Pejabat Struktural');
    }

    public function test_authenticated_user_can_access_unit_kerja_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/unit-kerja');

        $response->assertStatus(200);
        $response->assertSee('Formasi & Distribusi Unit Kerja');
    }

    public function test_authenticated_user_can_access_diagram_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/diagram');

        $response->assertStatus(200);
        $response->assertSee('Diagram & Visualisasi Analitika');
    }

    public function test_pegawai_detail_modal_endpoint(): void
    {
        $user = User::factory()->create();
        $pegawai = Pegawai::first();

        if ($pegawai) {
            $response = $this->actingAs($user)->get("/pegawai/{$pegawai->id}/detail");
            $response->assertStatus(200);
            $response->assertSee($pegawai->display_name);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_spreadsheet_raw_access_control(): void
    {
        // 1. Unauthorized regular user / viewer gets 403
        $viewer = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($viewer)->get('/spreadsheet-raw')->assertStatus(403);

        // 2. Superadmin gets 200
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)->get('/spreadsheet-raw')
            ->assertStatus(200)
            ->assertSee('Data Mentah Google Spreadsheet');

        // 3. Admin gets 200
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/spreadsheet-raw')->assertStatus(200);

        // 4. Maintenance gets 200
        $maintenance = User::factory()->create(['role' => 'maintenance']);
        $this->actingAs($maintenance)->get('/spreadsheet-raw')->assertStatus(200);
    }

    public function test_change_log_access_and_features(): void
    {
        // 1. Regular user gets 403
        $viewer = User::factory()->create(['role' => 'user']);
        $this->actingAs($viewer)->get('/log-perubahan')->assertStatus(403);

        // 2. Superadmin can access
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $response = $this->actingAs($superadmin)->get('/log-perubahan');
        $response->assertStatus(200);
        $response->assertSee('Log Perubahan & Audit Sinkronisasi');

        // 3. Maintenance can access and sync pending
        $maintenance = User::factory()->create(['role' => 'maintenance']);
        $response = $this->actingAs($maintenance)->get('/log-perubahan');
        $response->assertStatus(200);

        $syncRes = $this->actingAs($maintenance)->post('/log-perubahan/sync-pending');
        $syncRes->assertStatus(302);
    }

    public function test_aggregate_filter_modal_endpoint(): void
    {
        $user = User::factory()->create();

        // 1. Total pegawai filter
        $response = $this->actingAs($user)->get('/pegawai/filter-modal?type=total_pegawai');
        $response->assertStatus(200);
        $response->assertSee('Seluruh Personil KPKNL Palembang');

        // 2. Generasi filter
        $response = $this->actingAs($user)->get('/pegawai/filter-modal?type=generasi&value=milenial');
        $response->assertStatus(200);

        // 3. KGB Alert filter
        $response = $this->actingAs($user)->get('/pegawai/filter-modal?type=kgb_alert');
        $response->assertStatus(200);
    }

    public function test_pegawai_pp17_2020_pensiun_calculation(): void
    {
        // 1. Pejabat Struktural Eselon III/IV -> BUP 58
        $struktural = new Pegawai([
            'nama_jabatan_raw' => 'Kepala Seksi Piutang Negara',
            'tanggal_lahir' => '1980-05-15',
        ]);
        $this->assertEquals(58, $struktural->bup_tahun);
        $this->assertEquals('2038-06-01', $struktural->tgl_pensiun?->format('Y-m-d'));

        // 2. Fungsional Madya (Penilai/Pelelang Madya) -> BUP 60
        $madya = new Pegawai([
            'nama_jabatan_raw' => 'Pelelang Ahli Madya',
            'tanggal_lahir' => '1975-03-20',
        ]);
        $this->assertEquals(60, $madya->bup_tahun);
        $this->assertEquals('2035-04-01', $madya->tgl_pensiun?->format('Y-m-d'));

        // 3. Fungsional Pertama/Muda -> BUP 58
        $muda = new Pegawai([
            'nama_jabatan_raw' => 'Penilai Pemerintah Ahli Muda',
            'tanggal_lahir' => '1990-08-10',
        ]);
        $this->assertEquals(58, $muda->bup_tahun);
        $this->assertEquals('2048-09-01', $muda->tgl_pensiun?->format('Y-m-d'));

        // 4. Fungsional Ahli Utama -> BUP 65
        $utama = new Pegawai([
            'nama_jabatan_raw' => 'Pelelang Ahli Utama',
            'tanggal_lahir' => '1965-01-10',
        ]);
        $this->assertEquals(65, $utama->bup_tahun);
        $this->assertEquals('2030-02-01', $utama->tgl_pensiun?->format('Y-m-d'));
    }

    public function test_pegawai_create_and_update_with_changelog(): void
    {
        Storage::fake('public');
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $initialAvatar = UploadedFile::fake()->image('initial_avatar.jpg', 120, 120);

        // 1. Create Pegawai with Avatar
        $storeResponse = $this->actingAs($superadmin)->post(route('pegawai.store'), [
            'nama' => 'Budi Santoso Testing',
            'nip' => '198801012010011001',
            'nama_jabatan_raw' => 'Pengolah Data dan Informasi',
            'tipe_pegawai' => 'pns',
            'job_grade' => 8,
            'jenis_kelamin' => 'L',
            'avatar' => $initialAvatar,
        ]);

        $storeResponse->assertStatus(200);
        $storeResponse->assertJson(['success' => true]);

        $createdPegawai = Pegawai::where('nip', '198801012010011001')->first();
        $this->assertNotNull($createdPegawai);
        $this->assertNotNull($createdPegawai->avatar_url);
        $this->assertTrue(Storage::disk('public')->exists($createdPegawai->avatar_url));
        $oldAvatarUrl = $createdPegawai->avatar_url;

        // Verify ChangeLog record created
        $this->assertDatabaseHas('change_logs', [
            'nip' => '198801012010011001',
            'action' => 'create',
        ]);

        // 2. Update Pegawai with New Avatar (replaces old)
        $fakeAvatar = UploadedFile::fake()->image('updated_profil.jpg', 200, 200);

        $updateResponse = $this->actingAs($superadmin)->post(route('pegawai.update', ['id' => $createdPegawai->id]), [
            'nama' => 'Budi Santoso Testing Updated',
            'nip' => '198801012010011001',
            'nama_jabatan_raw' => 'Pelelang Ahli Pertama',
            'tipe_pegawai' => 'pns',
            'job_grade' => 9,
            'jenis_kelamin' => 'L',
            'avatar' => $fakeAvatar,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        // Verify ChangeLog record for UPDATE
        $this->assertDatabaseHas('change_logs', [
            'pegawai_id' => $createdPegawai->id,
            'action' => 'update',
        ]);

        $createdPegawai->refresh();
        $this->assertNotNull($createdPegawai->avatar_url);
        $this->assertTrue(Storage::disk('public')->exists($createdPegawai->avatar_url));
        // Verify old avatar was deleted
        $this->assertFalse(Storage::disk('public')->exists($oldAvatarUrl));
    }
}

