<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetImportItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
        ];
    }

    public function assetImport()
    {
        return $this->belongsTo(AssetImport::class, 'asset_import_id');
    }

    public function createdAsset()
    {
        return $this->belongsTo(Asset::class, 'created_asset_id');
    }
}
