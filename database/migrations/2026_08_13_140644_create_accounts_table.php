<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.1.
 * The billing/plan/tenant-owner record. One Account can own many
 * Profiles (only actually happens starting Phase 8's agency tier).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_user_id')->constrained('users');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('plan_slug')->default('free');
            $table->timestamp('trial_ends_at')->nullable();
            $table->unsignedInteger('ai_generations_used_current_period')->default(0);
            $table->timestamp('ai_generations_period_started_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
