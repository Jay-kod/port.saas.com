# 04 — Theming Overhaul & Custom Domains (Phases 5, 6)

**Status: ⬜ NOT STARTED.**

Prerequisite: Phase 1 (`docs/agents/02-MULTI-TENANCY-FOUNDATION.md`)
for `profiles.account_id`/`profile_id` scoping. Phase 5 (theming) can
technically start in parallel with Phase 1 in principle, but this
guide assumes Phase 1 has landed since it references per-Profile theme
settings. Phase 6 (custom domains) additionally needs Phase 3's routing
middleware (`docs/agents/03-BILLING-ONBOARDING-ROUTING.md`).

These two phases are grouped because they're the "make each tenant's
public-facing site feel like their own" arc, and because a second
agent/session can plausibly work on this file's Phase 5 while another
session works on Phase 4's billing — they touch almost entirely
disjoint files.

---

## PHASE 5 — Theme system overhaul: light/dark mode + expanded catalog

### Goal
Treat "theme" (accent palette) and "mode" (light/dark) as two
independent, orthogonal settings, instead of doubling the number of
themes. This lets you add light mode without redesigning the 3 existing
dark themes from scratch.

### Current state (read this before changing anything)

- `app/Models/Theme.php` — `colors` is cast to `array`, currently a
  **flat 9-token map**: `background`, `surface`, `primary`,
  `secondary`, `accent`, `text`, `text_muted`, `border`, `success`.
- `database/seeders/ThemeSeeder.php` seeds exactly 3 dark themes:
  Cyber Matrix (`is_active: true`, `is_default: true`), Bioluminescent,
  Toxic Cyberpunk. This seeder is the extension point referenced below
  — add new themes here, don't create a second seeder.
- `app/Services/ThemeService.php` has 4 public methods:
  `getActiveTheme(): ?Theme`, `getColors(): array`,
  `getCssVariableString(): string`, `getDefaultThemeColors(): array`,
  plus `initializeDefaultThemes(): void`. **None of these take a
  Profile or mode parameter today.**
- The one call site that injects theme CSS variables is
  `resources/views/layouts/app.blade.php`:
  ```blade
  <style>
      :root { {!! app(\App\Services\ThemeService::class)->getCssVariableString() !!} }
      body { background: var(--color-background); color: var(--color-text); }
  </style>
  ```
  This file already has a large "SaaS NOTE (Phase 5)" comment block —
  read it in place before editing.
- There is currently **no Filament `ThemeSelector`/admin theme-picker
  page** in this codebase — the original plan's section 5.5 assumed one
  existed. You'll be building it fresh in this phase, not modifying an
  existing one. A basic `ThemeResource` (CRUD for `Theme` rows) already
  exists at `app/Filament/Resources/Themes/ThemeResource.php` from the
  baseline scaffold — that's for platform-admin theme catalog
  management, not the same thing as a per-tenant "pick your theme"
  picker; build the picker as a separate Filament page.

### 5.1 Data model changes

New migration:
```php
Schema::table('profiles', function (Blueprint $table) {
    $table->foreignId('theme_id')->nullable()->after('slug')->constrained('themes');
    $table->string('theme_mode_default')->default('system')->after('theme_id'); // light|dark|system
});
```

Reshape `themes.colors` — write a data migration (not just a schema
one) that wraps every existing row's flat map under a `"dark"` key:
```php
Theme::query()->get()->each(function (Theme $theme) {
    $colors = $theme->colors;

    if (! isset($colors['dark']) && ! isset($colors['light'])) {
        $theme->update(['colors' => ['dark' => $colors, 'light' => null]]);
    }
});
```
Run this as an `up()` step in a migration (not a seeder — it must run
against whatever real theme rows already exist, seeded or not) guarded
by the `isset` check so it's safe to run twice.

Update `ThemeSeeder.php`'s 3 existing entries to also provide a
hand-tuned `"light"` sibling for each (see 5.3 — do not
auto-invert the dark palette; hand-tune contrast/readability for each).

### 5.2 Service changes

```php
public function getColors(?Profile $profile = null, ?string $mode = null): array
{
    $theme = $profile?->theme ?? $this->getActiveTheme();
    $resolvedMode = $mode ?? $profile?->theme_mode_default ?? 'system';

    // 'system' has no server-side color truth — fall back to dark
    // server-side (matches today's only-dark-exists behavior) and let
    // the client-side toggle (5.4) override via a re-render/JS swap.
    $key = $resolvedMode === 'light' ? 'light' : 'dark';

    return $theme?->colors[$key] ?? $theme?->colors['dark'] ?? $this->getDefaultThemeColors();
}

public function getCssVariableString(?Profile $profile = null, ?string $mode = null): string
{
    $vars = [];
    foreach ($this->getColors($profile, $mode) as $token => $value) {
        $vars[] = '--color-'.str_replace('_', '-', $token).': '.$value.';';
    }
    return implode(' ', $vars);
}
```
Keep both params optional/nullable and defaulting to today's behavior
(`getActiveTheme()`, dark) when called with no arguments — this means
any call site you *don't* get to updating won't break, but you should
still update the one known call site:

`resources/views/layouts/app.blade.php`:
```blade
:root { {!! app(\App\Services\ThemeService::class)->getCssVariableString(app(\App\Services\CurrentProfileResolver::class)->resolve()) !!} }
```
This is the "one-line update per call site" the plan promises — don't
need to touch anything else.

### 5.3 Expand the catalog

Add to `ThemeSeeder.php` (alongside the existing 3, each now with both
`dark` and `light` keys designed by hand): "Slate Professional",
"Warm Editorial", "Ocean", "Classic Mono" — 4 new entries, bringing the
catalog to 7 total. Follow the existing array shape in the seeder
exactly; each needs both `dark` and `light` sub-maps with all 9 tokens,
tuned for readable contrast (don't just invert hex values
mathematically — check text-on-background contrast manually, aim for
WCAG AA at minimum on `text`/`background` and `text_muted`/`background`
pairs).

