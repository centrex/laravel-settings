# Manage settings in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/centrex/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/laravel-settings/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/laravel-settings?style=flat-square)](https://packagist.org/packages/centrex/laravel-settings)

Database-backed application settings with a caching layer, Laravel config integration, and convenient helper functions. Settings are stored in a `settings` table and cached forever until changed.

## Installation

```bash
composer require centrex/laravel-settings
php artisan vendor:publish --tag="laravel-settings-migrations"
php artisan migrate
```

## Usage

### Via helper functions

```php
// Get a setting (with optional default)
settings('site.name', 'My App');
get_setting('site.name', 'My App');

// Set a setting
set_setting('site.name', 'Acme Corp');

// Check existence
setting_exists('site.name');  // true

// Remove a setting
remove_setting('site.name');

// Get the Settings service instance
$settingsService = settings();
```

### Via facade

```php
use Centrex\Settings\Facades\Settings;

Settings::set('maintenance.enabled', true);
Settings::get('maintenance.enabled', false);
Settings::has('maintenance.enabled');
Settings::forget('maintenance.enabled');

// Force a cache refresh
Settings::refreshCache();
```

### Config integration

Settings are merged into the Laravel config on boot via `chargeConfig()`. After that, database settings are accessible through `config()`:

```php
config('site.name');  // returns the value stored in the settings table
```

### Caching

- Reads use `Cache::rememberForever` per key.
- Any `set()` or `forget()` call automatically flushes the cache and reloads config.

## Testing

```bash
composer test        # full suite
composer test:unit   # pest only
composer test:types  # phpstan
composer lint        # pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [centrex](https://github.com/centrex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
