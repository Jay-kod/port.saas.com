<?php

namespace App\Filament\Pages;

use App\Models\Account;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Phase 8 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * White-label agency branding settings page.
 */
class AgencyBrandingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Agency Branding';

    protected static ?string $title = 'Agency White-Label Branding';

    protected static ?int $navigationSort = 88;

    protected string $view = 'filament.pages.agency-branding-settings';

    public static function canAccess(): bool
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! $account || ! $user) {
            return false;
        }

        if ($user->is_super_admin || $account->owner_user_id === $user->id || $account->getUserRole($user) === 'owner') {
            return true;
        }

        return false;
    }

    public ?string $custom_brand_name = '';

    public ?string $custom_logo_path = '';

    public bool $hide_platform_branding = false;

    public function mount(): void
    {
        $account = $this->getAccount();

        if ($account) {
            $this->custom_brand_name = $account->custom_brand_name ?? '';
            $this->custom_logo_path = $account->custom_logo_path ?? '';
            $this->hide_platform_branding = (bool) $account->hide_platform_branding;
        }
    }

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function canUseWhiteLabel(): bool
    {
        $account = $this->getAccount();
        $planSlug = $account?->plan_slug ?: 'free';

        return (bool) config("plans.{$planSlug}.remove_branding", false);
    }

    public function saveBranding(): void
    {
        if (! $this->canUseWhiteLabel()) {
            Notification::make()
                ->title('Agency Plan Required')
                ->body('White-label custom branding is exclusive to the Agency tier.')
                ->warning()
                ->send();

            return;
        }

        $account = $this->getAccount();

        if ($account) {
            $account->update([
                'custom_brand_name' => $this->custom_brand_name ?: null,
                'custom_logo_path' => $this->custom_logo_path ?: null,
                'hide_platform_branding' => $this->hide_platform_branding,
            ]);

            Notification::make()
                ->title('Branding Settings Saved')
                ->body('Your white-label configuration has been updated.')
                ->success()
                ->send();
        }
    }
}
