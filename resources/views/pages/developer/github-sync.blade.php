<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\GithubSetting;
use App\Services\GitHubSyncService;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('GitHub Sync Studio');

state([
    'username' => fn () => Auth::user()?->profile?->githubSetting?->username ?? '',
    'access_token' => '',
    'auto_sync' => fn () => (bool) (Auth::user()?->profile?->githubSetting?->auto_sync ?? false),
    'last_synced_at' => fn () => Auth::user()?->profile?->githubSetting?->last_synced_at?->toDayDateTimeString(),
    'isSyncing' => false,
    'savedMessage' => '',
    'errorMessage' => '',
]);

rules([
    'username' => 'required|string|max:255',
    'access_token' => 'nullable|string|max:255',
    'auto_sync' => 'boolean',
]);

$saveSettings = function () {
    $this->validate();
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $setting = GithubSetting::firstOrNew(['profile_id' => $profile->id]);
    $setting->username = $this->username;
    if ($this->access_token) {
        $setting->access_token = $this->access_token;
    }
    $setting->auto_sync = (bool) $this->auto_sync;
    $setting->save();

    $this->savedMessage = 'GitHub credentials saved successfully!';
};

$syncNow = function () {
    $this->savedMessage = '';
    $this->errorMessage = '';

    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $setting = GithubSetting::where('profile_id', $profile->id)->first();
    if (! $setting || ! $setting->username) {
        $this->errorMessage = 'Please configure and save your GitHub username first.';
        return;
    }

    $this->isSyncing = true;

    try {
        $syncService = new GitHubSyncService($setting);
        $count = $syncService->sync();

        $setting->update(['last_synced_at' => now()]);
        $this->last_synced_at = now()->toDayDateTimeString();
        $this->savedMessage = "GitHub synchronization complete! Synced {$count} public repositories into your Showcase Projects.";
    } catch (\Throwable $e) {
        $this->errorMessage = 'Sync failed: ' . $e->getMessage();
    } finally {
        $this->isSyncing = false;
    }
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
                GitHub Repository Sync
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Connect your GitHub profile to automatically synchronize repositories and convert them into portfolio showcase projects.
            </p>
        </div>

        <button wire:click="syncNow" wire:loading.attr="disabled" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            <span wire:loading.remove>Sync Now</span>
            <span wire:loading>Syncing Repositories...</span>
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

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-slate-400 hover:text-white">&times;</button>
        </div>
    @endif

    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>GitHub Connection Settings</span>
            </h3>

            @if($last_synced_at)
                <span class="text-xs font-mono text-slate-400">
                    Last Synced: <span class="text-emerald-400 font-bold">{{ $last_synced_at }}</span>
                </span>
            @endif
        </div>

        <form wire:submit.prevent="saveSettings" class="space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">GitHub Username *</label>
                <div class="flex items-center">
                    <span class="px-3 py-2.5 rounded-l-xl bg-slate-950 border border-r-0 border-slate-800 text-slate-500 text-xs font-mono">github.com/</span>
                    <input type="text" wire:model="username" class="w-full px-4 py-2.5 rounded-r-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:border-emerald-500 focus:outline-none" placeholder="torvalds" required />
                </div>
                @error('username') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">Personal Access Token (Optional for higher API rate limits)</label>
                <input type="password" wire:model="access_token" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx" />
                <p class="text-[11px] text-slate-500">Encrypted at rest with AES-256. Required only for private repos or 5,000 req/hr rate limits.</p>
            </div>

            <label class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3 cursor-pointer">
                <input type="checkbox" wire:model="auto_sync" class="rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                <div>
                    <div class="text-xs font-semibold text-white">Enable Automated Nightly Synchronization</div>
                    <div class="text-[11px] text-slate-400">Keep your portfolio project descriptions and stars up to date automatically.</div>
                </div>
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md">
                    Save GitHub Settings
                </button>
            </div>
        </form>
    </div>
</div>
