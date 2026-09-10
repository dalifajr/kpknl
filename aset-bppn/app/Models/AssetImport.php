<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class AssetImport extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'selected_columns' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(AssetImportItem::class, 'asset_import_id');
    }

    public function failedItems()
    {
        return $this->hasMany(AssetImportItem::class, 'asset_import_id')->where('status', 'failed');
    }

    public function successItems()
    {
        return $this->hasMany(AssetImportItem::class, 'asset_import_id')->where('status', 'success');
    }

    public function getSuccessRateAttribute(): int
    {
        if ($this->total_rows <= 0) return 0;
        return round(($this->success_rows / $this->total_rows) * 100);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
