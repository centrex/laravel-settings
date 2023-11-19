<?php

if (!function_exists('get_setting')) {
    
    /**
     * Get setting
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    function get_option($key, $value = null)
    {
        return app('settings')->get($key, $value);
    }
}

if (!function_exists('set_setting')) {
    
    /**
     * Set setting
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    function set_option($key, $value = null)
    {
        return app('settings')->set($key, $value);
    }
}

if (!function_exists('option_exists')) {
    
    /**
     * Check if setting exists
     *
     * @param string $key
     * @return bool
     */
    function option_exists($key) : bool
    {
        return app('settings')->exists($key);
    }
}