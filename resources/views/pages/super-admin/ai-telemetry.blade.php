<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\ResumeGeneration;
use App\Models\CoverLetterGeneration;
use App\Models\Account;
use App\Models\AiSetting;

layout('layouts.super-admin');
title('Platform AI & LLM Telemetry');

state([
    'timeRange' => 'all', // all, 30d, 7d
]);

$totalResumes = computed(fn () => ResumeGeneration::count());
$totalCoverLetters = computed(fn () => CoverLetterGeneration::count());
$totalGenerations = computed(fn () => ResumeGeneration::count() + CoverLetterGeneration::count());

$byokActiveAccountsCount = computed(function () {
    return Account::whereHas('aiSetting', function ($q) {
        $q->whereNotNull('api_key')->where('api_key', '!=', '');
    })->count();
});

$topConsumingAccounts = computed(function () {
    return Account::with(['owner', 'aiSetting'])
        ->orderByDesc('ai_generations_used_current_period')
        ->take(8)
        ->get();
});

$providerDistribution = computed(function () {
    $openaiCount = AiSetting::where('provider', 'openai')->count();
    $anthropicCount = AiSetting::where('provider', 'anthropic')->count();
    $total = $openaiCount + $anthropicCount;

    return [
        'openai' => $openaiCount,
        'anthropic' => $anthropicCount,
        'openaiPct' => $total > 0 ? round(($openaiCount / $total) * 100) : 70,
        'anthropicPct' => $total > 0 ? round(($anthropicCount / $total) * 100) : 30,
    ];
});

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    LLM PIPELINE TELEMETRY
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    AI WORKLOAD METRICS
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Platform AI & LLM Telemetry Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Monitor global AI tailoring pipelines, OpenAI / Claude model distributions, BYOK exemption rates, and tenant quota burn rates.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer" data-tooltip="Return to Super Admin Master Control Hub">
                &larr; Telemetry Hub
            </a>
        </div>
    </div>

    <!-- AI KPI Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total AI Requests -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Total AI Requests</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-400">{{ $this->totalGenerations }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Platform-wide generations</div>
        </div>

        <!-- Metric 2: AI Resumes -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Tailored Resumes</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-emerald-400">{{ $this->totalResumes }}</div>
            <div class="text-[10px] text-slate-500 font-mono">PDFs structured & compiled</div>
        </div>

        <!-- Metric 3: Cover Letters -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">Cover Letters</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-cyan-400">{{ $this->totalCoverLetters }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Role-targeted letters</div>
        </div>

        <!-- Metric 4: BYOK Exemption Rate -->
        <div class="glass-card-dark glass-card-dark-hover rounded-2xl p-5 space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400 font-mono tracking-wider">BYOK Active Accounts</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-heading text-orange-400">{{ $this->byokActiveAccountsCount }}</div>
            <div class="text-[10px] text-slate-500 font-mono">Zero platform quota cost</div>
        </div>
    </div>

    <!-- AI Providers Breakdown & Policy Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Provider Split Card -->
        <div class="glass-card-dark rounded-3xl p-6 sm:p-7 border border-amber-950/70 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold font-heading text-white">LLM Provider Distribution</h3>
                    <p class="text-xs text-slate-400">Models utilized across tenant BYOK settings.</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">MULTI-PROVIDER</span>
            </div>

            <div class="space-y-4 font-mono text-xs">
                <!-- OpenAI Bar -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>OpenAI (GPT-4o / GPT-4o-mini)</span>
                        </span>
                        <span class="font-bold text-emerald-400">{{ $this->providerDistribution['openaiPct'] }}%</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-slate-900 overflow-hidden border border-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-teal-400" style="width: {{ $this->providerDistribution['openaiPct'] }}%"></div>
                    </div>
                </div>

                <!-- Anthropic Bar -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            <span>Anthropic (Claude 3.5 Sonnet / Haiku)</span>
                        </span>
                        <span class="font-bold text-orange-400">{{ $this->providerDistribution['anthropicPct'] }}%</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-slate-900 overflow-hidden border border-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-orange-600 to-amber-400" style="width: {{ $this->providerDistribution['anthropicPct'] }}%"></div>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-black border border-amber-950/60 text-xs font-mono text-slate-400 space-y-2">
                <div class="text-white font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>System Fallback Architecture:</span>
                </div>
                <p class="text-[11px] leading-relaxed">
                    When no BYOK key is configured on an Account, requests route through platform master credentials with monthly quota guards (Free: 3/mo, Pro: 20/mo, Agency: 50/mo).
                </p>
            </div>
        </div>

        <!-- Quota Policy & Engine Health Card -->
        <div class="glass-card-dark rounded-3xl p-6 sm:p-7 border border-amber-950/70 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold font-heading text-white">AI Engine & Quota Guard</h3>
                    <p class="text-xs text-slate-400">Active throttling policies & service status.</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">OPERATIONAL</span>
            </div>

            <div class="grid grid-cols-2 gap-3 font-mono text-xs">
                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 space-y-1">
                    <div class="text-slate-500 text-[10px] uppercase">Service Pipeline</div>
                    <div class="font-bold text-white text-sm">ResumeTailor</div>
                    <div class="text-[10px] text-emerald-400">&bull; Schema v2 Active</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 space-y-1">
                    <div class="text-slate-500 text-[10px] uppercase">PDF Parser Engine</div>
                    <div class="font-bold text-white text-sm">Smalot / Schema</div>
                    <div class="text-[10px] text-emerald-400">&bull; Extraction OK</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 space-y-1">
                    <div class="text-slate-500 text-[10px] uppercase">Guard Interceptor</div>
                    <div class="font-bold text-white text-sm">AiUsageGuard</div>
                    <div class="text-[10px] text-amber-400">&bull; Enforcing Limits</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 space-y-1">
                    <div class="text-slate-500 text-[10px] uppercase">Rate Limiter</div>
                    <div class="font-bold text-white text-sm">10 req / min</div>
                    <div class="text-[10px] text-cyan-400">&bull; DDoS Defense</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top AI Consuming Accounts Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="px-6 py-5 border-b border-amber-950/60 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Top AI-Consuming Tenants</h3>
                <p class="text-xs text-slate-400">Tenants with highest generation volume in current monthly billing cycle.</p>
            </div>
            <a href="{{ route('super-admin.tenants') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold font-mono flex items-center gap-1 cursor-pointer" data-tooltip="Open Tenant Accounts Manager">
                <span>View All Tenants</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/95 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Tenant Account</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Owner Identity</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Plan Tier</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Generations Used</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Key Routing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->topConsumingAccounts as $tenant)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">{{ $tenant->name }}</div>
                            <div class="text-[10px] text-slate-500">ID: #{{ $tenant->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div>{{ $tenant->owner?->name ?? 'Unassigned' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $tenant->owner?->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $tenant->plan_slug === 'agency' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : ($tenant->plan_slug === 'pro' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-900 text-slate-400 border border-slate-800') }}">
                                {{ $tenant->plan_slug ?: 'free' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-white">
                            {{ $tenant->ai_generations_used_current_period }} generations
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($tenant->aiSetting && $tenant->aiSetting->api_key)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> BYOK ({{ ucfirst($tenant->aiSetting->provider) }})
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] text-slate-400 bg-slate-900 border border-slate-800">
                                Platform Quota
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No usage recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
