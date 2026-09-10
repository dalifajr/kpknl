<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Models\RisalahMinuta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReturnPeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_borrow_confirmation_and_return_cycle(): void
    {
        $admin = User::create([
            'username' => 'admin_flow',
            'email' => 'admin_flow@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $peminjam = User::create([
            'username' => 'peminjam_flow',
            'email' => 'peminjam_flow@kpknl.go.id',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        $minuta = RisalahMinuta::create([
            'no_risalah' => 'RL-FLOW-01',
            'tgl_risalah' => '2026-01-20',
            'nama_pelelang' => 'pelelang_flow',
            'pemohon_lelang' => 'Bank Mandiri',
            'status' => 'sedang_dipinjam',
        ]);

        $loan = Peminjaman::create([
            'nama_peminjam' => 'peminjam_flow',
            'no_risalah' => 'RL-FLOW-01',
            'tgl_risalah' => '2026-01-20',
            'nama_pelelang' => 'pelelang_flow',
            'pemohon_lelang' => 'Bank Mandiri',
            'tgl_peminjaman' => '2026-01-20',
            'status' => Peminjaman::STATUS_PROSES,
            'alasan_peminjaman' => 'Audit',
        ]);

        // Step 1: Admin Validates Loan (Peminjaman) -> Moves to 'Sedang Dipinjam'
        $resApprove = $this->actingAs($admin)->post("/admin/peminjaman/{$loan->id}/approve");
        $resApprove->assertStatus(302);
        $this->assertEquals(Peminjaman::STATUS_SEDANG_DIPINJAM, $loan->fresh()->status);

        // Step 2: Borrower Clicks 'Kembalikan' -> Moves to 'Proses Pengembalian'
        $resReturnReq = $this->actingAs($peminjam)->post("/peminjam/pinjaman/{$loan->id}/kembalikan");
        $resReturnReq->assertStatus(302);
        $this->assertEquals(Peminjaman::STATUS_PROSES_PENGEMBALIAN, $loan->fresh()->status);

        // Step 3: Admin Validates Return (Pengembalian) -> Moves to 'Sudah Dikembalikan'
        $resReturnApprove = $this->actingAs($admin)->post("/admin/pengembalian/{$loan->id}/approve");
        $resReturnApprove->assertStatus(302);
        $this->assertEquals(Peminjaman::STATUS_SUDAH_DIKEMBALIKAN, $loan->fresh()->status);

        // Step 4: Source Risalah is restored to 'tersedia'
        $this->assertEquals('tersedia', $minuta->fresh()->status);
    }
}
