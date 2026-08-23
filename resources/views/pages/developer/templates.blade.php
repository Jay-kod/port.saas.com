<?php

use function Livewire\Volt\{state, layout, title};
use App\Models\Template;

layout('layouts.dashboard');
title('Resume PDF Templates');

state([
    'templates' => fn () => Template::whereNull('account_id')->orWhere('account_id', auth()->user()?->defaultTenant?->id ?? 1)->get(),
]);

?>

<div class="space-y-8 max-w-5xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    PLATFORM DESIGN
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Resume PDF Templates
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Browse PDF styling engines used by the AI Resume Tailor to render ATS-compliant documents.
            </p>
        </div>

        <a href="{{ route('developer.resumes') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            <span>Tailor a Resume</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($templates as $template)
            <div class="glass-card glass-card-hover rounded-3xl p-6 sm:p-7 border border-white/5 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            {{ strtoupper($template->slug) }}
                        </span>
                        <span class="text-xs font-mono text-emerald-400 font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>ACTIVE PDF ENGINE</span>
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold font-heading text-white">{{ $template->name }}</h3>
                        <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                            {{ $template->description ?: 'Clean, ATS-optimized layout engineered for technical candidates and engineering leadership roles.' }}
                        </p>
                    </div>

                    {{-- Mini Mockup Preview --}}
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5 font-mono text-[10px]">
                        <div class="h-2 w-1/3 bg-emerald-500/60 rounded"></div>
                        <div class="h-1.5 w-1/2 bg-slate-700 rounded"></div>
                        <div class="h-px bg-white/10 my-2"></div>
                        <div class="space-y-1.5">
                            <div class="h-1.5 w-full bg-slate-800 rounded"></div>
                            <div class="h-1.5 w-4/5 bg-slate-800 rounded"></div>
                        </div>
                        <div class="pt-2 flex gap-2">
                            <div class="h-4 w-12 bg-emerald-950 border border-emerald-500/30 rounded text-[8px] text-emerald-400 flex items-center justify-center">Skill A</div>
                            <div class="h-4 w-12 bg-emerald-950 border border-emerald-500/30 rounded text-[8px] text-emerald-400 flex items-center justify-center">Skill B</div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                    <span class="text-slate-500 text-[11px]">View: {{ $template->blade_view }}</span>
                    <a href="{{ route('developer.resumes') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                        <span>Use in Generator &rarr;</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
