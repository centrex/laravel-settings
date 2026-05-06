<?php

declare(strict_types = 1);

use Centrex\Settings\Models\Setting;
use Centrex\Settings\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

it('loads defaults into config and persists settings through the service', function (): void {
    config()->set('settings.defaults', [
        'mail.from.address' => 'default@example.com',
    ]);

    settings()->loadIntoConfig();

    expect(config('mail.from.address'))->toBe('default@example.com');

    set_setting('mail.from.address', 'ops@example.com');

    expect(get_setting('mail.from.address'))->toBe('ops@example.com')
        ->and(setting_exists('mail.from.address'))->toBeTrue()
        ->and(config('mail.from.address'))->toBe('ops@example.com')
        ->and(Setting::query()->where('key', 'mail.from.address')->exists())->toBeTrue();
});

it('forgets persisted settings cleanly', function (): void {
    set_setting('app.timezone', 'Asia/Dhaka');

    remove_setting('app.timezone');

    expect(setting_exists('app.timezone'))->toBeFalse()
        ->and(get_setting('app.timezone', 'UTC'))->toBe('UTC');
});

it('rebuilds autoload cache when a serialized object payload cannot be restored', function (): void {
    Setting::query()->create([
        'tenant_id' => 1,
        'key'       => 'app.name',
        'value'     => 'Octopus',
        'autoload'  => true,
        'group'     => 'app',
        'type'      => 'string',
    ]);

    Cache::put(
        'settings:autoload:1:settings.cache',
        unserialize('O:29:"Illuminate\Support\Collection":0:{}', ['allowed_classes' => false]),
    );

    $settings = app(Settings::class)->autoloaded();

    expect($settings)->toBeInstanceOf(Collection::class)
        ->and($settings->get('app.name'))->toBeInstanceOf(Setting::class)
        ->and($settings->get('app.name')->value)->toBe('Octopus')
        ->and(Cache::get('settings:autoload:1:settings.cache'))->toBeArray();
});

it('stores cached settings as primitive arrays', function (): void {
    set_setting('mail.from.name', 'Support');

    expect(get_setting('mail.from.name'))->toBe('Support');

    expect(Cache::get('settings:autoload:1:settings.cache'))->toBeArray()
        ->and(Cache::get('settings:item:1:global:mail.from.name'))->toBeArray();
});
