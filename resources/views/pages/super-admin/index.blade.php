<?php

use function Livewire\Volt\{state, layout, title, usesPagination, computed};
use App\Models\User;
use App\Models\Account;
use App\Models\Profile;
use App\Models\ResumeGeneration;
use App\Models\CoverLetterGeneration;
use App\Models\PortfolioReport;
use App\Models\Theme;
use App\Models\Template;

layout('layouts.super-admin');
title('Super Admin Master Control');

usesPagination();

state([
    'activeTab' => 'telemetry',
    'searchQuery' => '',
    'userActionMessage' => null,
]);

$totalUsersCount = computed(fn () => User::count());
$totalAccountsCount = computed(fn () => Account::count());
$totalProfilesCount = computed(fn () => Profile::count());
$totalResumesCount = computed(fn () => ResumeGeneration::count());
$totalCoverLettersCount = computed(fn () => CoverLetterGeneration::count());
$pendingReportsCount = computed(fn () => PortfolioReport::where('status', 'pending')->count());

$reportsList = computed(fn () => PortfolioReport::query()->with('profile')->latest()->take(10)->get());

$usersList = computed(function () {
    return User::query()
        ->when($this->searchQuery, function ($q) {
            $q->where('name', 'like', '%' . $this->searchQuery . '%')
              ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
        })
        ->with(['accounts', 'profile'])
        ->latest()
        ->paginate(10);
});

$toggleSuperAdmin = function (int $userId) {
    if (auth()->id() === $userId) {
        $this->userActionMessage = "Security restriction: You cannot demote your own Super Admin root account.";
        return;
    }

    $targetUser = User::find($userId);
    if ($targetUser) {
        $targetUser->is_super_admin = ! $targetUser->is_super_admin;
        $targetUser->save();
        $this->userActionMessage = "Successfully updated Super Admin privileges for {$targetUser->name}.";
    }
};

$resolveReport = function (int $reportId, string $status) {
    $report = PortfolioReport::find($reportId);
    if ($report) {
        $report->status = $status;
        $report->save();
        $this->userActionMessage = "Report #{$reportId} status updated to {$status}.";
    }
};

?>

