<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7.3 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Creates the job_applications table for the Job Application Kanban tracker.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('company');
            $table->string('role');
            $table->string('job_url')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('status')->default('saved'); // saved, applied, interviewing, offer, rejected
            $table->date('applied_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('resume_generation_id')->nullable()->constrained('resume_generations')->nullOnDelete();
            $table->foreignId('cover_letter_generation_id')->nullable()->constrained('cover_letter_generations')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
