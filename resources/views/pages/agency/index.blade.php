<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Profile;
use App\Models\Account;
use App\Models\Theme;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Agency Command Center');

state([
    'showCreateModal' => false,
    'newClientName' => '',
    'newClientHeadline' => '',
    'newClientSlug' => '',
    'newClientThemeId' => 1,
    'successMessage' => '',
    'errorMessage' => '',
]);

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$profiles = computed(function () {
    return $this->account ? $this->account->profiles()->latest()->get() : collect();
});

$members = computed(function () {
    return $this->account ? $this->account->members()->get() : collect();
});

$themes = computed(function () {
    return Theme::all();
});

$activeProfile = computed(function () {
    if (session('active_profile_id')) {
        return Profile::find(session('active_profile_id'));
    }
    return $this->profiles->first() ?? Auth::user()?->profile;
});

$createClient = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->account) {
        $this->errorMessage = 'No active agency account found.';
        return;
    }

    if (! $this->account->canCreateProfile()) {
        $this->errorMessage = 'Profile limit reached for current plan tier. Please upgrade your Agency plan to provision more clients.';
        return;
    }

    $validated = $this->validate([
        'newClientName' => ['required', 'string', 'max:255'],
        'newClientHeadline' => ['nullable', 'string', 'max:255'],
        'newClientSlug' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:profiles,slug'],
    ]);

    $slug = !empty($this->newClientSlug) 
        ? Str::slug($this->newClientSlug) 
        : Str::slug($this->newClientName) . '-' . Str::random(4);

    $baseSlug = $slug;
    $counter = 1;
    while (Profile::where('slug', $slug)->exists()) {
        $slug = "{$baseSlug}-{$counter}";
        $counter++;
    }

    $validThemeId = Theme::find($this->newClientThemeId)?->id;

    $newProfile = Profile::create([
        'account_id' => $this->account->id,
        'user_id' => Auth::id(),
        'full_name' => $this->newClientName,
        'headline' => $this->newClientHeadline ?: 'Software Engineer',
        'slug' => $slug,
        'theme_id' => $validThemeId,
        'theme_mode_default' => 'dark',
        'is_published' => false,
    ]);

    // Set as active profile in session
    session(['active_profile_id' => $newProfile->id]);

    $this->reset(['newClientName', 'newClientHeadline', 'newClientSlug']);
    $this->showCreateModal = false;
    $this->successMessage = "Client portfolio for '{$newProfile->full_name}' provisioned successfully!";
};

