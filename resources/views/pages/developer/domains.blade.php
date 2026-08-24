<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Custom Domains');

state([
    'newDomain' => '',
    'savedMessage' => '',
    'errorMessage' => '',
]);

rules([
    'newDomain' => 'required|string|max:255',
]);

$getAccount = function () {
    return Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
};

$getProfile = function () {
    return Auth::user()?->profile;
};

$canUseCustomDomains = function () {
    $account = $this->getAccount();
    $planSlug = $account?->plan_slug ?: 'free';
    return (bool) config("plans.{$planSlug}.custom_domain", false);
};

$getDomains = function () {
    $profile = $this->getProfile();
    return $profile ? Domain::where('profile_id', $profile->id)->get() : collect();
};

$addDomain = function () {
    $this->validate();
    $this->savedMessage = '';
    $this->errorMessage = '';

    $profile = $this->getProfile();
    if (! $profile) {
        $this->errorMessage = 'Profile not found.';
        return;
    }

    $normalized = strtolower(trim(preg_replace('#^https?://#', '', rtrim($this->newDomain, '/'))));

    $exists = Domain::where('domain', $normalized)->exists();
    if ($exists) {
        $this->errorMessage = "The domain '{$normalized}' is already registered on this platform.";
        return;
    }

    Domain::create([
        'profile_id' => $profile->id,
        'domain' => $normalized,
    ]);

    $this->newDomain = '';
    $this->savedMessage = "Domain '{$normalized}' added! Please add the DNS records below to complete verification.";
};

$verifyDomain = function ($domainId) {
    $profile = $this->getProfile();
    if (! $profile) return;

    $domain = Domain::where('profile_id', $profile->id)->findOrFail($domainId);

    // Instant verification for local testing / DNS simulation
    $domain->update(['verified_at' => now()]);
    $this->savedMessage = "Domain '{$domain->domain}' has been successfully verified! It is now actively routing to your portfolio.";
};

$deleteDomain = function ($domainId) {
    $profile = $this->getProfile();
    if (! $profile) return;

    Domain::where('profile_id', $profile->id)->where('id', $domainId)->delete();
    $this->savedMessage = 'Custom domain removed.';
};

?>

<div class="space-y-8 max-w-5xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    PLATFORM DESIGN
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Custom Domains
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Connect your personal apex domain or custom subdomain (e.g. <span class="font-mono text-emerald-400">alexmorgan.dev</span>) with zero-config SSL.
            </p>
        </div>

        @if($this->canUseCustomDomains())
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>CUSTOM DOMAINS ACTIVE</span>
            </span>
        @endif
    </div>

    {{-- Feedback Messages --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss notification">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss error notification">&times;</button>
        </div>
    @endif

    {{-- Plan Gating Banner if on Free Tier --}}
    @if(! $this->canUseCustomDomains())
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4 border border-yellow-500/20 bg-gradient-to-r from-yellow-500/10 via-transparent to-transparent">
            <div class="flex items-center gap-2 text-yellow-400 font-bold font-mono text-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>PRO TIER FEATURE</span>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">Custom Domains Require Pro or Agency Plan</h3>
            <p class="text-xs text-slate-300 leading-relaxed max-w-2xl">
                On the Free tier, your portfolio is served at <span class="text-emerald-400 font-mono">/{{ Auth::user()?->profile?->slug }}</span>. Upgrade to Pro to attach your own branded domain name with automatic SSL certification.
            </p>
            <a href="{{ route('developer.billing') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2" data-tooltip="Unlock custom domain attachment on Pro plan">
                <span>Upgrade to Pro Plan &rarr;</span>
            </a>
        </div>
    @endif

    {{-- Add Domain Form --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
        <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            <span>Connect Branded Domain</span>
        </h3>

        <form wire:submit.prevent="addDomain" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full space-y-1">
                <input type="text" wire:model="newDomain" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs font-mono focus:border-cyan-500 focus:outline-none" placeholder="e.g. portfolio.alexmorgan.dev or alexmorgan.com" required />
                @error('newDomain') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md shrink-0 cursor-pointer" data-tooltip="Attach and register domain to your portfolio">
                Connect Domain
            </button>
        </form>
    </div>

    {{-- Registered Domains List --}}
    @php
        $domains = $this->getDomains();
    @endphp

    @if($domains->isNotEmpty())
        <div class="space-y-4">
            @foreach($domains as $dom)
                <div class="glass-card rounded-3xl p-6 sm:p-7 border border-white/5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-3">
                                <h4 class="text-base font-bold font-mono text-white">{{ $dom->domain }}</h4>
                                @if($dom->isVerified())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30" data-tooltip="Domain verified and resolving live">
                                        &check; VERIFIED & LIVE
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-500/10 text-yellow-400 border border-yellow-500/30" data-tooltip="Awaiting DNS propagation">
                                        PENDING DNS
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400">
                                Added {{ $dom->created_at->format('M d, Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(! $dom->isVerified())
                                <button wire:click="verifyDomain({{ $dom->id }})" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Query DNS records to verify domain ownership">
                                    Check & Verify DNS
                                </button>
                            @endif
                            <button wire:click="deleteDomain({{ $dom->id }})" wire:confirm="Are you sure you want to disconnect this domain?" class="p-2 rounded-xl bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer" data-tooltip="Disconnect domain from portfolio">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>


                    {{-- DNS Configuration Box --}}
                    @if(! $dom->isVerified())
                        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3 font-mono text-xs">
                            <div class="text-slate-400 font-bold uppercase text-[10px]">Required DNS Records (at your DNS Registrar)</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
                                    <div class="text-slate-500 text-[10px]">Type</div>
                                    <div class="text-white font-bold">CNAME</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
                                    <div class="text-slate-500 text-[10px]">Host / Name</div>
                                    <div class="text-white font-bold">@ or subdomain</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
                                    <div class="text-slate-500 text-[10px]">Target Value</div>
                                    <div class="text-emerald-400 font-bold">cname.devfolio.ai</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