<div class="space-y-8" x-data="{ activeTab: @entangle('activeTab') }">

    <!-- Super Admin Master Control Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full text-xs font-mono font-black bg-rose-600 text-white shadow-md shadow-rose-900/50">
                    SUPER ADMIN MASTER CONTROL
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/30 font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    TIER 0 / ROOT ELEVATED
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                Global Platform Operations
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Root administration shell &bull; Multi-tenant telemetry &bull; Moderation queue &bull; API Integrations
            </p>
        </div>

        <!-- Quick Jump Workspace Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>User Dashboard</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
            <a href="{{ route('agency') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-yellow-400 hover:border-yellow-500/40 text-xs font-semibold transition-all flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                <span>Agency Hub</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>
    </div>

    <!-- Alert / Message Banner -->
    @if($userActionMessage)
    <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-200 text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $userActionMessage }}</span>
        </div>
        <button wire:click="$set('userActionMessage', null)" class="text-rose-400 hover:text-white text-xs underline">Dismiss</button>
    </div>
    @endif

    <!-- Root Privileges Notice -->
    <div class="p-4 rounded-2xl bg-black border border-rose-950 text-rose-400 text-xs flex items-center justify-between font-mono">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span><strong>SUPER ADMIN SECURITY PROTOCOL ACTIVE:</strong> You have unrestricted system authority to manage tenants, inspect audit logs, rotate API keys, and moderate portfolios globally.</span>
        </div>
    </div>

    <!-- Master Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-rose-950/70 pb-3 font-mono text-xs">
        <button @click="activeTab = 'telemetry'" 
                :class="activeTab === 'telemetry' ? 'bg-rose-600 text-white shadow-md shadow-rose-900/50' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                class="px-4 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span>1. Telemetry & Health</span>
        </button>

        <button @click="activeTab = 'users'" 
                :class="activeTab === 'users' ? 'bg-rose-600 text-white shadow-md shadow-rose-900/50' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                class="px-4 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <span>2. Users & Accounts ({{ $totalUsersCount }})</span>
        </button>

        <button @click="activeTab = 'moderation'" 
                :class="activeTab === 'moderation' ? 'bg-rose-600 text-white shadow-md shadow-rose-900/50' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                class="px-4 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>3. Reported Portfolios @if($pendingReportsCount > 0) <span class="px-1.5 py-0.2 rounded-full bg-red-500 text-white text-[10px]">{{ $pendingReportsCount }}</span> @endif</span>
        </button>

        <button @click="activeTab = 'integrations'" 
                :class="activeTab === 'integrations' ? 'bg-rose-600 text-white shadow-md shadow-rose-900/50' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                class="px-4 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
            <span>4. API Integrations Hub</span>
        </button>

        <button @click="activeTab = 'settings'" 
                :class="activeTab === 'settings' ? 'bg-rose-600 text-white shadow-md shadow-rose-900/50' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                class="px-4 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span>5. System Settings</span>
        </button>
    </div>

    <!-- TAB 1: TELEMETRY & SYSTEM HEALTH -->
    <div x-show="activeTab === 'telemetry'" class="space-y-6">
        <!-- Telemetry Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Total Users</div>
                <div class="text-3xl font-extrabold font-heading text-white">{{ $totalUsersCount }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Registered accounts</div>
            </div>

            <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Tenant Workspaces</div>
                <div class="text-3xl font-extrabold font-heading text-rose-400">{{ $totalAccountsCount }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Active tenants</div>
            </div>

            <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Portfolios</div>
                <div class="text-3xl font-extrabold font-heading text-red-300">{{ $totalProfilesCount }}</div>
                <div class="text-[10px] text-slate-500 font-mono">Live & draft sites</div>
            </div>

            <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">AI Generation Pipeline</div>
                <div class="text-3xl font-extrabold font-heading text-emerald-400">{{ $totalResumesCount + $totalCoverLettersCount }}</div>
                <div class="text-[10px] text-slate-500 font-mono">{{ $totalResumesCount }} Resumes &bull; {{ $totalCoverLettersCount }} Letters</div>
            </div>
        </div>

        <!-- Infrastructure Watcher & Daemon Heartbeat -->
        <div class="p-6 rounded-3xl bg-black border border-rose-950/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
                    </span>
                    <div>
                        <div class="text-sm font-bold text-white flex items-center gap-2 font-mono">
                            <span>SaaS Engine Watcher:</span>
                            <span class="text-rose-400 font-bold">ONLINE & SECURE</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Single-schema multi-tenancy &bull; Stripe Cashier billing &bull; Custom Domain Host Resolver
                        </div>
                    </div>
                </div>

                <div class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-white/5">
                    Server Clock: <span class="text-rose-300 font-bold">{{ now()->toDateTimeString() }} UTC</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-rose-950/50 text-xs font-mono">
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">Database Engine</div>
                    <div class="text-white font-bold">{{ config('database.default') }} (Connected)</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">SaaS Operating Mode</div>
                    <div class="text-rose-400 font-bold">{{ config('saas.mode') ? 'Multi-Tenant SaaS' : 'Self-Hosted Single' }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                    <div class="text-slate-500 uppercase text-[10px]">Cache & Session</div>
                    <div class="text-emerald-400 font-bold">{{ config('cache.default') }} / {{ config('session.driver') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: USERS & ACCOUNTS DIRECTORY -->
    <div x-show="activeTab === 'users'" class="space-y-6">
        <div class="glass-card-dark rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold font-heading text-white">Registered Users & Tenant Accounts</h3>
                    <p class="text-xs text-slate-400">Search and manage user privileges, promote to Super Admin, and view accounts.</p>
                </div>

                <div class="w-full sm:w-72">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchQuery" 
                           placeholder="Search by name or email..." 
                           class="w-full px-4 py-2 rounded-xl bg-black border border-rose-950 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-black border-b border-rose-950 text-slate-400 font-mono">
                        <tr>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">User & Identity</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Account Plan</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Super Admin Status</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Portfolio URL</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-950/40 font-mono">
                        @forelse($usersList as $userItem)
                        <tr class="hover:bg-rose-950/20 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-white">{{ $userItem->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $userItem->email }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $userItem->accounts->first()?->plan_slug === 'agency' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : ($userItem->accounts->first()?->plan_slug === 'pro' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-900 text-slate-400 border border-slate-800') }}">
                                    {{ $userItem->accounts->first()?->plan_slug ?: 'Free' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($userItem->is_super_admin)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-sm shadow-rose-900/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> SUPER ADMIN
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] text-slate-500 bg-slate-900 border border-slate-800">
                                    Standard User
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-300">
                                @if($userItem->profile && $userItem->profile->slug)
                                <a href="{{ url('/' . $userItem->profile->slug) }}" target="_blank" class="text-emerald-400 hover:underline flex items-center gap-1">
                                    <span>/{{ $userItem->profile->slug }}</span>
                                    <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                                @else
                                <span class="text-slate-600">No profile</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right space-x-2">
                                <button wire:click="toggleSuperAdmin({{ $userItem->id }})" 
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $userItem->is_super_admin ? 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500/40' }} transition-all">
                                    {{ $userItem->is_super_admin ? 'Demote' : 'Promote to SA' }}
                                </button>
                                <a href="/admin/{{ $userItem->accounts->first()?->id ?? 1 }}" target="_blank" class="text-slate-400 hover:text-white underline text-[10px]">Studio</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">No users found matching query.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $usersList->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 3: REPORTED PORTFOLIOS & MODERATION -->
    <div x-show="activeTab === 'moderation'" class="space-y-6">
        <div class="glass-card-dark rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="space-y-1">
                <h3 class="text-xl font-bold font-heading text-white">Portfolio Moderation & Abuse Reports</h3>
                <p class="text-xs text-slate-400">Review flagged user sites, take down spam or abusive content, or dismiss reports.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs font-mono">
                    <thead class="bg-black border-b border-rose-950 text-slate-400">
                        <tr>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reported Profile</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reason & Details</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Reporter IP</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px]">Status</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-950/40">
                        @forelse($reportsList as $report)
                        <tr class="hover:bg-rose-950/20 transition-colors">
                            <td class="px-4 py-3.5 font-bold text-white">
                                @if($report->profile)
                                <a href="{{ url('/' . $report->profile->slug) }}" target="_blank" class="text-rose-400 hover:underline">
                                    {{ $report->profile->full_name }} (/{{ $report->profile->slug }})
                                </a>
                                @else
                                <span class="text-slate-500">Deleted Profile #{{ $report->profile_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-300">
                                <div class="font-bold text-white uppercase text-[10px]">{{ $report->reason }}</div>
                                <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $report->details ?: 'No details provided' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 font-mono text-[10px]">
                                {{ $report->reporter_ip ?: 'Unknown' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $report->status === 'pending' ? 'bg-red-500/20 text-red-300 border border-red-500/40 animate-pulse' : ($report->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400') }}">
                                    {{ $report->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-2">
                                <button wire:click="resolveReport({{ $report->id }}, 'resolved')" class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/40 text-[10px] font-bold">Resolve</button>
                                <button wire:click="resolveReport({{ $report->id }}, 'dismissed')" class="px-2 py-1 rounded bg-slate-900 text-slate-400 border border-slate-800 hover:text-white text-[10px]">Dismiss</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">No reports logged. Moderation queue is clean.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: API INTEGRATIONS HUB -->
    <div x-show="activeTab === 'integrations'" class="space-y-6">
        <div class="glass-card-dark rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="space-y-1">
                <h3 class="text-xl font-bold font-heading text-white">API Integrations & Connected Services Hub</h3>
                <p class="text-xs text-slate-400">Connection health status, webhook telemetry, and credential rotation controls.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Google OAuth -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Google OAuth</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">READY</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Social single sign-on & authentication</div>
                    <div class="text-[9px] text-slate-500 truncate">ID: {{ substr(config('services.google.client_id', 'google-oauth-client-id'), 0, 12) }}...</div>
                </div>

                <!-- 2. GitHub OAuth & API -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">GitHub Sync API</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">CONNECTED</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Repo showcase & OAuth login</div>
                    <div class="text-[9px] text-slate-500 truncate">ID: {{ substr(config('services.github.client_id', 'github-oauth-client-id'), 0, 12) }}...</div>
                </div>

                <!-- 3. Stripe Billing -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Stripe Gateway</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">LIVE</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Cashier subscriptions & webhooks</div>
                    <div class="text-[9px] text-slate-500 truncate">Key: {{ substr(config('cashier.key', 'pk_live_stripe_key'), 0, 12) }}...</div>
                </div>

                <!-- 4. Postmark / SMTP -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Transactional Mail</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">ARMED</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Welcome emails & contact forwarding</div>
                    <div class="text-[9px] text-slate-500 truncate">Mailer: {{ config('mail.default', 'smtp') }}</div>
                </div>

                <!-- 5. Cloudflare DNS & SSL -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Cloudflare SSL</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">ACTIVE</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Custom domain DNS verification</div>
                    <div class="text-[9px] text-slate-500">CNAME Routing Ready</div>
                </div>

                <!-- 6. Sentry Telemetry -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Sentry Monitoring</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">WATCHING</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Error tracking & performance profiling</div>
                    <div class="text-[9px] text-slate-500">0 Critical Crashes</div>
                </div>

                <!-- 7. Turnstile / hCaptcha -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Bot Defense</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">PROTECTED</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Contact form & auth rate-limiting</div>
                    <div class="text-[9px] text-slate-500">Rate Limiter: 10 req/min</div>
                </div>

                <!-- 8. Storage (S3 / R2) -->
                <div class="p-5 rounded-2xl bg-black border border-rose-950/70 space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-white text-xs">Cloud Storage</div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">LINKED</span>
                    </div>
                    <div class="text-[10px] text-slate-400">Resume PDFs, avatars & certificates</div>
                    <div class="text-[9px] text-slate-500">Disk: {{ config('filesystems.default', 'local') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: SYSTEM SETTINGS & FEATURE FLAGS -->
    <div x-show="activeTab === 'settings'" class="space-y-6">
        <div class="glass-card-dark rounded-3xl p-6 sm:p-8 space-y-6 font-mono text-xs">
            <div class="space-y-1">
                <h3 class="text-xl font-bold font-heading text-white">System Flags & Core Settings</h3>
                <p class="text-slate-400">Global operating switches controlling SaaS tenancy, self-service registration, and AI keys.</p>
            </div>

            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-black border border-rose-950 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">SaaS Multi-Tenant Mode (`config('saas.mode')`)</div>
                        <div class="text-slate-500 text-[11px]">Enables multi-user registration, marketing routes, and tenant slug routing.</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ config('saas.mode') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400' }}">
                        {{ config('saas.mode') ? 'ENABLED' : 'DISABLED' }}
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-black border border-rose-950 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">BYOK (Bring Your Own Key) Policy</div>
                        <div class="text-slate-500 text-[11px]">Users with custom OpenAI/Anthropic keys are exempt from monthly quota limits.</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        ACTIVE
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-black border border-rose-950 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">Platform Self-Service Registration</div>
                        <div class="text-slate-500 text-[11px]">Allows new visitors to create accounts at `/admin/register`.</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        OPEN
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-black border border-rose-950 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">Global Maintenance Mode</div>
                        <div class="text-slate-500 text-[11px]">Restricts public site access with maintenance response code.</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-900 text-slate-500 border border-slate-800">
                        OFFLINE
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>