$switchActiveProfile = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->find($profileId);
    if ($profile) {
        session(['active_profile_id' => $profile->id]);
        $this->successMessage = "Active client switched to: {$profile->full_name}";
    }
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header with Agency Badges & Quick Action Triggers --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                    AGENCY STUDIO
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                    MULTI-TENANT HUB
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Agency Client Command Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage multi-tenant developer portfolios, team seats, white-label branding, and client growth pipelines.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('agency.team') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-teal-400 hover:border-teal-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                <span>Team & Seats</span>
            </a>
            <a href="{{ route('agency.branding') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-cyan-400 hover:border-cyan-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                <span>White-Label</span>
            </a>
            <button type="button" wire:click="$set('showCreateModal', true)" class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 hover:opacity-95 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-teal-950/40 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>+ Provision Client</span>
            </button>
        </div>
    </div>

    {{-- Feedback Notifications --}}
    @if($successMessage)
        <div class="p-4 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')" class="text-teal-400 hover:text-white">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    @endif

    {{-- PRIMARY KPI RIBBON --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Managed Client Portfolios --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">MANAGED CLIENTS</span>
                <div class="w-8 h-8 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->profiles->count() }}</span>
                <span class="text-xs text-slate-500 font-mono">/ {{ $this->account?->plan_slug === 'agency' ? '∞' : '1' }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400">
                <span>Published: <strong class="text-teal-400">{{ $this->profiles->where('is_published', true)->count() }}</strong></span>
                <span>Drafts: <strong class="text-slate-300">{{ $this->profiles->where('is_published', false)->count() }}</strong></span>
            </div>
        </div>

        {{-- Team Seat Capacity --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">TEAM CAPACITY</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">{{ $this->members->count() + 1 }}</span>
                <span class="text-xs text-slate-500 font-mono">/ 10 Seats</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] text-slate-400">
                <span class="text-cyan-400 font-bold">1 Owner</span>
                <span>&bull;</span>
                <span>{{ $this->members->count() }} Collaborators</span>
            </div>
        </div>

        {{-- White-Label Branding State --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">WHITE-LABEL</span>
                <div class="w-8 h-8 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold font-heading text-white tracking-tight">
                    {{ $this->account?->hide_platform_branding ? 'ACTIVE' : 'STANDARD' }}
                </span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 truncate">
                {{ $this->account?->custom_brand_name ?: 'Custom branding ready' }}
            </div>
        </div>

        {{-- Agency Tier Quota --}}
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-semibold text-slate-400">AI USAGE METER</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold font-heading text-white tracking-tight">
                    {{ $this->account?->ai_generations_used_current_period ?? 0 }}
                </span>
                <span class="text-xs text-slate-500 font-mono">/ 50 mo</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex justify-between">
                <span>Tier: <strong class="text-white">{{ strtoupper($this->account?->plan_slug ?: 'agency') }}</strong></span>
                <a href="{{ route('agency.billing') }}" class="text-teal-400 hover:underline">Manage &rarr;</a>
            </div>
        </div>
    </div>

    {{-- ACTIVE CLIENT CONTEXT SWITCHER BANNER --}}
    @if($this->activeProfile)
        <div class="glass-card rounded-3xl p-6 border border-teal-500/30 bg-slate-900/60">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-400 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($this->activeProfile->full_name ?: 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-bold uppercase text-teal-400 bg-teal-500/10 px-2 py-0.5 rounded border border-teal-500/20">
                                ACTIVE CLIENT CONTEXT
                            </span>
                            <span class="text-xs font-mono text-slate-400">/{{ $this->activeProfile->slug }}</span>
                        </div>
                        <h3 class="text-lg font-bold font-heading text-white mt-1">
                            {{ $this->activeProfile->full_name ?: 'Unnamed Profile' }}
                        </h3>
                        <p class="text-xs text-slate-400">{{ $this->activeProfile->headline ?: 'Professional Profile' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('developer.profile') }}" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-teal-500/40 text-slate-300 text-xs font-semibold transition-all">
                        Edit Content Studio
                    </a>
                    <a href="{{ route('developer.resumes') }}" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-teal-500/40 text-slate-300 text-xs font-semibold transition-all">
                        AI Resumes
                    </a>
                    @if($this->activeProfile->is_published)
                        <a href="{{ url('/' . $this->activeProfile->slug) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-slate-950 font-bold text-xs transition-all flex items-center gap-1.5">
                            <span>Live Site</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- CLIENT PROFILES ROSTER TABLE --}}
    <div class="glass-card rounded-3xl overflow-hidden border border-white/10">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Managed Client Portfolios</h3>
                <p class="text-xs text-slate-400">All developer profiles registered under this agency tenant.</p>
            </div>
            <a href="{{ route('agency.clients') }}" class="text-xs text-teal-400 hover:text-teal-300 font-semibold flex items-center gap-1">
                <span>View Full Client Manager</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-900/80 border-b border-white/5 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Client / Profile</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Slug & URL</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Context</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($this->profiles as $profileItem)
                        @php
                            $isActive = ($this->activeProfile?->id === $profileItem->id);
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition-colors {{ $isActive ? 'bg-teal-950/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $isActive ? 'bg-teal-500/20 border-teal-500 text-teal-300' : 'bg-slate-800 border-slate-700 text-slate-300' }} border flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($profileItem->full_name ?: 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-2">
                                            <span>{{ $profileItem->full_name ?: 'Unnamed Profile' }}</span>
                                            @if($isActive)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-teal-500/20 text-teal-300 border border-teal-500/30">ACTIVE</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400">{{ $profileItem->headline ?: 'Software Engineer' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-300">
                                /{{ $profileItem->slug }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($profileItem->is_published)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> Live
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isActive)
                                    <span class="text-xs text-teal-400 font-semibold font-mono">Current Workspace</span>
                                @else
                                    <button type="button" wire:click="switchActiveProfile({{ $profileItem->id }})" class="text-xs text-slate-400 hover:text-teal-300 font-semibold transition-colors cursor-pointer">
                                        Switch Active &rarr;
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="{{ url('/' . $profileItem->slug) }}" target="_blank" class="text-slate-400 hover:text-teal-400 transition-colors">Preview</a>
                                <a href="{{ route('agency.clients') }}" class="text-teal-400 hover:text-teal-300 font-semibold transition-colors">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">
                                No client developer portfolios provisioned yet. Click "+ Provision Client" above to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- QUICK CLIENT PROVISIONING MODAL --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-teal-500/30 bg-slate-950 shadow-2xl relative space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-white">Provision New Client Portfolio</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Add a new developer profile under your agency account.</p>
                    </div>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="createClient" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Client Full Name *</label>
                        <input type="text" wire:model="newClientName" placeholder="e.g. Sarah Jenkins" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                        @error('newClientName') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Professional Headline</label>
                        <input type="text" wire:model="newClientHeadline" placeholder="e.g. Senior Cloud Architect & DevOps Lead" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Custom URL Slug (Optional)</label>
                        <div class="flex items-center rounded-xl bg-slate-900 border border-slate-800 focus-within:border-teal-500 overflow-hidden">
                            <span class="px-3 text-slate-500 font-mono text-xs">saas.com/</span>
                            <input type="text" wire:model="newClientSlug" placeholder="sarah-jenkins" class="w-full py-2.5 pr-4 bg-transparent text-white text-xs focus:outline-none font-mono" />
                        </div>
                        @error('newClientSlug') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Starter Theme</label>
                        <select wire:model="newClientThemeId" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none">
                            @foreach($this->themes as $themeItem)
                                <option value="{{ $themeItem->id }}">{{ $themeItem->name }} ({{ $themeItem->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-teal-950/50">
                            Provision Client Portfolio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
