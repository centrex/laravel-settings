<?php

declare(strict_types = 1);

if (!function_exists('settings')) {
    /**
     * Get or set application settings from database
     *
     * @param  string|null  $key  Setting key (dot notation supported)
     * @param  mixed  $default  Default value if setting doesn't exist
     * @return mixed|Settings Returns setting value or Settings instance when no key provided
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $settings = app('settings');

        if (is_null($key)) {
            return $settings;
        }

        return $settings->get($key, value($default));
    }
}

if (!function_exists('get_setting')) {
    /**
     * Get a setting value
     *
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value if setting doesn't exist
     */
    function get_setting(string $key, mixed $default = null): mixed
    {
        return app('settings')->get($key, $default);
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set a setting value
     *
     * @param  string  $key  Setting key
     * @param  mixed  $value  Value to set
     */
    function set_setting(string $key, mixed $value = null): void
    {
        app('settings')->set($key, $value);
    }
}

if (!function_exists('setting_exists')) {
    /**
     * Check if a setting exists
     *
     * @param  string  $key  Setting key to check
     */
    function setting_exists(string $key): bool
    {
        return app('settings')->has($key);
    }
}

if (!function_exists('remove_setting')) {
    /**
     * Remove a setting
     *
     * @param  string  $key  Setting key to remove
     */
    function remove_setting(string $key): void
    {
        app('settings')->forget($key);
    }
}
