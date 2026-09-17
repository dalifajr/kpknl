<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardPageActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_have_action_strip_buttons(): void
    {
        $user = User::create([
            'name' => 'Admin Lelang',
            'username' => 'adminlelang',
            'email' => 'admin@kpknl.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Kelola Master');
        $response->assertDontSee('Ekspor Laporan');
        $response->assertDontSee('Cetak Grafik');
    }

    public function test_statistik_does_not_have_action_strip_buttons(): void
    {
        $user = User::create([
            'name' => 'Admin Lelang',
            'username' => 'adminlelang2',
            'email' => 'admin2@kpknl.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/statistik');

        $response->assertStatus(200);
        $response->assertDontSee('Cetak Grafik');
        $response->assertDontSee('Unduh Data CSV');
    }
}
