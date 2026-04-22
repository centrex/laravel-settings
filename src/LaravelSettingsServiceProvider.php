<?php

declare(strict_types = 1);

namespace Centrex\Settings;

use Illuminate\Support\ServiceProvider;

final class LaravelSettingsServiceProvider extends ServiceProvider
{
    /** Bootstrap the application services. */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->app->booted(fn (): \Centrex\Settings\Settings => $this->app->make(\Centrex\Settings\Settings::class)->loadIntoConfig());

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('settings.php'),
            ], 'settings-config');

            $this->commands([
                Commands\SettingsSetCommand::class,
            ]);
        }
    }

    /** Register the application services. */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'settings');
        $this->app->singleton(Settings::class, fn (): Settings => new Settings());
        $this->app->alias(Settings::class, 'settings');
    }
}
