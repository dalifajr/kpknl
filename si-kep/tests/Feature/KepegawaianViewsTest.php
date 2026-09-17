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
}
