<?php
// PHP, RoleHelpTest.php; Laravel/PHPUnit; task guidance respects MonLap roles.

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guidance_matches_task_and_review_permissions(): void
    {
        foreach (['user', 'admin', 'superadmin', 'maintenance'] as $role) {
            $this->actingAs(User::create(['sso_id' => random_int(10000, 999999), 'name' => 'Uji ' . $role,
                'email' => $role . '@example.test', 'username' => $role, 'role' => $role]));
            $html = view('components.role-help')->render();
            $this->assertStringContainsString('Mengisi laporan tugas', $html);
            if ($role === 'user') {
                $this->assertStringNotContainsString('Membuat dan meninjau tugas', $html);
            } else {
                $this->assertStringContainsString('Membuat dan meninjau tugas', $html);
            }
            $this->assertSame(in_array($role, ['superadmin', 'maintenance']), str_contains($html, 'Superadmin dan Maintenance dapat membatalkan ACC'));
            $this->get(route('dashboard'))->assertOk()->assertSee('Panduan MonLap');
        }
    }
}
