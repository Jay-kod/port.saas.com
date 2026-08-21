<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SaaS NOTE: `colors` is a flat token map today. Phase 5 (theming +
 * light/dark mode) changes it to a nested {"dark": {...}, "light": {...}}
 * map. See docs/agents/04-THEMING-DOMAINS.md before changing the shape
 * of this data.
 */
class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'colors',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
