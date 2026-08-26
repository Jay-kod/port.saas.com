<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-mode="dark" class="overflow-x-hidden w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $currentProfile = app(\App\Services\CurrentProfileResolver::class)->resolve();
        $pageTitle = $currentProfile?->full_name ? $currentProfile->full_name . ' | ' . ($currentProfile->headline ?: 'Portfolio') : config('app.name', 'DevFolio AI');
        $metaDescription = $currentProfile?->meta_description ?: ($currentProfile?->bio ?: 'AI Developer Portfolio & ATS Resume Suite.');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ Str::limit($metaDescription, 160) }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ Str::limit($metaDescription, 200) }}">
    <meta property="og:type" content="website">

    {{--
        Phase 5 (docs/agents/04-THEMING-DOMAINS.md):
        1. Anti-FOUC synchronous script sets data-theme-mode on <html> before first paint.
        2. ThemeService injects dual-mode [data-theme-mode="dark"] and [data-theme-mode="light"] CSS variables.
        3. Client-side toggle button updates <html> and persists to localStorage.
    --}}
    <script>
        (function() {
            try {
                const stored = localStorage.getItem('theme-mode');
                const defaultMode = '{{ $currentProfile?->theme_mode_default ?? "system" }}';
                const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                let mode = stored;
                if (!mode || (mode !== 'light' && mode !== 'dark')) {
                    mode = defaultMode === 'system' ? (systemDark ? 'dark' : 'light') : defaultMode;
                }
                if (mode !== 'light' && mode !== 'dark') mode = 'dark';
                document.documentElement.setAttribute('data-theme-mode', mode);
            } catch (e) {
                document.documentElement.setAttribute('data-theme-mode', 'dark');
            }
        })();
    </script>

    <style>
        {!! app(\App\Services\ThemeService::class)->getCssVariableString($currentProfile) !!}
        html, body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
        }
        body {
            background-color: var(--color-background);
            color: var(--color-text);
            transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen antialiased relative w-full overflow-x-hidden font-body">
    {{-- Floating Theme Light/Dark Mode Switcher (≥44px Touch Target) --}}
    <div class="fixed bottom-20 right-4 sm:bottom-auto sm:top-20 sm:right-6 z-[55]">
        <button
            type="button"
            id="theme-mode-toggle"
            onclick="(function(){
                const current = document.documentElement.getAttribute('data-theme-mode') === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme-mode', current);
                localStorage.setItem('theme-mode', current);
            })()"
            class="touch-target p-3 rounded-full shadow-lg border backdrop-blur-md transition-all duration-300 hover:scale-105 cursor-pointer min-h-[48px] min-w-[48px]"
            style="background-color: var(--color-surface); border-color: var(--color-border); color: var(--color-text);"
            data-tooltip="Toggle Dark and Light interface theme"
            data-tooltip-pos="left"
            aria-label="Toggle Theme Mode"
        >
            {{-- Moon icon (shown in light mode to switch to dark) --}}
            <svg class="w-5 h-5 hidden [html[data-theme-mode='light']_&]:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            {{-- Sun icon (shown in dark mode to switch to light) --}}
            <svg class="w-5 h-5 hidden [html[data-theme-mode='dark']_&]:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    {{ $slot }}

    {{-- Public White-Label Branding / Platform Badge --}}
    @php
        $hidePlatformBranding = $currentProfile?->account?->hide_platform_branding && ($currentProfile?->account?->plan_slug === 'agency');
    @endphp

    @if(! $hidePlatformBranding && config('saas.mode'))
        <footer class="py-6 sm:py-8 text-center font-caption opacity-75 border-t" style="border-color: var(--color-border); color: var(--color-text-muted);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <p>
                    Powered by <a href="{{ route('home') }}" class="font-bold underline hover:opacity-100 transition" style="color: var(--color-primary);" data-tooltip="Visit DevFolio SaaS Platform Homepage">DevFolio.AI</a>
                    @if(Route::has('terms'))
                        &bull; <a href="{{ route('terms') }}" class="hover:underline" data-tooltip="Read Terms of Service">Terms</a>
                    @endif
                    @if(Route::has('privacy'))
                        &bull; <a href="{{ route('privacy') }}" class="hover:underline" data-tooltip="Read Privacy Policy">Privacy</a>
                    @endif
                </p>
            </div>
        </footer>
    @endif

    {{-- Cookie Consent Banner --}}
    <div
        id="cookie-consent-banner"
        class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 max-w-md p-5 rounded-2xl shadow-2xl border backdrop-blur-xl z-50 transition-all duration-300 hidden"
        style="background-color: var(--color-surface); border-color: var(--color-border); color: var(--color-text);"
    >
        <div class="flex items-start gap-3">
            <div class="text-2xl shrink-0">🍪</div>
            <div class="space-y-3 flex-1">
                <p class="font-caption leading-relaxed" style="color: var(--color-text-muted);">
                    We use cookies and storage to manage theme preferences and sessions per our <a href="{{ Route::has('privacy') ? route('privacy') : '#' }}" class="underline font-bold" style="color: var(--color-primary);">Privacy Policy</a>.
                </p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="(function(){
                            localStorage.setItem('cookie-consent', 'accepted');
                            document.getElementById('cookie-consent-banner').classList.add('hidden');
                        })()"
                        class="min-h-[44px] px-4 rounded-xl text-sm font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 transition cursor-pointer inline-flex items-center justify-center"
                        data-tooltip="Accept essential cookies and preference tracking"
                    >
                        Accept All
                    </button>
                    <button
                        type="button"
                        onclick="(function(){
                            localStorage.setItem('cookie-consent', 'essential');
                            document.getElementById('cookie-consent-banner').classList.add('hidden');
                        })()"
                        class="min-h-[44px] px-4 rounded-xl text-sm font-semibold hover:opacity-80 transition cursor-pointer inline-flex items-center justify-center border"
                        style="border-color: var(--color-border); color: var(--color-text-muted);"
                        data-tooltip="Only save essential theme and session cookies"
                    >
                        Essential Only
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (!localStorage.getItem('cookie-consent')) {
                const banner = document.getElementById('cookie-consent-banner');
                if (banner) banner.classList.remove('hidden');
            }
        });
    </script>

    @livewireScripts
</body>
</html>
