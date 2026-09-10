<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Models\RisalahBatal;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GrafikTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'admin_test',
            'email' => 'admin@kpknl.go.id',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);

        // Seed some sample data for charts
        RisalahMinuta::create([
            'no_risalah' => 'RL-MIN-01',
            'tgl_risalah' => '2025-05-15',
            'nama_pelelang' => 'Andri Rachmawan',
            'pemohon_lelang' => 'Bank Mandiri',
            'status' => 'tersedia',
        ]);

        RisalahTap::create([
            'no_risalah' => 'RL-TAP-01',
            'tgl_risalah' => '2025-05-20',
            'nama_pelelang' => 'Andri Rachmawan',
            'pemohon_lelang' => 'BRI',
            'status' => 'tersedia',
        ]);

        RisalahBatal::create([
            'no_risalah' => 'RL-BATAL-01',
            'tgl_risalah' => '2025-06-10',
            'nama_pelelang' => 'Eko Prasetyo',
            'pemohon_lelang' => 'BNI',
            'status' => 'tersedia',
        ]);

        Peminjaman::create([
            'nama_peminjam' => 'Dinda Puspita',
            'no_risalah' => 'RL-MIN-01',
            'tgl_risalah' => '2025-05-15',
            'nama_pelelang' => 'Andri Rachmawan',
            'pemohon_lelang' => 'Bank Mandiri',
            'tgl_peminjaman' => '2025-05-18',
            'status' => 'Sudah Dikembalikan',
        ]);
    }

    public function test_can_access_informasi_grafik_main_menu(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik');
        $response->assertStatus(200);
        $response->assertSee('INFORMASI GRAFIK');
        $response->assertSee('Grafik Bulanan');
        $response->assertSee('Grafik Tahunan');
        $response->assertSee('Grafik Pelelang');
        $response->assertSee('Grafik Peminjam');
    }

    public function test_can_access_grafik_bulanan_submenu(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/bulanan');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK BULANAN');
        $response->assertSee('Grafik Bulanan Pelelangan');
        $response->assertSee('Grafik Bulanan Peminjaman');
    }

    public function test_can_access_grafik_tahunan_submenu(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/tahunan');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK TAHUNAN');
        $response->assertSee('Grafik Tahunan Pelelangan');
        $response->assertSee('Grafik Tahunan Peminjaman');
    }

    public function test_can_access_grafik_bulanan_pelelangan_page_and_ajax_data(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/bulanan/pelelangan');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK BULANAN PER TAHUN PELELANGAN');
        $response->assertSee('2025');

        // Test AJAX data endpoint for Semua
        $ajaxResponse = $this->actingAs($this->user)->getJson('/grafik/bulanan/pelelangan/data?year=2025&status=');
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonStructure([
            'labels',
            'datasets' => [
                '*' => ['label', 'data', 'borderColor']
            ]
        ]);

        // Test AJAX data endpoint for single status
        $ajaxMinuta = $this->actingAs($this->user)->getJson('/grafik/bulanan/pelelangan/data?year=2025&status=minuta');
        $ajaxMinuta->assertStatus(200);
        $ajaxMinuta->assertJsonStructure(['labels', 'values']);
        $this->assertEquals(1, $ajaxMinuta->json('values.4')); // May (index 4) has 1 minuta
    }

    public function test_can_access_grafik_bulanan_peminjaman(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/bulanan/peminjaman');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK BULANAN PEMINJAMAN (TOTAL SEMUA PEMINJAM)');
        $response->assertSee('2025');
    }

    public function test_can_access_grafik_tahunan_pelelangan(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/tahunan/pelelangan?jenis=minuta');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK TAHUNAN PELELANGAN');
        $response->assertSee('Minuta');
    }

    public function test_can_access_grafik_tahunan_peminjaman(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/tahunan/peminjaman');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK TAHUNAN PEMINJAMAN');
    }

    public function test_can_access_grafik_pelelang(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/pelelang?pelelang=Andri+Rachmawan&jenis=bulanan');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK PELELANG');
        $response->assertSee('GRAFIK BULANAN ANDRI RACHMAWAN');
    }

    public function test_can_access_grafik_peminjam(): void
    {
        $response = $this->actingAs($this->user)->get('/grafik/peminjam?peminjam=Dinda+Puspita&jenis=bulanan');
        $response->assertStatus(200);
        $response->assertSee('GRAFIK PEMINJAM');
        $response->assertSee('GRAFIK BULANAN DINDA PUSPITA');
    }
}
