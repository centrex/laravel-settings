<?php

declare(strict_types=1);

namespace Centrex\LaravelSettings\Models;

use Centrex\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The table that this model should read from
     *
     * @var string
     */
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            Settings::refreshCache();
        });

        static::deleted(function ($setting) {
            Settings::refreshCache();
        });
    }

    /**
     * Check if a key exists in the database
     *
     * @param  string  $key
     */
    public function exists($key): bool
    {
        return self::where('key', $key)->exists();
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => unserialize($value),
            set: fn ($value) => serialize($value),
        );
    }

    /**
     * Delete a key from the database
     *
     * @param  string  $key
     */
    public function remove($key): bool
    {
        return (bool) self::where('key', $key)->delete();
    }

    public function scopeAutoload($query)
    {
        return $query->where('autuload', 1);
    }
}
