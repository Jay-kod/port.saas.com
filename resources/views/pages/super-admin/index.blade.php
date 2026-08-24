<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\User;
use App\Models\Account;
use App\Models\Profile;
use App\Models\ResumeGeneration;
use App\Models\CoverLetterGeneration;
use App\Models\PortfolioReport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

layout('layouts.super-admin');
title('SUPER ADMIN MASTER CONTROL - Global Platform Operations');

state([
    'actionMessage' => '',
    'actionType' => 'success', // success, error
]);

$totalUsersCount = computed(fn () => User::count());
$totalAccountsCount = computed(fn () => Account::count());
$totalProfilesCount = computed(fn () => Profile::count());
$liveProfilesCount = computed(fn () => Profile::where('is_published', true)->count());
$totalResumesCount = computed(fn () => ResumeGeneration::count());
$totalCoverLettersCount = computed(fn () => CoverLetterGeneration::count());
$pendingReportsCount = computed(fn () => PortfolioReport::where('status', 'pending')->count());

$estimatedMrr = computed(function () {
    $proAccounts = Account::where('plan_slug', 'pro')->count();
    $agencyAccounts = Account::where('plan_slug', 'agency')->count();
    return ($proAccounts * 19) + ($agencyAccounts * 79);
});

$recentAccounts = computed(function () {
    return Account::with('owner')->latest()->take(6)->get();
});

$pendingReportsList = computed(function () {
    return PortfolioReport::with('profile')->where('status', 'pending')->latest()->take(5)->get();
});

$purgeCache = function () {
    try {
        Artisan::call('optimize:clear');
        $this->actionMessage = 'Platform optimization cache successfully purged.';
        $this->actionType = 'success';
    } catch (\Throwable $e) {
        $this->actionMessage = 'Error clearing cache: ' . $e->getMessage();
        $this->actionType = 'error';
    }
};

$toggleSuperAdmin = function (int $userId) {
    $this->actionMessage = '';

    if (Auth::id() === $userId) {
        $this->actionMessage = 'Security restriction: You cannot demote your own Super Admin root account';
        $this->actionType = 'error';
        return;
    }

    $target = User::findOrFail($userId);
    $target->is_super_admin = ! $target->is_super_admin;
    $target->save();

    $this->actionMessage = "Successfully updated Super Admin privileges for {$target->name}";
    $this->actionType = 'success';
};

