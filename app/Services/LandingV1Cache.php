<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LandingV1Cache
{
    public static function minutes(): int
    {
        return (int) config('landing_v1.page_cache_minutes', config('landing_v1.homepage_cache_minutes', 10));
    }

    public static function enabled(): bool
    {
        return self::minutes() > 0;
    }

    public static function key(string $suffix): string
    {
        return 'landing_v1.' . $suffix . '.' . app()->getLocale();
    }

    public static function remember(string $key, callable $callback)
    {
        if (!self::enabled()) {
            return $callback();
        }

        return Cache::remember($key, now()->addMinutes(self::minutes()), $callback);
    }
}
