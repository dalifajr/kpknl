<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_assets_index()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/assets');
        
        $response->assertStatus(200);
        $response->assertSee('Gudang Aset');
    }

    public function test_user_can_create_asset_with_full_specification()
    {
        $user = User::factory()->create();
        
        $assetData = [
            'kode_aset' => 'AST-001',
            'jenis_aset' => 'Tanah',
            'luas_tanah' => 150.5,
            'kondisi_aset' => 'DIHUNI',
            'alamatlama_namajalan' => 'Jl. Merdeka Lama',
            'alamat_namajalan' => 'Jl. Merdeka Baru',
            'koordinat_latitude' => -2.990934,
            'koordinat_longitude' => 104.756554,
        ];

        $response = $this->actingAs($user)->post('/assets', $assetData);
        
        $response->assertRedirect('/assets');
        $this->assertDatabaseHas('assets', [
            'kode_aset' => 'AST-001',
            'alamatlama_namajalan' => 'Jl. Merdeka Lama',
            'koordinat_link' => 'https://maps.google.com/?q=-2.990934,104.756554'
        ]);
    }

    public function test_user_can_update_asset()
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-002',
            'jenis_aset' => 'Bangunan'
        ]);

        $response = $this->actingAs($user)->put("/assets/{$asset->id}", [
            'kode_aset' => 'AST-002',
            'jenis_aset' => 'Tanah dan Bangunan',
            'kondisi_aset' => 'KOSONG'
        ]);

        $response->assertRedirect('/assets');
        $this->assertDatabaseHas('assets', ['jenis_aset' => 'Tanah dan Bangunan', 'kondisi_aset' => 'KOSONG']);
    }

    public function test_user_can_delete_asset()
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-003',
            'jenis_aset' => 'Tanah'
        ]);

        $response = $this->actingAs($user)->delete("/assets/{$asset->id}");

        $response->assertRedirect('/assets');
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_user_can_download_buku_profil_pdf()
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-004',
            'jenis_aset' => 'Tanah'
        ]);

        $response = $this->actingAs($user)->get("/assets/{$asset->id}/buku-profil");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
