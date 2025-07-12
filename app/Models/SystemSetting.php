<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'setting_group',
        'setting_label',
        'setting_description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get setting value by key with caching.
     */
    public static function getValue($key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('setting_key', $key)->first();
            return $setting ? static::castValue($setting->setting_value, $setting->setting_type) : $default;
        });
    }

    /**
     * Set setting value by key.
     */
    public static function setValue($key, $value, $type = 'string', $group = 'general', $label = null, $description = null)
    {
        $setting = static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'setting_group' => $group,
                'setting_label' => $label ?? ucwords(str_replace('_', ' ', $key)),
                'setting_description' => $description,
            ]
        );

        Cache::forget("setting.{$key}");
        return $setting;
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue($value, $type)
    {
        return match($type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode($value, true),
            'float' => (float) $value,
            default => $value,
        };
    }

    /**
     * Get all settings by group.
     */
    public static function getByGroup($group)
    {
        return static::where('setting_group', $group)->get();
    }

    /**
     * Get EFRIS settings.
     */
    public static function getEfrisSettings()
    {
        return static::getByGroup('efris');
    }

    /**
     * Get email settings.
     */
    public static function getEmailSettings()
    {
        return static::getByGroup('email');
    }

    /**
     * Get system settings.
     */
    public static function getSystemSettings()
    {
        return static::getByGroup('system');
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache()
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("setting.{$setting->setting_key}");
        }
    }
} 