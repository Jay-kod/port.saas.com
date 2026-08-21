# 03 — Onboarding, Routing & Billing (Phases 2, 3, 4)

**Status: ⬜ NOT STARTED.**

Prerequisite: Phase 1 is complete (`docs/agents/02-MULTI-TENANCY-FOUNDATION.md`).
Every task below assumes `accounts`, `profiles.account_id/user_id/slug`,
and `profile_id` on the tenant-scoped tables already exist and are
populated. Do not start this file's work if that isn't true yet —
check the root `AGENTS.md` "Current Status" section first.

These three phases are grouped because they're the "make signups
possible and profitable" arc: Phase 2 creates tenants, Phase 3 gives
them a URL, Phase 4 makes them pay. Do them in that order.

---

## PHASE 2 — Auth, registration & onboarding

### Goal
A stranger can sign up and end up with their own empty, uniquely-slugged
portfolio and admin access to only their own data — without a human
touching the database.

### Tasks

1. **Registration.** This project doesn't have Breeze/Fortify/Jetstream
   installed. Two reasonable options, pick one:
   - Install `laravel/fortify` for registration/login/password-reset
     controllers, or
   - Use Filament's own panel-level registration
     (`Filament\Panel::registration()`) so signups happen through the
     `/admin` panel's own auth UI rather than building a separate
     public registration form. This is less code and fits this
     project's existing "the admin panel is the product's control
     surface" pattern — prefer this unless there's a reason to build a
     separate public-facing signup flow (e.g. as part of the Phase 3
     marketing site's own CTA).

2. **On successful registration**, in a listener on the registration
   event (or directly in the Fortify/Filament registration action):
   ```php
   $account = Account::create([
       'name' => $user->name,
       'owner_user_id' => $user->id,
       'plan_slug' => 'free',
   ]);

   $slug = Str::slug($user->name) ?: 'portfolio';
   // ensure uniqueness the same way TenancyBackfill does (Phase 1, section 1.2)

   Profile::create([
       'account_id' => $account->id,
       'user_id' => $user->id,
       'slug' => $slug,
       'full_name' => $user->name,
       'is_published' => false, // don't show an empty portfolio publicly until they've added content
   ]);
   ```

3. **Onboarding wizard.** Build as a new Volt page (a good fit for this
   codebase's existing patterns — see `resources/views/pages/*.blade.php`
   for the Volt conventions already in use: `state()`, `computed()`,
   accessing computed props as `$this->propName` in Blade). Suggested
   steps:
   - Step 1: confirm/edit slug (`yoursaas.com/{slug}` preview once
     Phase 3 routing exists).
   - Step 2: `full_name`, `headline`, `bio`.
   - Step 3: pick a starter `Theme` (ties into Phase 5 — if Phase 5
     hasn't landed yet, just let them pick from the existing 3 dark
     themes seeded by `ThemeSeeder`).
   - Step 4: redirect to `/admin`, showing a checklist widget (a
     Filament dashboard widget is the natural fit —
     `app/Filament/Widgets/`) prompting "add your first project",
     "connect GitHub", "generate your first resume".
   - Set `profiles.is_published = true` once they've completed (or
     explicitly skipped) onboarding.

4. **Email verification** — enable Laravel's built-in
   `MustVerifyEmail` on the `User` model and the corresponding
   middleware. Needed before Phase 4's billing (you don't want to take
   payment info from unverified emails) and before Phase 9's
   transactional email hardening.

### Acceptance criteria
- A new signup gets a working, empty, uniquely-slugged Profile and can
  log into `/admin` seeing only their own Account's data (this should
  already hold from Phase 1's Filament tenancy — this phase just
  automates *creating* that Account/Profile pair instead of requiring
  a human to seed it).
- The existing seeded admin account is completely unaffected.

---

## PHASE 3 — Public routing overhaul + SAAS_MODE

### Goal
`SAAS_MODE=false` (default): `routes/web.php` behaves exactly as it
does today — root-level `/`, `/about`, `/projects`, etc., serving the
one profile resolved by `CurrentProfileResolver`.
`SAAS_MODE=true`: `/` becomes a marketing homepage, and each tenant's
portfolio moves under `/{slug}/...`.

### 3.1 Route restructuring

Current `routes/web.php` (as of Phase 0/1) registers 8 `Volt::route(...)`
calls at the root using plain Volt component names (`'home'`, `'about'`,
etc. — not dotted paths; see the comment block already in that file
about why). Wrap them:

