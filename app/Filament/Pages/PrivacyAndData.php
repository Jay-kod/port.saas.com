<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Services\GdprService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Phase 9 (docs/agents/05-GROWTH-AGENCY-HARDENING-LAUNCH.md):
 * GDPR Data Export and Account Deletion management page.
 */
class PrivacyAndData extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Privacy & Data';

    protected static ?string $title = 'GDPR & Privacy Rights';

    protected static ?int $navigationSort = 92;

    protected string $view = 'filament.pages.privacy-and-data';

    public string $deleteConfirmation = '';

    public function getAccount(): ?Account
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account;
    }

    public function isOwner(): bool
    {
        $account = $this->getAccount();
        $user = Auth::user();

        return $account && $user && ($account->owner_user_id === $user->id || $account->getUserRole($user) === 'owner');
    }

    public function exportData(): ?StreamedResponse
    {
        $account = $this->getAccount();

        if (! $account) {
            return null;
        }

        $service = app(GdprService::class);
        $data = $service->exportAccountData($account);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $filename = 'devfolio-export-account-' . $account->id . '-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function deleteAccount(): void
    {
        if (! $this->isOwner()) {
            Notification::make()->title('Permission Denied')->danger()->send();

            return;
        }

        if (trim($this->deleteConfirmation) !== 'DELETE') {
            Notification::make()
                ->title('Confirmation Required')
                ->body('Please type "DELETE" exactly to confirm account deletion.')
                ->danger()
                ->send();

            return;
        }

        $account = $this->getAccount();

        if ($account) {
            app(GdprService::class)->deleteAccount($account);

            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->redirect('/');
        }
    }
}
