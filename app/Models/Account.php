<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

/**
 * The billing/plan/tenant-owner record introduced in Phase 1
 * (docs/agents/02-MULTI-TENANCY-FOUNDATION.md). One Account can own
 * many Profiles — only actually exercised starting Phase 8's agency
 * tier; for Phases 1-7 every Account has exactly one Profile.
 *
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Uses Laravel\Cashier\Billable for Stripe subscriptions and billing.
 */
class Account extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'owner_user_id',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'stripe_customer_id',
        'stripe_subscription_id',
        'plan_slug',
        'custom_brand_name',
        'custom_logo_path',
        'hide_platform_branding',
        'trial_ends_at',
        'ai_generations_used_current_period',
        'ai_generations_period_started_at',
    ];

    public function stripeEmail(): ?string
    {
        return $this->owner?->email;
    }

    public function stripeName(): ?string
    {
        return $this->name;
    }

    protected $casts = [
        'hide_platform_branding' => 'boolean',
        'trial_ends_at' => 'datetime',
        'ai_generations_period_started_at' => 'datetime',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'account_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function canManageBilling(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->owner_user_id === $user->id) {
            return true;
        }

        $member = $this->members()->where('users.id', $user->id)->first();

        return $member?->pivot?->role === 'owner';
    }

    public function canCreateProfile(): bool
    {
        $maxProfiles = config('plans.' . ($this->plan_slug ?: 'free') . '.max_profiles', 1);

        if ($maxProfiles === null) {
            return true;
        }

        return $this->profiles()->count() < $maxProfiles;
    }

    public function getUserRole(User $user): string
    {
        if ($this->owner_user_id === $user->id) {
            return 'owner';
        }

        $member = $this->members()->where('users.id', $user->id)->first();

        return $member?->pivot?->role ?? 'viewer';
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function aiSettings()
    {
        return $this->hasMany(AiSetting::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }
}