```php
if (! config('saas.mode')) {
    Volt::route('/', 'home')->name('home');
    Volt::route('/about', 'about')->name('about');
    Volt::route('/projects', 'projects')->name('projects');
    Volt::route('/projects/{slug}', 'project-detail')->name('projects.show');
    Volt::route('/skills', 'skills')->name('skills');
    Volt::route('/certificates', 'certificates')->name('certificates');
    Volt::route('/certificates/{slug}', 'certificate-detail')->name('certificates.show');
    Volt::route('/contact', 'contact')->name('contact');
} else {
    Volt::route('/', 'marketing.home')->name('home');
    Volt::route('/pricing', 'marketing.pricing')->name('pricing');

    Route::middleware('resolve.tenant')->group(function () {
        Volt::route('/{slug}', 'about')->name('about'); // adjust per-page mapping below
        Volt::route('/{slug}/projects', 'projects')->name('projects');
        Volt::route('/{slug}/projects/{projectSlug}', 'project-detail')->name('projects.show');
        Volt::route('/{slug}/skills', 'skills')->name('skills');
        Volt::route('/{slug}/certificates', 'certificates')->name('certificates');
        Volt::route('/{slug}/certificates/{certSlug}', 'certificate-detail')->name('certificates.show');
        Volt::route('/{slug}/contact', 'contact')->name('contact');
    });
}
```

Important naming note: today's route names (`home`, `about`, `projects`,
etc.) are used throughout the 8 Volt page files
(`resources/views/pages/*.blade.php`) via `route('about')`,
`route('projects.show', $project->slug)`, etc. Keep the **same route
names** in both branches so those Blade files don't need per-mode
conditionals — only the URL *shape* changes between modes, not the
name you `route()` to. This is exactly the point of centralizing tenant
resolution in Phase 0: the pages don't know or care which mode
produced their URL.

Decide how `/{slug}` itself resolves — likely reusing the `home`/`about`
Volt page rather than inventing a new one; adjust the mapping above to
match whatever you decide the "profile landing page at `/{slug}`" should
render (this plan intentionally leaves the exact choice to whoever
implements this phase — reusing `about` vs. a new lightweight `home`
sub-page is a legitimate judgment call either way).

### 3.2 Tenant-resolution middleware

Create `app/Http/Middleware/ResolveTenantFromSlug.php`, registered as
`resolve.tenant` in `bootstrap/app.php` (Laravel 13's middleware
registration style — check `bootstrap/app.php` for the existing
`withMiddleware()` pattern, since this project has no `Kernel.php`):

```php
public function handle(Request $request, Closure $next): Response
{
    $slug = $request->route('slug');

    $profile = Profile::query()->where('slug', $slug)->where('is_published', true)->first();

    abort_unless($profile, 404);

    app(CurrentProfileResolver::class)->setResolved($profile);

    return $next($request);
}
```

This is the one place Phase 1's `setResolved()` escape hatch (added in
`CurrentProfileResolver` back in Phase 0, see
`docs/agents/01-GROUNDWORK.md`) gets used for real. No Volt page needs
to change.

### 3.3 Marketing site

Build `resources/views/pages/marketing/home.blade.php` and
`.../marketing/pricing.blade.php` (or similar — match whatever
directory convention you pick, Volt will find them as long as they're
under a location it's mounted against — see `VoltServiceProvider`).
Reuse the existing dark theme CSS variables from
`resources/views/layouts/app.blade.php` for visual consistency; this
marketing site does **not** need per-tenant theming (it's the
platform's own brand, not a tenant's).

Content: hero, feature highlights (AI resume tailoring, GitHub sync,
PDF export, custom domains once Phase 6 ships), pricing teaser linking
to `/pricing`, signup CTA linking to registration (Phase 2).

### Acceptance criteria
- `SAAS_MODE=false`: identical behavior to before this phase — rerun
  `tests/Feature/PublicRoutesTest.php` unmodified, it must still pass.
- `SAAS_MODE=true`: `/{slug}` (and its sub-routes) render the correct
  tenant only; an unknown slug 404s; `/` renders the marketing page.
- New test `tests/Feature/SaasRoutingTest.php`: seed two profiles with
  different slugs and distinct project titles; with `SAAS_MODE=true`
  (use `config(['saas.mode' => true])` in the test or an env override),
  assert `/{slugA}/projects` shows only Tenant A's project title and
  never Tenant B's, and vice versa. This supersedes/extends the
  query-level isolation test from Phase 1 (`MultiTenancyIsolationTest`)
  with a real HTTP-level assertion.

---

## PHASE 4 — Billing, plans, and usage metering

### Goal
Turn signups into revenue; protect AI API spend from runaway usage.

### 4.1 Billing integration

```
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

Add `Billable` to `App\Models\Account` (not `User` — Account is "the
thing that pays" in this schema, per Phase 1's model). Configure Stripe
keys in `.env` (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` —
add these to `.env.example` too, matching the existing pattern of
documenting every env var there).

