<?php

namespace Tests\Feature;

use App\Models\RisalahMinuta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RisalahTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelelang_can_create_risalah_and_it_enters_pending(): void
    {
        $pelelang = User::create([
            'username' => 'pejabat_eko',
            'email' => 'eko@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'pelelang',
        ]);

        $response = $this->actingAs($pelelang)->post('/pelelang/risalah', [
            'no_risalah' => 'RL-100/2026',
            'jenis' => 'minuta',
            'tgl_risalah' => '2026-03-15',
            'pemohon_lelang' => 'Bank Mandiri',
            'keterangan' => 'Lelang Hak Tanggungan',
        ]);

        $response->assertRedirect(route('pelelang.pending'));

        $this->assertDatabaseHas('risalah_pending', [
            'no_risalah' => 'RL-100/2026',
            'jenis' => 'minuta',
            'nama_pelelang' => 'pejabat_eko',
            'pemohon_lelang' => 'Bank Mandiri',
            'status' => 'belum_validasi',
        ]);
    }

    public function test_user_can_view_risalah_minuta_listing(): void
    {
        $admin = User::create([
            'username' => 'admin_test',
            'email' => 'adm@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        RisalahMinuta::create([
            'no_risalah' => 'RL-MINUTA-01',
            'tgl_risalah' => '2026-01-10',
            'tgl_validasi' => '2026-01-12',
            'nama_pelelang' => 'pejabat_eko',
            'pemohon_lelang' => 'BRI',
            'box' => 'B-01',
            'lemari' => 'L-01',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($admin)->get('/risalah/minuta');

        $response->assertStatus(200);
        $response->assertSee('RL-MINUTA-01');
        $response->assertSee('pejabat_eko');
    }
}
