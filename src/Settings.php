<?php

declare(strict_types = 1);

namespace Centrex\Settings;

use Centrex\Settings\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
        if (!$this->settingsTableExists()) {
            return;
        }

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
        $setting = $this->getCachedSettings()->get($key);

        return $setting?->value ?? value($default);
    }

    /**
     * Load settings into application config.
     */
    public function loadIntoConfig(): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        $settings = $this->getCachedSettings();
        $defaults = Arr::dot(config('settings.defaults', []));

        foreach ($defaults as $key => $value) {
            if (!$settings->has($key)) {
                config([$key => $value]);
            }
        }

        $settings
            ->filter(static fn (Setting $setting): bool => $setting->autoload)
            ->each(static function (Setting $setting): void {
                config([$setting->key => $setting->value]);
            });
    }

    /**
     * Backwards-compatible alias for older package consumers.
     */
    public function chargeConfig(): void
    {
        $this->loadIntoConfig();
    }

    /**
     * Refresh the settings cache.
     */
    public function refreshCache(): self
    {
        Cache::forget($this->cacheKey());
        $this->getCachedSettings();
        $this->loadIntoConfig();

        return $this;
    }

    /**
     * Get cached settings collection.
     */
    public function all(): array
    {
        return $this->getCachedSettings()
            ->mapWithKeys(static fn (Setting $setting): array => [$setting->key => $setting->value])
            ->all();
    }

    /**
     * Get cached settings collection.
     */
    private function getCachedSettings(): Collection
    {
        if (!$this->settingsTableExists()) {
            return collect();
        }

        return Cache::rememberForever(
            $this->cacheKey(),
            static fn (): Collection => Setting::query()->get()->keyBy('key'),
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
        if (!$this->settingsTableExists()) {
            return;
        }

        Setting::where('key', $key)->delete();
        $this->refreshCache();
    }

    private function cacheKey(): string
    {
        return config('settings.cache_key', self::CACHE_KEY);
    }

    private function settingsTableExists(): bool
    {
        return Schema::hasTable((new Setting())->getTable());
    }
}
