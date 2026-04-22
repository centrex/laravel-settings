<?php

declare(strict_types = 1);

namespace Centrex\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Settings\Settings
 */
final class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'settings';
    }
}
