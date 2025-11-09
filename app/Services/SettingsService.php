<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected $cacheKey = 'app_settings';

    public function all(): array
    {
        return Cache::rememberForever($this->cacheKey, function() {
            // load from DB table 'settings' if exists, or fallback to config
            if (class_exists(\App\Models\Setting::class)) {
                return \App\Models\Setting::all()->pluck('value','key')->toArray();
            }
            return config('app_settings', []);
        });
    }

    public function get(string $key)
    {
        $all = $this->all();
        return $all[$key] ?? null;
    }

    public function updateMany(array $data): array
    {
        // if you have settings table, upsert; otherwise store in cache
        if (class_exists(\App\Models\Setting::class)) {
            foreach ($data as $k => $v) {
                \App\Models\Setting::updateOrCreate(['key'=>$k], ['value'=>$v]);
            }
            Cache::forget($this->cacheKey);
            return $this->all();
        }

        // fallback: store in cache
        Cache::put($this->cacheKey, $data);
        return $data;
    }
}
