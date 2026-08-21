<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 9 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * Portfolio abuse reports model for public content moderation.
 */
class PortfolioReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'reason',
        'details',
        'reporter_ip',
        'status',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
