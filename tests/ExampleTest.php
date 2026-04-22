<?php

declare(strict_types = 1);

use Centrex\Settings\Models\Setting;

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
