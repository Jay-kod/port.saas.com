<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\Profile;
use App\Models\Theme;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Phase 5 (docs/agents/04-THEMING-DOMAINS.md):
 * Filament Theme & Appearance selector page with live preview.
 */
class ThemeSelector extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'Theme & Appearance';

    protected static ?string $title = 'Theme & Appearance';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.theme-selector';

    public ?int $selectedThemeId = null;

    public string $themeModeDefault = 'system';

    public ?string $previewSlug = null;

    public function mount(): void
    {
        $profile = $this->getProfile();

        if ($profile) {
            $defaultThemeId = Theme::query()->where('is_default', true)->value('id') ?? Theme::query()->value('id');
            $this->selectedThemeId = $profile->theme_id ?? $defaultThemeId;
            $this->themeModeDefault = $profile->theme_mode_default ?? 'system';
            $this->previewSlug = Theme::query()->where('id', $this->selectedThemeId)->value('slug');
        }
    }

    public function getProfile(): ?Profile
    {
        /** @var Account|null $account */
        $account = Filament::getTenant();

        return $account?->profiles()->first() ?? Profile::query()->first();
    }

    public function selectTheme(int $themeId): void
    {
        $this->selectedThemeId = $themeId;
        $this->previewSlug = Theme::query()->where('id', $themeId)->value('slug');
    }

    public function save(): void
    {
        $profile = $this->getProfile();

        if (! $profile) {
            Notification::make()
                ->title('No Profile Found')
                ->body('Unable to locate profile for the current account.')
                ->danger()
                ->send();

            return;
        }

        $profile->update([
            'theme_id' => $this->selectedThemeId,
            'theme_mode_default' => $this->themeModeDefault,
        ]);

        Notification::make()
            ->title('Theme Preferences Saved')
            ->body('Your portfolio theme and default mode have been updated.')
            ->success()
            ->send();
    }

    public function getThemesProperty()
    {
        return Theme::query()->get();
    }

    public function getPreviewUrlProperty(): string
    {
        $profile = $this->getProfile();
        $slug = $this->previewSlug;

        if (config('saas.mode') && $profile) {
            $baseUrl = url("/{$profile->slug}");
        } else {
            $baseUrl = url('/');
        }

        return $slug ? "{$baseUrl}?preview_theme={$slug}" : $baseUrl;
    }
}
