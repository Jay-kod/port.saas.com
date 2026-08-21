<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md): tracked per
 * paying Account, not per Profile — one Account with multiple
 * Profiles (Phase 8+) shares one AI budget.
 */
class AiSetting extends Model
{
    protected $fillable = [
        'account_id',
        'provider',
        'api_key',
        'model',
        'is_active',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
