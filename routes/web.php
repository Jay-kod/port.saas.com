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

Volt::route('/agency/login', 'auth.agency-login')->name('agency.login');
Volt::route('/super-admin/login', 'auth.super-admin-login')->name('super-admin.login');
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
    Volt::route('/agency', 'agency.index')->name('agency');
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
