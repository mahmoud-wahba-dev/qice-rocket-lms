<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        try {
            return Cache::remember($key, now()->addMinutes(self::minutes()), $callback);
        } catch (Throwable $e) {
            Log::warning('LandingV1Cache read failed, rebuilding', [
                'key' => $key,
                'message' => $e->getMessage(),
            ]);

            try {
                Cache::forget($key);
            } catch (Throwable) {
                // ignore
            }

            return $callback();
        }
    }
}
