<?php

use function Livewire\Volt\{state, layout, title, usesPagination, computed};
use App\Models\Profile;
use App\Models\Account;

layout('layouts.super-admin');
title('Global Portfolios Directory');

usesPagination();

state([
    'search' => '',
    'statusFilter' => 'all', // all, published, draft
    'discoverFilter' => 'all', // all, discoverable, hidden
    'successMessage' => '',
    'errorMessage' => '',
]);

$portfolios = computed(function () {
    return Profile::query()
        ->with(['account', 'user', 'domains', 'theme'])
        ->when($this->search, function ($query) {
            $query->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('headline', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%')
                  ->orWhereHas('account', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        })
        ->when($this->statusFilter === 'published', function ($query) {
            $query->where('is_published', true);
        })
        ->when($this->statusFilter === 'draft', function ($query) {
            $query->where('is_published', false);
        })
        ->when($this->discoverFilter === 'discoverable', function ($query) {
            $query->where('is_discoverable', true);
        })
        ->when($this->discoverFilter === 'hidden', function ($query) {
            $query->where('is_discoverable', false);
        })
        ->latest()
        ->paginate(12);
});

$togglePublish = function ($profileId) {
    $profile = Profile::findOrFail($profileId);
    $profile->is_published = ! $profile->is_published;
    $profile->save();

    $status = $profile->is_published ? 'Published (Live)' : 'Draft (Unpublished)';
    $this->successMessage = "Portfolio '{$profile->full_name}' (/{{ $profile->slug }}) status updated to {$status}.";
};

$toggleDiscoverable = function ($profileId) {
    $profile = Profile::findOrFail($profileId);
    $profile->is_discoverable = ! $profile->is_discoverable;
    $profile->save();

    $status = $profile->is_discoverable ? 'Visible in /discover Directory' : 'Hidden from /discover Directory';
    $this->successMessage = "Portfolio '{$profile->full_name}' is now {$status}.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    CONTENT GOVERNANCE
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    ALL PORTFOLIOS
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Global Portfolios Directory
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Global registry of all developer portfolios across every tenant workspace, live URLs, and SEO directory discoverability.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all">
                &larr; Telemetry Hub
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($successMessage)
    <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-200 text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ $successMessage }}</span>
        </div>
        <button wire:click="$set('successMessage', '')" class="text-amber-400 hover:text-white underline text-xs cursor-pointer">Dismiss</button>
    </div>
    @endif

    <!-- Toolbar: Search & Filters -->
    <div class="glass-card-dark rounded-3xl p-5 border border-amber-950/70 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search by name, headline, slug, or workspace..." 
                       class="w-full pl-10 pr-4 py-2 rounded-xl bg-black border border-amber-950 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 font-mono">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                <!-- Status Filter -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-amber-950/60">
                    <button type="button" 
                            wire:click="$set('statusFilter', 'all')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-amber-600 text-slate-950' : 'text-slate-400 hover:text-white' }}">
                        All Status
                    </button>
                    <button type="button" 
                            wire:click="$set('statusFilter', 'published')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'published' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white' }}">
                        Live
                    </button>
                    <button type="button" 
                            wire:click="$set('statusFilter', 'draft')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $statusFilter === 'draft' ? 'bg-slate-800 text-slate-300' : 'text-slate-400 hover:text-white' }}">
                        Draft
                    </button>
                </div>

                <!-- Directory Filter -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-amber-950/60">
                    <button type="button" 
                            wire:click="$set('discoverFilter', 'all')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $discoverFilter === 'all' ? 'bg-amber-600 text-slate-950' : 'text-slate-400 hover:text-white' }}">
                        All SEO
                    </button>
                    <button type="button" 
                            wire:click="$set('discoverFilter', 'discoverable')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $discoverFilter === 'discoverable' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                        /discover Listed
                    </button>
                    <button type="button" 
                            wire:click="$set('discoverFilter', 'hidden')"
                            class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer {{ $discoverFilter === 'hidden' ? 'bg-slate-800 text-slate-300' : 'text-slate-400 hover:text-white' }}">
                        Hidden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolios Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/95 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Portfolio / Developer</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Tenant Account</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Public URL / Domain</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Publish Status</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Directory SEO</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->portfolios as $profile)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">{{ $profile->full_name }}</div>
                            <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $profile->headline ?: 'Developer' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div>{{ $profile->account?->name ?? 'No Workspace' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $profile->account?->plan_slug ?: 'free' }} plan</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div class="flex flex-col">
                                <a href="{{ url('/' . $profile->slug) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1">
                                    <span>/{{ $profile->slug }}</span>
                                    <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                                @if($profile->domains->whereNotNull('verified_at')->first())
                                <span class="text-[10px] text-emerald-400 font-mono">
                                    {{ $profile->domains->whereNotNull('verified_at')->first()->domain }} (Verified)
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($profile->is_published)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Live
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] text-slate-500 bg-slate-900 border border-slate-800">
                                Draft
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($profile->is_discoverable)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/40">
                                Indexed
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] text-slate-500 bg-slate-900 border border-slate-800">
                                Hidden
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <button type="button" 
                                    wire:click="togglePublish({{ $profile->id }})" 
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $profile->is_published ? 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30' }}">
                                {{ $profile->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                            <button type="button" 
                                    wire:click="toggleDiscoverable({{ $profile->id }})" 
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $profile->is_discoverable ? 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' : 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 hover:bg-indigo-500/30' }}">
                                {{ $profile->is_discoverable ? 'Hide SEO' : 'List SEO' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No developer portfolios found matching filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-amber-950/60 bg-black/60">
            {{ $this->portfolios->links() }}
        </div>
    </div>
</div>
