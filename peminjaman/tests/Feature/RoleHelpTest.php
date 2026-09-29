<?php
// PHP, RoleHelpTest.php; Laravel/PHPUnit; help must render in the active BMN layout.

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_pages_show_help_for_each_role(): void
    {
        foreach (['admin', 'pelelang', 'peminjam'] as $role) {
            $user = User::create(['name' => 'Uji ' . $role, 'username' => $role,
                'email' => $role . '@example.test', 'password' => bcrypt('test-password'), 'role' => $role]);
            $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();
            $response->assertSee('Panduan Peminjaman Risalah Lelang')->assertSee('aria-haspopup="dialog"', false);
            if ($role === 'admin') {
                $response->assertSee('Memvalidasi dan menyerahkan berkas');
            } else {
                $response->assertDontSee('Memvalidasi dan menyerahkan berkas');
            }
            if ($role === 'peminjam') {
                $response->assertSee('Mengajukan dan mengembalikan pinjaman')->assertDontSee('Mendaftarkan dan merevisi risalah');
            } else {
                $response->assertSee('Mendaftarkan dan merevisi risalah');
            }
            $this->get(route('katalog.index'))->assertOk()->assertSee('Panduan Peminjaman Risalah Lelang');
        }
    }
}
