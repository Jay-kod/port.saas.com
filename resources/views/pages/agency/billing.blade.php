<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Account;
use App\Models\AiSetting;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Agency Billing & Quotas');

state([
    'successMessage' => '',
    'errorMessage' => '',
]);

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$isOwner = computed(function () {
    $user = Auth::user();
    if (! $this->account || ! $user) return false;
    return $this->account->owner_user_id === $user->id || $this->account->getUserRole($user) === 'owner';
});

$aiUsage = computed(function () {
    $account = $this->account;
    if (! $account) return ['used' => 0, 'limit' => 50, 'hasByok' => false, 'percentage' => 0];

    $planSlug = $account->plan_slug ?: 'agency';
    $limit = config("plans.{$planSlug}.ai_resumes_monthly_limit", 50);
    $used = $account->ai_generations_used_current_period ?? 0;
    $hasByok = AiSetting::where('account_id', $account->id)->where('is_active', true)->whereNotNull('api_key')->exists();
    $percentage = $limit > 0 ? min(100, (int)round(($used / $limit) * 100)) : 0;

    return [
        'used' => $used,
        'limit' => $limit,
        'hasByok' => $hasByok,
        'percentage' => $percentage,
        'planSlug' => $planSlug,
    ];
});

$redirectToPortal = function () {
    if (! $this->isOwner) {
        $this->errorMessage = 'Billing portal access is restricted to the Agency account owner.';
        return;
    }

    $account = $this->account;
    if ($account && $account->stripe_id) {
        try {
            return redirect()->away($account->billingPortalUrl(route('agency.billing')));
        } catch (\Throwable $e) {
            $this->errorMessage = 'Could not redirect to Stripe Customer Portal. Using sandbox billing management.';
        }
    } else {
        $this->successMessage = 'Agency subscription is active in sandbox mode. Stripe customer portal is enabled upon live checkout.';
    }
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    AGENCY SUBSCRIPTION
                </span>
                <span class="text-xs text-slate-500 font-mono">BILLING & CAPACITY</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Agency Billing & Multi-Client Quotas
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage agency subscription tier, team seats, and aggregate multi-client AI generation allowances.
            </p>
        </div>

        @if($this->isOwner)
            <button type="button" wire:click="redirectToPortal" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <span>Stripe Customer Portal</span>
            </button>
        @endif
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

    @if(!$this->isOwner)
        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5 text-slate-400 text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>You are viewing billing in collaborator mode. Subscription management and payment methods are restricted to the Agency Owner.</span>
        </div>
    @endif

    {{-- Aggregate AI Usage Meter Card --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    <span>Multi-Client Monthly AI Usage Meter</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">Aggregate AI Resume tailoring generations across all managed client developer portfolios.</p>
            </div>

            <div class="flex items-center gap-2">
                @if($this->aiUsage['hasByok'])
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        BYOK ACTIVE &bull; UNLIMITED
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        MONTHLY ALLOWANCE
                    </span>
                @endif
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between text-xs font-mono">
                <span class="text-slate-300">
                    Generations Used: <strong class="text-white font-bold">{{ $this->aiUsage['used'] }}</strong> / {{ $this->aiUsage['hasByok'] ? '∞' : $this->aiUsage['limit'] }}
                </span>
                <span class="text-teal-400 font-bold">
                    {{ $this->aiUsage['hasByok'] ? 'No Cap' : ($this->aiUsage['limit'] - $this->aiUsage['used']) . ' remaining' }}
                </span>
            </div>

            <div class="w-full bg-slate-900 border border-slate-800 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 h-full rounded-full transition-all duration-500" style="width: {{ $this->aiUsage['hasByok'] ? '100' : $this->aiUsage['percentage'] }}%"></div>
            </div>

            <div class="flex justify-between items-center text-[11px] text-slate-500">
                <span>Cycles reset on the 1st of every month</span>
                <a href="{{ route('developer.ai-settings') }}" class="text-teal-400 hover:underline">Configure BYOK Keys &rarr;</a>
            </div>
        </div>
    </div>

    {{-- Plan Comparison Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-3xl p-6 space-y-4 opacity-60">
            <div class="text-xs font-mono uppercase text-slate-400">Starter Tier</div>
            <div class="text-2xl font-bold font-heading text-white">Free</div>
            <p class="text-xs text-slate-400">For individual developers starting their career portfolio.</p>
            <ul class="space-y-2 text-xs text-slate-400 pt-2 border-t border-white/5">
                <li>1 Developer Profile</li>
                <li>3 AI Resumes / mo</li>
                <li>DevFolio Subdomain</li>
            </ul>
        </div>

        <div class="glass-card rounded-3xl p-6 space-y-4 opacity-60">
            <div class="text-xs font-mono uppercase text-slate-400">Professional</div>
            <div class="text-2xl font-bold font-heading text-white">$19 <span class="text-xs text-slate-500 font-normal">/ mo</span></div>
            <p class="text-xs text-slate-400">For senior engineers actively hunting high-paying roles.</p>
            <ul class="space-y-2 text-xs text-slate-400 pt-2 border-t border-white/5">
                <li>1 Developer Profile</li>
                <li>15 AI Resumes / mo</li>
                <li>1 Custom Apex Domain</li>
            </ul>
        </div>

        <div class="glass-card rounded-3xl p-6 space-y-4 border-2 border-teal-500 bg-teal-950/20 relative">
            <div class="absolute -top-3 right-6 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-500 text-slate-950">
                CURRENT ACTIVE TIER
            </div>
            <div class="text-xs font-mono uppercase text-teal-400 font-bold">Agency Studio</div>
            <div class="text-3xl font-extrabold font-heading text-white">$79 <span class="text-xs text-slate-400 font-normal">/ mo</span></div>
            <p class="text-xs text-slate-300">For agencies, dev studios, and placement firms.</p>
            <ul class="space-y-2 text-xs text-slate-200 pt-2 border-t border-white/10">
                <li class="flex items-center gap-2"><span class="text-teal-400 font-bold">&check;</span> <strong>Unlimited</strong> Client Portfolios</li>
                <li class="flex items-center gap-2"><span class="text-teal-400 font-bold">&check;</span> <strong>10</strong> Team Collaborator Seats</li>
                <li class="flex items-center gap-2"><span class="text-teal-400 font-bold">&check;</span> <strong>50</strong> AI Tailored Resumes / mo</li>
                <li class="flex items-center gap-2"><span class="text-teal-400 font-bold">&check;</span> Full <strong>White-Label</strong> Branding</li>
                <li class="flex items-center gap-2"><span class="text-teal-400 font-bold">&check;</span> Multi-Client Custom Domains</li>
            </ul>
        </div>
    </div>
</div>
