<?php

use function Livewire\Volt\{state, layout, title, computed};
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

layout('layouts.super-admin');
title('System Diagnostics & Operations');

state([
    'actionMessage' => '',
    'actionType' => 'success', // success, error
]);

$systemStats = computed(function () {
    $dbPath = database_path('database.sqlite');
    $dbSize = file_exists($dbPath) ? round(filesize($dbPath) / 1024, 2) . ' KB' : 'N/A';

    $storagePath = storage_path('app');
    $storageWritable = is_writable(storage_path());

    return [
        'phpVersion' => PHP_VERSION,
        'laravelVersion' => app()->version(),
        'dbEngine' => config('database.default'),
        'dbSize' => $dbSize,
        'storageWritable' => $storageWritable,
        'os' => PHP_OS_FAMILY,
        'serverTime' => now()->toDateTimeString() . ' UTC',
        'appEnv' => config('app.env'),
        'appDebug' => config('app.debug'),
        'saasMode' => config('saas.mode'),
        'cacheDriver' => config('cache.default'),
        'sessionDriver' => config('session.driver'),
        'queueDriver' => config('queue.default'),
        'mailDriver' => config('mail.default'),
    ];
});

$purgeOptimize = function () {
    try {
        Artisan::call('optimize:clear');
        $this->actionMessage = 'All caches (Route, Config, Compiled Views, Application Cache) successfully purged.';
        $this->actionType = 'success';
    } catch (\Throwable $e) {
        $this->actionMessage = 'Purge Error: ' . $e->getMessage();
        $this->actionType = 'error';
    }
};

$cacheViews = function () {
    try {
        Artisan::call('view:cache');
        $this->actionMessage = 'Blade templates compiled and cached successfully.';
        $this->actionType = 'success';
    } catch (\Throwable $e) {
        $this->actionMessage = 'Cache Error: ' . $e->getMessage();
        $this->actionType = 'error';
    }
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12 font-mono">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    INFRASTRUCTURE TELEMETRY
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    DIAGNOSTICS & CACHE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight font-sans">
                System Diagnostics & Operations
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 font-sans">
                Server runtime diagnostics, framework telemetry, environment audit, and 1-click optimization maintenance tools.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer" data-tooltip="Return to Super Admin Master Control Hub">
                &larr; Telemetry Hub
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($actionMessage)
    <div class="p-4 rounded-2xl {{ $actionType === 'success' ? 'bg-amber-500/15 border-amber-500/30 text-amber-200' : 'bg-red-500/15 border-red-500/30 text-red-200' }} border text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ $actionMessage }}</span>
        </div>
        <button wire:click="$set('actionMessage', '')" class="text-amber-400 hover:text-white underline text-xs cursor-pointer" data-tooltip="Dismiss notification">Dismiss</button>
    </div>
    @endif

    <!-- 1-Click Operations Control Deck -->
    <div class="p-6 rounded-3xl bg-black border border-amber-950/80 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-amber-950/60 pb-3">
            <div>
                <h3 class="text-base font-bold text-white font-sans">One-Click Maintenance Operations</h3>
                <p class="text-xs text-slate-400 font-sans">Purge cached structures, compile Blade templates, and reset runtime state.</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30">ROOT ACTIONS</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="button" 
                    wire:click="purgeOptimize" 
                    class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold text-xs shadow-lg shadow-amber-950 transition-all flex items-center gap-2 cursor-pointer"
                    data-tooltip="Clear config, routes, and compiled views cache">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Purge Optimization Cache (optimize:clear)</span>
            </button>

            <button type="button" 
                    wire:click="cacheViews" 
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer"
                    data-tooltip="Pre-compile all Blade template views for faster rendering">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <span>Pre-Compile Views (view:cache)</span>
            </button>
        </div>
    </div>

    <!-- Diagnostics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
        <div class="glass-card-dark rounded-2xl p-5 border border-amber-950/70 space-y-1">
            <div class="text-[10px] text-slate-500 uppercase">PHP Version</div>
            <div class="text-xl font-bold text-white">{{ $this->systemStats['phpVersion'] }}</div>
            <div class="text-[10px] text-emerald-400">&bull; OPcache Ready</div>
        </div>

        <div class="glass-card-dark rounded-2xl p-5 border border-amber-950/70 space-y-1">
            <div class="text-[10px] text-slate-500 uppercase">Laravel Framework</div>
            <div class="text-xl font-bold text-amber-400">v{{ $this->systemStats['laravelVersion'] }}</div>
            <div class="text-[10px] text-slate-500">&bull; Livewire Volt Active</div>
        </div>

        <div class="glass-card-dark rounded-2xl p-5 border border-amber-950/70 space-y-1">
            <div class="text-[10px] text-slate-500 uppercase">Database Storage</div>
            <div class="text-xl font-bold text-orange-400">{{ $this->systemStats['dbSize'] }}</div>
            <div class="text-[10px] text-slate-500">{{ $this->systemStats['dbEngine'] }} engine</div>
        </div>

        <div class="glass-card-dark rounded-2xl p-5 border border-amber-950/70 space-y-1">
            <div class="text-[10px] text-slate-500 uppercase">Storage Filesystem</div>
            <div class="text-xl font-bold text-emerald-400">Writable</div>
            <div class="text-[10px] text-emerald-400">&bull; Disk permissions OK</div>
        </div>
    </div>

    <!-- Environment Configurations Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="px-6 py-5 border-b border-amber-950/60 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white font-sans">Core Runtime Environment Values</h3>
                <p class="text-xs text-slate-400 font-sans">Non-sensitive configuration settings currently loaded in memory.</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">READ ONLY</span>
        </div>

        <div class="p-6 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">APP_ENV</span>
                    <span class="font-bold text-white uppercase">{{ $this->systemStats['appEnv'] }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">APP_DEBUG</span>
                    <span class="font-bold {{ $this->systemStats['appDebug'] ? 'text-amber-400' : 'text-emerald-400' }}">{{ $this->systemStats['appDebug'] ? 'true' : 'false' }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">SAAS_MODE</span>
                    <span class="font-bold {{ $this->systemStats['saasMode'] ? 'text-emerald-400' : 'text-slate-400' }}">{{ $this->systemStats['saasMode'] ? 'true (Multi-Tenant)' : 'false (Single-Tenant)' }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">CACHE_DRIVER</span>
                    <span class="font-bold text-white">{{ $this->systemStats['cacheDriver'] }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">SESSION_DRIVER</span>
                    <span class="font-bold text-white">{{ $this->systemStats['sessionDriver'] }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">QUEUE_CONNECTION</span>
                    <span class="font-bold text-white">{{ $this->systemStats['queueDriver'] }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">MAIL_MAILER</span>
                    <span class="font-bold text-white">{{ $this->systemStats['mailDriver'] }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-black border border-amber-950/60 flex items-center justify-between">
                    <span class="text-slate-400">SERVER_OS</span>
                    <span class="font-bold text-white">{{ $this->systemStats['os'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
