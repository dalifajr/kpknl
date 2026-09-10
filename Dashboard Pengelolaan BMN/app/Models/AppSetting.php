<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $guarded = ['id'];

    /**
     * Get setting value by key with optional default fallback
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue(string $key, ?string $value, string $group = 'general', ?string $desc = null): static
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_group' => $group,
                'description' => $desc,
            ]
        );
    }
}
