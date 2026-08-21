<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7.2 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Creates the cover_letter_generations table for AI cover letters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letter_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->text('job_description');
            $table->longText('content')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letter_generations');
    }
};
