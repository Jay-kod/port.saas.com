<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE (SaaS Phase 1): gains a profile_id FK. See
        // docs/agents/02-MULTI-TENANCY-FOUNDATION.md.
        Schema::create('resume_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->text('job_description')->nullable();
            $table->json('tailored_content')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('pending'); // pending|completed|failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_generations');
    }
};
