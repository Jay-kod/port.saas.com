<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Theme;

/**
 * Phase 5 (docs/agents/04-THEMING-DOMAINS.md):
 * Resolves color tokens and generates dual-mode CSS variables for public portfolio pages.
 */
class ThemeService
{
    public function getActiveTheme(?Profile $profile = null): ?Theme
    {
        // Live preview override via ?preview_theme=slug query param
        if (request()->filled('preview_theme')) {
            $previewTheme = Theme::query()->where('slug', request('preview_theme'))->first();
            if ($previewTheme) {
                return $previewTheme;
            }
        }

        // Tenant custom theme preference
        if ($profile && $profile->theme) {
            return $profile->theme;
        }

        // Platform default theme fallback
        return Theme::query()->where('is_active', true)->first()
            ?? Theme::query()->where('is_default', true)->first()
            ?? Theme::query()->first();
    }

    /**
     * Get 9 color tokens for a given mode ('dark' or 'light').
     */
    public function getColors(?Profile $profile = null, ?string $mode = null): array
    {
        $theme = $this->getActiveTheme($profile);
        $resolvedMode = $mode ?? $profile?->theme_mode_default ?? 'system';
        $key = $resolvedMode === 'light' ? 'light' : 'dark';

        $colors = $theme?->colors;

        if (is_array($colors) && (isset($colors['dark']) || isset($colors['light']))) {
            return $colors[$key] ?? $colors['dark'] ?? $this->getDefaultThemeColors()[$key] ?? $this->getDefaultThemeColors()['dark'];
        }

        if (is_array($colors)) {
            return $colors; // Flat map fallback
        }

        return $this->getDefaultThemeColors()[$key] ?? $this->getDefaultThemeColors()['dark'];
    }

    /**
     * Generates CSS variable rules for both dark and light modes.
     * Allows instant, FOUC-free client-side mode toggling via [data-theme-mode].
     */
    public function getCssVariableString(?Profile $profile = null): string
    {
        $theme = $this->getActiveTheme($profile);
        $colors = $theme?->colors;

        $darkColors = is_array($colors) && isset($colors['dark'])
            ? $colors['dark']
            : (is_array($colors) && ! isset($colors['light']) ? $colors : $this->getDefaultThemeColors()['dark']);

        $lightColors = is_array($colors) && isset($colors['light']) && is_array($colors['light'])
            ? $colors['light']
            : $this->getDefaultThemeColors()['light'];

        $darkVars = [];
        foreach ($darkColors as $token => $value) {
            $darkVars[] = '--color-'.str_replace('_', '-', $token).': '.$value.';';
        }

        $lightVars = [];
        foreach ($lightColors as $token => $value) {
            $lightVars[] = '--color-'.str_replace('_', '-', $token).': '.$value.';';
        }

        return ':root, [data-theme-mode="dark"] { ' . implode(' ', $darkVars) . ' } '
            . '[data-theme-mode="light"] { ' . implode(' ', $lightVars) . ' }';
    }

    public function getDefaultThemeColors(): array
    {
        return [
            'dark' => [
                'background' => '#0a0e14',
                'surface' => '#111722',
                'primary' => '#00ff9c',
                'secondary' => '#00c2ff',
                'accent' => '#ff2ec4',
                'text' => '#e6f1ff',
                'text_muted' => '#8a9bb5',
                'border' => '#1f2b3d',
                'success' => '#00ff9c',
            ],
            'light' => [
                'background' => '#f4f8f6',
                'surface' => '#ffffff',
                'primary' => '#059669',
                'secondary' => '#0284c7',
                'accent' => '#db2777',
                'text' => '#0f172a',
                'text_muted' => '#64748b',
                'border' => '#e2e8f0',
                'success' => '#16a34a',
            ],
        ];
    }

    /**
     * Idempotently ensures the platform's default theme catalog exists.
     */
    public function initializeDefaultThemes(): void
    {
        if (Theme::query()->exists()) {
            return;
        }

        Theme::create([
            'name' => 'Cyber Matrix',
            'slug' => 'cyber-matrix',
            'colors' => $this->getDefaultThemeColors(),
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
