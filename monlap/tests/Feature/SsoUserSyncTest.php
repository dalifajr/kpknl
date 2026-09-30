<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoUserSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignable_users_only_includes_non_admin_pic_users(): void
    {
        $superadmin = User::create([
            'sso_id' => '1',
            'name' => 'Kepala Kantor',
            'email' => 'kepala@example.test',
            'role' => 'superadmin',
        ]);

        $admin = User::create([
            'sso_id' => '2',
            'name' => 'Sekretaris',
            'email' => 'sekretaris@example.test',
            'role' => 'admin',
        ]);

        $maintenance = User::create([
            'sso_id' => '18',
            'name' => 'Tim Maintenance',
            'email' => 'maintenance@example.test',
            'role' => 'maintenance',
        ]);

        $picUmum = User::create([
            'sso_id' => '22',
            'name' => 'PIC Subbagian Umum',
            'email' => 'pic.umum@example.test',
            'role' => 'user',
        ]);

        $picPkn = User::create([
            'sso_id' => '23',
            'name' => 'PIC Seksi Pengelolaan Kekayaan Negara',
            'email' => 'pic.pkn@example.test',
            'role' => 'user',
        ]);

        $unassigned = User::create([
            'sso_id' => '99',
            'name' => 'Pegawai Lain',
            'email' => 'other@example.test',
            'role' => 'unassigned',
        ]);

        $assignable = User::where('role', 'user')->orderBy('name', 'asc')->get();

        $this->assertCount(2, $assignable);
        $this->assertTrue($assignable->contains('id', $picUmum->id));
        $this->assertTrue($assignable->contains('id', $picPkn->id));
        $this->assertFalse($assignable->contains('id', $superadmin->id));
        $this->assertFalse($assignable->contains('id', $admin->id));
        $this->assertFalse($assignable->contains('id', $maintenance->id));
        $this->assertFalse($assignable->contains('id', $unassigned->id));
    }

    public function test_sync_sso_users_artisan_command_runs_successfully(): void
    {
        $this->artisan('monlap:sync-sso-users')
            ->assertExitCode(0);
    }
}
