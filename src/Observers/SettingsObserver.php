<?php

declare(strict_types=1);

namespace Centrex\Settings\Observers;

use Centrex\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsObserver
{
    /**
     * @param Setting $settings
     * @return void
     */
    public function updated(Setting $settings)
    {
        // Refresh the cached list of settings
        Cache::forget('settings.cache');
    }
}