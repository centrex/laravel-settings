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
        Schema::create('settings', function (Blueprint $table) {
            // Recommended for proper Unicode support
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->engine = 'InnoDB';

            $table->id();

            // Setting identification
            $table->string('key', 191)
                ->unique()
                ->comment('Unique setting identifier (max 191 chars for index compatibility)');

            // Setting value storage
            $table->text('value')
                ->nullable()
                ->comment('Serialized setting value');

            // Setting metadata
            $table->boolean('autoload')
                ->default(true)
                ->index()
                ->comment('Whether to load this setting automatically');

            $table->string('group', 50)
                ->default('general')
                ->index()
                ->comment('Logical grouping of settings');

            // Add encryption flag if needed
            $table->boolean('is_encrypted')->default(false);

            // Add validation rules column if needed
            $table->text('validation_rules')->nullable();

            // Add tenant scope if needed
            $table->unsignedBigInteger('tenant_id')->default(1)->index();

            // Add type casting information
            $table->enum('type', ['string', 'boolean', 'array', 'json'])->default('string');

            // Recommended for audit logging
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Advanced indexing
            $table->index(['group', 'autoload'], 'settings_group_autoload_index');

            // Full-text search if needed (MySQL/PostgreSQL)
            // $table->fullText(['key', 'value'], 'settings_search_index');
        });

        // For large installations, consider partitioning:
        // DB::statement('ALTER TABLE settings PARTITION BY KEY(`group`) PARTITIONS 5');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');

        // If using partitioning:
        // DB::statement('ALTER TABLE settings REMOVE PARTITIONING');
    }
};
