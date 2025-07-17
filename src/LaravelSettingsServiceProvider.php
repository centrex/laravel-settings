<?php

declare(strict_types = 1);

namespace Centrex\Settings;

use Illuminate\Support\ServiceProvider;
use Centrex\Settings\Observers\SettingsObserver;

final class LaravelSettingsServiceProvider extends ServiceProvider
{
    /** Bootstrap the application services. */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'settings');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'settings');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('settings.php'),
            ], 'settings-config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/settings'),
            ], 'settings-views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/settings'),
            ], 'settings-assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/settings'),
            ], 'settings-lang');*/

            // Registering package commands.
            $this->commands([
                Commands\SettingsSetCommand::class,
            ]);
        }
    }

    /** Register the application services. */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'settings');

        // Register the main class to use with the facade
        $this->app->singleton('settings', fn (): \Centrex\Settings\Settings => new Settings());

        // Register the observer
        Settings::observe(SettingsObserver::class);
    }
}
