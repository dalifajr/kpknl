<?php

namespace Tests\Feature;

use App\Models\RisalahPending;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RisalahValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_validate_and_approve_pending_minuta(): void
    {
        $admin = User::create([
            'username' => 'admin_valid',
            'email' => 'admin_v@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $pending = RisalahPending::create([
            'no_risalah' => 'RL-VALID-01',
            'jenis' => 'minuta',
            'tgl_risalah' => '2026-02-01',
            'nama_pelelang' => 'pelelang_budi',
            'pemohon_lelang' => 'BNI 46',
            'status' => 'belum_validasi',
        ]);

        $response = $this->actingAs($admin)->post("/admin/validasi/{$pending->id}/approve", [
            'box' => 'BOX-99',
            'lemari' => 'LEM-05',
            'tgl_validasi' => '2026-02-02',
            'link_erisalah' => 'https://erisalah.kpknl.go.id/doc/01',
        ]);

        $response->assertRedirect(route('admin.validasi.index'));

        // Assert pending was deleted
        $this->assertDatabaseMissing('risalah_pending', [
            'id' => $pending->id,
        ]);

        // Assert inserted into risalah_minuta
        $this->assertDatabaseHas('risalah_minuta', [
            'no_risalah' => 'RL-VALID-01',
            'nama_pelelang' => 'pelelang_budi',
            'box' => 'BOX-99',
            'lemari' => 'LEM-05',
            'status' => 'tersedia',
        ]);
    }

    public function test_admin_can_validate_and_approve_pending_tap(): void
    {
        $admin = User::create([
            'username' => 'admin_valid2',
            'email' => 'admin_v2@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $pending = RisalahPending::create([
            'no_risalah' => 'RL-TAP-01',
            'jenis' => 'tap',
            'tgl_risalah' => '2026-02-05',
            'nama_pelelang' => 'pelelang_budi',
            'pemohon_lelang' => 'Kejaksaan Negeri',
            'status' => 'belum_validasi',
        ]);

        $response = $this->actingAs($admin)->post("/admin/validasi/{$pending->id}/approve", [
            'box' => 'BOX-TAP',
            'lemari' => 'LEM-TAP',
            'tgl_validasi' => '2026-02-06',
        ]);

        $response->assertRedirect(route('admin.validasi.index'));

        $this->assertDatabaseMissing('risalah_pending', ['id' => $pending->id]);
        $this->assertDatabaseHas('risalah_tap', [
            'no_risalah' => 'RL-TAP-01',
            'nama_pelelang' => 'pelelang_budi',
            'status' => 'tersedia',
        ]);
    }
}
