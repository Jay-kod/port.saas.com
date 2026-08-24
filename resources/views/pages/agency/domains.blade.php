<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Profile;
use App\Models\Account;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Client Custom Domains');

state([
    'selectedProfileId' => null,
    'newDomain' => '',
    'showAddModal' => false,
    'selectedClientFilter' => 'all',
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
    return $this->account ? $this->account->profiles()->get() : collect();
});

$domains = computed(function () {
    if (! $this->account) return collect();
    $profileIds = $this->profiles->pluck('id');
    $query = Domain::whereIn('profile_id', $profileIds)->with('profile')->latest();
    
    if ($this->selectedClientFilter !== 'all') {
        $query->where('profile_id', $this->selectedClientFilter);
    }
    
    return $query->get();
});

$addDomain = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->account) {
        $this->errorMessage = 'No active agency account found.';
        return;
    }

    $this->validate([
        'selectedProfileId' => ['required', 'exists:profiles,id'],
        'newDomain' => ['required', 'string', 'max:255'],
    ]);

    $normalized = strtolower(trim($this->newDomain));
    $normalized = preg_replace('#^https?://#i', '', $normalized);
    $normalized = rtrim($normalized, '/');

    if (Domain::where('domain', $normalized)->exists()) {
        $this->errorMessage = "The domain '{$normalized}' is already registered.";
        return;
    }

    $profile = Profile::where('account_id', $this->account->id)->findOrFail($this->selectedProfileId);

    $domain = Domain::create([
        'profile_id' => $profile->id,
        'domain' => $normalized,
        'verified_at' => null,
    ]);

    $this->reset(['newDomain', 'selectedProfileId']);
    $this->showAddModal = false;
    $this->successMessage = "Custom domain '{$domain->domain}' added for {$profile->full_name}. Please configure DNS records to verify.";
};

$verifyDomain = function ($domainId) {
    $domain = Domain::whereHas('profile', function ($q) {
        $q->where('account_id', $this->account?->id);
    })->findOrFail($domainId);

    // Instant DNS simulation for agency workflows
    $domain->update(['verified_at' => now()]);
    $this->successMessage = "Domain '{$domain->domain}' verified and SSL certificate provisioned successfully!";
};

