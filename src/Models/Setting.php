<?php

declare(strict_types = 1);

namespace Centrex\Settings\Models;

use Centrex\Settings\Facades\Settings;
use Centrex\Settings\Observers\SettingsObserver;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class Setting extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'tenant_id',
        'scope_type',
        'scope_id',
        'is_encrypted',
        'validation_rules',
        'type',
        'is_locked',
        'description',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'autoload'   => 'boolean',
        'is_encrypted' => 'boolean',
        'is_locked' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable((string) config('settings.table', 'settings'));

        $connection = config('settings.connection');

        if (is_string($connection) && trim($connection) !== '') {
            $this->setConnection($connection);
        }
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function booted(): void
    {
        self::observe(SettingsObserver::class);
    }

    /**
     * Check if a setting key exists.
     */
    public static function exists(string $key): bool
    {
        $tenantId = (int) config('settings.tenant_id', 1);

        return Cache::remember(
            "setting_exists:{$tenantId}:{$key}",
            now()->addHour(),
            fn () => self::query()->where('tenant_id', $tenantId)->where('key', $key)->exists(),
        );
    }

    /**
     * Get the unserialized value attribute.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value): mixed => $this->decodeValue($value),
            set: fn ($value): ?string => $value === null ? null : serialize($value),
        );
    }

    /**
     * Remove a setting by its key.
     */
    public static function remove(string $key): bool
    {
        return (bool) self::query()
            ->where('tenant_id', (int) config('settings.tenant_id', 1))
            ->where('key', $key)
            ->delete();
    }

    public function scopeTenant(Builder $query, ?int $tenantId = null): Builder
    {
        return $query->where('tenant_id', $tenantId ?? (int) config('settings.tenant_id', 1));
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

    public function scopeForScope(Builder $query, ?Model $scope = null): Builder
    {
        if ($scope === null) {
            return $query->whereNull('scope_type')->whereNull('scope_id');
        }

        return $query
            ->where('scope_type', $scope->getMorphClass())
            ->where('scope_id', $scope->getKey());
    }

    public function scopeEffective(Builder $query, string $key, ?Model $scope = null, ?int $tenantId = null): Builder
    {
        $query->tenant($tenantId)->where('key', $key);

        if ($scope === null) {
            return $query->forScope();
        }

        return $query
            ->where(function (Builder $query) use ($scope): void {
                $query->forScope($scope)
                    ->orWhere(fn (Builder $fallback): Builder => $fallback->forScope());
            })
            ->orderByRaw('CASE WHEN scope_type IS NULL THEN 1 ELSE 0 END');
    }

    public function scope(): MorphTo
    {
        return $this->morphTo();
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

    private function decodeValue(mixed $value): mixed
    {
        if ($value === null || !is_string($value)) {
            return $value;
        }

        try {
            $decoded = @unserialize($value, ['allowed_classes' => false]);

            if ($decoded !== false || $value === 'b:0;') {
                return $decoded;
            }
        } catch (Throwable) {
            // Support legacy rows that were stored as plain strings.
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (Throwable) {
            // Support legacy rows that were stored as plain strings.
        }

        return $value;
    }
}
