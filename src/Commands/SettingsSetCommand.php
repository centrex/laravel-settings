<?php

declare(strict_types = 1);

namespace Centrex\Settings\Commands;

use Centrex\Settings\Facades\Settings;
use Illuminate\Console\Command;

final class SettingsSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Setting:set
                            {key : Setting key}
                            {value : Setting value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an setting.';

    /** Execute the console command. */
    public function handle(): void
    {
        Settings::set($this->argument('key'), $this->argument('value'));

        $this->info('Setting added.');
    }
}
