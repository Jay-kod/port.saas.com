# AGENTS.md — AI Portfolio Platform → SaaS Transformation

**Read this file first, every session.** It is the entry point for any AI
coding agent (or human developer) picking up this project. It tells you
what this project is, what already exists, what state the SaaS
transformation is in right now, and where to find the detailed
instructions for whatever you're about to build next.

If you are an AI agent starting a session with no other context, your
process should be:

1. Read this file in full.
2. Check the "Current Status" section below to see which phase is next.
3. Open the one `docs/agents/*.md` file that covers that phase.
4. Do the work described there, in the order described there.
5. Run `php artisan test` before and after your changes.
6. Update the "Current Status" section in this file when a phase (or a
   clearly-defined chunk of one) is complete, so the next session
   (possibly a different agent, possibly a different AI IDE entirely)
   knows where to resume.

---

## What this project is

A single-tenant "AI Portfolio Platform": a personal portfolio website
(about/projects/skills/certificates/contact pages) with an admin panel
(Filament) for managing that content, an AI-powered resume tailoring
pipeline (upload a job description, get a tailored resume + PDF), a
GitHub project sync, and a themeable public-facing design system.

It is being incrementally transformed into a multi-tenant SaaS product
— many customers, each with their own portfolio, billing, and settings
— **without discarding the single-tenant/self-hosted product**. Both
must keep working from the same codebase, switched by one feature flag:
`config('saas.mode')` (backed by the `SAAS_MODE` env var, default
`false`).

The full, original transformation plan (10 phases) is preserved
verbatim in **`docs/agents/SAAS_TRANSFORMATION_PLAN.md`**. The four
`docs/agents/0N-*.md` files split that plan into actionable, phase-
grouped implementation guides. This file is the map connecting them
all — it does not repeat their content in depth.

---

## Guiding principles (do not violate these)

1. **Additive, not destructive.** Every model, service, and admin
   resource that exists today (`Profile`, `Experience`, `Project`,
   `Skill`, `Certificate`, `ResumeGeneration`, `Template`, `Theme`,
   `AiSetting`, `GithubSetting`, `ResumeTailorService`,
   `GitHubSyncService`, `TemplateConversionService`, `ThemeService`,
   the DomPDF resume templates, the Filament admin panel) stays. Later
   phases generalize a singleton system into a multi-row system — they
   do not rewrite it.
2. **The self-hosted product must keep working.** `SAAS_MODE=false`
   (the default) must always behave exactly as it does today. This is
   the literal rollback plan: if SaaS mode breaks in production,
   flipping this one flag restores the original product.
3. **One seam, not a thousand call sites.** `App\Services\
   CurrentProfileResolver::resolve()` is the single place that answers
   "which profile/tenant is this request for?" Every public page and
   service should call it rather than `Profile::first()` or an
   unscoped query. This already exists and is already wired up (see
   Current Status) — keep using it, don't bypass it.
4. **Ship in reversible, tested slices.** Every phase must leave
   `php artisan test` green. Add tests for a new tenancy boundary
   *before* moving to the next phase, not after.
5. **Reuse Filament's native multi-tenancy** (`Panel::tenant()`)
   instead of hand-rolling `->where('account_id', ...)` scoping in
   every resource. See `docs/agents/02-MULTI-TENANCY-FOUNDATION.md`.
6. **Single database, shared schema.** No database-per-tenant, no
   `stancl/tenancy`-style package. Tenancy is columns
   (`account_id`/`profile_id`) plus scoped queries, not separate
   schemas.

## What this plan deliberately does NOT change

- The AI resume tailoring pipeline (`ResumeTailorService`) — reused
  as-is; only wrapped with a per-Account usage-quota check in Phase 4.
- GitHub sync (`GitHubSyncService`) — reused as-is; only re-scoped to
  `profile_id` in Phase 1.
- Template conversion / PDF export (`TemplateConversionService`, the
  DomPDF blade templates under `resources/views/resumes/templates/`)
  — reused as-is; templates gain an optional `account_id` later, the
  rendering pipeline itself never changes.
