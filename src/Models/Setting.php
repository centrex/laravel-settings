<?php

declare(strict_types=1);

namespace Centrex\Settings\Models;

use Centrex\Settings\Facades\Settings;
use Centrex\Settings\Observers\SettingsObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class Setting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'settings';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'autoload',
        'group',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'autoload' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function booted(): void
    {
        static::observe(SettingsObserver::class);
    }

    /**
     * Check if a setting key exists.
     */
    public static function exists(string $key): bool
    {
        return Cache::remember(
            "setting_exists:{$key}",
            now()->addHour(),
            fn () => self::where('key', $key)->exists()
        );
    }

    /**
     * Get the unserialized value attribute.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? unserialize($value) : null,
            set: fn ($value) => serialize($value),
        );
    }

    /**
     * Remove a setting by its key.
     */
    public static function remove(string $key): bool
    {
        return (bool) self::where('key', $key)->delete();
    }

    /**
     * Scope for autoloaded settings.
     */
    public function scopeAutoload(Builder $query): Builder
    {
        return $query->where('autoload', true);
    }

    /**
     * Scope for settings in a specific group.
     */
    public function scopeGroup(Builder $query, string $groupName): Builder
    {
        return $query->where('group', $groupName);
    }

    /**
     * Scope for settings matching a key pattern.
     */
    public function scopeKeyLike(Builder $query, string $pattern): Builder
    {
        return $query->where('key', 'LIKE', "{$pattern}%");
    }

    /**
     * Refresh the settings cache.
     */
    public function refreshCache(): void
    {
        Settings::refreshCache();
        Cache::forget("setting_exists:{$this->key}");
    }
}