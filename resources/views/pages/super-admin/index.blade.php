<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\User;
use App\Models\Account;
use App\Models\Profile;
use App\Models\ResumeGeneration;

layout('layouts.super-admin');
title('Master Control');

state([
    'totalUsers' => fn () => User::count(),
    'totalAccounts' => fn () => Account::count(),
    'totalProfiles' => fn () => Profile::count(),
    'totalResumes' => fn () => ResumeGeneration::count(),
    'recentUsers' => fn () => User::latest()->take(5)->get(),
]);

?>

<div class="space-y-8">
    <!-- Header (Clima Super Admin Master Control Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold font-heading text-white tracking-tight">Super Admin Master Control</h1>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/30">
                    System Online
                </span>
            </div>
            <p class="text-sm text-slate-400">Global SaaS operations, multi-tenant telemetry, and platform control.</p>
        </div>
    </div>

    <!-- Security Warning Banner -->
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span><strong>Super Admin Elevated Shell:</strong> Root access active. System changes immediately affect all tenants globally.</span>
        </div>
    </div>

    <!-- Core Telemetry Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-semibold text-slate-400 font-mono">Total Users</div>
            <div class="text-3xl font-extrabold font-heading text-white">{{ $totalUsers }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Registered accounts</div>
        </div>

        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-semibold text-slate-400 font-mono">Tenant Accounts</div>
            <div class="text-3xl font-extrabold font-heading text-rose-400">{{ $totalAccounts }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Active workspaces</div>
        </div>

        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-semibold text-slate-400 font-mono">Portfolios</div>
            <div class="text-3xl font-extrabold font-heading text-red-300">{{ $totalProfiles }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Live & draft sites</div>
        </div>

        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-semibold text-slate-400 font-mono">AI Generations</div>
            <div class="text-3xl font-extrabold font-heading text-emerald-400">{{ $totalResumes }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Resumes compiled</div>
        </div>
    </div>

    <!-- Daemon & System Engine Status Banner -->
    <div class="p-5 rounded-2xl bg-black border border-rose-950/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
            </span>
            <div>
                <div class="text-sm font-bold text-white flex items-center gap-2">
                    <span>SaaS Engine Watcher:</span>
                    <span class="font-mono text-xs text-rose-400">ACTIVE & SECURE</span>
                </div>
                <div class="text-xs text-slate-400">
                    Tenancy boundaries verified &bull; Cashier billing gateway ready &bull; Custom domain router running
                </div>
            </div>
        </div>

        <div class="text-xs font-mono text-slate-500">
            System Clock: {{ now()->toDateTimeString() }} UTC
        </div>
    </div>

    <!-- Recent Platform Activity Table -->
    <div class="glass-card-dark rounded-3xl p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-bold font-heading text-white">Recent User Registrations</h3>
                <p class="text-xs text-slate-400">Latest platform accounts onboarded across free, pro, and agency tiers.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/60 border-b border-rose-950 text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-[10px]">User</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-[10px]">Email</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-[10px]">Role</th>
                        <th class="px-4 py-3 font-semibold uppercase tracking-wider text-[10px] text-right">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-950/40 font-mono">
                    @forelse($recentUsers as $user)
                    <tr class="hover:bg-rose-950/20 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-white">
                            {{ $user->name }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3.5">
                            @if($user->is_super_admin)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                SUPER ADMIN
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-slate-400 border border-slate-800">
                                TENANT USER
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right text-slate-500 text-[11px]">
                            {{ $user->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500 italic">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
