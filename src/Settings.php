<?php

declare(strict_types = 1);

namespace Centrex\Settings;

use Centrex\Settings\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Cache, Schema};

/**
 * Application settings management service.
 *
 * Provides a fluent interface for managing application settings with:
 * - Database persistence
 * - Config integration
 * - Caching layer
 * - Dot-notation support
 */
final class Settings
{
    private const CACHE_KEY = 'settings.cache';

    /**
     * Set a setting value.
     *
     * @param  string  $key  Setting key (dot notation supported)
     * @param  mixed  $value  Setting value
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        $this->refreshCache();
    }

    /**
     * Get a setting value.
     *
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value if not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null)
    {
        return Cache::rememberForever(self::CACHE_KEY . ':' . $key, fn () => optional(Setting::where('key', $key)->first())->value ?? $default);
    }

    /**
     * Load settings into application config.
     */
    public function chargeConfig(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $settings = $this->getCachedSettings();

        // Merge default config values
        foreach (Arr::dot(config('settings', [])) as $key => $value) {
            if (!$settings->has($key)) {
                config([$key => $value]);
            }
        }

        // Apply database settings
        $settings->each(function ($setting): void {
            config([$setting->key => $setting->value]);
        });
    }

    /**
     * Refresh the settings cache.
     */
    public function refreshCache(): self
    {
        Cache::forget(self::CACHE_KEY);
        $this->getCachedSettings(true);

        $this->chargeConfig();

        return $this;
    }

    /**
     * Get cached settings collection.
     */
    private function getCachedSettings(bool $refresh = false): \Illuminate\Support\Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            $refresh ? 0 : null,
            fn () => Setting::get()->keyBy('key'),
        );
    }

    /**
     * Check if setting exists.
     */
    public function has(string $key): bool
    {
        return $this->getCachedSettings()->has($key);
    }

    /**
     * Remove a setting.
     */
    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY . ':' . $key);
        $this->refreshCache();
    }
}
