<?php

namespace App\Filament\Pages;

use App\Models\OauthSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class OAuthSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'OAuth Credentials';

    protected static ?string $title = 'OAuth & Social Login Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 96;

    protected string $view = 'filament.pages.oauth-settings';

    public ?string $github_client_id = null;
    public ?string $github_client_secret = null;
    public bool $github_is_enabled = true;

    public ?string $google_client_id = null;
    public ?string $google_client_secret = null;
    public bool $google_is_enabled = true;

    public function mount(): void
    {
        // Load GitHub
        $github = OauthSetting::where('provider', 'github')->first();
        $this->github_client_id = $github?->client_id ?? config('services.github.client_id', '');
        $this->github_client_secret = $github?->client_secret ?? config('services.github.client_secret', '');
        $this->github_is_enabled = $github ? (bool) $github->is_enabled : true;

        // Load Google
        $google = OauthSetting::where('provider', 'google')->first();
        $this->google_client_id = $google?->client_id ?? config('services.google.client_id', '');
        $this->google_client_secret = $google?->client_secret ?? config('services.google.client_secret', '');
        $this->google_is_enabled = $google ? (bool) $google->is_enabled : true;
    }

    public function save(): void
    {
        // Save GitHub
        OauthSetting::updateOrCreate(
            ['provider' => 'github'],
            [
                'client_id' => ! empty($this->github_client_id) ? trim($this->github_client_id) : null,
                'client_secret' => ! empty($this->github_client_secret) ? trim($this->github_client_secret) : null,
                'is_enabled' => $this->github_is_enabled,
            ]
        );

        // Save Google
        OauthSetting::updateOrCreate(
            ['provider' => 'google'],
            [
                'client_id' => ! empty($this->google_client_id) ? trim($this->google_client_id) : null,
                'client_secret' => ! empty($this->google_client_secret) ? trim($this->google_client_secret) : null,
                'is_enabled' => $this->google_is_enabled,
            ]
        );

        Notification::make()
            ->title('OAuth Credentials Saved')
            ->body('Google and GitHub OAuth keys have been stored securely in the database and are active immediately.')
            ->success()
            ->send();
    }

    public function isGithubConfigured(): bool
    {
        return ! empty($this->github_client_id) && ! empty($this->github_client_secret);
    }

    public function isGoogleConfigured(): bool
    {
        return ! empty($this->google_client_id) && ! empty($this->google_client_secret);
    }

    public function getGithubCallbackUrl(): string
    {
        return url('/auth/callback/github');
    }

    public function getGoogleCallbackUrl(): string
    {
        return url('/auth/callback/google');
    }

    public function getAppUrl(): string
    {
        return url('/');
    }
}
