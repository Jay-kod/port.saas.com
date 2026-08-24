<?php

use function Livewire\Volt\{state, layout, title, usesPagination, computed};
use App\Models\Account;
use App\Models\User;

layout('layouts.super-admin');
title('Tenant Accounts Manager');

usesPagination();

state([
    'search' => '',
    'planFilter' => 'all', // all, free, pro, agency
    'showEditPlanModal' => false,
    'selectedAccountId' => null,
    'overridePlanSlug' => 'free',
    'successMessage' => '',
    'errorMessage' => '',
]);

$accounts = computed(function () {
    return Account::query()
        ->with(['owner', 'profiles', 'members'])
        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('owner', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
        })
        ->when($this->planFilter !== 'all', function ($query) {
            $query->where('plan_slug', $this->planFilter);
        })
        ->latest()
        ->paginate(12);
});

$openEditPlanModal = function ($accountId) {
    $account = Account::findOrFail($accountId);
    $this->selectedAccountId = $account->id;
    $this->overridePlanSlug = $account->plan_slug ?: 'free';
    $this->showEditPlanModal = true;
};

$updatePlan = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->selectedAccountId) {
        return;
    }

    $account = Account::findOrFail($this->selectedAccountId);
    $oldPlan = $account->plan_slug;
    $account->plan_slug = $this->overridePlanSlug;
    $account->save();

    $this->successMessage = "Account '{$account->name}' successfully updated from plan [{$oldPlan}] to [{$this->overridePlanSlug}].";
    $this->showEditPlanModal = false;
};

$resetAiUsage = function ($accountId) {
    $account = Account::findOrFail($accountId);
    $account->ai_generations_used_current_period = 0;
    $account->save();

    $this->successMessage = "Monthly AI resume quota reset to 0 for account '{$account->name}'.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    TENANCY DIRECTORY
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    ACCOUNTS & QUOTAS
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Tenant Accounts Manager
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Search multi-tenant workspaces, override subscription plans (Free / Pro / Agency), and manage AI generation quotas.
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

    @if($errorMessage)
    <div class="p-4 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-200 text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>{{ $errorMessage }}</span>
        </div>
        <button wire:click="$set('errorMessage', '')" class="text-red-400 hover:text-white underline text-xs cursor-pointer">Dismiss</button>
    </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="glass-card-dark rounded-3xl p-5 border border-amber-950/70 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search by workspace name or owner email..." 
                       class="w-full pl-10 pr-4 py-2 rounded-xl bg-black border border-amber-950 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 font-mono">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Plan Filter Pills -->
            <div class="flex items-center gap-1.5 font-mono text-xs overflow-x-auto pb-1 sm:pb-0">
                <button type="button" 
                        wire:click="$set('planFilter', 'all')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $planFilter === 'all' ? 'bg-amber-600 text-slate-950 shadow-md shadow-amber-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    All
                </button>
                <button type="button" 
                        wire:click="$set('planFilter', 'free')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $planFilter === 'free' ? 'bg-slate-800 text-white border border-slate-700' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Free
                </button>
                <button type="button" 
                        wire:click="$set('planFilter', 'pro')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $planFilter === 'pro' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Pro
                </button>
                <button type="button" 
                        wire:click="$set('planFilter', 'agency')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $planFilter === 'agency' ? 'bg-teal-600 text-white shadow-md shadow-teal-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Agency
                </button>
            </div>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/95 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Workspace / ID</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Owner Identity</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Plan Tier</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">AI Usage Quota</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Portfolios</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->accounts as $account)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">{{ $account->name }}</div>
                            <div class="text-[10px] text-slate-500">ID: #{{ $account->id }} &bull; {{ $account->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div>{{ $account->owner?->name ?? 'Unassigned' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $account->owner?->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $account->plan_slug === 'agency' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : ($account->plan_slug === 'pro' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-900 text-slate-400 border border-slate-800') }}">
                                {{ $account->plan_slug ?: 'free' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white">{{ $account->ai_generations_used_current_period }}</span>
                                <span class="text-slate-500">/ {{ $account->plan_slug === 'agency' ? 50 : ($account->plan_slug === 'pro' ? 20 : 3) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-[10px] text-slate-300">
                                {{ $account->profiles->count() }} profiles
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <button type="button" 
                                    wire:click="openEditPlanModal({{ $account->id }})" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-[10px] font-bold transition-all cursor-pointer">
                                Override Plan
                            </button>
                            <button type="button" 
                                    wire:click="resetAiUsage({{ $account->id }})" 
                                    class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 text-[10px] transition-all cursor-pointer"
                                    title="Reset monthly AI quota">
                                Reset Quota
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No tenant accounts found matching filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-amber-950/60 bg-black/60">
            {{ $this->accounts->links() }}
        </div>
    </div>

    <!-- Edit Plan Override Modal -->
    @if($showEditPlanModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fadeIn font-mono">
        <div class="relative w-full max-w-md p-6 rounded-3xl glass-card-dark bg-black/95 border border-amber-500/40 shadow-2xl space-y-5" @click.outside="$set('showEditPlanModal', false)">
            <div class="flex items-center justify-between border-b border-amber-950/60 pb-3">
                <h3 class="text-base font-bold text-white">Override Tenant Plan</h3>
                <button type="button" wire:click="$set('showEditPlanModal', false)" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <div class="space-y-4 text-xs">
                <p class="text-slate-400">Select a new subscription tier for this account. This overrides plan entitlements immediately without triggering Stripe billing.</p>

                <div class="space-y-2">
                    <label class="block font-bold text-slate-300">Plan Tier</label>
                    <select wire:model="overridePlanSlug" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-amber-950 text-white focus:outline-none focus:border-amber-500">
                        <option value="free">Free Tier (3 AI Resumes/mo, 1 Profile)</option>
                        <option value="pro">Pro Tier ($19/mo, 20 AI Resumes/mo, 1 Profile, Custom Domains)</option>
                        <option value="agency">Agency Tier ($79/mo, 50 AI Resumes/mo, Unlimited Profiles, 10 Seats, White-Label)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-amber-950/60">
                <button type="button" wire:click="$set('showEditPlanModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 text-xs font-semibold hover:bg-slate-800">
                    Cancel
                </button>
                <button type="button" wire:click="updatePlan" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold text-xs shadow-lg shadow-amber-950">
                    Save Plan Override
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
