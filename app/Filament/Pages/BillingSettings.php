<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Services\AiUsageGuard;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Redirect;

/**
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Filament Billing & Usage Settings page for the current Account.
 */
class BillingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Billing & Usage';

    protected static ?string $title = 'Billing & Usage';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.billing-settings';

    public static function canAccess(): bool
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! $account || ! $user) {
            return false;
        }

        return $account->canManageBilling($user);
    }

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function getPlanDetails(): array
    {
        $account = $this->getAccount();
        $planSlug = $account?->plan_slug ?: 'free';
        $plans = config('plans', []);

        return $plans[$planSlug] ?? [
            'name' => ucfirst($planSlug),
            'max_profiles' => 1,
            'ai_generations_per_month' => 3,
            'custom_domain' => false,
            'remove_branding' => false,
        ];
    }

    public function getUsageStats(): array
    {
        $account = $this->getAccount();

        if (! $account) {
            return [
                'used' => 0,
                'limit' => 3,
                'remaining' => 3,
                'is_byok' => false,
                'is_unlimited' => false,
                'percentage' => 0,
            ];
        }

        $guard = app(AiUsageGuard::class);
        $isByok = $guard->isByokActive($account);
        $planSlug = $account->plan_slug ?: 'free';
        $limit = config("plans.{$planSlug}.ai_generations_per_month");
        $used = (int) $account->ai_generations_used_current_period;
        $isUnlimited = ($limit === null) || $isByok;

        $percentage = $limit ? min(100, (int) round(($used / $limit) * 100)) : 0;
        $remaining = $guard->getRemainingGenerations($account);

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => $remaining,
            'is_byok' => $isByok,
            'is_unlimited' => $isUnlimited,
            'percentage' => $percentage,
            'plan_slug' => $planSlug,
        ];
    }

    public function redirectToPortal()
    {
        $account = $this->getAccount();

        if (! $account || ! $account->stripe_id) {
            Notification::make()
                ->title('No Active Stripe Subscription')
                ->body('You do not have an active Stripe customer account yet. Upgrade your plan to access the portal.')
                ->warning()
                ->send();

            return null;
        }

        try {
            return $account->redirectToBillingPortal(
                static::getUrl()
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Billing Portal Unavailable')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }

    public function upgradeToPlan(string $planSlug)
    {
        $account = $this->getAccount();

        if (! $account) {
            return null;
        }

        $priceId = config("plans.{$planSlug}.stripe_price_id");

        if (! $priceId) {
            // For testing or when price ID is not set yet, update plan directly in dev/local
            if (app()->isLocal() || app()->runningUnitTests()) {
                $account->update(['plan_slug' => $planSlug]);

                Notification::make()
                    ->title('Plan Upgraded')
                    ->body("Your account plan has been updated to {$planSlug}.")
                    ->success()
                    ->send();

                return null;
            }

            Notification::make()
                ->title('Price ID Not Configured')
                ->body("Stripe Price ID for {$planSlug} is not configured in .env.")
                ->danger()
                ->send();

            return null;
        }

        try {
            return $account->newSubscription('default', $priceId)
                ->checkout([
                    'success_url' => static::getUrl() . '?checkout=success',
                    'cancel_url' => static::getUrl() . '?checkout=cancelled',
                ]);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Checkout Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
