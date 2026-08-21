<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.1.
 * Nullable for now — made required in make_tenancy_columns_required.php
 * after `php artisan tenancy:backfill` has run.
 *
 * Also converts projects.slug and certificates.slug from globally
 * unique to unique-per-profile, since two different tenants will
 * inevitably want the same slug (see section 1.4's note on this).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['experiences', 'skills', 'resume_generations', 'github_settings'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles');
            });
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles');
            $table->dropUnique(['slug']);
            $table->unique(['profile_id', 'slug']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles');
            $table->dropUnique(['slug']);
            $table->unique(['profile_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['profile_id', 'slug']);
            $table->dropConstrainedForeignId('profile_id');
            $table->unique(['slug']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['profile_id', 'slug']);
            $table->dropConstrainedForeignId('profile_id');
            $table->unique(['slug']);
        });

        foreach (['experiences', 'skills', 'resume_generations', 'github_settings'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('profile_id');
            });
        }
    }
};
