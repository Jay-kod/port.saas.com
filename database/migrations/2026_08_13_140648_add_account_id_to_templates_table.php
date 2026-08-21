<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.1.
 * NULL = global/platform template, visible to every tenant. Non-null =
 * a private template belonging to that Account. This column stays
 * nullable forever (unlike profile_id elsewhere) — NULL is a
 * meaningful, permanent state here, not just a backfill artifact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });
    }
};
