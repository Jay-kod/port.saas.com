<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE (SaaS Phase 1): gains an account_id FK (AI key/quota is
        // tracked per paying Account, not per Profile). See
        // docs/agents/02-MULTI-TENANCY-FOUNDATION.md.
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('openai'); // openai|anthropic|gemini...
            $table->text('api_key')->nullable(); // encrypted cast
            $table->string('model')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
