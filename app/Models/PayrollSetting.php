<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PayrollSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('payroll_settings_map', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    public static function getFloat(string $key, float $default = 0): float
    {
        return (float) static::getValue($key, $default);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }

    public static function forgetCache(): void
    {
        Cache::forget('payroll_settings_map');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }
}
