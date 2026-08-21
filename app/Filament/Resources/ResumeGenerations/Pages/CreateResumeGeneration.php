<?php

namespace App\Filament\Resources\ResumeGenerations\Pages;

use App\Exceptions\AiQuotaExceededException;
use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\ResumeGenerations\ResumeGenerationResource;
use App\Models\Account;
use App\Services\AiUsageGuard;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

/**
 * Phase 4 (docs/agents/03-BILLING-ONBOARDING-ROUTING.md):
 * Wraps resume generation creation with AiUsageGuard quota check.
 */
class CreateResumeGeneration extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = ResumeGenerationResource::class;

    protected function beforeCreate(): void
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        if (! $account) {
            $profile = static::getResource()::resolveCurrentTenantProfile();
            $account = $profile?->account;
        }

        if ($account) {
            try {
                app(AiUsageGuard::class)->ensureCanGenerate($account);
            } catch (AiQuotaExceededException $e) {
                Notification::make()
                    ->title('AI Quota Exceeded')
                    ->body($e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function afterCreate(): void
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        if (! $account) {
            $profile = static::getResource()::resolveCurrentTenantProfile();
            $account = $profile?->account;
        }

        if ($account) {
            app(AiUsageGuard::class)->recordGeneration($account);
        }
    }
}
