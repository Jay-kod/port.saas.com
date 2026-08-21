<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AiSetting;
use App\Models\Certificate;
use App\Models\Experience;
use App\Models\GithubSetting;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResumeGeneration;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Idempotent re-run of the Phase 1 backfill logic
 * (docs/agents/02-MULTI-TENANCY-FOUNDATION.md, section 1.2).
 *
 * The `make_tenancy_columns_required` migration already runs this same
 * logic automatically as part of `php artisan migrate` on this
 * codebase's history, so you should not normally need to run this
 * command by hand. It exists for edge cases such as restoring a
 * database backup taken before that migration existed, or manually
 * fixing a Profile that somehow still has a null account_id/slug.
 *
 * Safe to run multiple times — it only touches rows that are still
 * missing tenancy data.
 */
class TenancyBackfill extends Command
{
    protected $signature = 'tenancy:backfill';

    protected $description = 'Backfill account_id/user_id/slug on Profile and profile_id on tenant-scoped tables for any rows created before Phase 1 landed';

    public function handle(): int
    {
        $profile = Profile::query()->whereNull('account_id')->orWhereNull('slug')->first();

        if (! $profile) {
            $this->info('Nothing to backfill — every profile already has an account_id and slug.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($profile) {
            $owner = User::query()->first();

            if (! $owner) {
                throw new \RuntimeException('Cannot backfill: no User exists to own the Account.');
            }

            $account = Account::query()->firstOrCreate(
                ['owner_user_id' => $owner->id],
                ['name' => $profile->full_name ?: 'Default Account']
            );

            $slug = $profile->slug ?: (Str::slug($profile->full_name) ?: 'portfolio');
            $original = $slug;
            $i = 1;
            while (Profile::query()->where('slug', $slug)->where('id', '!=', $profile->id)->exists()) {
                $slug = $original.'-'.$i++;
            }

            $profile->forceFill([
                'account_id' => $profile->account_id ?: $account->id,
                'user_id' => $profile->user_id ?: $owner->id,
                'slug' => $slug,
            ])->save();

            foreach ([Experience::class, Project::class, Skill::class, Certificate::class, ResumeGeneration::class, GithubSetting::class] as $model) {
                $model::query()->whereNull('profile_id')->update(['profile_id' => $profile->id]);
            }

            AiSetting::query()->whereNull('account_id')->update(['account_id' => $account->id]);

            $this->info("Backfilled account #{$account->id} and profile #{$profile->id} (slug: {$slug}).");
        });

        return self::SUCCESS;
    }
}
