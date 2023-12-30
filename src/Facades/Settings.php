<?php

declare(strict_types = 1);

namespace Centrex\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Settings\Setting
 */
final class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\Settings\Settings::class;
    }
}
