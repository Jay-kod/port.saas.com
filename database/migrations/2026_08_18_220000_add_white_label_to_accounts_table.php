<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 8 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Adds white-label branding columns to accounts table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('custom_brand_name')->nullable()->after('plan_slug');
            $table->string('custom_logo_path')->nullable()->after('custom_brand_name');
            $table->boolean('hide_platform_branding')->default(false)->after('custom_logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['custom_brand_name', 'custom_logo_path', 'hide_platform_branding']);
        });
    }
};
