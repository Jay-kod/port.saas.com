<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('White-Label & Branding');

state([
    'customBrandName' => fn () => (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)?->custom_brand_name ?? Auth::user()?->defaultTenant?->custom_brand_name ?? '',
    'customLogoPath' => fn () => (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)?->custom_logo_path ?? Auth::user()?->defaultTenant?->custom_logo_path ?? '',
    'hidePlatformBranding' => fn () => (bool)((session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)?->hide_platform_branding ?? Auth::user()?->defaultTenant?->hide_platform_branding ?? false),
    'successMessage' => '',
    'errorMessage' => '',
]);

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$saveBranding = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->account) {
        $this->errorMessage = 'No active agency account found.';
        return;
    }

    $this->validate([
        'customBrandName' => ['nullable', 'string', 'max:100'],
        'customLogoPath' => ['nullable', 'string', 'max:500'],
        'hidePlatformBranding' => ['boolean'],
    ]);

    $this->account->update([
        'custom_brand_name' => $this->customBrandName,
        'custom_logo_path' => $this->customLogoPath,
        'hide_platform_branding' => $this->hidePlatformBranding,
    ]);

    $this->successMessage = 'White-label agency branding settings updated successfully!';
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    WHITE-LABEL
                </span>
                <span class="text-xs text-slate-500 font-mono">BRAND CUSTOMIZATION</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                White-Label & Agency Branding
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Customize agency badges, logo watermarks, and suppress platform branding across all client portfolios.
            </p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left 2 Columns: Branding Form --}}
        <div class="lg:col-span-2 glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Agency Branding Identity</h3>
                <p class="text-xs text-slate-400 mt-0.5">Control how your agency appears in client portfolio footers and meta tags.</p>
            </div>

            <form wire:submit="saveBranding" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Agency / Studio Brand Name</label>
                    <input type="text" wire:model.live.debounce.200ms="customBrandName" placeholder="e.g. Apex Talent Agency" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                    <span class="text-[11px] text-slate-500 mt-1 block">Displayed in portfolio footers as "Managed by [Brand Name]"</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Agency Logo Image URL</label>
                    <input type="text" wire:model.live.debounce.200ms="customLogoPath" placeholder="https://youragency.com/logo.png" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none font-mono" />
                    <span class="text-[11px] text-slate-500 mt-1 block">Provide a public HTTPS link to your agency mark or transparent PNG logo.</span>
                </div>

                <div class="p-5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer" data-tooltip="Hide DevFolio badge across all client portfolios">
                        <input type="checkbox" wire:model.live="hidePlatformBranding" class="mt-0.5 w-4 h-4 rounded text-teal-500 bg-slate-900 border-slate-800 focus:ring-teal-500" />
                        <div>
                            <span class="text-xs font-bold text-white block">Suppress Platform Branding</span>
                            <span class="text-[11px] text-slate-400 mt-0.5 block">
                                Completely hide default "Built with DevFolio" attribution badge across all client portfolios.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-white/5 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-teal-950/40 cursor-pointer" data-tooltip="Save custom brand name and logo configuration">
                        Save White-Label Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Right Column: Interactive Live Preview --}}
        <div class="space-y-6">
            <div class="glass-card rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono uppercase text-slate-400 font-semibold">Live Preview</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-teal-500/20 text-teal-300 border border-teal-500/30">CLIENT FOOTER</span>
                </div>

                <div class="p-6 rounded-2xl bg-slate-950 border border-white/10 text-center space-y-3">
                    <div class="text-[11px] text-slate-500 font-mono">&copy; {{ date('Y') }} Alex Developer. All rights reserved.</div>
                    
                    @if($hidePlatformBranding)
                        @if($customBrandName)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-white/10 text-slate-300 text-xs font-medium">
                                @if($customLogoPath)
                                    <img src="{{ $customLogoPath }}" alt="Logo" class="w-3.5 h-3.5 object-contain" onerror="this.style.display='none'" />
                                @endif
                                <span>Powered by <strong class="text-teal-400">{{ $customBrandName }}</strong></span>
                            </div>
                        @else
                            <div class="text-[10px] text-slate-600 italic">No attribution badge (Completely White-Labeled)</div>
                        @endif
                    @else
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-emerald-500/20 text-slate-300 text-xs font-medium">
                            <span>Built with <strong class="text-emerald-400">DevFolio.AI</strong></span>
                        </div>
                    @endif
                </div>

                <div class="text-[11px] text-slate-400 leading-relaxed">
                    This badge automatically renders at the bottom of each client's public website, matching their chosen color theme.
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6 space-y-3 border border-teal-500/20 bg-teal-950/10">
                <div class="flex items-center gap-2 text-teal-400 text-xs font-bold font-mono">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>AGENCY PLAN UNLOCKED</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    White-labeling is included on the Agency plan with zero DevFolio backlink requirements.
                </p>
            </div>
        </div>
    </div>
</div>