- The Filament admin panel's resources, forms and widgets — reused
  as-is, scoped via Filament's native tenancy rather than rewritten.

---

## Where things live (as actually built, not as originally guessed)

The original plan document (preserved in
`docs/agents/SAAS_TRANSFORMATION_PLAN.md`) guessed at some file paths
before this codebase existed. Where reality differs, reality wins —
this section is the source of truth:

| Concern | Actual location |
|---|---|
| Public Volt pages | `resources/views/pages/*.blade.php` (mounted by both Volt and Livewire's `pages` component namespace) |
| Public layout | `resources/views/layouts/app.blade.php` — **not** `resources/views/components/layouts/app.blade.php`. Livewire reserves the `layouts` anonymous-component namespace for `resources/views/layouts` by default (`config('livewire.component_layout') === 'layouts::app'`). Every `Volt::route(...)` page is auto-wrapped in this layout; pages do not (and must not) wrap themselves in a `<x-layouts.app>` tag. |
| Routes | `routes/web.php` |
| Tenant resolution seam | `app/Services/CurrentProfileResolver.php` |
| SaaS feature flag | `config/saas.php` → `config('saas.mode')`, backed by `env('SAAS_MODE')` |
| Models | `app/Models/*.php` |
| Services | `app/Services/*.php` |
| Filament admin panel provider | `app/Providers/Filament/AdminPanelProvider.php` |
| Filament resources | `app/Filament/Resources/{Profiles,Experiences,Projects,Skills,Certificates,ResumeGenerations,Templates,Themes,AiSettings,GithubSettings}/` |
| Resume PDF Blade templates | `resources/views/resumes/templates/{modern,classic}.blade.php` |
| Seeders | `database/seeders/{ThemeSeeder,TemplateSeeder,ProfileSeeder,DatabaseSeeder}.php` |
| Feature tests | `tests/Feature/PublicRoutesTest.php` |

Admin login: seeded user is `admin@example.com` / `password` (see
`database/seeders/DatabaseSeeder.php` — **change this before any real
deployment**).

---

## Current Status

> Update this section whenever you complete meaningful work. Be
> specific about what's done vs. in-progress so the next session
> doesn't have to re-derive it by reading every file.

**Phase 0 (Groundwork) — ✅ COMPLETE.**
- `config/saas.php` exists with `mode` backed by `SAAS_MODE` (default
  `false`).
- `App\Services\CurrentProfileResolver` exists and is registered as a
  scoped singleton in `AppServiceProvider`.
- All 8 public Volt pages (`home`, `about`, `projects`,
  `project-detail`, `skills`, `certificates`, `certificate-detail`,
  `contact`) resolve the current profile via
  `app(CurrentProfileResolver::class)->resolve()`, never via
  `Profile::first()` directly.
- `tests/Feature/PublicRoutesTest.php` covers all public routes with
  and without a seeded profile, plus a 404 case.
- Baseline single-tenant app is fully scaffolded and working: Laravel
  13, Filament 5.7, Livewire Volt, Tailwind 4, DomPDF. Migrations,
  models, seeders and Filament resources exist for `Profile`,
  `Experience`, `Project`, `Skill`, `Certificate`, `ResumeGeneration`,
  `Template`, `Theme`, `AiSetting`, `GithubSetting`.
- **Important caveat**: `Experience`/`Project`/`Skill`/`Certificate`/
  `ResumeGeneration`/`GithubSetting` do **not** yet have a `profile_id`
  column or relationship to `Profile` — they are queried directly and
  unscoped (e.g. `Project::query()->orderBy('sort_order')->get()`)
  throughout the public pages and `TemplateConversionService`, exactly
  as described in the plan's guiding principles ("scattered unscoped
  queries" is today's real starting state, not a simplification). This
  is precisely what Phase 1 fixes.
- `php artisan test` passes (4 tests, 17 assertions) as of this
  writing.

**Phase 1 (Multi-tenancy foundation) — ✅ COMPLETE.**
- All migrations already existed: `accounts` table, `account_id`/
  `user_id`/`slug` on `profiles`, `profile_id` on
  `experiences`/`projects`/`skills`/`certificates`/`resume_generations`/
  `github_settings`, `account_id` on `templates` and `ai_settings`,
  plus the `make_tenancy_columns_required` migration.
- `TenancyBackfill` command exists at `app/Console/Commands/
  TenancyBackfill.php`.
- `Account` model with `HasFactory`, relationships to `User`,
  `Profile`, `AiSetting`, `Template`.
- `Profile` model with `HasFactory`, relationships to `Account`, `User`,
  and all child models.
- `BelongsToProfile` trait on `Experience`, `Project`, `Skill`,
  `Certificate`, `ResumeGeneration`, `GithubSetting` — auto-scopes
  queries to the current profile on public pages.
- Filament native multi-tenancy: `->tenant(Account::class)` in
  `AdminPanelProvider`. `User` implements `HasTenants`/
  `HasDefaultTenant`.
- `ScopedToCurrentProfile` trait (fixed `isScopedToTenant` method
  override to avoid Filament 5.7 property collision) on all 6
  profile-scoped Filament resources.
- `CreatesScopedToCurrentProfile` trait on all 6 Create pages.
- `TemplateResource` overrides `getEloquentQuery()` to include global
  templates (`orWhereNull('account_id')`).
- `ProfileSeeder` updated to create `Account` and assign
  `account_id`/`user_id`/`slug`/`profile_id`.
- Factories: `AccountFactory`, `ProfileFactory`, `ProjectFactory`.
- `tests/Feature/MultiTenancyIsolationTest.php` — proves Tenant A's
  scoped data never leaks to Tenant B.
- `tests/Feature/PublicRoutesTest.php` updated for NOT NULL columns.
- `php artisan test` passes (5 tests, 21 assertions).
- `php artisan migrate:fresh --seed` succeeds.

**Phase 2 (Auth, Registration & Onboarding) — ✅ COMPLETE.**
- Custom Filament registration page at `App\Filament\Pages\Auth\Register`
  registered via `->registration(Register::class)` in `AdminPanelProvider`.
- Self-service registration automatically provisions an `Account`
  (`plan_slug = 'free'`) and an unpublished `Profile` with unique,
  collision-resistant slug.
- Interactive 4-step Volt onboarding wizard at `/onboarding`
  (`resources/views/pages/onboarding.blade.php`), allowing customization of
  slug (with live URL preview), full name, headline, bio, location, starter
  theme, and portfolio publishing (`is_published = true`).
- `OnboardingChecklistWidget` created at
  `app/Filament/Widgets/OnboardingChecklistWidget.php` and registered on the
  Filament dashboard to track setup progress.
- `bootstrap/app.php` configured with `redirectTo(guests: '/admin/login')`.
- `tests/Feature/RegistrationAndOnboardingTest.php` covers user registration,
  duplicate slug resolution, auth guards, profile publishing via onboarding
  wizard, and checklist progress calculations.
- `php artisan test` passes (10 tests, 61 assertions).

**Phase 3 (Public routing overhaul + SAAS_MODE) — ✅ COMPLETE.**
- Middleware `ResolveTenantFromSlug` (`app/Http/Middleware/ResolveTenantFromSlug.php`)
  registered as `resolve.tenant` alias in `bootstrap/app.php`.
- `routes/web.php` dual branch:
  - `SAAS_MODE=false`: root routes preserved for self-hosted mode.
  - `SAAS_MODE=true`: `/` renders marketing homepage, `/pricing` renders
    pricing cards, and published tenant portfolios are served under
    `/{slug}/...` with `URL::defaults(['slug' => $profile->slug])`.
- Updated `project-detail.blade.php` and `certificate-detail.blade.php`
  to handle both single-tenant `{slug}` and multi-tenant `{projectSlug}` /
  `{certSlug}` route parameters.
- High-converting marketing homepage (`resources/views/pages/marketing/home.blade.php`)
  and pricing page (`resources/views/pages/marketing/pricing.blade.php`).
- `tests/Feature/SaasRoutingTest.php` covers marketing site, pricing page,
  tenant slug routing, HTTP-level multi-tenant project isolation, and 404
  for unpublished/non-existent profiles.
- `php artisan test` passes (14 tests, 89 assertions).

**Phase 4 (Billing, plans, and usage metering) — ✅ COMPLETE.**
- `config/plans.php` defines tier quotas and entitlements for `free`, `pro`,
  and `agency` tiers.
- Stripe & Laravel Cashier integration installed (`laravel/cashier v16.7`).
- `Account` model implements `Laravel\Cashier\Billable` with `stripeEmail()`
  and `stripeName()` helpers.
- Migration `2026_08_14_150000_add_billing_and_usage_to_accounts_table.php`
  adds `stripe_id`, `pm_type`, `pm_last_four`, and creates `subscriptions` and
  `subscription_items` tables.
- `AiUsageGuard` (`app/Services/AiUsageGuard.php`) enforces per-Account monthly
  AI resume generation limits, increments usage, and grants BYOK
  (Bring-Your-Own-Key) exemptions when an active `AiSetting` key is present.
- `CreateResumeGeneration` hooks into `AiUsageGuard` to block quota-exceeded
  generations with `AiQuotaExceededException` and display friendly Filament danger
  notifications.
- Filament `BillingSettings` page (`app/Filament/Pages/BillingSettings.php` +
  `resources/views/filament/pages/billing-settings.blade.php`) provides a
  visual AI usage meter progress bar, subscription tier cards, Stripe Checkout
  upgrade triggers, and Stripe Customer Portal redirection.
- `tests/Feature/BillingAndUsageMeteringTest.php` verifies free quota enforcement,
  BYOK exemptions, Pro unlimited access, usage recording, and Filament page
  rendering.
- `php artisan test` passes (20 tests, 105 assertions).

**Phase 5 (Theming & Visual Design Overhaul) — ✅ COMPLETE.**
- Reshaped theme data model: migration adds `theme_id` and `theme_mode_default`
  (`light|dark|system`) to `profiles` table and reshapes existing theme colors.
- `ThemeSeeder` expanded to 7 handcrafted themes with full `dark` and `light`
  9-token palettes: *Cyber Matrix*, *Bioluminescent*, *Toxic Cyberpunk*,
  *Slate Professional*, *Warm Editorial*, *Ocean*, and *Classic Mono*.
- `ThemeService` updated to output dual-mode scoped CSS variables
  (`:root, [data-theme-mode="dark"]` and `[data-theme-mode="light"]`), respect
  tenant custom theme preferences, and support real-time `?preview_theme=` querystrings.
- Anti-FOUC synchronous script and client-side light/dark mode switcher added to
  `resources/views/layouts/app.blade.php` with `localStorage` persistence.
- Filament `ThemeSelector` page (`app/Filament/Pages/ThemeSelector.php` +
  `resources/views/filament/pages/theme-selector.blade.php`) built with interactive
  theme catalog cards, dual-mode color swatches, and live preview iframe.
- `tests/Feature/ThemingAndModeTest.php` covers catalog verification, dual-mode CSS
  generation, profile theme scoping, preview querystrings, and Filament settings saving.
- `php artisan test` passes (25 tests, 268 assertions).

**Phase 6 (Custom Domains) — ✅ COMPLETE.**
- Database & Model: migration `2026_08_16_170000_create_domains_table.php` created
  with `profile_id`, `domain` (unique index), `verification_token`, `verified_at`.
  `Domain` model created with automatic normalization and token generation.
- `Profile` model updated with `domains()` and `customDomain()` relationships.
- Tenant Resolution & Middleware: `ResolveTenantFromSlug` and `CurrentProfileResolver`
  updated to check incoming request `Host` header against verified custom domains
  before falling back to `{slug}` routing.
- Public Routes: `routes/web.php` and `marketing/home.blade.php` updated to serve
  tenant portfolios seamlessly on custom domain root (`/`) and subpages (`/about`,
  `/projects`, etc.) with zero `{slug}` prefix in URL.
- Filament Admin UI: `DomainSettings` page (`app/Filament/Pages/DomainSettings.php` +
  `resources/views/filament/pages/domain-settings.blade.php`) built with plan gating
  (Pro/Agency only), domain input form, DNS TXT/CNAME instructions, and instant
  verification / disconnect actions.
- `tests/Feature/CustomDomainRoutingTest.php` covers root & subpage resolution,
  unverified domain rejection, HTTP-level multi-tenant isolation, plan gating, and
  Filament domain management lifecycle.
- `php artisan test` passes (31 tests, 302 assertions).

**Phase 7 (Growth & Differentiation Features) — ✅ COMPLETE.**
- Resume Import & AI Parser (7.1): installed `smalot/pdfparser` for PDF extraction.
  Built `ResumeParserService` (`app/Services/ResumeParserService.php`) with OpenAI/Anthropic
  JSON schema extraction and fallback rule parsing. Built Filament `ResumeImport` page
  (`app/Filament/Pages/ResumeImport.php` + `resources/views/filament/pages/resume-import.blade.php`)
  with interactive 2-step review & edit workflow.
- Cover Letter Generator (7.2): migration `2026_08_17_180000_create_cover_letter_generations_table.php`,
  model `CoverLetterGeneration` with `BelongsToProfile`, `CoverLetterService` with `AiUsageGuard`
  metering, and Filament `CoverLetterGenerationResource` with automatic tailoring.
- Job Application Tracker (7.3): migration `2026_08_17_190000_create_job_applications_table.php`,
  model `JobApplication` with `BelongsToProfile`, and interactive 5-column Kanban board
  (`app/Filament/Pages/JobTracker.php` + `resources/views/filament/pages/job-tracker.blade.php`).
- Public Developer Directory & SEO (7.5): migration `2026_08_17_200000_add_directory_and_seo_to_profiles_table.php`
  adds `is_discoverable` and `meta_description`. Public `/discover` Volt directory page
  (`resources/views/pages/marketing/discover.blade.php`) with real-time search & category filters.
  Dynamic OpenGraph meta tags and `<title>` added to `resources/views/layouts/app.blade.php`.
- `tests/Feature/GrowthFeaturesTest.php` covers PDF resume extraction, portfolio import, cover letter
  generation with quota guards, job tracker Kanban workflow and tenant isolation, and discover directory filtering.
- `php artisan test` passes (37 tests, 330 assertions).

**Phase 8 (Agency / White-Label Tier) — ✅ COMPLETE.**
- Multi-User Teams: migration `2026_08_18_210000_create_account_user_table.php` creates
  pivot table with `owner`, `editor`, and `viewer` roles. `Account::members()` and
  `User::memberAccounts()` relationships defined with role permission helpers.
- Multiple Profiles per Account: plan limits enforced on `Profile` creation via
  `Account::canCreateProfile()`. `ScopedToCurrentProfile` updated to support session-based
  active profile switching for multi-client agency workflows.
- White-Label Branding: migration `2026_08_18_220000_add_white_label_to_accounts_table.php`
  adds `custom_brand_name`, `custom_logo_path`, and `hide_platform_branding`.
  Public layout `resources/views/layouts/app.blade.php` conditionally renders platform
  badge, suppressed for agency accounts with `hide_platform_branding = true`.
- Filament Admin UI: built `TeamSettings` page (`app/Filament/Pages/TeamSettings.php`)
  for team member invitations and role management, and `AgencyBrandingSettings` page
  (`app/Filament/Pages/AgencyBrandingSettings.php`). `BillingSettings` access restricted to owners.
- `tests/Feature/AgencyAndTeamTest.php` covers team invitations, owner-only billing access,
  plan-gated multi-profile limits, session profile switching, and white-label branding.
- `php artisan test` passes (42 tests, 356 assertions).

**Phase 9 (GDPR, Security & Hardening) — ✅ COMPLETE.**
- GDPR Data Rights: built `App\Services\GdprService` providing `exportAccountData()`
  (downloadable JSON archive of all profiles, projects, skills, resumes, cover letters,
  job applications, and settings) and `deleteAccount()` with zero-orphan deletion cascade.
- Built Filament `PrivacyAndData` page (`app/Filament/Pages/PrivacyAndData.php` + view)
  with data archive export streaming and double-confirmed account deletion.
- Legal & Compliance: built public `/terms` and `/privacy` Volt pages and added
  floating Cookie Consent banner with `localStorage` persistence in `app.blade.php`.
- Rate Limiting & Security: configured `contact-form` and `ai-generation` named rate
  limiters in `AppServiceProvider` and wired throttling into `contact.blade.php`.
- Abuse Reporting & Moderation: migration `2026_08_19_230000_create_portfolio_reports_table.php`,
  model `PortfolioReport`, and Filament moderation resource `PortfolioReportResource`.
- `tests/Feature/GdprAndSecurityTest.php` covers export payloads, zero-orphan cascade deletion,
  legal pages, contact form rate limiting, and report moderation.
- `php artisan test` passes (47 tests, 392 assertions).

**Phase 10 (Launch Checklist & Final Polish) — ✅ COMPLETE.**
- Production Health & Telemetry: verified `/up` endpoint and environment configurations.
- Route List Integrity: audited 74 registered endpoints via `php artisan route:list` across
  both `SAAS_MODE=true` and `SAAS_MODE=false`.
- Full-Spectrum Automated Testing: all 10 feature test suites pass (47 tests, 392 assertions)
  covering tenancy isolation, registration, onboarding, marketing, billing, AI quota guards,
  theming, custom domains, PDF parsing, cover letters, Kanban job tracker, teams, white-labeling,
  GDPR data export, and cascade deletion.
- Asset Bundling: verified Vite production bundle via `npm run build`.
- **Transformation Status: 100% COMPLETE across all Phases (0 through 10).**

**Dedicated Multi-Role Login Portals — ✅ COMPLETE.**
- Built 3 dedicated, standalone full-screen Livewire Volt login pages matching the canonical 4-tier visual hierarchy in `01_AGENT_GUIDE.md`:
  - **Developer / Portfolio Owner**: `/developer/login` & `/login` with Emerald Green theme (`#16A34A`), AI Resume highlights, Google/GitHub OAuth, 1-click demo autofill (`developer@example.com`), and redirect to `/developer/dashboard`.
  - **Agency Admin / Owner**: `/agency/login` with Teal theme (`#0D9488`), multi-client studio highlights, 1-click demo autofill (`agency@example.com`), and redirect to `/agency`.
  - **Super Admin (Platform Root)**: `/super-admin/login` & `/admin/login` with Amber Warning theme (`#D97706`), audit logging alerts, strict super-admin authorization guard (rejecting non-super-admins), 1-click demo autofill (`admin@example.com`), and redirect to `/super-admin`.
- Smart guest redirection configured in `bootstrap/app.php` to direct unauthorized visits to the corresponding role login portal.
- Universal logout in `routes/web.php` updated with role-aware redirection.
- `tests/Feature/DedicatedLoginPortalsTest.php` and `tests/Feature/MultiUserRoleLoginTest.php` cover all 3 portals, authorization checks, demo autofill, and guest redirects.

**Dedicated Developer Dashboard Workspace Pages — ✅ COMPLETE.**
- Built 16 dedicated, standalone Livewire Volt developer workspace pages under the Emerald Green theme (`#16A34A` / `#22C55E`), rendering inside `resources/views/layouts/dashboard.blade.php`:
  1. **Profile & Bio Studio** (`/developer/profile`)
  2. **Projects Showcase Studio** (`/developer/projects`)
  3. **Experience Timeline** (`/developer/experiences`)
  4. **Skills Matrix** (`/developer/skills`)
  5. **Certificates & Accreditations** (`/developer/certificates`)
  6. **AI Resume Tailor Studio** (`/developer/resumes`)
  7. **Cover Letter AI Generator** (`/developer/cover-letters`)
  8. **Job Application Tracker Kanban** (`/developer/job-tracker`)
  9. **Resume PDF Parser & Importer** (`/developer/resume-import`)
  10. **GitHub Repository Sync** (`/developer/github-sync`)
  11. **BYOK AI Configuration** (`/developer/ai-settings`)
  12. **Theme Catalog & Mode Switcher** (`/developer/themes`)
  13. **Custom Domains Manager** (`/developer/domains`)
  14. **Resume PDF Template Gallery** (`/developer/templates`)
  15. **Billing & AI Usage Quota** (`/developer/billing`)
  16. **GDPR Data Rights & Privacy** (`/developer/privacy`)
  17. **Developer Operations & Analytics Center** (`/developer/analytics`)
- Updated sidebar navigation (`resources/views/layouts/dashboard.blade.php`) and developer hub launcher cards (`resources/views/pages/dashboard/index.blade.php`) to seamlessly link to the new named `/developer/*` routes.
- `tests/Feature/DeveloperDashboardPagesTest.php` provides 100% automated coverage across all 17 routes, guest redirects, Livewire CRUD actions, and telemetry analytics calculations.
- Full test suite passes 100% (110 tests, 772 assertions).

---

## The four phase guides & design references

| File | Covers | Depends on |
|---|---|---|
| `01_AGENT_GUIDE.md` / `docs/agents/01_AGENT_GUIDE.md` | **Canonical Design Reference** — 4-tier dashboard visual hierarchy & color cues (Portfolio Owner: Green, Agency Owner: Teal, Team Member: Slate Blue, Super Admin: Amber/Orange) | Baseline CSS & Layouts |
| `docs/agents/01-GROUNDWORK.md` | Phase 0 — already done; read for historical context and to understand `CurrentProfileResolver`'s contract before you touch it | — |
| `docs/agents/02-MULTI-TENANCY-FOUNDATION.md` | Phase 1 — `accounts` table, `profile_id`/`account_id` columns, data backfill, Filament native tenancy | Phase 0 |
| `docs/agents/03-BILLING-ONBOARDING-ROUTING.md` | Phases 2, 3, 4 — registration/onboarding, `SAAS_MODE=true` routing + marketing site, Stripe billing + AI usage metering | Phase 1 |
| `docs/agents/04-THEMING-DOMAINS.md` | Phases 5, 6 — light/dark mode overhaul + expanded theme catalog, custom domains | Phase 1 (domains need `profile_id`); theming can start in parallel with Phase 1 in principle but is written assuming it lands after |
| `docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md` | Phases 7, 8, 9, 10 — resume import, cover letters, job tracker, agency/white-label tier, GDPR/security hardening, launch checklist | Phase 1 (all growth features need `profile_id` scoping); billing (Phase 4) for agency pricing |

Suggested execution order (same dependency logic as the original
plan): **0 → 1 → 2 → 3 → 4**, with **5** startable in parallel with 4
once Phase 1 lands, then **6 → 7 → 8 → 9 → 10**, with 6/8/9 gradually
trailing behind an initial launch rather than blocking it.

---

## Validation checklist for every phase

Run this before considering a phase "done" and before updating Current
Status above:

```
php artisan test
php artisan route:list        # sanity-check nothing unexpected broke
npm run build                 # if you touched anything under resources/
```

For any phase that changes tenancy boundaries (1, 3, 6, 8), add a test
that proves data/pages from Tenant A are never visible to Tenant B —
this is non-negotiable, not optional polish.

## Environment notes for this machine

- Windows host, PHP 8.3, Composer 2.10, Node 24. Laravel dev server:
  `php artisan serve`. SQLite database at `database/database.sqlite`.
- `npm install` followed by `npm run build` must succeed before relying
  on `@vite` in Blade — if `npm run build` fails with a native-binary
  error from `rolldown`/`esbuild`, delete `node_modules` +
  `package-lock.json` and reinstall; that has resolved it before on
  this environment.
