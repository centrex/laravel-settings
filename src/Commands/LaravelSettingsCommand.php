<?php

declare(strict_types=1);

namespace Centrex\LaravelSettings\Commands;

use Illuminate\Console\Command;

class LaravelSettingsCommand extends Command
{
    public $signature = 'laravel-settings';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
