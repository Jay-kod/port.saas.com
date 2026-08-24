<?php

use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public Portfolio Routes
|--------------------------------------------------------------------------
|
| Phase 3 & 6 (docs/agents/04-THEMING-DOMAINS.md):
| SAAS_MODE=false (default / self-hosted): root-level routes serving the
| single profile resolved by CurrentProfileResolver.
| SAAS_MODE=true: "/" serves marketing homepage or custom domain portfolio,
| "/pricing" serves pricing table, and tenant portfolios are served under
| "/{slug}/..." or directly via verified custom domains.
|
*/
// Socialite OAuth Routes (GitHub & Google)
Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

// Universal static routes available in both SaaS and Self-Hosted modes
Volt::route('/terms', 'marketing.terms')->name('terms');
Volt::route('/privacy', 'marketing.privacy')->name('privacy');
Volt::route('/onboarding', 'onboarding')->name('onboarding')->middleware(['auth', 'prevent.back']);

// Dedicated Multi-Role Login Routes
Volt::route('/developer/login', 'auth.developer-login')->name('developer.login');
Route::redirect('/login', '/developer/login', 302)->name('login');

Route::post('/developer/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    $remember = $request->boolean('remember');

    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, $remember)) {
        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $request->session()->regenerate();
    $user = \Illuminate\Support\Facades\Auth::user();

    $profile = $user->profile ?? $user->accounts()->first()?->profiles()->first();
    if (! $profile) {
        return redirect()->to('/onboarding');
    }

    $intended = session()->pull('url.intended');
    if ($intended && ! \Illuminate\Support\Str::contains($intended, ['/login', '/logout', '/register', 'password'])) {
        return redirect()->to($intended);
    }

    return redirect()->to(route('dashboard'));
})->name('developer.login.submit');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    return redirect()->route('developer.login.submit', $request->all());
});

Volt::route('/agency/login', 'auth.agency-login')->name('agency.login');
Route::post('/agency/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    $remember = $request->boolean('remember');

    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, $remember)) {
        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $request->session()->regenerate();
    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->isSuperAdmin()) {
        return redirect()->to(route('super-admin.dashboard'));
    }

    $intended = session()->pull('url.intended');
    if ($intended && ! \Illuminate\Support\Str::contains($intended, ['/login', '/logout', '/register', 'password'])) {
        return redirect()->to($intended);
    }

    return redirect()->to(route('agency'));
})->name('agency.login.submit');

Volt::route('/super-admin/login', 'auth.super-admin-login')->name('super-admin.login');
Route::post('/super-admin/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    $remember = $request->boolean('remember');

    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, $remember)) {
        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $user = \Illuminate\Support\Facades\Auth::user();

    if (! $user->isSuperAdmin()) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Access Restricted: This master console is strictly reserved for Platform Super Administrators.',
        ]);
    }

    $request->session()->regenerate();

    $intended = session()->pull('url.intended');
    if ($intended && ! \Illuminate\Support\Str::contains($intended, ['/login', '/logout', '/register', 'password'])) {
        return redirect()->to($intended);
    }

    return redirect()->to(route('super-admin.dashboard'));
})->name('super-admin.login.submit');

Volt::route('/forgot-password', 'auth.forgot-password')->name('password.request');

// Universal Logout Route with deliberate session invalidation & anti-cache headers
Route::match(['get', 'post'], '/logout', function (\Illuminate\Http\Request $request) {
    $user = \Illuminate\Support\Facades\Auth::user();
    $wasSuperAdmin = $user && $user->isSuperAdmin();
    $wasAgency = $user && $user->isAgencyUser();

    \Illuminate\Support\Facades\Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $redirectUrl = route('developer.login');
    if ($wasSuperAdmin) {
        $redirectUrl = route('super-admin.login');
    } elseif ($wasAgency) {
        $redirectUrl = route('agency.login');
    }

    return redirect()->to($redirectUrl)->withHeaders([
        'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
    ]);
})->name('logout');

