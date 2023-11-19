<?php

declare(strict_types=1);

namespace Centrex\LaravelSettings\Commands;

use Centrex\LaravelSettings\Facades\Setting;
use Illuminate\Console\Command;

class SettingsSetCommand extends Command
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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Setting::set($this->argument('key'), $this->argument('value'));

        $this->info('Setting added.');
    }
}
