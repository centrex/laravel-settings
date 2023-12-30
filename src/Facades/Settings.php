<?php

declare(strict_types=1);

namespace Centrex\LaravelSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\LaravelSettings\Setting
 */
final class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\LaravelSettings\Settings::class;
    }
}
