<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Models\RisalahMinuta;
use App\Models\RisalahTap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjam_can_borrow_multiple_available_risalah(): void
    {
        $peminjam = User::create([
            'username' => 'peminjam_andi',
            'email' => 'andi@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        $minuta = RisalahMinuta::create([
            'no_risalah' => 'RL-LOAN-01',
            'tgl_risalah' => '2026-01-10',
            'nama_pelelang' => 'pelelang_eko',
            'pemohon_lelang' => 'Bank Mandiri',
            'box' => 'B-01',
            'lemari' => 'L-01',
            'status' => 'tersedia',
        ]);

        $tap = RisalahTap::create([
            'no_risalah' => 'RL-LOAN-02',
            'tgl_risalah' => '2026-01-15',
            'nama_pelelang' => 'pelelang_eko',
            'pemohon_lelang' => 'BRI',
            'box' => 'B-02',
            'lemari' => 'L-02',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($peminjam)->post('/peminjam/pinjam', [
            'selected_items' => [
                "minuta:{$minuta->id}",
                "tap:{$tap->id}",
            ],
            'alasan_peminjaman' => 'Pemeriksaan berkas arsip lelang',
        ]);

        $response->assertRedirect(route('peminjam.pinjaman'));

        // Assert loan records created
        $this->assertDatabaseHas('peminjaman', [
            'nama_peminjam' => 'peminjam_andi',
            'no_risalah' => 'RL-LOAN-01',
            'status' => Peminjaman::STATUS_PROSES,
        ]);
        $this->assertDatabaseHas('peminjaman', [
            'nama_peminjam' => 'peminjam_andi',
            'no_risalah' => 'RL-LOAN-02',
            'status' => Peminjaman::STATUS_PROSES,
        ]);

        // Assert source risalah status changed to sedang_dipinjam
        $this->assertEquals('sedang_dipinjam', $minuta->fresh()->status);
        $this->assertEquals('sedang_dipinjam', $tap->fresh()->status);
    }
}
