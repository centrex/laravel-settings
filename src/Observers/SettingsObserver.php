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
        app(Settings::class)->refreshCache();
        cache()->forget("setting_exists:{$setting->key}");
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
