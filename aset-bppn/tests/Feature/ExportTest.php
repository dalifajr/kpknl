<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_excel()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/assets/export/excel');
        
        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=Laporan_Semester_Aset.xlsx');
    }

    public function test_user_can_download_pdf()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/assets/export/pdf');
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