### 5.4 Client-side toggle

In `resources/views/layouts/app.blade.php`:
- Add a blocking inline `<script>` in `<head>`, **before** the
  `<style>` block that injects CSS variables, that reads
  `localStorage.getItem('theme-mode')` (fallback:
  `matchMedia('(prefers-color-scheme: dark)')`) and sets
  `document.documentElement.dataset.themeMode` before first paint —
  this file already has `data-theme-mode="dark"` hardcoded on `<html>`
  as a placeholder; replace the hardcoded value with this script's
  output.
- Add a small Alpine.js toggle button (Alpine is not yet a project
  dependency — check `resources/js/app.js`; if it's not there, add it:
  `npm install alpinejs` and bootstrap it in `app.js`) that flips
  `data-theme-mode` and persists to `localStorage`.
- CSS variables need light/dark variants available client-side without
  a full page reload — the simplest correct approach: render **both**
  the dark and light CSS variable blocks into the page (scoped under
  `[data-theme-mode="dark"]`/`[data-theme-mode="light"]` selectors on
  `:root`), and let the toggle just flip the attribute. This avoids
  needing a Livewire round-trip to re-render `getCssVariableString()`
  for a purely cosmetic client-side toggle.

### 5.5 Admin theme picker

New Filament page, `php artisan make:filament-page ThemeSelector` (not
a Resource — this is a singleton settings-style page acting on the
current tenant's one `Profile` row, similar in spirit to how a billing
settings page works). Let the Account owner:
- browse the catalog (`Theme::all()`) with both mode swatches shown,
- set `profile.theme_id` and `profile.theme_mode_default`,
- see a live preview — easiest implementation: an `<iframe>` pointed at
  their own public portfolio URL with a `?preview_theme=` querystring
  the layout reads to override the resolved theme without persisting
  it, rather than building a separate preview renderer.

### Acceptance criteria
- Toggling light/dark on a public page updates instantly, no full
  reload, persists across navigation, no FOUC.
- 7 catalog entries exist, each with both modes.
- `SAAS_MODE=false` still renders correctly with zero config changes —
  verify `tests/Feature/PublicRoutesTest.php` still passes unmodified,
  since the migration wraps existing colors under `dark` automatically
  and `theme_mode_default` defaults to `'system'`.

---

## PHASE 6 — Custom domains

### Goal
Let `pro`/`agency` accounts serve their portfolio at their own domain
(`resume.johndoe.com`) instead of `yoursaas.com/johndoe`.

### Prerequisite check
Requires Phase 3's `ResolveTenantFromSlug` middleware
(`docs/agents/03-BILLING-ONBOARDING-ROUTING.md`, section 3.2) and
Phase 4's plan gating (`docs/agents/03-BILLING-ONBOARDING-ROUTING.md`,
section 4.2) to already exist, since this feature is gated behind
`pro`/`agency` and extends that same middleware.

### Tasks

1. New migration:
```php
Schema::create('domains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('profile_id')->constrained('profiles');
    $table->string('domain')->unique();
    $table->string('verification_token');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});
```

2. Domain management UI: a Filament resource or a section of an
   existing settings page where an owner adds a domain, sees a
   generated TXT record value (`verification_token`) to add to their
   DNS, and a "Verify now" action that performs a DNS TXT lookup
   (`dns_get_record($domain, DNS_TXT)` in PHP, or use a queued job with
   retries since DNS propagation is slow) and sets `verified_at` on
   match.

3. Extend `ResolveTenantFromSlug` (rename conceptually to
   "ResolveTenant" since it now resolves by host OR slug) — check the
   request's `Host` header against `domains` first:
```php
public function handle(Request $request, Closure $next): Response
{
    $host = $request->getHost();

    $domain = Domain::query()->where('domain', $host)->whereNotNull('verified_at')->first();

    if ($domain) {
        app(CurrentProfileResolver::class)->setResolved($domain->profile);
        return $next($request);
    }

    // fall through to existing {slug} resolution for the platform's own domain
    $slug = $request->route('slug');
    $profile = Profile::query()->where('slug', $slug)->where('is_published', true)->first();
    abort_unless($profile, 404);
    app(CurrentProfileResolver::class)->setResolved($profile);

    return $next($request);
}
```
   This needs a route registered for arbitrary hosts too — Laravel's
   router matches on path by default, so you'll need either a
   catch-all route registered without the `{slug}` prefix that this
   middleware disambiguates by host, or `Route::domain()` groups per
   verified domain (harder to keep in sync with a dynamic `domains`
   table — the catch-all + middleware-disambiguation approach above is
   simpler and is what's written above).

4. SSL: if deploying behind Caddy/Cloudflare/a PaaS with automatic
   per-domain certs, wire up whatever that platform's automation hook
   is when a domain's `verified_at` is set (this is infrastructure work
   outside this codebase's scope — document the chosen approach in this
   file once decided, since "budget real time for it" from the original
   plan still applies).

5. Gate behind plan: check `config('plans.'.$account->plan_slug.'.custom_domain')`
   before allowing a domain to be added in the UI.

### Acceptance criteria
- A verified domain serves the correct tenant over HTTPS with no
  `{slug}` in the URL.
- An unverified or unowned domain never serves anyone's content (should
  simply fall through to a 404, not silently match the wrong tenant).
- New test: two profiles, one with a verified domain — assert a request
  with that `Host` header resolves the correct profile, and a request
  with an unverified/unknown host does not resolve any tenant's private
  data.

**Next:** `docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md` (Phases 7-10).
