<?php

use function Livewire\Volt\{state};

?>

<div class="min-h-screen text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 flex flex-col justify-between overflow-x-hidden w-full font-body">
    <x-marketing-header />

    {{-- Pricing Header --}}
    <section class="section-marketing px-4 sm:px-6 text-center max-w-4xl mx-auto w-full">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full font-caption font-bold bg-amber-500/10 border border-amber-500/30 text-amber-500 dark:text-amber-400 mb-6">
            <span>⚡</span>
            <span>Transparent Pricing</span>
        </div>
        <h1 class="font-h1 text-gray-900 dark:text-white">
            Simple plans for ambitious developers
        </h1>
        <p class="font-hero-sub text-gray-600 dark:text-gray-400 max-w-[72ch] mx-auto mt-4 sm:mt-6 leading-relaxed px-2">
            Choose the plan that fits your career goals. Start free and upgrade whenever you need unlimited AI tailoring and custom domains.
        </p>
    </section>

    {{-- Pricing Cards --}}
    <section class="pb-16 sm:pb-24 px-4 sm:px-6 max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            {{-- Free Tier --}}
            <div class="p-8 rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h2 class="font-h3 text-gray-900 dark:text-white">Free</h2>
                        <span class="font-caption px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium">Starter</span>
                    </div>
                    <p class="font-caption text-gray-600 dark:text-gray-400 mb-6">Everything you need to launch a personal portfolio.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="font-h2 text-gray-900 dark:text-white">$0</span>
                        <span class="font-caption text-gray-600 dark:text-gray-400">/ forever</span>
                    </div>

                    <ul class="space-y-4 font-caption text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-800 pt-6">
                        <li class="flex items-center gap-2.5">
                            <span class="text-emerald-400 font-bold">✓</span> 1 Published Portfolio Profile
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-emerald-400 font-bold">✓</span> 3 AI Resume Generations / mo
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-emerald-400 font-bold">✓</span> Automatic GitHub Project Sync
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-emerald-400 font-bold">✓</span> ATS-Ready PDF Resume Export
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-emerald-400 font-bold">✓</span> Standard <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">devfolio.ai/{slug}</code> URL
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="/admin/register" class="btn-secondary w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-900 dark:text-white cursor-pointer" data-tooltip="Sign up for the free starter tier">
                        <span>Get Started Free</span>
                    </a>
                </div>
            </div>

            {{-- Pro Tier (Featured) --}}
            <div class="p-8 rounded-3xl border-2 border-amber-500 bg-white/90 dark:bg-gray-900/90 flex flex-col justify-between relative shadow-2xl shadow-amber-500/10">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-amber-500 text-gray-950 font-extrabold font-caption uppercase tracking-wider">
                    Most Popular
                </div>

                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h2 class="font-h3 text-gray-900 dark:text-white">Pro Developer</h2>
                        <span class="font-caption px-3 py-1 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-300 font-bold">Full Power</span>
                    </div>
                    <p class="font-caption text-gray-600 dark:text-gray-400 mb-6">For developers actively applying and interviewing.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="font-h2 text-gray-900 dark:text-white">$12</span>
                        <span class="font-caption text-gray-600 dark:text-gray-400">/ month</span>
                    </div>

                    <ul class="space-y-4 font-caption text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-800 pt-6">
                        <li class="flex items-center gap-2.5 font-bold text-gray-900 dark:text-white">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> <strong>Unlimited</strong> AI Resume Tailoring
                        </li>
                        <li class="flex items-center gap-2.5 font-medium text-gray-900 dark:text-white">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> Custom Domain Support (SSL included)
                        </li>
                        <li class="flex items-center gap-2.5 font-medium text-gray-900 dark:text-white">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> Remove Platform Branding
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> All Premium Themes & Accents
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> Priority GitHub Sync & Updates
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-amber-500 dark:text-amber-400 font-bold">✓</span> Bring Your Own AI Key (BYOK)
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="/admin/register" class="btn-primary w-full bg-amber-500 hover:bg-amber-400 text-gray-950 shadow-lg shadow-amber-500/25 cursor-pointer" data-tooltip="Select Pro plan for unlimited AI resume tailoring">
                        <span>Upgrade to Pro</span>
                    </a>
                </div>
            </div>

            {{-- Agency Tier --}}
            <div class="p-8 rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-baseline mb-4">
                        <h2 class="font-h3 text-gray-900 dark:text-white">Agency / Team</h2>
                        <span class="font-caption px-3 py-1 rounded-full bg-purple-500/20 text-purple-600 dark:text-purple-300 font-bold">Teams</span>
                    </div>
                    <p class="font-caption text-gray-600 dark:text-gray-400 mb-6">For bootcamps, agencies, and recruiter rosters.</p>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="font-h2 text-gray-900 dark:text-white">$49</span>
                        <span class="font-caption text-gray-600 dark:text-gray-400">/ month</span>
                    </div>

                    <ul class="space-y-4 font-caption text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-800 pt-6">
                        <li class="flex items-center gap-2.5">
                            <span class="text-purple-400 font-bold">✓</span> <strong>Unlimited</strong> Profiles & Portfolios
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-purple-400 font-bold">✓</span> White-Labeling & Custom Branding
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-purple-400 font-bold">✓</span> Multi-Tenant Management Dashboard
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-purple-400 font-bold">✓</span> Custom Resume Template Builder
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="text-purple-400 font-bold">✓</span> Dedicated Support & API Access
                        </li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="{{ route('agency.login') }}" class="btn-secondary w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/80 hover:bg-teal-600 hover:text-white text-gray-900 dark:text-white cursor-pointer" data-tooltip="Sign in to Agency Hub or configure team plan">
                        <span>Start Agency Hub</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-gray-800/80 py-12 sm:py-20 px-4 sm:px-6 bg-gray-50 dark:bg-gray-950 font-caption text-gray-600 dark:text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                &copy; {{ date('Y') }} DevFolio AI Platform. Built for developers with Laravel, Livewire Volt & Filament.
            </div>
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-6 gap-y-2">
                <a href="{{ route('home') }}#features" class="hover:text-gray-400 cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Jump to platform features">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-400 cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="View pricing tiers and features">Pricing</a>
                <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-500 hover:text-emerald-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Developer Workspace">Developer Login</a>
                <a href="{{ route('agency.login') }}" target="_blank" rel="noopener noreferrer" class="text-teal-500 hover:text-teal-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Agency Hub">Agency Hub</a>
                <a href="{{ route('super-admin.login') }}" target="_blank" rel="noopener noreferrer" class="text-amber-500 hover:text-amber-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Super Admin Portal">Super Admin</a>
            </div>
        </div>
    </footer>
</div>
