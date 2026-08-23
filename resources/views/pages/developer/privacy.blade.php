<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Account;
use App\Services\GdprService;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Privacy & GDPR Data Rights');

state([
    'confirmDelete' => '',
    'errorMessage' => '',
    'savedMessage' => '',
]);

$getAccount = function () {
    return Auth::user()?->defaultTenant ?? Auth::user()?->accounts()->first();
};

$exportData = function (GdprService $gdpr) {
    $account = $this->getAccount();
    if (! $account) return;

    $data = $gdpr->exportAccountData($account);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $filename = 'devfolio-export-' . ($account->name ? \Illuminate\Support\Str::slug($account->name) : 'data') . '-' . now()->format('Y-m-d') . '.json';

    return response()->streamDownload(function () use ($json) {
        echo $json;
    }, $filename, [
        'Content-Type' => 'application/json',
    ]);
};

?>

<div class="space-y-8 max-w-4xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    WORKSPACE & OPERATIONS
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Privacy & Data Rights (GDPR)
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Download a complete portable archive of your technical portfolios, resumes, and opportunity data.
            </p>
        </div>

        <button wire:click="exportData" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <span>Download Data Archive</span>
        </button>
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

    {{-- Section 1: Data Portability --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4 border border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div>
                <h3 class="text-base font-bold font-heading text-white">Right to Data Portability (Article 20)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Export all account records into machine-readable JSON format.</p>
            </div>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            Your export contains your profile bio, all showcase projects, experience history, skills matrix, verified certificates, generated AI resumes & cover letters, and tracked job opportunities.
        </p>

        <div class="pt-2">
            <button wire:click="exportData" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-200 text-xs font-semibold inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                <span>Export JSON Data Package</span>
            </button>
        </div>
    </div>

    {{-- Section 2: Privacy Guarantee --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4 border border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <div>
                <h3 class="text-base font-bold font-heading text-white">Zero Third-Party Data Sharing</h3>
                <p class="text-xs text-slate-400 mt-0.5">Your personal data is never sold, indexed without consent, or mined.</p>
            </div>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            All AI queries are processed under strict privacy boundaries and zero data retention agreements with AI providers. API keys provided under BYOK are encrypted with AES-256 and only used in real time.
        </p>
    </div>
</div>
