<?php

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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), sections 1.2 and
 * 1.3. Backfills any Profile/tenant-scoped rows created before this
 * migration existed, then makes the backfilled columns required.
 *
 * This is intentionally idempotent and safe to run on a fresh install
 * with zero data (nothing to backfill) as well as on the original
 * single-tenant seeded install. It mirrors `php artisan tenancy:backfill`
 * (app/Console/Commands/TenancyBackfill.php) — that standalone command
 * exists for re-running backfill on demand (e.g. after restoring a
 * backup taken before this migration existed); this migration's up()
 * runs the same logic automatically as part of `php artisan migrate`
 * so a single fresh `migrate` is always sufficient.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->backfill();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable(false)->change();
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
        });

        foreach (['experiences', 'projects', 'skills', 'certificates', 'resume_generations', 'github_settings'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('profile_id')->nullable(false)->change();
            });
        }

        Schema::table('ai_settings', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable(false)->change();
        });
    }

    protected function backfill(): void
    {
        $profile = Profile::query()->whereNull('account_id')->orWhereNull('slug')->first();

        if (! $profile) {
            return;
        }

        $owner = User::query()->first();

        if (! $owner) {
            return; // nothing to attach an orphaned profile to; leave for tenancy:backfill to handle later
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
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->change();
            $table->string('slug')->nullable()->change();
        });

        foreach (['experiences', 'projects', 'skills', 'certificates', 'resume_generations', 'github_settings'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('profile_id')->nullable()->change();
            });
        }

        Schema::table('ai_settings', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->change();
        });
    }
};
