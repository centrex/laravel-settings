<?php

declare(strict_types = 1);

namespace Centrex\Settings\Models;

use Centrex\Settings\Facades\Settings;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
     */
    protected static function booted(): void
    {
        static::saved(function ($setting): void {
            Settings::refreshCache();
        });

        static::deleted(function ($setting): void {
            Settings::refreshCache();
        });
    }

    /**
     * Check if a key exists in the database
     *
     * @param  string  $key
     */
    public static function exists($key): bool
    {
        return (bool) self::where('key', $key)->exists();
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value): mixed => unserialize($value),
            set: fn ($value): string => serialize($value),
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
        return $query->where('autoload', 1);
    }

    public function scopeGroup($query, $groupName)
    {
        return $query->whereGroup($groupName);
    }
}
