<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertSee('Dashboard Eksekutif');
        $response->assertSee('Total Personil');
        $response->assertSee('admin-app.css');
        $response->assertSee('Outfit');
    }

    public function test_authenticated_user_can_access_pegawai_directory(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/pegawai');

        $response->assertStatus(200);
        $response->assertSee('Direktori Data Kepegawaian');
        $response->assertSee('Daftar Personil Definitif KPKNL Palembang');
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
}