Create Stripe Products/Prices (via Stripe dashboard or `stripe` CLI in
test mode) for `pro` and `agency` plans (see 4.2); store their Stripe
Price IDs in a new `config/plans.php`.

Build a simple billing settings page — a Filament custom page
(`php artisan make:filament-page BillingSettings`) is the natural fit,
linking out to Cashier's `redirectToBillingPortal()` for subscribe/
manage/invoices rather than building custom UI for any of that.

Cashier auto-registers its webhook route; add the webhook secret and
confirm `php artisan route:list | grep stripe` shows it.

### 4.2 Plan definitions

`config/plans.php`:
```php
return [
    'free' => [
        'max_profiles' => 1,
        'ai_generations_per_month' => 3,
        'custom_domain' => false,
        'remove_branding' => false,
    ],
    'pro' => [
        'stripe_price_id' => env('STRIPE_PRICE_PRO'),
        'max_profiles' => 1,
        'ai_generations_per_month' => null, // null = unlimited (soft cap, see 4.3)
        'custom_domain' => true,
        'remove_branding' => true,
    ],
    'agency' => [
        'stripe_price_id' => env('STRIPE_PRICE_AGENCY'),
        'max_profiles' => null, // unlimited, Phase 8 feature
        'ai_generations_per_month' => null,
        'custom_domain' => true,
        'remove_branding' => true,
    ],
];
```
Keep this in config, not a DB table, until there's an actual need for
admin-editable pricing without a deploy.

### 4.3 AI usage metering

Add to the `accounts` table (new migration):
```php
$table->unsignedInteger('ai_generations_used_current_period')->default(0);
$table->timestamp('ai_generations_period_started_at')->nullable();
```

Wrap `ResumeTailorService::generate()` calls (today it's called
directly wherever a resume generation is triggered — search for
`ResumeTailorService` usages; if the Filament `CreateResume` page
referenced in the original plan doesn't exist yet in this codebase, the
call site to wrap is wherever `ResumeGeneration` rows get created from
a job description, likely a new Filament resource page/action you'll
add as part of this phase) with:

```php
class AiUsageGuard
{
    public function ensureCanGenerate(Account $account): void
    {
        if ($account->aiSettings()->where('is_active', true)->whereNotNull('api_key')->exists()) {
            return; // BYOK — exempt from platform quota
        }

        $limit = config('plans.'.$account->plan_slug.'.ai_generations_per_month');

        if ($limit === null) {
            return; // unlimited plan
        }

        if ($account->ai_generations_used_current_period >= $limit) {
            throw new AiQuotaExceededException(
                "You've used all {$limit} AI resume generations for this billing period. Upgrade to continue."
            );
        }
    }

    public function recordGeneration(Account $account): void
    {
        $account->increment('ai_generations_used_current_period');
    }
}
```
Catch `AiQuotaExceededException` wherever generation is triggered and
show a clear, non-crashing "upgrade to continue" notification (Filament
notifications API — `Notification::make()->danger()->send()` — fits
this codebase's existing admin panel conventions).

Reset the counter on subscription renewal: hook Cashier's
`WebhookController` events (`subscription.updated` at minimum), or add
a scheduled command (`php artisan schedule` entry) that resets any
Account whose `ai_generations_period_started_at` is more than a month
old.

### Acceptance criteria
- A free-plan Account is blocked (clear message, no 500) on its 4th
  generation in a period.
- A pro-plan Account with an active `AiSetting.api_key` is never
  blocked, regardless of usage count.
- Subscribing via Stripe test mode updates `plan_slug` and lifts the
  limit immediately (verify with Stripe CLI's `stripe trigger` or the
  test-mode dashboard).

---

## Validation checklist before moving to Phase 5/6

```
php artisan test
php artisan route:list
```
Both `SAAS_MODE=false` and `SAAS_MODE=true` code paths need their own
passing tests — don't let one mode's tests regress while adding the
other's.

**Next:** `docs/agents/04-THEMING-DOMAINS.md` (Phases 5-6) — can be
built in parallel with this file's Phase 4 by a second agent/session,
since they touch almost entirely different files (theme/CSS vs.
billing/Stripe). **Then** `docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md`
(Phases 7-10).
