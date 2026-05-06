<?php

declare(strict_types = 1);

namespace Centrex\Settings;

use Centrex\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\{Arr, Collection};
use Illuminate\Support\Facades\{Cache, Schema};
use RuntimeException;

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

    private ?bool $tableReady = null;

    /**
     * Set a setting value.
     *
     * @param  string  $key  Setting key (dot notation supported)
     * @param  mixed  $value  Setting value
     */
    public function set(string $key, mixed $value, array $options = []): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        $scope = $options['scope'] ?? null;
        $tenantId = (int) ($options['tenant_id'] ?? $this->tenantId());
        $identity = $this->identity($key, $scope instanceof Model ? $scope : null, $tenantId);
        $existing = Setting::query()->where($identity)->first();

        if ($existing?->is_locked && !($options['force'] ?? false)) {
            throw new RuntimeException("Setting [{$key}] is locked.");
        }

        Setting::withoutEvents(
            fn (): Setting => Setting::updateOrCreate(
                $identity,
                [
                    'value'            => $value,
                    'type'             => $options['type'] ?? $this->inferType($value),
                    'group'            => $options['group'] ?? $this->groupFromKey($key),
                    'autoload'         => (bool) ($options['autoload'] ?? true),
                    'is_encrypted'     => (bool) ($options['is_encrypted'] ?? false),
                    'validation_rules' => $options['validation_rules'] ?? null,
                    'is_locked'        => (bool) ($options['is_locked'] ?? false),
                    'description'      => $options['description'] ?? null,
                    'metadata'         => $options['metadata'] ?? null,
                    'updated_by'       => $options['updated_by'] ?? null,
                    'created_by'       => $options['created_by'] ?? null,
                ],
            ),
        );

        $this->forgetCachedKey($key, $scope instanceof Model ? $scope : null, $tenantId);

        if ((bool) ($options['refresh'] ?? true)) {
            $this->refreshCache($tenantId);
        }
    }

    /**
     * Get a setting value.
     *
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value if not found
     */
    public function get(string $key, mixed $default = null, ?Model $scope = null, ?int $tenantId = null): mixed
    {
        $setting = $this->getCachedSetting($key, $scope, $tenantId);

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

        $settings = $this->autoloaded();
        $defaults = Arr::dot(config('settings.defaults', []));

        foreach ($defaults as $key => $value) {
            if (!$settings->has($key)) {
                config([$key => $value]);
            }
        }

        $settings
            ->filter(static fn (Setting $setting): bool => str_contains((string) $setting->key, '.'))
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
    public function refreshCache(?int $tenantId = null): self
    {
        Cache::forget($this->autoloadCacheKey($tenantId));
        Cache::forget($this->cacheKey());
        $this->loadIntoConfig();

        return $this;
    }

    /**
     * Get cached settings collection.
     */
    public function all(): array
    {
        return $this->autoloaded()
            ->mapWithKeys(static fn (Setting $setting): array => [$setting->key => $setting->value])
            ->all();
    }

    /**
     * Get autoloaded cached settings.
     */
    public function autoloaded(?int $tenantId = null): Collection
    {
        if (!$this->settingsTableExists()) {
            return collect();
        }

        $cacheKey = $this->autoloadCacheKey($tenantId);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return collect($cached)
                ->mapWithKeys(fn (array $attributes): array => [
                    (string) $attributes['key'] => $this->settingFromAttributes($attributes),
                ]);
        }

        Cache::forget($cacheKey);

        $settings = Setting::query()
            ->tenant($tenantId ?? $this->tenantId())
            ->forScope()
            ->autoload()
            ->get()
            ->keyBy('key');

        Cache::put(
            $cacheKey,
            $settings
                ->map(static fn (Setting $setting): array => $setting->getAttributes())
                ->all(),
            $this->cacheTtl(),
        );

        return $settings;
    }

    /**
     * Check if setting exists.
     */
    public function has(string $key): bool
    {
        return $this->getCachedSetting($key) !== null;
    }

    /**
     * Remove a setting.
     */
    public function forget(string $key): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        Setting::query()->tenant($this->tenantId())->where('key', $key)->delete();
        $this->forgetCachedKey($key);
        $this->refreshCache();
    }

    private function cacheKey(): string
    {
        return config('settings.cache_key', self::CACHE_KEY);
    }

    private function autoloadCacheKey(?int $tenantId = null): string
    {
        return $this->cachePrefix() . ':autoload:' . ($tenantId ?? $this->tenantId()) . ':' . $this->cacheKey();
    }

    private function itemCacheKey(string $key, ?Model $scope = null, ?int $tenantId = null): string
    {
        $scopeKey = $scope instanceof Model
            ? $scope->getMorphClass() . ':' . $scope->getKey()
            : 'global';

        return $this->cachePrefix() . ':item:' . ($tenantId ?? $this->tenantId()) . ':' . $scopeKey . ':' . $key;
    }

    private function getCachedSetting(string $key, ?Model $scope = null, ?int $tenantId = null): ?Setting
    {
        if (!$this->settingsTableExists()) {
            return null;
        }

        $cacheKey = $this->itemCacheKey($key, $scope, $tenantId);
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && array_key_exists('found', $cached)) {
            return $cached['found'] === true
                ? $this->settingFromAttributes((array) $cached['attributes'])
                : null;
        }

        Cache::forget($cacheKey);

        $setting = Setting::query()
            ->effective($key, $scope, $tenantId ?? $this->tenantId())
            ->first();

        Cache::put(
            $cacheKey,
            $setting instanceof Setting
                ? ['found' => true, 'attributes' => $setting->getAttributes()]
                : ['found' => false],
            $this->cacheTtl(),
        );

        return $setting;
    }

    private function settingFromAttributes(array $attributes): Setting
    {
        return (new Setting())->newFromBuilder($attributes);
    }

    private function forgetCachedKey(string $key, ?Model $scope = null, ?int $tenantId = null): void
    {
        Cache::forget($this->itemCacheKey($key, $scope, $tenantId));

        if ($scope instanceof Model) {
            Cache::forget($this->itemCacheKey($key, null, $tenantId));
        }
    }

    public function forgetSettingCache(string $key, ?Model $scope = null, ?int $tenantId = null): void
    {
        $this->forgetCachedKey($key, $scope, $tenantId);
        Cache::forget('setting_exists:' . ($tenantId ?? $this->tenantId()) . ":{$key}");
        Cache::forget("setting_exists:{$key}");
    }

    private function tenantId(): int
    {
        return (int) config('settings.tenant_id', 1);
    }

    private function cachePrefix(): string
    {
        return (string) config('settings.cache_prefix', 'settings');
    }

    private function cacheTtl(): int
    {
        return max(1, (int) config('settings.cache_ttl', 3600));
    }

    private function identity(string $key, ?Model $scope, int $tenantId): array
    {
        return [
            'tenant_id'  => $tenantId,
            'scope_type' => $scope?->getMorphClass(),
            'scope_id'   => $scope?->getKey(),
            'key'        => $key,
        ];
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            $value === null   => 'null',
            is_bool($value)   => 'boolean',
            is_int($value)    => 'integer',
            is_float($value)  => 'float',
            is_array($value)  => 'array',
            is_object($value) => 'json',
            default           => 'string',
        };
    }

    private function groupFromKey(string $key): string
    {
        return str_contains($key, '.') ? str($key)->before('.')->toString() : 'general';
    }

    private function settingsTableExists(): bool
    {
        if ($this->tableReady === true) {
            return true;
        }

        try {
            $setting = new Setting();
            $schema = Schema::connection($setting->getConnectionName() ?: config('database.default'));
            $table = $setting->getTable();

            $ready = $schema->hasTable($table)
                && $schema->hasColumn($table, 'tenant_id')
                && $schema->hasColumn($table, 'scope_type')
                && $schema->hasColumn($table, 'scope_id')
                && $schema->hasColumn($table, 'deleted_at');

            if ($ready) {
                $this->tableReady = true;
            }

            return $ready;
        } catch (\Throwable) {
            return false;
        }
    }
}
