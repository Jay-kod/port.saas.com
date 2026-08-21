# 02 — Multi-Tenancy Foundation (Phase 1)

**Status: ⬜ NOT STARTED. This is the next work to do.**

Prerequisite: Phase 0 is done (`docs/agents/01-GROUNDWORK.md`). Read
that file first — you must not bypass `CurrentProfileResolver`.

Goal: make it possible for more than one portfolio to exist in the
database at once, safely, with the current single row of data preserved
and still fully functional under `SAAS_MODE=false`.

This is the highest-risk phase in the whole plan (schema + backfill on
real data). Budget real care here. Every step below ends in a state
where `php artisan test` passes.

---

## 1.1 New tables and columns

Create one new migration file (don't edit the existing ones in
`database/migrations/` — they're historical and some environments will
already have run them):

```
php artisan make:migration create_accounts_table
php artisan make:migration add_tenancy_columns_to_profiles_table
php artisan make:migration add_profile_id_to_tenant_scoped_tables
php artisan make:migration add_account_id_to_templates_table
php artisan make:migration add_account_id_to_ai_settings_table
```

**`accounts`** (new table):
```php
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('owner_user_id')->constrained('users');
    $table->string('stripe_customer_id')->nullable();
    $table->string('stripe_subscription_id')->nullable();
    $table->string('plan_slug')->default('free');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamps();
});
```

**`profiles`** gains (nullable at first — see 1.2 before making these
`NOT NULL`):
```php
Schema::table('profiles', function (Blueprint $table) {
    $table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
    $table->foreignId('user_id')->nullable()->after('account_id')->constrained('users');
    $table->string('slug')->nullable()->unique()->after('full_name');
});
```

**`experiences`, `projects`, `skills`, `certificates`,
`resume_generations`, `github_settings`** each gain:
```php
$table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles');
```
(Six separate `Schema::table(...)` calls in the one migration file is
fine — or split into per-table migrations if you prefer smaller diffs.)

**`templates`** gains:
```php
$table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
// NULL = global/platform template (unchanged for all seeded rows)
```

**`ai_settings`** gains:
```php
$table->foreignId('account_id')->nullable()->after('id')->constrained('accounts');
```

Run `php artisan migrate`. At this point every new column is nullable
and every existing row has `NULL` in it — nothing is broken yet, but
nothing is scoped yet either.

---

## 1.2 Backfill command (idempotent, run before making columns required)

Create `php artisan make:command TenancyBackfill --command=tenancy:backfill`
in `app/Console/Commands/TenancyBackfill.php`:

```php
public function handle(): int
{
    return DB::transaction(function () {
        $profile = Profile::query()->whereNull('account_id')->first();

        if (! $profile) {
            $this->info('No profile needs backfilling.');
            return self::SUCCESS;
        }

        $owner = User::query()->first();

        $account = Account::query()->firstOrCreate(
            ['owner_user_id' => $owner->id],
            ['name' => $profile->full_name ?: 'Default Account']
        );

        $slug = Str::slug($profile->full_name) ?: 'portfolio';
        $original = $slug;
        $i = 1;
        while (Profile::query()->where('slug', $slug)->where('id', '!=', $profile->id)->exists()) {
            $slug = $original.'-'.$i++;
        }

        $profile->update([
            'account_id' => $account->id,
            'user_id' => $owner->id,
            'slug' => $slug,
        ]);

        foreach ([Experience::class, Project::class, Skill::class, Certificate::class, ResumeGeneration::class, GithubSetting::class] as $model) {
            $model::query()->whereNull('profile_id')->update(['profile_id' => $profile->id]);
        }

        AiSetting::query()->whereNull('account_id')->update(['account_id' => $account->id]);

        $this->info("Backfilled account #{$account->id} and profile #{$profile->id} (slug: {$slug}).");

        return self::SUCCESS;
    });
}
```

Run it:
```
php artisan tenancy:backfill
```

Verify manually before proceeding:
```
php artisan tinker
>>> App\Models\Profile::whereNull('account_id')->count();   // expect 0
>>> App\Models\Project::whereNull('profile_id')->count();   // expect 0
```

**Do this against a copy of any real data first if this project has
been deployed anywhere with real content** — the original plan's
warning about backing up before running this against production data
still applies verbatim.

