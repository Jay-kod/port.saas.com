# 01 — Groundwork (Phase 0)

**Status: ✅ COMPLETE.** This file is historical context, not a to-do
list. Read it before you touch `CurrentProfileResolver` or the public
Volt pages, so you understand the contract they already follow — then
go to `docs/agents/02-MULTI-TENANCY-FOUNDATION.md` for the next actual
work.

## What "done" means here

Phase 0's entire goal was to introduce one seam — a single place every
page/service asks "which profile/tenant am I rendering for?" — without
changing any observable behavior. That seam exists and is in use.

## What was built

1. **`config/saas.php`** — the `SAAS_MODE` feature flag.
   ```php
   'mode' => env('SAAS_MODE', false),
   ```
   Default `false`. This is the single flag distinguishing "Path A"
   (self-hosted, single-tenant) from "Path B" (multi-tenant SaaS).
   **Never remove this flag or the `false` branch it guards** — it is
   the rollback plan referenced in Phase 10.

2. **`app/Services/CurrentProfileResolver.php`** — one public method,
   `resolve(): ?Profile`. Today it always returns `Profile::query()->first()`
   regardless of `SAAS_MODE`, because Phase 3 (routing overhaul) hasn't
   landed yet — there's no `{slug}` or custom domain to resolve from.
   It also exposes `setResolved(?Profile $profile)` so Phase 3's future
   tenant-resolution middleware can pin a specific Profile for the
   duration of a request (e.g. after resolving a slug or domain) without
   needing every call site to change.

   It's registered as a **scoped** singleton in
   `App\Providers\AppServiceProvider::register()`:
   ```php
   $this->app->scoped(CurrentProfileResolver::class);
   ```
   Scoped (not a plain singleton) matters once Phase 3 middleware starts
   pinning a resolved Profile per-request — a plain singleton would leak
   across requests in long-running workers (Octane, queue workers,
   tests).

3. **All 8 public Volt pages** under `resources/views/pages/` —
   `home`, `about`, `projects`, `project-detail`, `skills`,
   `certificates`, `certificate-detail`, `contact` — call
   `app(CurrentProfileResolver::class)->resolve()` via a Volt
   `computed()` property, never `Profile::first()` directly:
   ```php
   $profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
   ```
   Accessed in the Blade half of the file as `$this->profile` (Volt/
   Livewire computed properties are methods, not extracted variables —
   use `$this->propertyName`, not a bare `$propertyName`, inside the
   Blade markup).

4. **`tests/Feature/PublicRoutesTest.php`** — covers:
   - the home page rendering with no Profile at all (empty-state copy),
   - all 8 routes rendering correctly with a seeded Profile/Project/
     Certificate,
   - an unknown project slug 404ing.

## Deliberately NOT done in Phase 0 (this is correct, not a gap)

- `Experience`, `Project`, `Skill`, `Certificate`, `ResumeGeneration`,
  `GithubSetting` are **not** related to `Profile` by a foreign key yet
  and are queried directly/unscoped (`Project::query()->orderBy(...)->get()`)
  in the public pages and in `App\Services\TemplateConversionService`.
  This is the real, intentional starting state the SaaS plan describes
  as "scattered unscoped queries" — Phase 1 is what fixes it. Do not
  add `profile_id` columns or scoping logic as part of "finishing"
  Phase 0; that's explicitly Phase 1's job, described in
  `docs/agents/02-MULTI-TENANCY-FOUNDATION.md`.
- Filament admin resources still query these models unscoped too —
  also correct for now, also fixed by Phase 1 (Filament native
  tenancy), not before.

## Contract for future phases (don't break this)

Every future phase that changes *how* the current tenant is resolved
(slug-based routing in Phase 3, custom domains in Phase 6) should only
need to change the inside of `CurrentProfileResolver::resolve()` (and
wire up middleware that calls `setResolved()`) — it should never need
to touch the 8 Volt pages, `AppServiceProvider`, or any service that
already calls the resolver correctly. If you find yourself editing a
page file just to change tenant resolution, something has gone wrong —
fix the resolver instead.

## Before moving on

Run:
```
php artisan test
```
It should show `PublicRoutesTest` passing (3 tests) alongside the
default `Tests\Unit\ExampleTest`. If it doesn't, Phase 0 has regressed
— fix that before starting Phase 1's much bigger schema work.

**Next:** `docs/agents/02-MULTI-TENANCY-FOUNDATION.md` (Phase 1).
