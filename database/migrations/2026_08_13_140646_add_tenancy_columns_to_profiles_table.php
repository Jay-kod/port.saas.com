<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.1.
 * Nullable for now — made required in
 * make_tenancy_columns_required.php after `php artisan tenancy:backfill`
 * has run. Do not skip the backfill step before deploying this to an
 * environment with real data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
            $table->foreignId('user_id')->nullable()->after('account_id')->constrained('users');
            $table->string('slug')->nullable()->unique()->after('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('slug');
        });
    }
};
