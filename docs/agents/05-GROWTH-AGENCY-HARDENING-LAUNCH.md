# 05 — Growth, Agency Tier, Hardening & Launch (Phases 7, 8, 9, 10)

**Status: ⬜ NOT STARTED.**

Prerequisite: Phase 1 (`docs/agents/02-MULTI-TENANCY-FOUNDATION.md`) for
all growth features (they all need `profile_id` scoping). Phase 4's
billing (`docs/agents/03-BILLING-ONBOARDING-ROUTING.md`) for Phase 8's
agency pricing. Phase 7 can start as soon as Phase 2 (registration)
ships — it is explicitly **not** blocked by billing, so don't wait for
Phase 4 to finish before starting 7.1.

This is the "ongoing product work after initial launch" phase of the
plan — unlike Phases 0-6, most of this is designed to trail an initial
soft-launch rather than gate it. Read the "Suggested execution order"
section of `docs/agents/SAAS_TRANSFORMATION_PLAN.md` for the full
reasoning; the short version: ship 7.1 (resume import) as early as
possible since it's the best activation lever, and let 6/8/9 trail
behind revenue rather than block it.

---

## PHASE 7 — Growth & differentiation features

### 7.1 Resume import / parsing (do this first — highest priority)

- New service `App\Services\ResumeParserService`, mirroring
  `App\Services\ResumeTailorService`'s existing shape exactly (same
  constructor pattern — resolves an active `AiSetting`, same
  `provider` match-based dispatch to OpenAI/Anthropic, same JSON-parse-
  and-validate approach). Don't invent a new AI-provider abstraction;
  copy the pattern.
- Public method: `parse(string $rawText): array` returning a structured
  shape you can map onto `Profile`/`Experience`/`Skill` fields —
  suggested shape:
  ```php
  ['full_name' => ..., 'headline' => ..., 'bio' => ..., 'experiences' => [...], 'skills' => [...]]
  ```
- Text extraction from the uploaded PDF/DOCX happens *before* calling
  the service — use `barryvdh/laravel-dompdf`'s sibling extraction
  needs a real PDF-to-text library since DomPDF itself only renders
  HTML→PDF, it doesn't extract text from PDFs. Add
  `smalot/pdfparser` (`composer require smalot/pdfparser`) for PDF text
  extraction; DOCX extraction can use `phpoffice/phpword`'s reader if
  DOCX upload support is in scope for the first cut, otherwise ship
  PDF-only first and add DOCX as a fast-follow.
