<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Profile;
use App\Models\Account;

layout('layouts.dashboard');
title('Agency Workspace');

state([
    'user' => fn () => auth()->user(),
    'account' => fn () => auth()->user()?->defaultTenant ?? auth()->user()?->accounts->first(),
    'members' => fn () => (auth()->user()?->defaultTenant ?? auth()->user()?->accounts->first())?->members ?? collect(),
    'profiles' => fn () => (auth()->user()?->defaultTenant ?? auth()->user()?->accounts->first())?->profiles ?? collect(),
]);

?>

<div class="space-y-8">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                    Multi-Client Workspace
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                    Agency Suite
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                Agency Client Hub
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Manage multi-tenant client portfolios, team seats, and white-label branding.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/admin/{{ $account?->id ?? 1 }}/team-settings" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition-all">
                Team Settings
            </a>
            <a href="/admin/{{ $account?->id ?? 1 }}/agency-branding-settings" class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 text-slate-950 font-bold text-xs shadow-md shadow-teal-500/20 hover:opacity-95 transition-opacity">
                White-Label Settings
            </a>
        </div>
    </div>

    <!-- Agency Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="text-[10px] uppercase font-semibold text-slate-400 mb-1">Managed Portfolios</div>
            <div class="text-3xl font-extrabold font-heading text-teal-400 mb-1">{{ $profiles->count() }}</div>
            <div class="text-xs text-slate-400">Active client websites</div>
        </div>

        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="text-[10px] uppercase font-semibold text-slate-400 mb-1">Team Members</div>
            <div class="text-3xl font-extrabold font-heading text-cyan-400 mb-1">{{ $members->count() + 1 }}</div>
            <div class="text-xs text-slate-400">Owners, editors & viewers</div>
        </div>

        <div class="glass-card glass-card-hover rounded-3xl p-6 relative overflow-hidden">
            <div class="text-[10px] uppercase font-semibold text-slate-400 mb-1">White-Label Branding</div>
            <div class="text-3xl font-extrabold font-heading text-white mb-1">
                {{ $account?->hide_platform_branding ? 'Enabled' : 'Default' }}
            </div>
            <div class="text-xs text-slate-400">{{ $account?->custom_brand_name ?: 'Custom badge ready' }}</div>
        </div>
    </div>

    <!-- Managed Client Profiles Table -->
    <div class="glass-card rounded-3xl overflow-hidden border border-white/10">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Client Portfolios</h3>
                <p class="text-xs text-slate-400">All tenant profiles registered under this agency account.</p>
            </div>
            <a href="/admin/{{ $account?->id ?? 1 }}/profiles/create" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20 hover:bg-teal-500/20 transition-all">
                + Provision Client
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-900/80 border-b border-white/5 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Client / Profile Name</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Slug & URL</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($profiles as $clientProfile)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($clientProfile->full_name ?: 'C', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white">{{ $clientProfile->full_name ?: 'Unnamed Profile' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $clientProfile->headline ?: 'No headline configured' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-300">
                            /{{ $clientProfile->slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($clientProfile->is_published)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                <span class="w-1 h-1 rounded-full bg-teal-400"></span> Live
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                Draft
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <a href="{{ url('/' . $clientProfile->slug) }}" target="_blank" class="text-slate-400 hover:text-teal-400 transition-colors">Preview</a>
                            <a href="/admin/{{ $account?->id ?? 1 }}" class="text-teal-400 hover:text-teal-300 font-semibold transition-colors">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">
                            No secondary client portfolios provisioned yet. Use the button above to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
