<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Account;

/**
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Enforces per-Account AI resume generation usage quotas and BYOK exemptions.
 */
class AiUsageGuard
{
    public function canGenerate(Account $account): bool
    {
        try {
            $this->ensureCanGenerate($account);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check if the account is allowed to generate an AI resume.
     * Throws AiQuotaExceededException if quota is exhausted and BYOK is not active.
     */
    public function ensureCanGenerate(Account $account): void
    {
        if ($this->isByokActive($account)) {
            return; // BYOK — exempt from platform quota
        }

        $planSlug = $account->plan_slug ?: 'free';
        $limit = config("plans.{$planSlug}.ai_generations_per_month");

        if ($limit === null) {
            return; // Unlimited plan (e.g. Pro or Agency)
        }

        if ($account->ai_generations_used_current_period >= $limit) {
            throw new AiQuotaExceededException(
                "You've used all {$limit} AI resume generations for this billing period. Upgrade to Pro for unlimited generations."
            );
        }
    }

    /**
     * Record a successful AI generation.
     */
    public function recordGeneration(Account $account): void
    {
        $account->increment('ai_generations_used_current_period');
    }

    /**
     * Check if BYOK (Bring Your Own Key) is active for the account.
     */
    public function isByokActive(Account $account): bool
    {
        return $account->aiSettings()
            ->where('is_active', true)
            ->whereNotNull('api_key')
            ->where('api_key', '!=', '')
            ->exists();
    }

    /**
     * Get remaining generations for the account (null if unlimited / BYOK).
     */
    public function getRemainingGenerations(Account $account): ?int
    {
        if ($this->isByokActive($account)) {
            return null;
        }

        $planSlug = $account->plan_slug ?: 'free';
        $limit = config("plans.{$planSlug}.ai_generations_per_month");

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - (int) $account->ai_generations_used_current_period);
    }
}
