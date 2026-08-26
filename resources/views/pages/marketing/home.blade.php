<?php

use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$customDomainProfile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

?>

@if ($this->customDomainProfile)
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h1 class="text-4xl font-bold" style="color: var(--color-primary)">{{ $this->customDomainProfile->full_name }}</h1>
        <p class="mt-2 text-xl" style="color: var(--color-text-muted)">{{ $this->customDomainProfile->headline }}</p>
        <p class="mt-6">{{ $this->customDomainProfile->bio }}</p>

        <nav class="mt-10 flex gap-4 flex-wrap">
            <a href="{{ route('custom-domain.about') }}" class="underline">About</a>
            <a href="{{ route('custom-domain.projects') }}" class="underline">Projects</a>
            <a href="{{ route('custom-domain.skills') }}" class="underline">Skills</a>
            <a href="{{ route('custom-domain.certificates') }}" class="underline">Certificates</a>
            <a href="{{ route('custom-domain.contact') }}" class="underline">Contact</a>
        </nav>
    </div>
@else
<div class="min-h-screen text-gray-100 flex flex-col justify-between overflow-x-hidden w-full font-body" style="background-color: var(--color-background, #0a0e14);">
    <x-marketing-header />

    {{-- Hero Section --}}
    <section class="relative section-marketing px-4 sm:px-6 overflow-hidden w-full">
        {{-- Background glow effects with max-width containment --}}
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none max-w-full">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] sm:w-[500px] lg:w-[800px] h-[300px] sm:h-[500px] lg:h-[800px] rounded-full bg-amber-500/10 blur-3xl animate-pulse" style="animation-duration: 6s;"></div>
            <div class="absolute top-1/3 left-1/4 w-[200px] sm:w-[350px] h-[200px] sm:h-[350px] rounded-full bg-emerald-500/8 blur-3xl animate-pulse" style="animation-duration: 8s;"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[180px] sm:w-[280px] h-[180px] sm:h-[280px] rounded-full bg-cyan-500/8 blur-3xl animate-pulse" style="animation-duration: 10s;"></div>
        </div>

        <div class="max-w-5xl mx-auto text-center px-1 sm:px-0">
            {{-- Platform Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-400 font-caption font-bold mb-6 sm:mb-8 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>The Next-Gen SaaS Developer Portfolio</span>
            </div>

            {{-- Single Page H1 --}}
            <h1 class="font-h1 font-h1-hero text-gray-900 dark:text-white">
                Turn your code into an
                <br class="hidden sm:inline"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-emerald-400 to-cyan-400">
                    AI-powered portfolio
                </span>
            </h1>

            {{-- Subheading with 72ch constraint --}}
            <p class="font-hero-sub text-gray-600 dark:text-gray-300 max-w-[72ch] mx-auto leading-relaxed mt-4 sm:mt-6 px-2">
                Effortlessly showcase your projects with automated GitHub sync, create tailored ATS-optimized resumes for any job description in seconds, and share your personal developer website.
            </p>

            {{-- CTA Buttons (≥44px Touch Targets & 48px Height) --}}
            <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-4 max-w-xs sm:max-w-none mx-auto">
                <a href="/admin/register" class="btn-primary bg-amber-500 hover:bg-amber-400 text-gray-950 shadow-xl shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer" data-tooltip="Get started and create your developer portfolio">
                    <span>Create Your Portfolio Free</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('pricing') }}" class="btn-secondary border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-900 dark:text-white cursor-pointer" data-tooltip="View pricing tiers and features">
                    <span>View Pricing & Plans</span>
                </a>
            </div>

            {{-- Metric Highlights --}}
            <div class="mt-12 sm:mt-16 grid grid-cols-2 sm:grid-cols-4 gap-4 pt-8 sm:pt-10 border-t border-gray-200 dark:border-gray-800/80 max-w-4xl mx-auto">
                <div class="p-4 rounded-2xl bg-white/40 dark:bg-gray-900/40 border border-gray-200/60 dark:border-gray-800/60 text-center sm:text-left">
                    <p class="font-h2 text-gray-900 dark:text-white">100%</p>
                    <p class="font-caption text-gray-500 dark:text-gray-400 mt-1">ATS-Optimized Resumes</p>
                </div>
                <div class="p-4 rounded-2xl bg-white/40 dark:bg-gray-900/40 border border-gray-200/60 dark:border-gray-800/60 text-center sm:text-left">
                    <p class="font-h2 text-amber-400">&lt; 30s</p>
                    <p class="font-caption text-gray-500 dark:text-gray-400 mt-1">AI Tailoring Time</p>
                </div>
                <div class="p-4 rounded-2xl bg-white/40 dark:bg-gray-900/40 border border-gray-200/60 dark:border-gray-800/60 text-center sm:text-left">
                    <p class="font-h2 text-emerald-400">GitHub</p>
                    <p class="font-caption text-gray-500 dark:text-gray-400 mt-1">Auto Repo Sync</p>
                </div>
                <div class="p-4 rounded-2xl bg-white/40 dark:bg-gray-900/40 border border-gray-200/60 dark:border-gray-800/60 text-center sm:text-left">
                    <p class="font-h2 text-cyan-400">Custom</p>
                    <p class="font-caption text-gray-500 dark:text-gray-400 mt-1">URLs & Domains</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="section-marketing px-4 sm:px-6 border-t border-gray-200 dark:border-gray-800/80 bg-gray-50/40 dark:bg-gray-950/40">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="font-caption font-bold text-amber-400 uppercase tracking-widest">Engineered for Developers</span>
                <h2 class="font-h2 text-gray-900 dark:text-white mt-2">Everything you need to land your next high-impact role</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                {{-- Feature 1 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        🤖
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">AI Resume Tailoring</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Paste any target job description. Our AI analyzes the role and crafts a precisely tailored resume highlighting your matching skills and accomplishments.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        🐙
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">Automated GitHub Sync</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Connect your GitHub profile once. Your repositories, tech stack tags, stars, and descriptions stay effortlessly updated on your public site.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-cyan-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        📄
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">ATS-Ready PDF Export</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Generate clean, beautifully typeset PDF resumes with a single click. Formats are engineered to easily parse through recruiter applicant tracking systems.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-pink-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-pink-500/10 border border-pink-500/20 text-pink-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        🎨
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">Curated Developer Themes</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Choose from custom-crafted dark mode palettes including Cyber Matrix, Bioluminescent, and Toxic Cyberpunk for an unforgettable first impression.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-purple-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        🌐
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">Dedicated URL & Custom Domains</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Get your own clean web address like <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-caption text-amber-500 dark:text-amber-300">devfolio.ai/your-name</code>, or connect your personal domain with automatic SSL and fast edge delivery.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        ⚡
                    </div>
                    <h3 class="font-h3 text-gray-900 dark:text-white mb-2">Powerful Admin Control</h3>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed">
                        Manage your projects, experience, skills, certificates, and bring-your-own AI API keys seamlessly through a blazing fast admin dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA Banner --}}
    <section class="section-marketing px-4 sm:px-6 w-full">
        <div class="max-w-5xl mx-auto rounded-3xl p-8 sm:p-12 lg:p-16 border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-gray-900 to-gray-950 text-center relative overflow-hidden shadow-2xl">
            {{-- Decorative glow --}}
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <h2 class="font-h2 text-white relative">
                Ready to elevate your developer career?
            </h2>
            <p class="font-hero-sub text-gray-300 max-w-[72ch] mx-auto mt-4 relative leading-relaxed px-2">
                Join developers creating high-impact personal portfolios and landing roles with AI-tailored applications.
            </p>
            <div class="mt-8 flex justify-center relative">
                <a href="/admin/register" class="btn-primary bg-amber-500 hover:bg-amber-400 text-gray-950 shadow-xl shadow-amber-500/20 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer" data-tooltip="Get started and create your developer portfolio">
                    <span>Create Your Free Portfolio Now</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Footer (80px desktop / 48px mobile rhythm) --}}
    <footer class="border-t border-gray-800/80 py-12 sm:py-20 px-4 sm:px-6 bg-gray-950 font-caption text-gray-400 w-full">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
            <div class="font-caption text-gray-400">
                &copy; {{ date('Y') }} DevFolio AI Platform. Built for developers with Laravel, Livewire Volt & Filament.
            </div>
            <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-6 gap-y-2">
                <a href="#features" class="hover:text-white transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Jump to platform features">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-white transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="View pricing tiers and features">Pricing</a>
                <a href="{{ route('discover') }}" class="hover:text-white transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Browse public verified developer portfolios">Discover</a>
                <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:text-emerald-300 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Developer Workspace">Developer Login</a>
                <a href="{{ route('agency.login') }}" target="_blank" rel="noopener noreferrer" class="text-teal-400 hover:text-teal-300 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Agency Hub">Agency Hub</a>
                <a href="{{ route('super-admin.login') }}" target="_blank" rel="noopener noreferrer" class="text-amber-400 hover:text-amber-300 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Super Admin Portal">Super Admin</a>
            </div>
        </div>
    </footer>
</div>
@endif
