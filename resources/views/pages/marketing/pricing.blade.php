<?php

use function Livewire\Volt\{state};

?>

<div class="min-h-screen text-gray-100 flex flex-col justify-between" style="background-color: var(--color-background, #0a0e14);">
    {{-- Top Navigation --}}
    <header class="border-b border-gray-800/80 bg-gray-950/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-18 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center font-black text-xl shadow-lg shadow-amber-500/5">
                    ⚡
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            </a>

            <div class="flex items-center gap-6 sm:gap-8">
                <nav class="flex items-center gap-6 sm:gap-8 text-sm font-medium text-gray-300">
                    <a href="{{ route('home') }}" class="hover:text-amber-400 transition font-medium">Home</a>
                    <a href="{{ route('home') }}#features" class="hover:text-amber-400 transition">Features</a>
                    <a href="{{ route('pricing') }}" class="text-amber-400 font-semibold transition">Pricing</a>
                    <a href="{{ route('discover') }}" class="hover:text-amber-400 transition">Discover</a>
                </nav>

                <a href="/admin/login" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                    <span>Get Started</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </header>

    {{-- Pricing Header --}}
    <section class="pt-20 pb-16 px-6 text-center max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-6">
            Transparent Pricing
        </div>
        <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight">
            Simple plans for ambitious developers
        </h1>
        <p class="mt-6 text-lg text-gray-400 max-w-2xl mx-auto">
            Choose the plan that fits your career goals. Start free and upgrade whenever you need unlimited AI tailoring and custom domains.
        </p>
    </section>

    {{-- Pricing Cards --}}
    <section class="pb-24 px-6 max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Free Tier --}}
            <div class="p-8 rounded-3xl border border-gray-800 bg-gray-900/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h3 class="text-xl font-bold text-white">Free</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-gray-800 text-gray-400 font-medium">Starter</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-6">Everything you need to launch a personal portfolio.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-white">$0</span>
                        <span class="text-xs text-gray-400">/ forever</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-gray-300 border-t border-gray-800 pt-6">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400">✓</span> 1 Published Portfolio Profile
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400">✓</span> 3 AI Resume Generations / mo
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400">✓</span> Automatic GitHub Project Sync
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400">✓</span> ATS-Ready PDF Resume Export
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400">✓</span> Standard <code>devfolio.ai/{slug}</code> URL
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="/admin/register" class="w-full block text-center py-3 rounded-xl border border-gray-700 bg-gray-800/80 hover:bg-gray-800 text-white text-xs font-bold transition">
                        Get Started Free
                    </a>
                </div>
            </div>

            {{-- Pro Tier (Featured) --}}
            <div class="p-8 rounded-3xl border-2 border-amber-500 bg-gray-900/90 flex flex-col justify-between relative shadow-2xl shadow-amber-500/10">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-amber-500 text-gray-950 font-extrabold text-[10px] uppercase tracking-wider">
                    Most Popular
                </div>

                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h3 class="text-xl font-bold text-white">Pro Developer</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 font-medium">Full Power</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-6">For developers actively applying and interviewing.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-white">$12</span>
                        <span class="text-xs text-gray-400">/ month</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-gray-300 border-t border-gray-800 pt-6">
                        <li class="flex items-center gap-2 font-medium text-white">
                            <span class="text-amber-400">✓</span> <strong>Unlimited</strong> AI Resume Tailoring
                        </li>
                        <li class="flex items-center gap-2 font-medium text-white">
                            <span class="text-amber-400">✓</span> Custom Domain Support (SSL included)
                        </li>
                        <li class="flex items-center gap-2 font-medium text-white">
                            <span class="text-amber-400">✓</span> Remove Platform Branding
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-amber-400">✓</span> All Premium Themes & Custom Accents
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-amber-400">✓</span> Priority GitHub Sync & Updates
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-amber-400">✓</span> Bring Your Own AI Key (BYOK) Exemptions
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="/admin/register" class="w-full block text-center py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs font-extrabold transition shadow-lg shadow-amber-500/25">
                        Upgrade to Pro
                    </a>
                </div>
            </div>

            {{-- Agency Tier --}}
            <div class="p-8 rounded-3xl border border-gray-800 bg-gray-900/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h3 class="text-xl font-bold text-white">Agency / Team</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-purple-500/20 text-purple-300 font-medium">Teams</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-6">For bootcamps, agencies, and recruiter rosters.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-white">$49</span>
                        <span class="text-xs text-gray-400">/ month</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-gray-300 border-t border-gray-800 pt-6">
                        <li class="flex items-center gap-2">
                            <span class="text-purple-400">✓</span> <strong>Unlimited</strong> Profiles & Portfolios
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-purple-400">✓</span> White-Labeling & Custom Branding
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-purple-400">✓</span> Multi-Tenant Management Dashboard
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-purple-400">✓</span> Custom Resume Template Builder
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-purple-400">✓</span> Dedicated Support & API Access
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="/admin/register" class="w-full block text-center py-3 rounded-xl border border-gray-700 bg-gray-800/80 hover:bg-gray-800 text-white text-xs font-bold transition">
                        Start Agency Trial
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-gray-800/80 py-10 px-6 bg-gray-950 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} DevFolio AI Platform. Built for developers with Laravel, Livewire Volt & Filament.
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}#features" class="hover:text-gray-400">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-400">Pricing</a>
                <a href="/admin/login" class="hover:text-gray-400">Admin Login</a>
            </div>
        </div>
    </footer>
</div>
