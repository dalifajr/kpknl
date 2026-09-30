<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TaskIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(['*/api/sso/verify-session' => Http::response(['valid' => true])]);
    }

    public function test_tasks_index_renders_with_legacy_and_edge_case_period_types(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'role' => 'admin',
        ]);

        // 1. Task dengan format kapital 'Bulanan' dan deadline_rule string seperti laporan asli
        Task::create([
            'title' => 'Laporan Monitoring Kedisiplinan Pegawai (LMKP)',
            'period_type' => 'Bulanan',
            'deadline_type' => 'kalender',
            'deadline_rule' => 'Setiap tanggal 10 sebulan sekali',
            'is_active' => true,
        ]);

        // 2. Task Triwulan
        Task::create([
            'title' => 'Laporan Keuangan Triwulan',
            'period_type' => 'triwulan',
            'deadline_type' => 'kalender',
            'deadline_rule' => 15,
            'deadline_next_month' => true,
            'is_active' => true,
        ]);

        // 3. Task Tidak Rutin
        Task::create([
            'title' => 'Laporan Ad-hoc',
            'period_type' => 'tidak rutin',
            'deadline_rule' => '2026-10-15',
            'is_active' => true,
        ]);

        // 4. Task Custom
        Task::create([
            'title' => 'Proyek Khusus',
            'period_type' => 'custom',
            'custom_start_date' => '2026-09-01',
            'custom_end_date' => '2026-10-01',
            'is_active' => true,
        ]);

        // 5. Task edge case: unknown period_type
        Task::create([
            'title' => 'Unknown Period',
            'period_type' => 'mingguan',
            'deadline_rule' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSee('Laporan Monitoring Kedisiplinan Pegawai (LMKP)');
        $response->assertSee('Laporan Keuangan Triwulan');
        $response->assertSee('Proyek Khusus');
    }
}
