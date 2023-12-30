<?php

declare(strict_types = 1);

if (!function_exists('settings')) {
    /**
     * Function access to application settings in database
     *
     * @param  string  $key - key of setting
     * @param  string  $default - default value
     * @return object|string setting
     */
    function settings($key = null, $default = null)
    {
        if (is_null($key)) {
            return;
        }

        return app('settings')->get($key, value($default));
    }
}

if (!function_exists('get_setting')) {
    /**
     * Get setting
     *
     * @param  string  $key
     * @return mixed
     */
    function get_option($key, mixed $value = null)
    {
        return app('settings')->get($key, $value);
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set setting
     *
     * @param  string  $key
     * @return mixed
     */
    function set_option($key, mixed $value = null)
    {
        return app('settings')->set($key, $value);
    }
}

if (!function_exists('option_exists')) {
    /**
     * Check if setting exists
     *
     * @param  string  $key
     */
    function option_exists($key): bool
    {
        return app('settings')->exists($key);
    }
}
