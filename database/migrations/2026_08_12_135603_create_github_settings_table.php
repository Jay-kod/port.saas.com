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
        Schema::create('github_settings', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->text('access_token')->nullable(); // encrypted cast
            $table->boolean('auto_sync')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_settings');
    }
};
