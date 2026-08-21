<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 6 (docs/agents/04-THEMING-DOMAINS.md):
 * Custom domain mapping for a tenant's Profile.
 */
class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'domain',
        'verification_token',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Domain $domain) {
            if (empty($domain->verification_token)) {
                $domain->verification_token = 'port-verify-' . bin2hex(random_bytes(16));
            }

            // Normalize domain name: lowercase, strip scheme and trailing slash
            $domain->domain = strtolower(trim(preg_replace('#^https?://#', '', rtrim($domain->domain, '/'))));
        });
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function verify(): void
    {
        $this->update(['verified_at' => now()]);
    }
}