---

## 1.3 Make backfilled columns required (follow-up migration)

Once backfill is verified, add a second migration that changes
`profiles.account_id`, `profiles.user_id` to `NOT NULL`, and
`experiences/projects/skills/certificates/resume_generations/github_settings.profile_id`
to `NOT NULL` (leave `templates.account_id` and `ai_settings.account_id`
nullable — NULL is a meaningful state for both: global template, and
"no AI provider configured yet", respectively — actually
`ai_settings.account_id` should become required too since every
AiSetting row now belongs to exactly one Account; only `templates.account_id`
stays nullable on purpose).

SQLite (this project's default driver) can't `ALTER COLUMN` in-place
the way MySQL/Postgres can — Laravel's schema builder handles this via
`doctrine/dbal`-free column changes in modern Laravel by recreating the
table under the hood, but confirm behavior with `php artisan migrate`
in a scratch copy of `database/database.sqlite` before trusting it.

---

## 1.4 Model changes

**`app/Models/Profile.php`** — replace the current "deliberately no
relationships" docblock and add:
```php
public function account() { return $this->belongsTo(Account::class); }
public function user() { return $this->belongsTo(User::class); }
public function experiences() { return $this->hasMany(Experience::class); }
public function projects() { return $this->hasMany(Project::class); }
public function skills() { return $this->hasMany(Skill::class); }
public function certificates() { return $this->hasMany(Certificate::class); }
public function resumeGenerations() { return $this->hasMany(ResumeGeneration::class); }
public function githubSetting() { return $this->hasOne(GithubSetting::class); }
```

**New `app/Models/Account.php`**:
```php
public function owner() { return $this->belongsTo(User::class, 'owner_user_id'); }
public function profiles() { return $this->hasMany(Profile::class); }
public function aiSettings() { return $this->hasMany(AiSetting::class); }
public function templates() { return $this->hasMany(Template::class); }
```

**`BelongsToProfile` trait** — create `app/Models/Concerns/BelongsToProfile.php`,
added to `Experience`, `Project`, `Skill`, `Certificate`,
`ResumeGeneration`, `GithubSetting`:
```php
trait BelongsToProfile
{
    protected static function bootBelongsToProfile(): void
    {
        static::addGlobalScope(function (Builder $builder) {
            if (app()->runningInConsole() || request()?->is('admin*')) {
                return; // Filament scopes explicitly via its own tenancy; console (seeders, tests, artisan commands) stays unscoped intentionally.
            }

            $profile = app(CurrentProfileResolver::class)->resolve();

            $builder->when($profile, fn ($q) => $q->where('profile_id', $profile->id));
        });
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
```
This is what lets every existing `Project::query()->orderBy('sort_order')->get()`
call in the public Volt pages (`resources/views/pages/*.blade.php`) and
in `TemplateConversionService` keep working completely unchanged, while
becoming automatically tenant-scoped. **You should not need to edit
those Blade files or that service in this phase** — that's the entire
point of this trait. If a call site still leaks cross-tenant data after
adding the trait, that call site is bypassing Eloquent (raw SQL, DB::table)
and needs to be found and fixed individually.

Add the trait to each model and update `$fillable`/relationships to
include `profile_id`. Also add `getRouteKeyName()`-style `slug`
uniqueness validation is already scoped globally on `projects`/
`certificates` — decide in this phase whether project/certificate
slugs should become unique *per profile* instead of globally unique
(recommended: change the unique index to `unique(['profile_id', 'slug'])`
in the same migration from 1.1, since two different tenants will
inevitably want the same slug, e.g. "portfolio-site").

**`CurrentProfileResolver::resolve()`** — no code change needed yet in
this phase for the actual resolution logic (still `Profile::first()`
when `SAAS_MODE` is false, still returns null/first when true) — Phase
3 is what makes it slug/domain-aware. Do not jump ahead and add
slug-resolution logic here; it has nowhere to read a `{slug}` from
yet since routes aren't restructured until Phase 3.

---

## 1.5 Filament native multi-tenancy

In `app/Providers/Filament/AdminPanelProvider.php`, add:
```php
->tenant(Account::class)
```
(a docblock already flags this exact spot — search for "SaaS NOTE
(Phase 1)" in that file). Filament will now:
- Scope every resource query to the logged-in user's current Account,
  automatically, via its own tenancy machinery — you do not need to add
  `->where('account_id', ...)` manually to any resource.
- Show a tenant switcher if a user belongs to more than one Account
  (irrelevant until Phase 8, harmless now).

You'll also need:
- `Account` to implement `Filament\Models\Contracts\HasCurrentTenantLabel`
  or similar per Filament 5's tenancy docs, and a way to attach the
  logged-in `User` to their `Account` (`HasTenants` on `User`, returning
  `$this->accounts` — for now, since one User owns exactly one Account,
  this can be a simple `hasMany`/`belongsToMany` returning a single-item
  collection).
- Each Filament resource for `Experience`, `Project`, `Skill`,
  `Certificate`, `ResumeGeneration`, `GithubSetting` needs a way to pick
  *which* Profile within the tenant's Account they belong to. For
  Phases 1-7 an Account has exactly one Profile, so the simplest correct
  approach is: in each resource's form/table query, resolve "the
  account's one profile" and scope/set `profile_id` automatically
  (a `mutateFormDataBeforeCreate` hook or similar) rather than exposing
  a Profile picker in the UI yet. Defer building a real multi-profile
  picker UI to Phase 8, where it's actually needed.
- `Template` and `AiSetting` resources should show: rows where
  `account_id` is null (global/platform, read-only for tenants) plus
  rows where `account_id` matches the current tenant. Filament's
  tenancy scoping handles the "belongs to this account" half
  automatically; add an `orWhereNull('account_id')` to those two
  resources' `getEloquentQuery()` explicitly, since global rows aren't
  really "this tenant's" in the normal sense.

---

## 1.6 Required test before moving to Phase 2

Add `tests/Feature/MultiTenancyIsolationTest.php`:
```php
public function test_one_accounts_data_never_leaks_into_another_accounts_public_page(): void
{
    $ownerA = User::factory()->create();
    $accountA = Account::factory()->create(['owner_user_id' => $ownerA->id]);
    $profileA = Profile::factory()->create(['account_id' => $accountA->id, 'user_id' => $ownerA->id, 'slug' => 'tenant-a']);
    Project::factory()->create(['profile_id' => $profileA->id, 'title' => 'Tenant A Project']);

    $ownerB = User::factory()->create();
    $accountB = Account::factory()->create(['owner_user_id' => $ownerB->id]);
    $profileB = Profile::factory()->create(['account_id' => $accountB->id, 'user_id' => $ownerB->id, 'slug' => 'tenant-b']);
    Project::factory()->create(['profile_id' => $profileB->id, 'title' => 'Tenant B Project']);

    // Once Phase 3 wires up {slug} routing this test gets a real HTTP
    // assertion per tenant. Until then, assert at the query level:
    app(CurrentProfileResolver::class)->setResolved($profileA);
    $this->assertTrue(Project::query()->pluck('title')->contains('Tenant A Project'));
    $this->assertFalse(Project::query()->pluck('title')->contains('Tenant B Project'));
}
```
You'll need `ProfileFactory`, `AccountFactory`, `ProjectFactory` —
create them under `database/factories/` if they don't exist
(`php artisan make:factory ProfileFactory --model=Profile`, etc).

This test (or one that supersedes it once Phase 3 adds real HTTP-level
slug routing) is **non-negotiable** per the guiding principles in the
root `AGENTS.md` — don't skip it to move faster.

---

## 1.7 Acceptance criteria (all must hold before starting Phase 2)

- `php artisan test` passes, including the new isolation test.
- `SAAS_MODE=false` still shows exactly the one seeded profile's data
  on every public page — verify by re-running
  `tests/Feature/PublicRoutesTest.php` unchanged.
- `php artisan migrate:fresh --seed` still works end-to-end (seeders in
  1.2's backfill logic assumptions may need small tweaks once
  `account_id`/`profile_id` are required — update `ProfileSeeder` to
  create an `Account` + set `profile_id` on the records it creates for
  `Experience`/`Project`/`Skill`/`Certificate`).
- Logging into `/admin` as the seeded admin user shows only that one
  Account's data, with no visible errors from the new `->tenant()` call.

**Next:** `docs/agents/03-BILLING-ONBOARDING-ROUTING.md` (Phases 2-4).
