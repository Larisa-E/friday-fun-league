<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class StatsPageCache
{
    private const KEY = 'stats.page.payload.v1';

    public static function remember(Closure $resolver): array
    {
        return Cache::remember(self::key(), now()->addMinute(), $resolver);
    }

    public static function forget(): void
    {
        Cache::forget(self::key());
    }

    private static function key(): string
    {
        return self::KEY . '.' . app()->environment();
    }
}