<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7.5 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Adds is_discoverable and meta_description columns to profiles table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('is_discoverable')->default(false)->after('is_published');
            $table->string('meta_description')->nullable()->after('is_discoverable');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['is_discoverable', 'meta_description']);
        });
    }
};
