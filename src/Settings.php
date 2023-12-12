<?php

declare(strict_types=1);

namespace Centrex\LaravelSettings;

use Centrex\LaravelSettings\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Settings
{
    public function set($key, $value)
    {
        Setting::updateOrCreate([
            'key' => $key,
        ], [
            'value' => $value,
        ]);
    }

    // Get setting value by key or default value if not exists
    public function get($key, $default = null)
    {
        if (Setting::exists($key)) {
            return Setting::where('key', $key)->first()->value;
        }

        return $default;
    }

    public function chargeConfig()
    {
        if ( ! Schema::hasTable('settings')) {
            return;
        }

        $settings = Cache::rememberForever('settings-db', function () {
            return Setting::autoload()->get()->toBase();
        });

        foreach (Arr::dot(config('settings')) as $key => $setting) {
            if ( ! $settings->contains('key', $key)) {
                app('config')->set([$key => $setting]);
            }
        }

        $settings->each(fn ($setting) => app('config')->set([$setting->key => $setting->value]));
    }

    public function refreshCache()
    {
        Cache::forget('settings-db');
        Cache::rememberForever('settings-db', fn () => Setting::get()->toBase());

        return $this->chargeConfig();
    }
}
