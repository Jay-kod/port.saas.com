<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md): `account_id`
 * is NULL for the platform's global template catalog, non-null for a
 * tenant's private template (Phase 7+ feature — not built yet).
 */
class Template extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'slug',
        'description',
        'blade_view',
        'preview_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function resumeGenerations()
    {
        return $this->hasMany(ResumeGeneration::class);
    }
}
