# Manage settings in laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/centrex/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-settings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-settings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/laravel-settings/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/laravel-settings?style=flat-square)](https://packagist.org/packages/centrex/laravel-settings)

Settings for Laravel allows you to store your application settings in the database. It works alongside of the built-in configuration system that Laravel offers. With this package, you can store application specific settings.

## Contents

- [Installation](#installation)
- [Usage Examples](#usage)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require centrex/laravel-settings
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="settings-config"
```

You can publish and run the migrations with:

```bash
php artisan migrate
```

## Usage

To get and retrieve stored settings, you can do it easily with the Settings Facade or by using the settings() helper function:

```php
// Set a setting
Settings::set('foo', 'bar');
settings()->set('foo', 'bar');
settings(['foo' => 'bar']);

// Get a setting
Settings::get('foo'); // 'bar'
settings()->get('foo');
settings('foo');

// Check existence
if (settings()->has('app.debug')) {
    // ...
}

// Remove a setting
settings()->forget('old.setting');

// Refresh cache
settings()->refreshCache();
```

```php
// Check if setting exists (cached)
Setting::exists('app.timezone');

// Get autoloaded settings
Setting::autoload()->get();

// Get settings in a group
Setting::group('email')->get();

// Find settings with matching keys
Setting::keyLike('app.')->get();

// Remove a setting
Setting::remove('old.setting');
```
### Helpers

```php
// Get a setting with default value
$timezone = settings('app.timezone', 'UTC');

// Get using dedicated function
$debug = get_setting('app.debug', false);

// Set a setting
set_setting('app.maintenance_mode', true);

// Check existence
if (setting_exists('app.feature_flag')) {
    // ...
}

// Remove a setting
remove_setting('old.setting');

// Access Settings instance directly
settings()->refreshCache();
```

## Testing

🧹 Keep a modern codebase with **Pint**:
```bash
composer lint
```

✅ Run refactors using **Rector**
```bash
composer refacto
```

⚗️ Run static analysis using **PHPStan**:
```bash
composer test:types
```

✅ Run unit tests using **PEST**
```bash
composer test:unit
```

🚀 Run the entire test suite:
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [centrex](https://github.com/centrex)
- [All Contributors](../../contributors)
- [rawilk/laravel-settings](https://github.com/rawilk/laravel-settings)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
