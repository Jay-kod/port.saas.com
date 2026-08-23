<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Account;
use App\Services\AiUsageGuard;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Billing & AI Usage');

state([
    'savedMessage' => '',
]);

$getAccount = function () {
    return Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
};

$getPlanSlug = function () {
    return $this->getAccount()?->plan_slug ?: 'free';
};

$getUsageData = function (AiUsageGuard $guard) {
    $account = $this->getAccount();
    if (! $account) return ['used' => 0, 'limit' => 3, 'percentage' => 0, 'isByok' => false];

    $planSlug = $account->plan_slug ?: 'free';
    $limit = config("plans.{$planSlug}.ai_generations_per_month");
    $used = (int) $account->ai_generations_used_current_period;
    $isByok = $guard->isByokActive($account);

    $percentage = ($limit !== null && $limit > 0) ? min(100, round(($used / $limit) * 100)) : 0;

    return [
        'used' => $used,
        'limit' => $limit,
        'percentage' => $percentage,
        'isByok' => $isByok,
    ];
};

$upgradePlan = function ($targetPlan) {
    $account = $this->getAccount();
    if (! $account) return;

    $account->update(['plan_slug' => $targetPlan]);
    $this->savedMessage = "Account subscription updated to " . strtoupper($targetPlan) . " tier!";
};

?>

<div class="space-y-8 max-w-5xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    WORKSPACE & OPERATIONS
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Billing & AI Usage Quotas
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Monitor your monthly AI resume tailoring quotas, current plan tier, and subscription billing.
            </p>
        </div>

        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 shrink-0">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>CURRENT PLAN: {{ strtoupper($this->getPlanSlug()) }}</span>
        </span>
    </div>

    {{-- Feedback Messages --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white">&times;</button>
        </div>
    @endif

    @php
        $usage = $this->getUsageData(app(AiUsageGuard::class));
        $planSlug = $this->getPlanSlug();
    @endphp

    {{-- Usage Meter Card --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6 border border-white/5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold font-heading text-white">Monthly AI Generation Meter</h3>
                <p class="text-xs text-slate-400 mt-0.5">Resets at the start of each monthly billing cycle.</p>
            </div>

            <div class="text-right">
                @if($usage['isByok'])
                    <span class="text-xs font-mono font-bold text-emerald-400">UNLIMITED (BYOK Active)</span>
                @elseif($usage['limit'] === null)
                    <span class="text-xs font-mono font-bold text-emerald-400">UNLIMITED (Pro Tier)</span>
                @else
                    <span class="text-xs font-mono font-bold text-white">{{ $usage['used'] }} / {{ $usage['limit'] }} generations used</span>
                @endif
            </div>
        </div>

        <div class="space-y-2">
            <div class="w-full bg-slate-950 rounded-full h-3 overflow-hidden border border-slate-800">
                <div class="bg-gradient-to-r from-emerald-500 to-cyan-400 h-3 rounded-full transition-all duration-500" style="width: {{ $usage['isByok'] || $usage['limit'] === null ? '100' : $usage['percentage'] }}%"></div>
            </div>
            <div class="flex justify-between text-[11px] font-mono text-slate-400">
                <span>{{ $usage['used'] }} used this period</span>
                <span>{{ $usage['isByok'] || $usage['limit'] === null ? 'No cap' : ($usage['limit'] - $usage['used']) . ' remaining' }}</span>
            </div>
        </div>
    </div>

    {{-- Plan Tier Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Free Plan --}}
        <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 border {{ $planSlug === 'free' ? 'border-emerald-500/40 bg-slate-900/90' : 'border-white/5' }}">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-base font-bold font-heading text-white">Starter</h4>
                    @if($planSlug === 'free')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">CURRENT</span>
                    @endif
                </div>
                <div class="text-2xl font-extrabold text-white font-heading">$0 <span class="text-xs font-normal text-slate-400">/mo</span></div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2">&check; 1 Developer Portfolio</li>
                    <li class="flex items-center gap-2">&check; 3 AI Resume Generations / mo</li>
                    <li class="flex items-center gap-2">&check; Standard Subdomain</li>
                    <li class="flex items-center gap-2">&check; 7 Dual-Mode Themes</li>
                </ul>
            </div>

            @if($planSlug !== 'free')
                <button wire:click="upgradePlan('free')" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-bold border border-slate-800">
                    Downgrade to Free
                </button>
            @endif
        </div>

        {{-- Pro Plan --}}
        <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 border {{ $planSlug === 'pro' ? 'border-emerald-500/40 bg-slate-900/90' : 'border-white/5' }}">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-base font-bold font-heading text-white">Professional</h4>
                    @if($planSlug === 'pro')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">CURRENT</span>
                    @endif
                </div>
                <div class="text-2xl font-extrabold text-emerald-400 font-heading">$12 <span class="text-xs font-normal text-slate-400">/mo</span></div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2 text-white font-semibold">&check; Unlimited AI Generations</li>
                    <li class="flex items-center gap-2 text-white font-semibold">&check; Branded Custom Apex Domain</li>
                    <li class="flex items-center gap-2">&check; Cover Letter AI Generator</li>
                    <li class="flex items-center gap-2">&check; Kanban Job Tracker Pipeline</li>
                    <li class="flex items-center gap-2">&check; Priority PDF Parsing</li>
                </ul>
            </div>

            @if($planSlug !== 'pro')
                <button wire:click="upgradePlan('pro')" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 text-xs font-bold shadow-md">
                    Upgrade to Pro ($12/mo)
                </button>
            @endif
        </div>

        {{-- Agency Plan --}}
        <div class="glass-card rounded-3xl p-6 flex flex-col justify-between space-y-6 border {{ $planSlug === 'agency' ? 'border-teal-500/40 bg-slate-900/90' : 'border-white/5' }}">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-base font-bold font-heading text-white">Agency Studio</h4>
                    @if($planSlug === 'agency')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">CURRENT</span>
                    @endif
                </div>
                <div class="text-2xl font-extrabold text-teal-400 font-heading">$49 <span class="text-xs font-normal text-slate-400">/mo</span></div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2 text-white font-semibold">&check; 25 Client Portfolios</li>
                    <li class="flex items-center gap-2 text-white font-semibold">&check; Multi-User Team Seats</li>
                    <li class="flex items-center gap-2 text-white font-semibold">&check; White-Label Branding</li>
                    <li class="flex items-center gap-2">&check; Everything in Pro Unlimited</li>
                </ul>
            </div>

            @if($planSlug !== 'agency')
                <button wire:click="upgradePlan('agency')" class="w-full py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-slate-950 text-xs font-bold shadow-md">
                    Upgrade to Agency ($49/mo)
                </button>
            @endif
        </div>
    </div>
</div>