- Wire into onboarding (Phase 2's wizard) as an alternate "Import from
  an existing resume" path, and/or as a standalone Filament page/action
  for existing tenants who want to re-import. Show the parsed result in
  an editable form before saving — mirror the "generate then let them
  review/edit" UX pattern this project already uses for AI resume
  tailoring, rather than silently overwriting their Profile.

### 7.2 Cover letter generator

- Add `generateCoverLetter(Profile $profile, string $jobTitle, string $jobDescription): array`
  to `App\Services\ResumeTailorService` (or a new sibling
  `CoverLetterService` if you'd rather keep the two concerns separate —
  either is fine, but don't duplicate the provider-dispatch logic;
  extract a shared protected method if you split the class).
- New model + migration `CoverLetterGeneration`, same shape as
  `ResumeGeneration` (`profile_id`, `job_title`, `company_name`,
  `job_description`, `tailored_content` json, `status`,
  `error_message`, timestamps) plus a corresponding Filament resource
  (`php artisan make:filament-resource CoverLetterGeneration --generate`).

### 7.3 Job application tracker

- New model + migration `JobApplication`: `profile_id`, `company`,
  `role`, `job_url`, `status` (string enum:
  `saved|applied|interviewing|offer|rejected`), `applied_at` (nullable
  date), `notes` (text, nullable), `resume_generation_id` (nullable FK).
- Build the Kanban board as a Livewire component (not necessarily Volt
  — a stateful drag-and-drop board is a reasonable case for a
  full class-based Livewire component under `app/Livewire/`, registered
  as a Filament page via `Filament\Pages\Page` with a custom view, or
  embedded directly). This is explicitly called out in the plan as the
  single biggest retention lever — don't under-scope it to a plain CRUD
  table; at minimum support drag-between-status-columns.
- Scope via the `BelongsToProfile` trait from Phase 1 — this model
  needs it from day one, unlike the pre-Phase-1 models that had to
  retrofit it.

### 7.4 Browser extension

- Defer until 7.1-7.3 are live and you have real usage data. When you
  do build it: a minimal Manifest V3 extension capturing
  `document.title` + `location.href` + selected/visible job-description
  text, deep-linking to `/admin` with querystring params pre-filling a
  "generate resume for this job" form. This is a separate repo/build
  target, not part of this Laravel codebase — note that clearly if/when
  you start it, and consider a `docs/agents/06-BROWSER-EXTENSION.md` at
  that point rather than cramming it into this file.

### 7.5 Public opt-in portfolio directory

- New route `/discover` (only meaningful under `SAAS_MODE=true`),
  listing `Profile::where('is_published', true)->where('is_discoverable', true)`
  — add `is_discoverable` (bool, default false) to `profiles` via
  migration, opt-in per tenant via a settings toggle.
  Filterable by skill/tech stack — join against `skills`/
  `projects.tech_stack` for filtering; a simple query-string-driven
  filter UI (Volt `state()` bound to query params) is sufficient for a
  first cut, no need for a search-engine dependency at this stage.
- SEO: ensure each directory-listed profile's public pages have proper
  `<title>`/meta description tags (check whether
  `resources/views/layouts/app.blade.php` has these yet — as of Phase
  0 it has a basic `<title>` only; extend it with meta description
  pulling from `profile.headline`/`bio`).

### Acceptance criteria (ship each independently, don't block on all 5)
- Resume import populates a real, previously-empty Profile correctly
  from an actual uploaded PDF in a manual test.
- Cover letter generation works end-to-end, stored and retrievable via
  the Filament resource.
- Job tracker CRUD is scoped correctly per-profile (covered by the
  Phase 1 tenancy isolation test pattern — extend it for this model).

---

## PHASE 8 — Agency / white-label tier

### Goal
Unlock the highest-revenue-per-account segment: coaching businesses,
bootcamps managing many students' portfolios under one paid account.

### Tasks

1. New pivot table `account_user` (migration): `account_id`, `user_id`,
   `role` (string enum: `owner|editor|viewer`), timestamps. Keep
   `accounts.owner_user_id` as-is (added in Phase 1) as a convenience
   pointer to the primary owner — don't remove it, just stop treating
   it as the only membership. Add `Account::members()` (belongsToMany
   `User` through `account_user`, `withPivot('role')`).
2. Multiple Profiles per Account: already schema-supported since Phase
   1 (`profiles.account_id` was never capped at one row per account in
   the schema — only in application-level assumptions made by earlier
   phases, e.g. Phase 1's Filament resource scoping logic in section
   1.5). This phase is where you build:
   - A real Filament "switch profile" control (extend the Account
     tenant switcher Filament already renders — see
     `docs/agents/02-MULTI-TENANCY-FOUNDATION.md` section 1.5 for where
     the single-profile assumption was baked in; replace that
     assumption with an actual picker here).
   - Enforcement that only `agency`-plan accounts can create a second
     Profile (`config('plans.'.$account->plan_slug.'.max_profiles')`
     check before allowing creation).
3. White-label branding: extend `accounts` (migration) with
   `custom_logo_path`, `custom_brand_name`, `hide_platform_branding`
   (bool, gated to agency plan via the same config check pattern as
   above). Apply to:
   - Admin panel chrome (`AdminPanelProvider` — Filament supports
     per-tenant branding via its tenancy features; check current
     Filament 5 docs for the exact API, e.g.
     `tenantMenuItems()`/branding slots tied to the current tenant).
   - Each managed Profile's public pages — the "powered by [platform]"
     footer/badge (add one now if one doesn't exist yet, in
     `resources/views/layouts/app.blade.php`, specifically so this
     phase has something concrete to hide).
4. Bulk actions: a Filament page/action accepting a CSV of
   student name/email pairs, creating a `User` + `Profile` (attached to
   the coach's existing `Account`) per row, with role `editor` in
   `account_user` for the coach on each. Send an invite email per
   created user (ties into Phase 9's transactional email work — fine to
   build this with `Mail::fake()`-testable stubs before Phase 9's real
   provider wiring is in place).
5. Pricing: add the `agency` plan's Stripe price (already stubbed in
   `config/plans.php` from Phase 4) — decide flat vs. seat-based and
   reflect it in the `/pricing` marketing page from Phase 3.

### Acceptance criteria
- An agency-plan Account can create, switch between, and manage
  multiple Profiles from one login.
- A member with `editor` role can edit assigned Profiles but cannot
  access the billing settings page from Phase 4; only `owner` can.
- New test: assert a `viewer`-role member gets read-only access (no
  save actions succeed) — extend the Phase 1 tenancy test file's
  patterns.

---

## PHASE 9 — Hardening for a real, paying-customer SaaS

### Goal
The mandatory-but-unglamorous work protecting real money and real PII
(resumes) once there are paying customers.

### Tasks

1. **GDPR data rights.** Build:
   - Export: a Filament action producing a JSON/zip bundle of an
     Account's Profile + all related Experience/Project/Skill/
     Certificate/ResumeGeneration/GithubSetting/CoverLetterGeneration/
     JobApplication rows.
   - Delete: cascading deletion through the same graph — verify every
     migration from Phases 1 and 7 either uses `->cascadeOnDelete()` on
     the `profile_id`/`account_id` foreign keys, or add an explicit
     deletion-cascade service if any table intentionally doesn't
     cascade (e.g. you may want to soft-delete `ResumeGeneration` for
     audit reasons — decide and document here if you deviate from
     cascade-delete for a specific table).
2. Terms of Service + Privacy Policy pages (static Volt/Blade pages,
   low effort — don't over-engineer, a lawyer-reviewed static page is
   fine). Cookie consent banner if targeting EU users (a simple Alpine
   component storing consent in `localStorage` is sufficient at this
   scale — no need for a consent-management platform).
3. Rate limiting: use Laravel's built-in `RateLimiter` facade /
   `throttle` middleware on login, registration, and the AI-generation
   endpoints (both resume tailoring and the Phase 7.1 parser/7.2 cover
   letter endpoints) — per-Account **and** per-IP, since a single
   compromised account and a single abusive IP are different threats
   worth limiting independently.
4. Platform-wide AI spend guardrail: a scheduled command checking
   total AI generations across all accounts against a hardcoded daily/
   monthly ceiling (an env var, e.g. `AI_PLATFORM_DAILY_CAP`), alerting
   (email/Slack webhook) if breached — this is separate from and in
   addition to Phase 4's per-account quotas, protecting against a bug
   or coordinated abuse across many accounts at once.
5. Transactional email: `config/services.php` already has a Postmark
   config stub in a stock Laravel install — verify it, then wire
   `MAIL_MAILER=postmark` in production `.env`, and confirm
   verification/password-reset/receipt/"resume ready" notifications
   actually send (write a notification class per event if none exist
   yet — check `app/Notifications/` first).
6. Error tracking + uptime monitoring: `composer require sentry/sentry-laravel`
   (or equivalent), configure `SENTRY_LARAVEL_DSN`; pair with an
   external uptime checker (e.g. a free-tier pinger) hitting a
   `/up` health-check route (Laravel 13 ships one by default — verify
   `routes/web.php` or `bootstrap/app.php` for `->withRouting(health: '/up')`
   or similar and don't duplicate it if so).
7. Backups: automate `database.sqlite` (or whatever production DB
   engine is actually deployed — confirm before assuming SQLite ships
   to production; SQLite is fine for this project's current dev/test
   setup but reconsider before real multi-tenant production load) —
   set up scheduled backups and **actually test a restore**, not just
   that the backup file exists.
8. Abuse review: a simple "report this portfolio" button on public
   pages creating a `PortfolioReport` row (`profile_id`, `reason`,
   `reporter_ip`, timestamps), reviewed via a basic Filament resource
   with an "unpublish" bulk action.

### Acceptance criteria
- A test account can fully export then fully delete itself with zero
  orphaned rows left (write a test asserting row counts across every
  related table are 0 after deletion).
- Rate limits verifiably trigger under a simple test hitting an
  endpoint N+1 times.

---

## PHASE 10 — Launch readiness checklist

Not code — a checklist to walk through in a staging environment before
advertising to real customers:

- [ ] `SAAS_MODE=true` verified end-to-end in staging: register →
  onboard (Phase 2) → subscribe (Phase 4) → publish a portfolio (Phase
  3) → generate a resume (existing `ResumeTailorService`, now
  quota-guarded) → export a PDF (existing `TemplateConversionService`)
  → verify a custom domain (Phase 6, if in scope for launch).
- [ ] `/pricing` matches actual live Stripe prices (not test-mode
  placeholders).
- [ ] A monitored support inbox exists.
- [ ] Confirm `SAAS_MODE=false` still works as a rollback valve — this
  is the whole point of the flag introduced in Phase 0; don't launch
  without re-verifying it one more time, since nothing between Phase 0
  and here should have required deleting that code path.

Run the full validation suite from the root `AGENTS.md` one more time
before flipping `SAAS_MODE=true` in real production:
```
php artisan test
php artisan route:list
npm run build
```

**This is the last file in the sequence.** Once Phase 10's checklist is
complete, update the root `AGENTS.md` "Current Status" section to
reflect a fully-shipped SaaS transformation, and treat Phase 7's
unshipped sub-items (7.4 browser extension, any deferred 7.5 directory
polish) and Phase 8/9 items explicitly deferred pre-launch as the
ongoing post-launch backlog, not unfinished "phase 10" work.
