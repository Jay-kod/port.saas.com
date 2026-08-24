<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Certificate;
use App\Models\CoverLetterGeneration;
use App\Models\Experience;
use App\Models\JobApplication;
use App\Models\Project;
use App\Models\ResumeGeneration;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('User Dashboard');

state([
    'activeTab' => 'telemetry',
    'user' => fn () => Auth::user(),
    'profile' => fn () => Auth::user()?->profile,
    'account' => fn () => Auth::user()?->defaultTenant ?? Auth::user()?->accounts->first(),
    'totalProjects' => function () {
        $p = Auth::user()?->profile;
        return $p ? Project::where('profile_id', $p->id)->count() : 0;
    },
    'totalExperiences' => function () {
        $p = Auth::user()?->profile;
        return $p ? Experience::where('profile_id', $p->id)->count() : 0;
    },
    'totalSkills' => function () {
        $p = Auth::user()?->profile;
        return $p ? Skill::where('profile_id', $p->id)->count() : 0;
    },
    'totalResumes' => function () {
        $p = Auth::user()?->profile;
        return $p ? ResumeGeneration::where('profile_id', $p->id)->count() : 0;
    },
    'totalCertificates' => function () {
        $p = Auth::user()?->profile;
        return $p ? Certificate::where('profile_id', $p->id)->count() : 0;
    },
    'totalCoverLetters' => function () {
        $p = Auth::user()?->profile;
        return $p ? CoverLetterGeneration::where('profile_id', $p->id)->count() : 0;
    },
    'totalApplications' => function () {
        $p = Auth::user()?->profile;
        return $p ? JobApplication::where('profile_id', $p->id)->count() : 0;
    },
]);

?>

@php
    $profileData = $this->profile;
    $accountData = $this->account;
@endphp

