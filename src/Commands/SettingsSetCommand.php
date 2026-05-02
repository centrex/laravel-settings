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
                            {value : Setting value}
                            {--group= : Logical setting group}
                            {--type=string : Value type: string, integer, float, boolean, array, json, null}
                            {--no-autoload : Do not load into config automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an setting.';

    /** Execute the console command. */
    public function handle(): void
    {
        Settings::set($this->argument('key'), $this->castValue((string) $this->argument('value')), [
            'group' => $this->option('group'),
            'type' => $this->option('type') ?: 'string',
            'autoload' => !$this->option('no-autoload'),
        ]);

        $this->info('Setting added.');
    }

    private function castValue(string $value): mixed
    {
        return match ($this->option('type')) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => json_decode($value, true, flags: JSON_THROW_ON_ERROR),
            'null' => null,
            default => $value,
        };
    }
}
