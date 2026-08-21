<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 9 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Creates portfolio_reports table for abuse reporting and moderation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('reason'); // spam, inappropriate, copyright, scam, other
            $table->text('details')->nullable();
            $table->string('reporter_ip')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_reports');
    }
};