$resolveReport = function ($reportId, $status) {
    $report = PortfolioReport::findOrFail($reportId);
    $report->status = $status;
    $report->save();

    $this->actionMessage = "Report #{$report->id} status updated to {$status}";
    $this->actionType = 'success';
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header with Root Protocol Badges & Quick Jump Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-black bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1.5 shadow-sm shadow-amber-950">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    SUPER ADMIN ROOT ELEVATED
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-orange-500/10 text-orange-300 border border-orange-500/20">
                    TIER 0 / ROOT ELEVATED
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                SUPER ADMIN MASTER CONTROL
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Global Platform Operations, tenant governance, multi-schema accounts, and infrastructure telemetry.
            </p>
        </div>

        <!-- Quick Jump Workspace Actions -->
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer" data-tooltip="Open Developer Studio workspace in a new tab">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Developer Studio</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
            <a href="{{ route('agency') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-teal-400 hover:border-teal-500/40 text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer" data-tooltip="Open Agency Multi-Client Hub in a new tab">
                <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                <span>Agency Hub</span>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
            <button type="button" wire:click="purgeCache" class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 font-mono font-bold text-xs transition-all flex items-center gap-2 cursor-pointer shadow-lg shadow-amber-950/50" data-tooltip="Flush application cache, config, routes, and views">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Purge Cache</span>
            </button>
        </div>
    </div>

    <!-- Alert / Message Banner -->
    @if($actionMessage)
    <div class="p-4 rounded-2xl {{ $actionType === 'success' ? 'bg-amber-500/15 border-amber-500/30 text-amber-200' : 'bg-red-500/15 border-red-500/30 text-red-200' }} border text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-3">
            <svg class="w-4 h-4 shrink-0 {{ $actionType === 'success' ? 'text-amber-400' : 'text-red-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $actionMessage }}</span>
        </div>
        <button wire:click="$set('actionMessage', '')" class="text-amber-400 hover:text-white text-xs underline cursor-pointer" data-tooltip="Dismiss notification">Dismiss</button>
    </div>
    @endif

    <!-- Primary Platform Telemetry KPI Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Metric 1: Total Tenants -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Tenant Accounts</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-400">{{ $this->totalAccountsCount }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Workspaces active</div>
        </div>

        <!-- Metric 2: Registered Users -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Registered Users</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-white">{{ $this->totalUsersCount }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Platform members</div>
        </div>

        <!-- Metric 3: Total Portfolios -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Portfolios</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-orange-400">{{ $this->totalProfilesCount }}</div>
            <div class="text-[10px] text-slate-500 font-mono">{{ $this->liveProfilesCount }} Published / Live</div>
        </div>

        <!-- Metric 4: AI Generation Pipeline -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">AI Generations</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-emerald-400">{{ $this->totalResumesCount + $this->totalCoverLettersCount }}</div>
            <div class="text-[10px] text-slate-500 font-mono">{{ $this->totalResumesCount }} Resumes &bull; {{ $this->totalCoverLettersCount }} Letters</div>
        </div>

        <!-- Metric 5: Estimated MRR -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1 col-span-2 lg:col-span-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Estimated MRR</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-300 font-mono">${{ number_format($this->estimatedMrr) }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Pro ($19) + Agency ($79)</div>
        </div>
    </div>

    <!-- Watcher Heartbeat & Infrastructure Status -->
    <div class="p-6 rounded-3xl bg-black border border-amber-950/80 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <div>
                    <div class="text-sm font-bold text-white flex items-center gap-2 font-mono">
                        <span>SaaS Engine Watcher:</span>
                        <span class="text-amber-400 font-bold">ONLINE & SECURE</span>
                    </div>
                    <div class="text-xs text-slate-400">
                        Single-schema multi-tenancy &bull; Stripe Cashier billing &bull; Custom Domain Host Resolver
                    </div>
                </div>
            </div>

            <div class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-white/5">
                Server Clock: <span class="text-amber-300 font-bold">{{ now()->toDateTimeString() }} UTC</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-amber-950/50 text-xs font-mono">
            <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                <div class="text-slate-500 uppercase text-[10px]">Database Engine</div>
                <div class="text-white font-bold">{{ config('database.default') }} (Connected)</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                <div class="text-slate-500 uppercase text-[10px]">SaaS Operating Mode</div>
                <div class="text-amber-400 font-bold">{{ config('saas.mode') ? 'Multi-Tenant SaaS' : 'Self-Hosted Single' }}</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                <div class="text-slate-500 uppercase text-[10px]">Cache & Session</div>
                <div class="text-emerald-400 font-bold">{{ config('cache.default') }} / {{ config('session.driver') }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Launchpad to Dedicated Super Admin Pages -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 1. Tenants & Accounts Manager -->
        <a href="{{ route('super-admin.tenants') }}" class="glass-card-dark glass-card-dark-hover rounded-3xl p-6 space-y-3 group border border-amber-500/20">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <h3 class="text-base font-bold font-heading text-white group-hover:text-amber-300 transition-colors">Tenant Accounts Manager</h3>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Search workspaces, override subscription plans (Free/Pro/Agency), and reset monthly AI quotas.</p>
            </div>
            <div class="text-xs font-mono text-amber-400 font-bold flex items-center gap-1">
                <span>Manage Accounts ({{ $this->totalAccountsCount }})</span> &rarr;
            </div>
        </a>

        <!-- 2. Users & Privileges -->
        <a href="{{ route('super-admin.users') }}" class="glass-card-dark glass-card-dark-hover rounded-3xl p-6 space-y-3 group border border-amber-500/20">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h3 class="text-base font-bold font-heading text-white group-hover:text-amber-300 transition-colors">Users & Role Privileges</h3>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Promote or demote Super Admin accounts, audit identity records, and inspect user memberships.</p>
            </div>
            <div class="text-xs font-mono text-amber-400 font-bold flex items-center gap-1">
                <span>Manage Users ({{ $this->totalUsersCount }})</span> &rarr;
            </div>
        </a>

        <!-- 3. Abuse & Moderation Queue -->
        <a href="{{ route('super-admin.reports') }}" class="glass-card-dark glass-card-dark-hover rounded-3xl p-6 space-y-3 group border border-amber-500/20">
            <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div>
                <h3 class="text-base font-bold font-heading text-white group-hover:text-red-300 transition-colors">Abuse Moderation Queue</h3>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Review user abuse reports, take down spam portfolios, and resolve incident investigations.</p>
            </div>
            <div class="text-xs font-mono text-red-400 font-bold flex items-center gap-1">
                <span>Pending Reports ({{ $this->pendingReportsCount }})</span> &rarr;
            </div>
        </a>
    </div>

    <!-- Active Abuse Reports Queue Preview -->
    @if($this->pendingReportsList->count() > 0)
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-red-500/30 shadow-xl">
        <div class="px-6 py-5 border-b border-red-500/20 flex items-center justify-between bg-red-950/20">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-400 animate-ping"></span>
                <h3 class="text-base font-bold font-heading text-white">Pending Moderation Investigations</h3>
            </div>
            <a href="{{ route('super-admin.reports') }}" class="text-xs text-red-400 hover:text-red-300 font-semibold font-mono flex items-center gap-1">
                <span>Open Moderation Queue ({{ $this->pendingReportsCount }})</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="divide-y divide-red-950/40 font-mono text-xs">
            @foreach($this->pendingReportsList as $report)
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-red-950/10 transition-colors">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-500/20 text-red-300">#{{ $report->id }} &bull; {{ $report->reason }}</span>
                        <span class="font-bold text-white">{{ $report->profile?->full_name ?? 'Deleted Profile' }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">{{ $report->details }}</div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" wire:click="resolveReport({{ $report->id }}, 'resolved')" class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold hover:bg-emerald-500/30 cursor-pointer" data-tooltip="Mark report resolved and take corrective action">
                        Resolve
                    </button>
                    <button type="button" wire:click="resolveReport({{ $report->id }}, 'dismissed')" class="px-3 py-1 rounded-lg bg-slate-900 text-slate-400 hover:text-white border border-slate-800 cursor-pointer" data-tooltip="Dismiss abuse report as false positive">
                        Dismiss
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Platform Workspaces & Portfolios Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-xl">
        <div class="px-6 py-5 border-b border-amber-950/60 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Recent Tenant Registrations</h3>
                <p class="text-xs text-slate-400">Latest workspaces registered on the DevFolio SaaS platform.</p>
            </div>
            <a href="{{ route('super-admin.tenants') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold font-mono flex items-center gap-1">
                <span>View All Tenants</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/90 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Tenant Account</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Owner Identity</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Plan Tier</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">AI Usage</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->recentAccounts as $account)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">{{ $account->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">ID: #{{ $account->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div>{{ $account->owner?->name ?? 'No Owner' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $account->owner?->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $account->plan_slug === 'agency' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : ($account->plan_slug === 'pro' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-900 text-slate-400 border border-slate-800') }}">
                                {{ $account->plan_slug }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            {{ $account->ai_generations_used_current_period }} / {{ $account->plan_slug === 'agency' ? 50 : ($account->plan_slug === 'pro' ? 20 : 3) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-slate-400 text-[10px]">
                            {{ $account->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No accounts registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
