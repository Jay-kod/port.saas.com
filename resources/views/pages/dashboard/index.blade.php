<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Project;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\ResumeGeneration;

layout('layouts.dashboard');
title('User Dashboard');

state([
    'user' => fn () => auth()->user(),
    'profile' => fn () => auth()->user()?->profile,
    'account' => fn () => auth()->user()?->defaultTenant ?? auth()->user()?->accounts->first(),
    'totalProjects' => function () {
        $p = auth()->user()?->profile;
        return $p ? Project::where('profile_id', $p->id)->count() : 0;
    },
    'totalExperiences' => function () {
        $p = auth()->user()?->profile;
        return $p ? Experience::where('profile_id', $p->id)->count() : 0;
    },
    'totalSkills' => function () {
        $p = auth()->user()?->profile;
        return $p ? Skill::where('profile_id', $p->id)->count() : 0;
    },
    'totalResumes' => function () {
        $p = auth()->user()?->profile;
        return $p ? ResumeGeneration::where('profile_id', $p->id)->count() : 0;
    },
]);

?>

<div class="space-y-8">
    <!-- Header Banner (Clima Style) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    AI Portfolio Engine Active
                </span>
                @if($profile && $profile->is_published)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-300 border border-yellow-500/20">
                    Publicly Discoverable
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                    Draft Mode
                </span>
                @endif
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                Hello, {{ $user->name }}
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                @if($profile)
                    Your personal portfolio is configured as <span class="text-emerald-400 font-semibold">{{ $profile->headline ?: 'Professional Developer' }}</span>.
                @else
                    Get started by customizing your portfolio headline and experiences.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($profile && $profile->slug)
            <a href="{{ url('/' . $profile->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>View Portfolio</span>
            </a>
            @endif
            <a href="/admin/{{ $account?->id ?? 1 }}" class="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/20 text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Content Studio</span>
            </a>
        </div>
    </div>

    <!-- Metric Status Cards (Clima Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Active Plan -->
        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">
                <span>Active Plan</span>
                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold uppercase text-[10px]">
                    {{ $account?->plan_slug ? ucfirst($account->plan_slug) : 'Free Tier' }}
                </span>
            </div>
            <div class="text-2xl font-bold font-heading text-white mb-1">
                {{ $account?->plan_slug === 'agency' ? 'Agency Suite' : ($account?->plan_slug === 'pro' ? 'Pro Developer' : 'Free Standard') }}
            </div>
            <p class="text-xs text-slate-400">Unlimited themes & instant GitHub sync.</p>
        </div>

        <!-- Card 2: Total Projects -->
        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">
                <span>Showcased Work</span>
                <span class="px-2 py-0.5 rounded-md bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 font-bold text-[10px]">
                    {{ $totalProjects }} Projects
                </span>
            </div>
            <div class="text-2xl font-bold font-heading text-white mb-1">
                {{ $totalProjects }} Listed
            </div>
            <p class="text-xs text-slate-400">{{ $totalSkills }} verified technical skills tagged.</p>
        </div>

        <!-- Card 3: Experience & Career -->
        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">
                <span>Career History</span>
                <span class="px-2 py-0.5 rounded-md bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-bold text-[10px]">
                    Verified
                </span>
            </div>
            <div class="text-2xl font-bold font-heading text-white mb-1">
                {{ $totalExperiences }} Positions
            </div>
            <p class="text-xs text-slate-400">Timeline milestones & achievements.</p>
        </div>

        <!-- Card 4: AI Resume Pipeline -->
        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">
                <span>AI Generations</span>
                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-bold text-[10px]">
                    Ready
                </span>
            </div>
            <div class="text-2xl font-bold font-heading text-white mb-1">
                {{ $totalResumes }} Resumes
            </div>
            <p class="text-xs text-slate-400">Tailored PDFs exported with zero watermarks.</p>
        </div>
    </div>

    <!-- Quick Action Hubs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Panel: Portfolio Hub -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h3 class="text-xl font-bold font-heading text-white">Portfolio Command Center</h3>
                    <p class="text-xs text-slate-400">Manage your publicly displayed projects, experiences, and theme.</p>
                </div>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="/admin/{{ $account?->id ?? 1 }}/projects" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <div class="font-bold text-sm text-white group-hover:text-emerald-400 transition-colors">Manage Projects</div>
                        <div class="text-xs text-slate-400">Add GitHub repos and case studies</div>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="/admin/{{ $account?->id ?? 1 }}/resume-generations" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <div class="font-bold text-sm text-white group-hover:text-yellow-400 transition-colors">AI Resume Tailor</div>
                        <div class="text-xs text-slate-400">Generate targeted PDF resumes</div>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="/admin/{{ $account?->id ?? 1 }}/theme-selector" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <div class="font-bold text-sm text-white group-hover:text-emerald-400 transition-colors">Theme Customizer</div>
                        <div class="text-xs text-slate-400">Switch palettes & light/dark mode</div>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="/admin/{{ $account?->id ?? 1 }}/job-tracker" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-cyan-500/40 transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <div class="font-bold text-sm text-white group-hover:text-cyan-400 transition-colors">Job Application Kanban</div>
                        <div class="text-xs text-slate-400">Track interviews and offers</div>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Side Panel: Account & Status Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold font-heading text-white">Account Status</h3>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Primary Tenant</span>
                        <span class="font-semibold text-white font-mono">{{ $account?->name ?: 'Personal' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Custom Domain</span>
                        <span class="font-semibold text-yellow-400">
                            {{ $profile?->customDomain?->domain ?: 'Subdomain only' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Monthly AI Meter</span>
                        <span class="font-semibold text-emerald-400">Active</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-white/5 space-y-2">
                <a href="{{ route('onboarding') }}" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-yellow-500 text-slate-950 font-bold text-xs shadow-md hover:opacity-95 transition-opacity">
                    Re-run Setup Wizard
                </a>
            </div>
        </div>
    </div>
</div>
