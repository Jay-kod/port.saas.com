<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 (docs/agents/04-THEMING-DOMAINS.md):
 * Adds theme_id and theme_mode_default to profiles table,
 * and ensures existing theme color rows have nested dark/light shape.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('theme_id')->nullable()->after('slug')->constrained('themes')->nullOnDelete();
            $table->string('theme_mode_default')->default('system')->after('theme_id'); // light|dark|system
        });

        // Reshape existing flat color maps to dual-mode structure
        Theme::query()->get()->each(function (Theme $theme) {
            $colors = $theme->colors;

            if (is_array($colors) && ! isset($colors['dark']) && ! isset($colors['light'])) {
                $theme->update(['colors' => ['dark' => $colors, 'light' => null]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->dropColumn([
                'theme_id',
                'theme_mode_default',
            ]);
        });
    }
};
