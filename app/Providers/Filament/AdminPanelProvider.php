<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Widgets\OnboardingChecklistWidget;
use App\Models\Account;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Phase 1 (docs/agents/02-MULTI-TENANCY-FOUNDATION.md), section 1.5:
 * Filament's native multi-tenancy scopes every resource to the
 * logged-in user's current Account. `Profile`, `Template` and
 * `AiSetting` resources have a direct `account()` relationship and
 * are scoped by Filament automatically (Template/AiSetting also
 * manually allow NULL/global rows through — see those resources'
 * getEloquentQuery()). `Experience`/`Project`/`Skill`/`Certificate`/
 * `ResumeGeneration`/`GithubSetting` only relate to Account indirectly
 * (through Profile), so those 6 resources explicitly disable
 * Filament's automatic tenant scope (`$isScopedToTenant = false`) and
 * scope themselves to "the current Account's one Profile" manually —
 * see any of their Resource classes for the pattern. Do not hand-roll
 * ->where('account_id', ...) scoping anywhere else.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->registration(Register::class)
            ->tenant(Account::class, slugAttribute: 'id')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->topbar(false)
            ->sidebarCollapsibleOnDesktop()
            ->brandName('DevFolio.AI')
            ->brandLogo(fn () => view('filament.components.brand-logo'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                OnboardingChecklistWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
