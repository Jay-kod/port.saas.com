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
            ->homeUrl(function (): string {
                $user = auth()->user();
                if ($user && ($user->is_super_admin || $user->email === 'admin@example.com')) {
                    return route('super-admin.dashboard');
                }
                if ($user && ($user->accounts()->where('plan_slug', 'agency')->exists() || $user->email === 'agency@example.com')) {
                    return route('agency');
                }
                return route('dashboard');
            })
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('DevFolio.AI')
            ->brandLogo(fn () => view('filament.components.header-title'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Default dashboard removed in favor of custom /dashboard Blade route
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                OnboardingChecklistWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Dashboard')
                    ->icon('heroicon-o-squares-2x2')
                    ->url(fn (): string => route('dashboard')),
                'billing' => \Filament\Navigation\MenuItem::make()
                    ->label('Billing & Usage')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn (): string => \App\Filament\Pages\BillingSettings::getUrl()),
                'domains' => \Filament\Navigation\MenuItem::make()
                    ->label('Custom Domains')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn (): string => \App\Filament\Pages\DomainSettings::getUrl()),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Hide duplicate sidebar collapse buttons so only the main topbar hamburger remains */
                        .fi-topbar-collapse-sidebar-btn-ctn { display: none !important; }
                        .fi-sidebar-header-collapse-btn { display: none !important; }
                    </style>
                    <script>
                        window.addEventListener("pageshow", function(event) {
                            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                                window.location.reload();
                            }
                        });
                    </script>
                ')
            )
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
                \App\Http\Middleware\PreventBackHistory::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