<div class="space-y-8">

    {{-- Top Header Banner --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full text-xs font-mono font-black bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50">
                    DEVELOPER MASTER WORKSPACE
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    TIER 1 / BUILDER ACCESS
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                Portfolio Engineering Console
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Developer operations shell for portfolio growth, AI job assets, and application tracking.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($profileData && $profileData->slug)
                <a href="{{ url('/' . $profileData->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2" data-tooltip="View your public live portfolio website" data-tooltip-pos="bottom">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Live Portfolio</span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            @endif
            <a href="{{ route('developer.projects') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2" data-tooltip="Manage showcased apps, GitHub repos, and live deployments" data-tooltip-pos="bottom">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Projects Studio</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>
    </div>

    {{-- System Protocol Callout --}}
    <div class="p-4 rounded-2xl bg-black border border-emerald-950 text-emerald-300 text-xs flex items-center justify-between font-mono">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span><strong>DEVELOPER PROTOCOL ACTIVE:</strong> You can manage portfolio assets, generate AI career documents, and monitor application progress from one console.</span>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-emerald-950/70 pb-3 font-mono text-xs">
        <button type="button" 
                wire:click="$set('activeTab', 'telemetry')" 
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'telemetry' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold' }}"
                data-tooltip="View system telemetry, health score, and quick launch triggers"
                data-tooltip-pos="bottom">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span>1. Telemetry & Health</span>
        </button>

        <button type="button" 
                wire:click="$set('activeTab', 'studio')" 
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'studio' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold' }}"
                data-tooltip="Manage profile bio, projects, experience, skills, and certificates"
                data-tooltip-pos="bottom">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
            <span>2. Portfolio Studio</span>
        </button>

        <button type="button" 
                wire:click="$set('activeTab', 'career')" 
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'career' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold' }}"
                data-tooltip="AI resume tailoring, cover letter generator, and Kanban job tracker"
                data-tooltip-pos="bottom">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
            <span>3. AI Career Suite</span>
        </button>

        <button type="button" 
                wire:click="$set('activeTab', 'ops')" 
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'ops' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold' }}"
                data-tooltip="BYOK AI settings, themes, custom domains, and billing"
                data-tooltip-pos="bottom">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h.08a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h.08a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.08a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z" /></svg>
            <span>4. Workspace Settings</span>
        </button>

        <button type="button" 
                wire:click="$set('activeTab', 'resources')" 
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'resources' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold' }}"
                data-tooltip="Developer API tokens, exports, and GDPR privacy settings"
                data-tooltip-pos="bottom">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6m-2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V9l-6-6z" /></svg>
            <span>5. Dev Resources</span>
        </button>
    </div>

    {{-- TAB 1: Telemetry & Health --}}
    @if($activeTab === 'telemetry')
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card glass-card-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Active Plan</div>
                <div class="text-3xl font-extrabold font-heading text-white">{{ $accountData?->plan_slug ? strtoupper($accountData->plan_slug) : 'FREE' }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Billing tier</div>
            </div>
            <div class="glass-card glass-card-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Projects</div>
                <div class="text-3xl font-extrabold font-heading text-emerald-400">{{ $totalProjects }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Portfolio showcases</div>
            </div>
            <div class="glass-card glass-card-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Skills</div>
                <div class="text-3xl font-extrabold font-heading text-cyan-300">{{ $totalSkills }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Tagged capabilities</div>
            </div>
            <div class="glass-card glass-card-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">AI Assets</div>
                <div class="text-3xl font-extrabold font-heading text-yellow-300">{{ $totalResumes + $totalCoverLetters }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Resumes + cover letters</div>
            </div>
        </div>

        <div class="p-6 rounded-3xl bg-black border border-emerald-950/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div>
                        <div class="text-sm font-bold text-white flex items-center gap-2 font-mono">
                            <span>Developer Workflow State:</span>
                            <span class="text-emerald-400 font-bold">ONLINE & SYNCED</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Portfolio publishing &bull; AI generation &bull; job pipeline tracking
                        </div>
                    </div>
                </div>

                <div class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-white/5">
                    Server Clock: <span class="text-emerald-300 font-bold">{{ now()->toDateTimeString() }} UTC</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-emerald-950/50 text-xs font-mono">
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">Profile Visibility</div>
                    <div class="{{ $profileData?->is_published ? 'text-emerald-400' : 'text-yellow-300' }} font-bold">{{ $profileData?->is_published ? 'Published' : 'Draft' }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">Certificates</div>
                    <div class="text-white font-bold">{{ $totalCertificates }} credential records</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">Applications</div>
                    <div class="text-emerald-400 font-bold">{{ $totalApplications }} tracked roles</div>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('developer.analytics') }}" class="w-full p-4 rounded-2xl bg-gradient-to-r from-emerald-950/60 to-slate-950 border border-emerald-500/30 hover:border-emerald-500/60 flex items-center justify-between transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Developer Operations & Analytics Center</div>
                            <div class="text-xs text-slate-400">View real-time telemetry, portfolio health score, skills radar & job conversion funnel</div>
                        </div>
                    </div>
                    <span class="text-emerald-400 font-bold text-xs flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Open Analytics</span>
                        <span>&rarr;</span>
                    </span>
                </a>
            </div>
        </div>
    </div>

    {{-- TAB 2: Portfolio Studio --}}
    @elseif($activeTab === 'studio')
    <div class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div>
                <h3 class="text-xl font-bold font-heading text-white">Portfolio Studio</h3>
                <p class="text-xs text-slate-400 mt-1">Control your public profile, showcases, and technical credibility assets.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('developer.profile') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Profile & Bio</div>
                        <div class="text-xs text-slate-400 mt-0.5">Headline, summary, avatar & public bio</div>
                    </div>
                </a>

                <a href="{{ route('developer.projects') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Projects Showcase</div>
                        <div class="text-xs text-slate-400 mt-0.5">Featured showcases, screenshots & stacks</div>
                    </div>
                </a>

                <a href="{{ route('developer.experiences') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Experience History</div>
                        <div class="text-xs text-slate-400 mt-0.5">Career timeline, roles & achievements</div>
                    </div>
                </a>

                <a href="{{ route('developer.skills') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Skills Matrix</div>
                        <div class="text-xs text-slate-400 mt-0.5">Categorized tech competencies & mastery</div>
                    </div>
                </a>

                <a href="{{ route('developer.certificates') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Certificates</div>
                        <div class="text-xs text-slate-400 mt-0.5">Accreditations, licenses & verifications</div>
                    </div>
                </a>

                <a href="{{ route('developer.themes') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Theme Catalog</div>
                        <div class="text-xs text-slate-400 mt-0.5">7 handcrafted dark/light themes & palettes</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- TAB 3: AI Career Suite --}}
    @elseif($activeTab === 'career')
    <div class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div>
                <h3 class="text-xl font-bold font-heading text-white">AI Career Suite</h3>
                <p class="text-xs text-slate-400 mt-1">Generate tailored assets and track opportunities with your integrated workflow.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('developer.resumes') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">AI Resume Tailor</div>
                        <div class="text-xs text-slate-400 mt-0.5">Tailor resumes to job descriptions & export PDF</div>
                    </div>
                </a>

                <a href="{{ route('developer.cover-letters') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">Cover Letter AI</div>
                        <div class="text-xs text-slate-400 mt-0.5">Company-targeted compelling cover letters</div>
                    </div>
                </a>

                <a href="{{ route('developer.job-tracker') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">Job Tracker Kanban</div>
                        <div class="text-xs text-slate-400 mt-0.5">5-stage pipeline with interview tracking</div>
                    </div>
                </a>

                <a href="{{ route('developer.resume-import') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">Import Resume PDF</div>
                        <div class="text-xs text-slate-400 mt-0.5">Extract bio, projects, and skills automatically</div>
                    </div>
                </a>

                <a href="{{ route('developer.github-sync') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">GitHub Sync</div>
                        <div class="text-xs text-slate-400 mt-0.5">Automated repository synchronization</div>
                    </div>
                </a>

                <a href="{{ route('developer.ai-settings') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-yellow-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-yellow-400 transition-colors">BYOK AI Provider</div>
                        <div class="text-xs text-slate-400 mt-0.5">Custom OpenAI & Anthropic API keys</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- TAB 4: Workspace Settings --}}
    @elseif($activeTab === 'ops')
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8 space-y-6">
                <div>
                    <h3 class="text-xl font-bold font-heading text-white">Workspace Settings</h3>
                    <p class="text-xs text-slate-400 mt-1">Configure your domain, themes, plan tier, and onboarding.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('developer.themes') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-cyan-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                            </div>
                            <span class="text-slate-600 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">Theme & Mode</div>
                            <div class="text-xs text-slate-400 mt-0.5">Light / dark default & active palette</div>
                        </div>
                    </a>

                    <a href="{{ route('developer.domains') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-cyan-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                            </div>
                            <span class="text-slate-600 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">Custom Domains</div>
                            <div class="text-xs text-slate-400 mt-0.5">Attach branded apex domains or CNAMEs</div>
                        </div>
                    </a>

                    <a href="{{ route('developer.billing') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-cyan-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            </div>
                            <span class="text-slate-600 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">Billing & Usage</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tier quotas, AI usage meter & Stripe portal</div>
                        </div>
                    </a>

                    <a href="{{ route('onboarding') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-cyan-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <span class="text-slate-600 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">Setup Wizard</div>
                            <div class="text-xs text-slate-400 mt-0.5">Re-run 4-step onboarding checklist</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <h3 class="text-lg font-bold font-heading text-white">Status</h3>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Tenant</span>
                        <span class="font-semibold text-white font-mono">{{ $accountData?->name ?: 'Personal' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Visibility</span>
                        <span class="font-semibold {{ $profileData?->is_published ? 'text-emerald-400' : 'text-yellow-300' }}">{{ $profileData?->is_published ? 'Published' : 'Draft' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Skills Tagged</span>
                        <span class="font-semibold text-cyan-300">{{ $totalSkills }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">AI Resumes</span>
                        <span class="font-semibold text-emerald-300">{{ $totalResumes }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Cover Letters</span>
                        <span class="font-semibold text-emerald-300">{{ $totalCoverLetters }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 5: Developer Resources --}}
    @elseif($activeTab === 'resources')
    <div class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div>
                <h3 class="text-xl font-bold font-heading text-white">Developer Resources</h3>
                <p class="text-xs text-slate-400 mt-1">Operational links and controls used frequently during portfolio and job campaign cycles.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('developer.privacy') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Privacy & Data</div>
                        <div class="text-xs text-slate-400 mt-0.5">GDPR export & data erasure</div>
                    </div>
                </a>

                <a href="{{ route('developer.templates') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Resume Templates</div>
                        <div class="text-xs text-slate-400 mt-0.5">PDF themes & styles</div>
                    </div>
                </a>

                <a href="{{ route('developer.ai-settings') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">AI Provider Settings</div>
                        <div class="text-xs text-slate-400 mt-0.5">OpenAI/Anthropic BYOK keys</div>
                    </div>
                </a>

                <a href="{{ route('developer.github-sync') }}" class="group p-5 rounded-2xl bg-slate-900/60 border border-emerald-500/50 hover:bg-slate-900/90 transition-all flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <span class="text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">GitHub Sync</div>
                        <div class="text-xs text-slate-400 mt-0.5">Automated repo sync</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