// Custom Dashboards (must be registered before wildcard /{slug} routes)
Route::middleware(['auth', 'prevent.back'])->group(function () {
    Volt::route('/developer/dashboard', 'dashboard.index')->name('dashboard');
    Route::redirect('/dashboard', '/developer/dashboard', 302);
    Route::redirect('/developer', '/developer/dashboard', 302);

    // Dedicated Developer Workspace Studios & Tools
    Volt::route('/developer/profile', 'developer.profile')->name('developer.profile');
    Volt::route('/developer/projects', 'developer.projects')->name('developer.projects');
    Volt::route('/developer/experiences', 'developer.experiences')->name('developer.experiences');
    Volt::route('/developer/skills', 'developer.skills')->name('developer.skills');
    Volt::route('/developer/certificates', 'developer.certificates')->name('developer.certificates');
    Volt::route('/developer/resumes', 'developer.resumes')->name('developer.resumes');
    Volt::route('/developer/cover-letters', 'developer.cover-letters')->name('developer.cover-letters');
    Volt::route('/developer/job-tracker', 'developer.job-tracker')->name('developer.job-tracker');
    Volt::route('/developer/resume-import', 'developer.resume-import')->name('developer.resume-import');
    Volt::route('/developer/github-sync', 'developer.github-sync')->name('developer.github-sync');
    Volt::route('/developer/ai-settings', 'developer.ai-settings')->name('developer.ai-settings');
    Volt::route('/developer/themes', 'developer.themes')->name('developer.themes');
    Volt::route('/developer/domains', 'developer.domains')->name('developer.domains');
    Volt::route('/developer/billing', 'developer.billing')->name('developer.billing');
    Volt::route('/developer/privacy', 'developer.privacy')->name('developer.privacy');
    Volt::route('/developer/templates', 'developer.templates')->name('developer.templates');
    Volt::route('/developer/analytics', 'developer.analytics')->name('developer.analytics');

    Volt::route('/agency', 'agency.index')->name('agency');
    Volt::route('/agency/clients', 'agency.clients')->name('agency.clients');
    Volt::route('/agency/team', 'agency.team')->name('agency.team');
    Volt::route('/agency/branding', 'agency.branding')->name('agency.branding');
    Volt::route('/agency/domains', 'agency.domains')->name('agency.domains');
    Volt::route('/agency/billing', 'agency.billing')->name('agency.billing');
    Volt::route('/agency/analytics', 'agency.analytics')->name('agency.analytics');
});

Route::middleware(['auth', 'super_admin', 'prevent.back'])->group(function () {
    Volt::route('/super-admin', 'super-admin.index')->name('super-admin.dashboard');
});

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
    // Custom domain sub-routes without slug prefix (resolved via Host header in resolve.tenant)
    Route::middleware('resolve.tenant')->group(function () {
        Volt::route('/about', 'about')->name('custom-domain.about');
        Volt::route('/projects', 'projects')->name('custom-domain.projects');
        Volt::route('/projects/{projectSlug}', 'project-detail')->name('custom-domain.projects.show');
        Volt::route('/skills', 'skills')->name('custom-domain.skills');
        Volt::route('/certificates', 'certificates')->name('custom-domain.certificates');
        Volt::route('/certificates/{certSlug}', 'certificate-detail')->name('custom-domain.certificates.show');
        Volt::route('/contact', 'contact')->name('custom-domain.contact');
    });

    Volt::route('/', 'marketing.home')->name('home');
    Volt::route('/discover', 'marketing.discover')->name('discover');
    Volt::route('/pricing', 'marketing.pricing')->name('pricing');

    // Tenant slug routes (e.g. saas.com/{slug}/...) - Catch-all wildcard at the bottom
    Route::middleware('resolve.tenant')->group(function () {
        Volt::route('/{slug}', 'home')->name('tenant.home');
        Volt::route('/{slug}/about', 'about')->name('about');
        Volt::route('/{slug}/projects', 'projects')->name('projects');
        Volt::route('/{slug}/projects/{projectSlug}', 'project-detail')->name('projects.show');
        Volt::route('/{slug}/skills', 'skills')->name('skills');
        Volt::route('/{slug}/certificates', 'certificates')->name('certificates');
        Volt::route('/{slug}/certificates/{certSlug}', 'certificate-detail')->name('certificates.show');
        Volt::route('/{slug}/contact', 'contact')->name('contact');
    });
}
