<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoRoleMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_maps_to_admin_administrator(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'mock_token_superadmin',
                'user' => [
                    'id' => 1,
                    'name' => 'Kepala Kantor',
                    'username' => 'kpknlpalembang',
                    'email' => 'kpknlpalembang@kemenkeu.go.id',
                    'is_superadmin' => true,
                    'is_admin' => false,
                    'is_maintenance' => false,
                    'primary_role' => 'superadmin',
                    'roles' => ['superadmin'],
                ]
            ], 200),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'code_superadmin', 'state' => 'xyz']));

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('Administrator Arsip', $user->getRoleLabel());
    }

    public function test_maintenance_maps_to_admin_administrator(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'mock_token_maintenance',
                'user' => [
                    'id' => 18,
                    'name' => 'Tim Maintenance',
                    'username' => 'maintenance',
                    'email' => 'maintenance@kpknl.go.id',
                    'is_superadmin' => false,
                    'is_admin' => false,
                    'is_maintenance' => true,
                    'primary_role' => 'maintenance',
                    'roles' => ['maintenance'],
                ]
            ], 200),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'code_maintenance', 'state' => 'xyz']));

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('Administrator Arsip', $user->getRoleLabel());
    }

    public function test_admin_maps_to_pelelang_pejabat_lelang(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'mock_token_admin',
                'user' => [
                    'id' => 2,
                    'name' => 'Pejabat Lelang Eko',
                    'username' => 'sekretaris',
                    'email' => 'sekretaris@kpknl.go.id',
                    'is_superadmin' => false,
                    'is_admin' => true,
                    'is_maintenance' => false,
                    'primary_role' => 'admin',
                    'roles' => ['admin'],
                ]
            ], 200),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'code_admin', 'state' => 'xyz']));

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('pelelang', $user->role);
        $this->assertEquals('Pejabat Lelang', $user->getRoleLabel());
    }

    public function test_regular_user_maps_to_peminjam_berkas(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'mock_token_user',
                'user' => [
                    'id' => 3,
                    'name' => 'Staf Seksi PKN',
                    'username' => 'seksipkn',
                    'email' => 'seksipkn@kpknl.go.id',
                    'is_superadmin' => false,
                    'is_admin' => false,
                    'is_maintenance' => false,
                    'primary_role' => 'user',
                    'roles' => ['user'],
                ]
            ], 200),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'code_user', 'state' => 'xyz']));

        $this->assertAuthenticated();
        $user = Auth::user();
        $this->assertEquals('peminjam', $user->role);
        $this->assertEquals('Peminjam Berkas', $user->getRoleLabel());
    }
}
