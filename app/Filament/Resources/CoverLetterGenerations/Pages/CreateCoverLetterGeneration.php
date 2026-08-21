<?php

namespace App\Filament\Resources\CoverLetterGenerations\Pages;

use App\Exceptions\AiQuotaExceededException;
use App\Filament\Resources\Concerns\CreatesScopedToCurrentProfile;
use App\Filament\Resources\CoverLetterGenerations\CoverLetterGenerationResource;
use App\Models\Account;
use App\Services\AiUsageGuard;
use App\Services\CoverLetterService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCoverLetterGeneration extends CreateRecord
{
    use CreatesScopedToCurrentProfile;

    protected static string $resource = CoverLetterGenerationResource::class;

    protected function beforeCreate(): void
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();
        $profile = static::getResource()::resolveCurrentTenantProfile();

        if (! $account) {
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

        // Generate tailored cover letter if content was not provided manually
        if (empty($this->data['content'])) {
            if ($profile && ! empty($this->data['job_title']) && ! empty($this->data['company_name']) && ! empty($this->data['job_description'])) {
                try {
                    $coverLetter = app(CoverLetterService::class)->generate(
                        $profile,
                        $this->data['job_title'],
                        $this->data['company_name'],
                        $this->data['job_description']
                    );
                    $this->data['content'] = $coverLetter;
                    $this->data['status'] = 'completed';
                } catch (\Throwable $e) {
                    $this->data['status'] = 'failed';
                    $this->data['error_message'] = $e->getMessage();
                }
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
