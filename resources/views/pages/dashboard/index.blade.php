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

<div class="space-y-8" x-data="{ activeTab: @entangle('activeTab') }">

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
                <a href="{{ url('/' . $profileData->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Live Portfolio</span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            @endif
            <a href="/admin/{{ $accountData?->id ?? 1 }}/projects" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Projects Studio</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>
    </div>

    <div class="p-4 rounded-2xl bg-black border border-emerald-950 text-emerald-300 text-xs flex items-center justify-between font-mono">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span><strong>DEVELOPER PROTOCOL ACTIVE:</strong> You can manage portfolio assets, generate AI career documents, and monitor application progress from one console.</span>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-b border-emerald-950/70 pb-3 font-mono text-xs">
        <button @click="activeTab = 'telemetry'" :class="activeTab === 'telemetry' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span>1. Telemetry & Health</span>
        </button>

        <button @click="activeTab = 'studio'" :class="activeTab === 'studio' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
            <span>2. Portfolio Studio</span>
        </button>

        <button @click="activeTab = 'career'" :class="activeTab === 'career' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
            <span>3. AI Career Suite</span>
        </button>

        <button @click="activeTab = 'ops'" :class="activeTab === 'ops' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h.08a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h.08a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.08a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z" /></svg>
            <span>4. Workspace Settings</span>
        </button>

        <button @click="activeTab = 'resources'" :class="activeTab === 'resources' ? 'bg-emerald-600 text-slate-950 shadow-md shadow-emerald-900/50 font-black' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 font-bold'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6m-2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V9l-6-6z" /></svg>
            <span>5. Dev Resources</span>
        </button>
    </div>

    <div x-show="activeTab === 'telemetry'" class="space-y-6">
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
        </div>
    </div>

    <div x-show="activeTab === 'studio'" class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <h3 class="text-xl font-bold font-heading text-white">Portfolio Studio</h3>
            <p class="text-xs text-slate-400">Control your public profile, showcases, and technical credibility assets.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="/admin/{{ $accountData?->id ?? 1 }}/profiles" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Profile & Bio</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/projects" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Projects</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/experiences" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Experience</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/skills" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Skills Matrix</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/certificates" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Certificates</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/themes" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Theme Catalog</a>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'career'" class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
            <h3 class="text-xl font-bold font-heading text-white">AI Career Suite</h3>
            <p class="text-xs text-slate-400">Generate tailored assets and track opportunities with your integrated workflow.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="/admin/{{ $accountData?->id ?? 1 }}/resume-generations" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">AI Resume Tailor</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/cover-letter-generations" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">Cover Letters</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/job-tracker" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">Job Tracker Kanban</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/resume-import" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">Import Resume</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/oauth-settings" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">OAuth Integrations</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/github-settings" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-yellow-500/40 transition-all text-sm font-semibold text-white">GitHub Sync</a>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'ops'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <h3 class="text-xl font-bold font-heading text-white">Workspace Settings</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="/admin/{{ $accountData?->id ?? 1 }}/theme-selector" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-cyan-500/40 transition-all text-sm font-semibold text-white">Theme & Mode</a>
                    <a href="/admin/{{ $accountData?->id ?? 1 }}/domain-settings" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-cyan-500/40 transition-all text-sm font-semibold text-white">Custom Domains</a>
                    <a href="/admin/{{ $accountData?->id ?? 1 }}/billing-settings" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-cyan-500/40 transition-all text-sm font-semibold text-white">Billing & Usage</a>
                    <a href="{{ route('onboarding') }}" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-cyan-500/40 transition-all text-sm font-semibold text-white">Setup Wizard</a>
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

    <div x-show="activeTab === 'resources'" class="space-y-6">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <h3 class="text-xl font-bold font-heading text-white">Developer Resources</h3>
            <p class="text-xs text-slate-400">Operational links and controls used frequently during portfolio and job campaign cycles.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="/admin/{{ $accountData?->id ?? 1 }}/privacy-and-data" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Privacy & Data Controls</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/portfolio-reports" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Abuse Reports</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/templates" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">Resume Templates</a>
                <a href="/admin/{{ $accountData?->id ?? 1 }}/ai-settings" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-all text-sm font-semibold text-white">AI Provider Settings</a>
            </div>
        </div>
    </div>
</div>
