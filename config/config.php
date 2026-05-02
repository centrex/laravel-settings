<?php

return [
    'table' => env('SETTINGS_TABLE', 'settings'),
    'connection' => env('SETTINGS_DB_CONNECTION'),
    'load_migrations' => env('SETTINGS_LOAD_MIGRATIONS', true),

    'cache_key' => 'settings.cache',
    'cache_prefix' => env('SETTINGS_CACHE_PREFIX', 'settings'),
    'cache_ttl' => env('SETTINGS_CACHE_TTL', 3600),
    'tenant_id' => env('SETTINGS_TENANT_ID', 1),

    'defaults' => [],
];
