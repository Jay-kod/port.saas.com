<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE (SaaS Phase 5): `colors` is currently a flat token map. It
        // becomes a nested {"dark": {...}, "light": {...}} map when the
        // light/dark mode overhaul lands. See
        // docs/agents/04-THEMING-DOMAINS.md before editing this file.
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('colors'); // flat token map today (pre-Phase-5)
            $table->boolean('is_active')->default(false); // "recommended" theme
            $table->boolean('is_default')->default(false); // fallback theme
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
