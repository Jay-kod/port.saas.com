<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.1.
 * AI provider key + quota tracked per paying Account, not per Profile.
 * Nullable for now — made required in make_tenancy_columns_required.php
 * after `php artisan tenancy:backfill` has run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });
    }
};
