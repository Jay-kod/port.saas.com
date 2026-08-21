<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Account;
use App\Models\Profile;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.5.
 *
 * For Filament resources whose model relates to the current tenant
 * (Account) only indirectly, through Profile — Experience, Project,
 * Skill, Certificate, ResumeGeneration, GithubSetting. Those models
 * have no `account()` relationship, so Filament's own automatic
 * tenant-ownership scoping (which expects one) can't be used for
 * them; this trait disables that automatic scope
 * ($isScopedToTenant = false) and replaces it with an explicit query
 * scoped to "the current Account's one Profile" instead.
 *
 * For Phases 1-7 every Account has exactly one Profile, so
 * resolveCurrentTenantProfile() picking the first one is correct.
 * Phase 8 (agency tier, multiple Profiles per Account) is what
 * requires replacing this with a real "current Profile" picker in the
 * admin UI — see docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md.
 *
 * Use alongside CreatesScopedToCurrentProfile on the resource's
 * CreateRecord page so new rows get profile_id filled in too.
 */
trait ScopedToCurrentProfile
{
    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $profile = static::resolveCurrentTenantProfile();

        return parent::getEloquentQuery()
            ->when($profile, fn (Builder $query) => $query->where('profile_id', $profile->id))
            ->unless($profile, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function resolveCurrentTenantProfile(): ?Profile
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        if (! $account) {
            return null;
        }

        $activeProfileId = session('active_profile_id');

        if ($activeProfileId) {
            $activeProfile = $account->profiles()->find($activeProfileId);
            if ($activeProfile) {
                return $activeProfile;
            }
        }

        return $account->profiles()->first();
    }
}
