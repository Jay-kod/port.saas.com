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
<div class="min-h-screen text-gray-100 flex flex-col justify-between" style="background-color: var(--color-background, #0a0e14);">
    <x-marketing-header />

    {{-- Hero Section --}}
    <section class="relative pt-12 sm:pt-20 lg:pt-28 pb-16 sm:pb-24 lg:pb-32 px-4 sm:px-6 overflow-hidden">
        {{-- Background glow effects --}}
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] sm:w-[600px] lg:w-[800px] h-[400px] sm:h-[600px] lg:h-[800px] rounded-full bg-amber-500/8 blur-3xl animate-pulse" style="animation-duration: 6s;"></div>
            <div class="absolute top-1/3 left-1/4 w-[250px] sm:w-[400px] h-[250px] sm:h-[400px] rounded-full bg-emerald-500/6 blur-3xl animate-pulse" style="animation-duration: 8s;"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[200px] sm:w-[300px] h-[200px] sm:h-[300px] rounded-full bg-cyan-500/5 blur-3xl animate-pulse" style="animation-duration: 10s;"></div>
        </div>

        <div class="max-w-5xl mx-auto text-center">

            {{-- Heading --}}
            <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                Turn your code into an
                <br class="hidden sm:inline"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-emerald-400 to-cyan-400">
                    AI-powered portfolio
                </span>
            </h1>

            {{-- Subheading --}}
            <p class="mt-5 sm:mt-8 text-sm sm:text-lg md:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed px-2">
                Effortlessly showcase your projects with automated GitHub sync, create tailored ATS-optimized resumes for any job description in seconds, and share your personal developer website.
            </p>

            {{-- CTA Buttons --}}
            <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                <a href="/admin/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm sm:text-base font-bold transition-all duration-200 shadow-xl shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0">
                    <span>Create Your Portfolio Free</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('pricing') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-8 py-3.5 sm:py-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 hover:bg-gray-100 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 text-gray-900 dark:text-white text-sm sm:text-base font-semibold transition-all duration-200">
                    View Pricing & Plans
                </a>
            </div>

            {{-- Metric Highlights --}}
            <div class="mt-12 sm:mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 pt-8 sm:pt-10 border-t border-gray-200 dark:border-gray-800/80 max-w-4xl mx-auto">
                <div class="text-center sm:text-left p-3 sm:p-0">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 dark:text-white">100%</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">ATS-Optimized Resumes</p>
                </div>
                <div class="text-center sm:text-left p-3 sm:p-0">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-amber-400">&lt; 30s</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">AI Tailoring Time</p>
                </div>
                <div class="text-center sm:text-left p-3 sm:p-0">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-emerald-400">GitHub</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">Auto Repo Sync</p>
                </div>
                <div class="text-center sm:text-left p-3 sm:p-0">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-cyan-400">Multi-Tenant</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">Custom URLs & Domains</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-16 sm:py-24 px-4 sm:px-6 border-t border-gray-200 dark:border-gray-800/80 bg-gray-50/40 dark:bg-gray-950/40">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-16">
                <h2 class="text-xs font-bold text-amber-400 uppercase tracking-widest">Engineered for Developers</h2>
                <p class="mt-3 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-white leading-tight">Everything you need to land your next high-impact role</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                {{-- Feature 1 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        🤖
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">AI Resume Tailoring</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Paste any target job description. Our AI analyzes the role and crafts a precisely tailored resume highlighting your matching skills and accomplishments.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        🐙
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Automated GitHub Sync</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Connect your GitHub profile once. Your repositories, tech stack tags, stars, and descriptions stay effortlessly updated on your public site.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-cyan-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        📄
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">ATS-Ready PDF Export</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Generate clean, beautifully typeset PDF resumes with a single click. Formats are engineered to easily parse through recruiter applicant tracking systems.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-pink-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-pink-500/10 border border-pink-500/20 text-pink-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        🎨
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Curated Developer Themes</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Choose from custom-crafted dark mode palettes including Cyber Matrix, Bioluminescent, and Toxic Cyberpunk for an unforgettable first impression.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-purple-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        🌐
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Dedicated URL & Custom Domains</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Get your own clean web address like <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-[11px] text-amber-500 dark:text-amber-300">devfolio.ai/your-name</code>, or connect your personal domain with automatic SSL and fast global CDN edge delivery.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl sm:text-2xl mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        ⚡
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Powerful Admin Control</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Manage your projects, experience, skills, certificates, and bring-your-own AI API keys seamlessly through a blazing fast admin dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA Banner --}}
    <section class="py-12 sm:py-20 px-4 sm:px-6">
        <div class="max-w-5xl mx-auto rounded-2xl sm:rounded-3xl p-8 sm:p-10 lg:p-16 border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-gray-900 to-gray-950 text-center relative overflow-hidden shadow-2xl">
            {{-- Decorative glow --}}
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white relative">
                Ready to elevate your developer career?
            </h2>
            <p class="mt-3 sm:mt-4 text-gray-400 max-w-xl mx-auto text-xs sm:text-sm lg:text-base relative">
                Join developers creating high-impact personal portfolios and landing roles with AI-tailored applications.
            </p>
            <div class="mt-6 sm:mt-8 flex justify-center relative">
                <a href="/admin/register" class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-sm sm:text-base transition-all duration-200 shadow-xl shadow-amber-500/20 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0">
                    <span>Create Your Free Portfolio Now</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-gray-800/80 py-8 sm:py-10 px-4 sm:px-6 bg-gray-950 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                &copy; {{ date('Y') }} DevFolio AI Platform. Built for developers with Laravel, Livewire Volt & Filament.
            </div>
            <div class="flex items-center gap-4 sm:gap-6">
                <a href="#features" class="hover:text-gray-400 transition">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-400 transition">Pricing</a>
                <a href="/admin/login" class="hover:text-gray-400 transition">Admin Login</a>
            </div>
        </div>
    </footer>
</div>
@endif
