<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for application settings storage.
 *
 * Creates a flexible key-value store for application settings with:
 * - Unique key indexing
 * - Group organization
 * - Autoload control
 * - Full audit capabilities
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('settings.table', 'settings');
        $connection = config('settings.connection');

        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            // Recommended for proper Unicode support
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('tenant_id')->default(1)->index();
            $table->nullableMorphs('scope');
            $table->string('key', 191)
                ->comment('Unique setting identifier (max 191 chars for index compatibility)');

            $table->mediumText('value')
                ->nullable()
                ->comment('Serialized setting value');

            $table->boolean('autoload')
                ->default(true)
                ->index()
                ->comment('Whether to load this setting automatically');

            $table->string('group', 50)
                ->default('general')
                ->index()
                ->comment('Logical grouping of settings');

            $table->boolean('is_encrypted')->default(false);
            $table->text('validation_rules')->nullable();
            $table->enum('type', ['string', 'integer', 'float', 'boolean', 'array', 'json', 'null'])->default('string');
            $table->boolean('is_locked')->default(false)->index();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index(['group', 'autoload'], 'settings_group_autoload_index');
            $table->index(['tenant_id', 'group', 'autoload'], 'settings_tenant_group_autoload_index');
            $table->index(['tenant_id', 'scope_type', 'scope_id'], 'settings_tenant_scope_index');
            $table->unique(['tenant_id', 'scope_type', 'scope_id', 'key'], 'settings_scope_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('settings.connection'))->dropIfExists(config('settings.table', 'settings'));
    }
};
