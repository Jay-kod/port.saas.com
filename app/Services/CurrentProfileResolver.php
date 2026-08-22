<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Profile;

/**
 * Phase 0, 3 & 6 (docs/agents/04-THEMING-DOMAINS.md):
 * The single seam every page/service asks: "which profile/tenant am I rendering for right now?"
 */
class CurrentProfileResolver
{
    protected ?Profile $resolved = null;

    protected bool $hasResolved = false;

    public function resolve(): ?Profile
    {
        if ($this->hasResolved) {
            return $this->resolved;
        }

        $this->hasResolved = true;

        if (config('saas.mode')) {
            // Check if current host matches a verified custom domain
            $host = strtolower(trim(request()->getHost()));
            $domain = Domain::query()
                ->with(['profile.account', 'profile.theme'])
                ->where('domain', $host)
                ->whereNotNull('verified_at')
                ->first();

            if ($domain && $domain->profile && $domain->profile->is_published) {
                return $this->resolved = $domain->profile;
            }

            return $this->resolved = null;
        }

        // SAAS_MODE=false (default / self-hosted): always the first profile.
        return $this->resolved = Profile::query()->with(['account', 'theme'])->first();
    }

    /**
     * Allows middleware to pin the resolver to a specific Profile
     * for the duration of the request.
     */
    public function setResolved(?Profile $profile): void
    {
        $this->resolved = $profile;
        $this->hasResolved = true;
    }
}
