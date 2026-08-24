<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\AiSetting;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('AI Provider Settings (BYOK)');

state([
    'provider' => function () {
        $account = Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
        $setting = $account ? AiSetting::where('account_id', $account->id)->first() : null;
        return $setting?->provider ?? 'openai';
    },
    'api_key' => '',
    'model' => function () {
        $account = Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
        $setting = $account ? AiSetting::where('account_id', $account->id)->first() : null;
        return $setting?->model ?? '';
    },
    'is_active' => function () {
        $account = Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
        $setting = $account ? AiSetting::where('account_id', $account->id)->first() : null;
        return (bool) ($setting?->is_active ?? true);
    },
    'hasExistingKey' => function () {
        $account = Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
        $setting = $account ? AiSetting::where('account_id', $account->id)->first() : null;
        return (bool) (!empty($setting?->api_key));
    },
    'savedMessage' => '',
]);

rules([
    'provider' => 'required|string|in:openai,anthropic',
    'api_key' => 'nullable|string|max:500',
    'model' => 'nullable|string|max:100',
    'is_active' => 'boolean',
]);

$saveSettings = function () {
    $this->validate();
    $account = Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
    if (! $account) return;

    $setting = AiSetting::firstOrNew(['account_id' => $account->id]);
    $setting->provider = $this->provider;
    if (!empty(trim($this->api_key))) {
        $setting->api_key = trim($this->api_key);
        $this->hasExistingKey = true;
    }
    $setting->model = $this->model ?: null;
    $setting->is_active = (bool) $this->is_active;
    $setting->save();

    $this->api_key = '';
    $this->savedMessage = 'AI Provider credentials saved successfully!';
};

?>

<div class="space-y-8 max-w-4xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                    CAREER & AI SUITE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                AI Provider Settings (BYOK)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Bring Your Own Key (BYOK) for OpenAI or Anthropic to bypass monthly generation quotas and use custom LLM models.
            </p>
        </div>

        @if($hasExistingKey)
            <div class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 shrink-0" data-tooltip="Your account is exempt from AI usage limits" data-tooltip-pos="bottom">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>BYOK ACTIVE (UNLIMITED)</span>
            </div>
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

    {{-- BYOK Perks Callout --}}
    <div class="p-5 rounded-3xl bg-gradient-to-r from-yellow-500/10 via-emerald-500/5 to-transparent border border-yellow-500/20 text-xs space-y-2">
        <div class="flex items-center gap-2 text-yellow-400 font-bold font-mono">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            <span>BRING-YOUR-OWN-KEY EXEMPTION</span>
        </div>
        <p class="text-slate-300 leading-relaxed">
            When you provide an active OpenAI or Anthropic API key, all monthly AI generation quotas on your account are waived. Generations will be billed directly through your own provider account.
        </p>
    </div>

    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
        <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>LLM Provider Configuration</span>
        </h3>

        <form wire:submit.prevent="saveSettings" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">AI Provider *</label>
                    <select wire:model="provider" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none">
                        <option value="openai">OpenAI (GPT-4o, GPT-4o-mini)</option>
                        <option value="anthropic">Anthropic (Claude 3.5 Sonnet, Claude 3 Opus)</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Model Name Override (Optional)</label>
                    <input type="text" wire:model="model" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="e.g. gpt-4o, claude-3-5-sonnet-20241022" />
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">
                    API Key {{ $hasExistingKey ? '(Configured & Encrypted)' : '*' }}
                </label>
                <input type="password" wire:model="api_key" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="{{ $hasExistingKey ? 'Leave blank to keep existing encrypted key...' : 'sk-...' }}" />
                <p class="text-[11px] text-slate-500">Stored using Laravel AES-256 encryption. Never exposed to public users or client-side scripts.</p>
            </div>

            <label class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3 cursor-pointer" data-tooltip="Toggle BYOK key for generation pipelines">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                <div>
                    <div class="text-xs font-semibold text-white">Enable BYOK Key for Generations</div>
                    <div class="text-[11px] text-slate-400">Route resume and cover letter pipelines through this custom key.</div>
                </div>
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Encrypt and save API credentials">
                    Save AI Settings
                </button>
            </div>
        </form>
    </div>
</div>

