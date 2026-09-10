<?php

namespace Tests\Feature;

use App\Models\RisalahPending;
use App\Models\RisalahRevisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RisalahRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_revision_and_pelelang_can_resubmit(): void
    {
        $admin = User::create([
            'username' => 'admin_rev',
            'email' => 'admin_r@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $pelelang = User::create([
            'username' => 'pelelang_rev',
            'email' => 'pelelang_r@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'pelelang',
        ]);

        $pending = RisalahPending::create([
            'no_risalah' => 'RL-REV-01',
            'jenis' => 'minuta',
            'tgl_risalah' => '2026-02-10',
            'nama_pelelang' => 'pelelang_rev',
            'pemohon_lelang' => 'Bank BCA',
            'status' => 'belum_validasi',
        ]);

        // Admin returns for revision
        $response = $this->actingAs($admin)->post("/admin/validasi/{$pending->id}/revisi", [
            'catatan' => 'Perbaiki nama pemohon lelang',
            'catatan_pemohon' => 'Gunakan PT Bank Central Asia Tbk',
        ]);

        $response->assertRedirect(route('admin.validasi.index'));

        $this->assertDatabaseMissing('risalah_pending', ['id' => $pending->id]);
        $this->assertDatabaseHas('risalah_revisi', [
            'no_risalah' => 'RL-REV-01',
            'nama_pelelang' => 'pelelang_rev',
            'status' => 'revisi',
        ]);

        $revisi = RisalahRevisi::where('no_risalah', 'RL-REV-01')->first();

        // Pelelang fixes and resubmits
        $resubmitResponse = $this->actingAs($pelelang)->post("/pelelang/revisi/{$revisi->id}/resubmit", [
            'no_risalah' => 'RL-REV-01',
            'jenis' => 'minuta',
            'tgl_risalah' => '2026-02-10',
            'pemohon_lelang' => 'PT Bank Central Asia Tbk',
            'keterangan' => 'Sudah disesuaikan',
        ]);

        $resubmitResponse->assertRedirect(route('pelelang.revisi.index'));

        $this->assertDatabaseMissing('risalah_revisi', ['id' => $revisi->id]);
        $this->assertDatabaseHas('risalah_pending', [
            'no_risalah' => 'RL-REV-01',
            'nama_pelelang' => 'pelelang_rev',
            'pemohon_lelang' => 'PT Bank Central Asia Tbk',
            'status' => 'belum_validasi',
        ]);
    }
}
