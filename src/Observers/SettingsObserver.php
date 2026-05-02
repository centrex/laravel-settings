<?php

declare(strict_types = 1);

namespace Centrex\Settings\Observers;

use Centrex\Settings\Models\Setting;
use Centrex\Settings\Settings;

class SettingsObserver
{
    /**
     * Handle cache invalidation when a setting is updated.
     *
     * @param  Setting  $setting  The setting model instance
     */
    public function updated(Setting $setting): void
    {
        $this->flushSettingCache($setting);
    }

    /**
     * Handle cache invalidation when a setting is created.
     *
     * @param  Setting  $setting  The setting model instance
     */
    public function created(Setting $setting): void
    {
        $this->flushSettingCache($setting);
    }

    /**
     * Handle cache invalidation when a setting is deleted.
     *
     * @param  Setting  $setting  The setting model instance
     */
    public function deleted(Setting $setting): void
    {
        $this->flushSettingCache($setting);
    }

    /**
     * Flush all relevant cache entries for a setting.
     *
     * @param  Setting  $setting  The setting model instance
     */
    protected function flushSettingCache(Setting $setting): void
    {
        $settings = app(Settings::class);
        $tenantId = (int) $setting->tenant_id;

        $settings->forgetSettingCache(
            (string) $setting->key,
            $setting->scope,
            $tenantId,
        );

        $settings->refreshCache($tenantId);
        cache()->forget("setting_exists:{$setting->tenant_id}:{$setting->key}");
    }

    /**
     * Handle cache invalidation when a setting is restored (if using soft deletes).
     *
     * @param  Setting  $setting  The setting model instance
     */
    public function restored(Setting $setting): void
    {
        $this->flushSettingCache($setting);
    }
}
