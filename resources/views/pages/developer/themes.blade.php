<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Profile;
use App\Models\Theme;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Themes & Appearance');

state([
    'selectedThemeId' => function () {
        $profile = Auth::user()?->profile;
        $defaultId = Theme::where('is_default', true)->value('id') ?? Theme::value('id');
        return $profile?->theme_id ?? $defaultId;
    },
    'themeModeDefault' => function () {
        return Auth::user()?->profile?->theme_mode_default ?? 'system';
    },
    'previewSlug' => function () {
        $profile = Auth::user()?->profile;
        $themeId = $profile?->theme_id ?? Theme::where('is_default', true)->value('id') ?? Theme::value('id');
        return Theme::where('id', $themeId)->value('slug') ?? 'slate-professional';
    },
    'savedMessage' => '',
]);

$selectTheme = function ($id) {
    $this->selectedThemeId = $id;
    $this->previewSlug = Theme::where('id', $id)->value('slug');
};

$saveThemeSettings = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $profile->update([
        'theme_id' => $this->selectedThemeId,
        'theme_mode_default' => $this->themeModeDefault,
    ]);

    $this->savedMessage = 'Theme and appearance settings updated successfully!';
};

$getThemes = function () {
    return Theme::where('is_active', true)->get();
};

?>

<div class="space-y-8 max-w-6xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    PLATFORM DESIGN
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Themes & Design Systems
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Choose from 7 handcrafted dual-mode design systems with custom tokens, typography, and contrast ratios.
            </p>
        </div>

        <button wire:click="saveThemeSettings" type="button" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0" data-tooltip="Save theme design system and default mode to portfolio" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>Apply Theme</span>
        </button>
    </div>

    {{-- Feedback Messages --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss notification">&times;</button>
        </div>
    @endif

    {{-- Theme Mode Picker --}}
    <div class="glass-card rounded-3xl p-6 space-y-4">
        <h3 class="text-sm font-bold font-heading text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>Default Light / Dark Mode Preference</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="p-4 rounded-2xl bg-slate-900/80 border transition-all cursor-pointer flex items-center gap-3 {{ $themeModeDefault === 'system' ? 'border-cyan-500 bg-cyan-950/20' : 'border-slate-800' }}" data-tooltip="Auto-detect visitor operating system light/dark mode">
                <input type="radio" wire:model.live="themeModeDefault" value="system" class="text-cyan-500 focus:ring-cyan-500" />
                <div>
                    <div class="text-xs font-bold text-white">System Preference</div>
                    <div class="text-[11px] text-slate-400">Match visitor's OS setting</div>
                </div>
            </label>

            <label class="p-4 rounded-2xl bg-slate-900/80 border transition-all cursor-pointer flex items-center gap-3 {{ $themeModeDefault === 'dark' ? 'border-cyan-500 bg-cyan-950/20' : 'border-slate-800' }}" data-tooltip="Default public portfolio to dark mode">
                <input type="radio" wire:model.live="themeModeDefault" value="dark" class="text-cyan-500 focus:ring-cyan-500" />
                <div>
                    <div class="text-xs font-bold text-white">Dark Mode First</div>
                    <div class="text-[11px] text-slate-400">Sleek, high-contrast dark aesthetic</div>
                </div>
            </label>

            <label class="p-4 rounded-2xl bg-slate-900/80 border transition-all cursor-pointer flex items-center gap-3 {{ $themeModeDefault === 'light' ? 'border-cyan-500 bg-cyan-950/20' : 'border-slate-800' }}" data-tooltip="Default public portfolio to light mode">
                <input type="radio" wire:model.live="themeModeDefault" value="light" class="text-cyan-500 focus:ring-cyan-500" />
                <div>
                    <div class="text-xs font-bold text-white">Light Mode First</div>
                    <div class="text-[11px] text-slate-400">Crisp, editorial daylight palette</div>
                </div>
            </label>
        </div>
    </div>

    {{-- Theme Catalog Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($this->getThemes() as $theme)
            @php
                $colors = (array) $theme->colors;
                $darkColors = $colors['dark'] ?? $colors;
                $isSelected = ($selectedThemeId == $theme->id);
            @endphp
            <div wire:click="selectTheme({{ $theme->id }})" class="glass-card rounded-3xl p-5 border cursor-pointer transition-all duration-200 flex flex-col justify-between space-y-4 {{ $isSelected ? 'border-emerald-500 ring-2 ring-emerald-500/30 bg-slate-900/90 shadow-xl shadow-emerald-950/40' : 'border-white/5 hover:border-slate-700 bg-slate-900/50' }}" data-tooltip="Activate {{ $theme->name }} design system">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold font-heading text-white">{{ $theme->name }}</span>
                        @if($isSelected)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500 text-slate-950">
                                ACTIVE
                            </span>
                        @endif
                    </div>

                    {{-- Palette Swatches --}}
                    <div class="p-3 rounded-2xl bg-slate-950 border border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
                            <span>Palette Tokens</span>
                            <span>{{ $theme->slug }}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5 h-6">
                            <div class="rounded-md" style="background-color: {{ $darkColors['primary'] ?? '#10b981' }};" title="Primary"></div>
                            <div class="rounded-md" style="background-color: {{ $darkColors['surface'] ?? '#0f172a' }};" title="Surface"></div>
                            <div class="rounded-md" style="background-color: {{ $darkColors['accent'] ?? '#06b6d4' }};" title="Accent"></div>
                            <div class="rounded-md" style="background-color: {{ $darkColors['card'] ?? '#1e293b' }};" title="Card"></div>
                            <div class="rounded-md" style="background-color: {{ $darkColors['border'] ?? '#334155' }};" title="Border"></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-white/5 font-mono">
                    <span class="text-slate-400 text-[11px]">7 Tokens Dual-Mode</span>
                    <span class="{{ $isSelected ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">
                        {{ $isSelected ? 'Selected' : 'Click to select' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

</div>
