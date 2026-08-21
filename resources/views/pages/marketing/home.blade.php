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
    {{-- Top Navigation --}}
    <header class="border-b border-gray-800/80 bg-gray-950/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-18 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center font-black text-xl shadow-lg shadow-amber-500/5">
                    ⚡
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            </div>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-300">
                <a href="{{ route('home') }}" class="text-white hover:text-amber-400 transition font-semibold">Home</a>
                <a href="#features" class="hover:text-amber-400 transition">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-amber-400 transition">Pricing</a>
                <a href="{{ route('discover') }}" class="hover:text-amber-400 transition">Discover</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="/admin/login" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                    <span>Get Started</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </header>

    {{-- Hero Section --}}
    <section class="relative pt-20 pb-28 px-6 overflow-hidden">
        <div class="absolute inset-0 -z-10 flex items-center justify-center opacity-30 pointer-events-none">
            <div class="w-[600px] h-[600px] rounded-full bg-amber-500/10 blur-3xl"></div>
            <div class="w-[400px] h-[400px] rounded-full bg-emerald-500/10 blur-3xl -translate-x-48"></div>
        </div>

        <div class="max-w-5xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-8 shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                The Next-Gen Developer Portfolio & AI Resume Suite
            </div>

            <h1 class="text-5xl sm:text-7xl font-black tracking-tight text-white leading-tight sm:leading-none">
                Turn your code into an <br class="hidden sm:inline"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-emerald-400 to-cyan-400">
                    AI-powered portfolio
                </span>
            </h1>

            <p class="mt-8 text-lg sm:text-xl text-gray-400 max-w-3xl mx-auto leading-relaxed">
                Effortlessly showcase your projects with automated GitHub sync, create tailored ATS-optimized resumes for any job description in seconds, and share your personal developer website.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/admin/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-base font-bold transition shadow-xl shadow-amber-500/25">
                    <span>Create Your Portfolio Free</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('pricing') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-2xl border border-gray-700 bg-gray-900/60 hover:bg-gray-800 text-white text-base font-semibold transition">
                    View Pricing & Plans
                </a>
            </div>

            {{-- Metric Highlights --}}
            <div class="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-6 pt-10 border-t border-gray-800/80 max-w-4xl mx-auto text-left">
                <div>
                    <p class="text-2xl sm:text-3xl font-extrabold text-white">100%</p>
                    <p class="text-xs text-gray-400 mt-1">ATS-Optimized Resumes</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-extrabold text-amber-400">&lt; 30s</p>
                    <p class="text-xs text-gray-400 mt-1">AI Tailoring Time</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-extrabold text-emerald-400">GitHub</p>
                    <p class="text-xs text-gray-400 mt-1">Auto Repo Sync</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-extrabold text-cyan-400">Multi-Tenant</p>
                    <p class="text-xs text-gray-400 mt-1">Custom URLs & Domains</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-24 px-6 border-t border-gray-800/80 bg-gray-950/40">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-400 uppercase tracking-widest">Engineered for Developers</h2>
                <p class="mt-3 text-3xl sm:text-4xl font-extrabold text-white">Everything you need to land your next high-impact role</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        🤖
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">AI Resume Tailoring</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Paste any target job description. Our AI analyzes the role and crafts a precisely tailored resume highlighting your matching skills and accomplishments.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-emerald-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        🐙
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Automated GitHub Sync</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Connect your GitHub profile once. Your repositories, tech stack tags, stars, and descriptions stay effortlessly updated on your public site.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-cyan-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        📄
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">ATS-Ready PDF Export</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Generate clean, beautifully typeset PDF resumes with a single click. Formats are engineered to easily parse through recruiter applicant tracking systems.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-pink-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-pink-500/10 border border-pink-500/20 text-pink-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        🎨
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Curated Developer Themes</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Choose from custom-crafted dark mode palettes including Cyber Matrix, Bioluminescent, and Toxic Cyberpunk for an unforgettable first impression.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-purple-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        🌐
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Dedicated URL & Custom Domains</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Get your own clean web address like <code>devfolio.ai/your-name</code>, or connect your personal domain with automatic SSL and fast global CDN edge delivery.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-8 rounded-3xl border border-gray-800/80 bg-gray-900/60 backdrop-blur-sm hover:border-amber-500/30 transition group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition">
                        ⚡
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Powerful Admin Control</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Manage your projects, experience, skills, certificates, and bring-your-own AI API keys seamlessly through a blazing fast admin dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA Banner --}}
    <section class="py-20 px-6">
        <div class="max-w-5xl mx-auto rounded-3xl p-10 sm:p-16 border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-gray-900 to-gray-950 text-center relative overflow-hidden shadow-2xl">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">
                Ready to elevate your developer career?
            </h2>
            <p class="mt-4 text-gray-400 max-w-xl mx-auto text-sm sm:text-base">
                Join developers creating high-impact personal portfolios and landing roles with AI-tailored applications.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="/admin/register" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-base transition shadow-xl shadow-amber-500/20">
                    <span>Create Your Free Portfolio Now</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
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
                <a href="#features" class="hover:text-gray-400">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-400">Pricing</a>
                <a href="/admin/login" class="hover:text-gray-400">Admin Login</a>
            </div>
        </div>
    </footer>
</div>
@endif