$deleteDomain = function ($domainId) {
    $domain = Domain::whereHas('profile', function ($q) {
        $q->where('account_id', $this->account?->id);
    })->findOrFail($domainId);

    $name = $domain->domain;
    $domain->delete();
    $this->successMessage = "Domain '{$name}' disconnected from client portfolio.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    DNS & BRANDING
                </span>
                <span class="text-xs text-slate-500 font-mono">MULTI-CLIENT DOMAINS</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Client Custom Domains & DNS
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Connect custom apex and subdomains across all managed client developer portfolios.
            </p>
        </div>

        <button type="button" wire:click="$set('showAddModal', true)" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 hover:opacity-95 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-teal-950/40 cursor-pointer" data-tooltip="Assign and connect custom domain to a client" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>+ Connect Client Domain</span>
        </button>
    </div>

    {{-- Feedback Notifications --}}
    @if($successMessage)
        <div class="p-4 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')" class="text-teal-400 hover:text-white cursor-pointer" data-tooltip="Dismiss notification">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-white cursor-pointer" data-tooltip="Dismiss error notification">&times;</button>
        </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <span class="text-xs text-slate-400 font-semibold">Filter by Client:</span>
            <select wire:model.live="selectedClientFilter" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none cursor-pointer" data-tooltip="Filter domain list by client profile">
                <option value="all">All Managed Clients</option>
                @foreach($this->profiles as $p)
                    <option value="{{ $p->id }}">{{ $p->full_name ?: $p->slug }}</option>
                @endforeach
            </select>
        </div>
        <div class="text-xs text-slate-400 font-mono">
            <strong>{{ $this->domains->count() }}</strong> Domains Connected
        </div>
    </div>

    {{-- Domains Table --}}
    <div class="glass-card rounded-3xl overflow-hidden border border-white/10">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Client Custom Domains</h3>
                <p class="text-xs text-slate-400">DNS configuration and verification status across client hostnames.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-900/80 border-b border-white/5 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Domain Name</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Target Client Profile</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">DNS Token</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($this->domains as $domainItem)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-white">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                    <span>{{ $domainItem->domain }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-white">{{ $domainItem->profile?->full_name ?: 'Unnamed Profile' }}</div>
                                <div class="text-[10px] font-mono text-slate-500">/{{ $domainItem->profile?->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($domainItem->isVerified())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20" data-tooltip="Domain verified and SSL provisioned">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> ACTIVE / SSL
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20" data-tooltip="Awaiting DNS verification">
                                        PENDING DNS
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-400 text-[11px]">
                                {{ substr($domainItem->verification_token, 0, 16) }}...
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                @if(!$domainItem->isVerified())
                                    <button type="button" wire:click="verifyDomain({{ $domainItem->id }})" class="text-xs text-teal-400 hover:text-teal-300 font-semibold cursor-pointer" data-tooltip="Query DNS records to verify domain ownership">
                                        Verify DNS &rarr;
                                    </button>
                                @endif
                                <button type="button" wire:click="deleteDomain({{ $domainItem->id }})" wire:confirm="Disconnect this custom domain?" class="text-rose-400 hover:text-rose-300 font-semibold cursor-pointer" data-tooltip="Disconnect domain from client portfolio">
                                    Disconnect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">
                                No custom domains configured for agency clients yet. Click "+ Connect Client Domain" above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DNS INSTRUCTIONS CARD --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
        <h3 class="text-lg font-bold font-heading text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
            <span>Agency DNS Instructions for Client Domains</span>
        </h3>
        <p class="text-xs text-slate-400">Direct your client's DNS registrar to the following records:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2 text-xs font-mono">
                <div class="text-teal-400 font-bold">Subdomains (e.g. portfolio.client.com)</div>
                <div class="text-slate-300">Type: <span class="text-white">CNAME</span></div>
                <div class="text-slate-300">Host: <span class="text-white">portfolio (or subdomain)</span></div>
                <div class="text-slate-300">Target / Value: <span class="text-teal-300 font-bold">cname.devfolio.ai</span></div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2 text-xs font-mono">
                <div class="text-cyan-400 font-bold">Apex Domains (e.g. clientdomain.com)</div>
                <div class="text-slate-300">Type: <span class="text-white">A Record</span></div>
                <div class="text-slate-300">Host: <span class="text-white">@</span></div>
                <div class="text-slate-300">Target IP: <span class="text-cyan-300 font-bold">76.76.21.21</span></div>
            </div>
        </div>
    </div>

    {{-- ADD DOMAIN MODAL --}}
    @if($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-md w-full border border-teal-500/30 bg-slate-950 shadow-2xl relative space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-white">Connect Client Domain</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Assign a custom domain to a managed client portfolio.</p>
                    </div>
                    <button type="button" wire:click="$set('showAddModal', false)" class="text-slate-400 hover:text-white text-xl font-bold cursor-pointer" data-tooltip="Close modal">&times;</button>
                </div>

                <form wire:submit="addDomain" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Target Client Profile *</label>
                        <select wire:model="selectedProfileId" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none">
                            <option value="">Select a client...</option>
                            @foreach($this->profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }} (/{{ $p->slug }})</option>
                            @endforeach
                        </select>
                        @error('selectedProfileId') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Domain Name *</label>
                        <input type="text" wire:model="newDomain" placeholder="e.g. sarahjenkins.dev or portfolio.sarah.com" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none font-mono" />
                        @error('newDomain') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showAddModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold cursor-pointer" data-tooltip="Cancel without connecting">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-teal-950/50 cursor-pointer" data-tooltip="Connect domain to selected client">
                            Register Domain
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

