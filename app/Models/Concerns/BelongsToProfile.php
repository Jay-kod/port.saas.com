<?php

namespace App\Models\Concerns;

use App\Models\Profile;
use App\Services\CurrentProfileResolver;
use Illuminate\Database\Eloquent\Builder;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.4.
 *
 * Automatically constrains queries on Experience/Project/Skill/
 * Certificate/ResumeGeneration/GithubSetting to the "current" profile
 * resolved via CurrentProfileResolver, so every pre-existing unscoped
 * call site in the public Volt pages
 * (resources/views/pages/*.blade.php) and in
 * App\Services\TemplateConversionService becomes tenant-scoped without
 * being edited.
 *
 * The scope is intentionally skipped for:
 *   - requests under /admin — Filament's own tenancy already scopes
 *     resources explicitly there (see AdminPanelProvider's
 *     ->tenant(Account::class)); double-scoping would be redundant and
 *     would break Filament's cross-profile platform-admin views.
 *   - real Artisan console commands (seeders, tinker, custom commands)
 *     — these often need deliberately unscoped access across tenants.
 *
 * It is deliberately NOT skipped during automated tests, even though
 * PHPUnit itself runs via the CLI SAPI (which would otherwise make
 * app()->runningInConsole() report true) — see the
 * runningUnitTests() check below. Tenancy-isolation tests rely on the
 * scope being active during simulated HTTP requests.
 */
trait BelongsToProfile
{
    protected static function bootBelongsToProfile(): void
    {
        static::addGlobalScope(function (Builder $builder) {
            if (static::shouldBypassProfileScope()) {
                return;
            }

            $profile = app(CurrentProfileResolver::class)->resolve();

            $builder->when($profile, fn (Builder $q) => $q->where($builder->getModel()->getTable().'.profile_id', $profile->id));
        });

        static::creating(function ($model) {
            if (! $model->profile_id && ! static::shouldBypassProfileScope()) {
                $profile = app(CurrentProfileResolver::class)->resolve();

                if ($profile) {
                    $model->profile_id = $profile->id;
                }
            }
        });
    }

    protected static function shouldBypassProfileScope(): bool
    {
        if (request()?->is('admin*')) {
            return true;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return true;
        }

        return false;
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
