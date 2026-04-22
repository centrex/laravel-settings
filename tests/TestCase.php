<?php

declare(strict_types = 1);

namespace Centrex\Settings\Tests;

use Centrex\Settings\LaravelSettingsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Centrex\\Settings\\Database\\Factories\\' . class_basename($modelName) . 'Factory',
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Schema::defaultStringLength(191);
    }
}
