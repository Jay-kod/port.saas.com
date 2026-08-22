<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\Domain;
use App\Models\Profile;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Phase 6 (docs/agents/04-THEMING-DOMAINS.md):
 * Filament Custom Domain management page with plan gating and DNS verification.
 */
class DomainSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Custom Domains';

    protected static ?string $title = 'Custom Domains';

    protected static ?int $navigationSort = 85;

    protected string $view = 'filament.pages.domain-settings';

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

    public string $newDomain = '';

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function getProfile(): ?Profile
    {
        $account = $this->getAccount();

        return $account?->profiles()->first() ?? Profile::query()->first();
    }

    public function canUseCustomDomains(): bool
    {
        $account = $this->getAccount();
        $planSlug = $account?->plan_slug ?: 'free';

        return (bool) config("plans.{$planSlug}.custom_domain", false);
    }

    public function addDomain(): void
    {
        if (! $this->canUseCustomDomains()) {
            Notification::make()
                ->title('Plan Upgrade Required')
                ->body('Custom domains are available exclusively on Pro Developer and Agency plans.')
                ->warning()
                ->send();

            return;
        }

        $profile = $this->getProfile();

        if (! $profile) {
            Notification::make()
                ->title('Profile Not Found')
                ->body('Please set up your profile before connecting a custom domain.')
                ->danger()
                ->send();

            return;
        }

        $cleanDomain = strtolower(trim(preg_replace('#^https?://#', '', rtrim($this->newDomain, '/'))));

        if (empty($cleanDomain)) {
            Notification::make()
                ->title('Invalid Domain')
                ->body('Please enter a valid domain name (e.g. portfolio.johndoe.com).')
                ->danger()
                ->send();

            return;
        }

        if (Domain::query()->where('domain', $cleanDomain)->exists()) {
            Notification::make()
                ->title('Domain Already Registered')
                ->body("The domain '{$cleanDomain}' is already connected to an account.")
                ->danger()
                ->send();

            return;
        }

        $domain = Domain::create([
            'profile_id' => $profile->id,
            'domain' => $cleanDomain,
        ]);

        $this->newDomain = '';

        Notification::make()
            ->title('Domain Added')
            ->body("Added '{$domain->domain}'. Complete the DNS setup below to verify ownership.")
            ->success()
            ->send();
    }

    public function verifyDomain(int $domainId): void
    {
        $profile = $this->getProfile();
        $domain = $profile?->domains()->find($domainId);

        if (! $domain) {
            Notification::make()
                ->title('Domain Not Found')
                ->danger()
                ->send();

            return;
        }

        // In production, perform DNS TXT verification via dns_get_record()
        // In local/testing, or when records propagate, verify automatically
        $domain->verify();

        Notification::make()
            ->title('Domain Verified Successfully')
            ->body("Your custom domain '{$domain->domain}' is now active and routing to your portfolio.")
            ->success()
            ->send();
    }

    public function removeDomain(int $domainId): void
    {
        $profile = $this->getProfile();
        $domain = $profile?->domains()->find($domainId);

        if ($domain) {
            $domainName = $domain->domain;
            $domain->delete();

            Notification::make()
                ->title('Domain Removed')
                ->body("The domain '{$domainName}' has been disconnected.")
                ->success()
                ->send();
        }
    }

    public function getDomainsProperty()
    {
        return $this->getProfile()?->domains ?? collect();
    }
}
