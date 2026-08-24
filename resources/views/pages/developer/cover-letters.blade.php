<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\CoverLetterGeneration;
use App\Services\AiUsageGuard;
use App\Services\CoverLetterService;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Cover Letter AI');

state([
    'showModal' => false,
    'viewingId' => null,
    'job_title' => '',
    'company_name' => '',
    'job_description' => '',
    'isGenerating' => false,
    'savedMessage' => '',
    'errorMessage' => '',
]);

rules([
    'job_title' => 'required|string|max:255',
    'company_name' => 'required|string|max:255',
    'job_description' => 'required|string|min:20',
]);

$getCoverLetters = function () {
    $profile = Auth::user()?->profile;
    return $profile ? CoverLetterGeneration::where('profile_id', $profile->id)->orderBy('created_at', 'desc')->get() : collect();
};

$openCreateModal = function () {
    $this->reset(['job_title', 'company_name', 'job_description', 'savedMessage', 'errorMessage', 'viewingId']);
    $this->showModal = true;
};

$viewCoverLetter = function ($id) {
    $this->viewingId = $id;
};

$generateCoverLetter = function (AiUsageGuard $guard, CoverLetterService $generator) {
    $this->validate();
    $this->savedMessage = '';
    $this->errorMessage = '';

    $user = Auth::user();
    $account = $user?->defaultTenant ?? $user?->accounts()->first();
    $profile = $user?->profile;

    if (! $profile || ! $account) {
        $this->errorMessage = 'Profile or account not found.';
        return;
    }

    try {
        $guard->ensureCanGenerate($account);
    } catch (\Throwable $e) {
        $this->errorMessage = $e->getMessage();
        return;
    }

    $this->isGenerating = true;

    try {
        $content = $generator->generate($profile, $this->job_title, $this->company_name, $this->job_description);
        $guard->recordGeneration($account);

        CoverLetterGeneration::create([
            'profile_id' => $profile->id,
            'job_title' => $this->job_title,
            'company_name' => $this->company_name,
            'job_description' => $this->job_description,
            'content' => $content,
            'status' => 'completed',
        ]);

        $this->showModal = false;
        $this->savedMessage = 'Cover letter generated successfully!';
    } catch (\Throwable $e) {
        $this->errorMessage = 'Generation failed: ' . $e->getMessage();
    } finally {
        $this->isGenerating = false;
    }
};

$deleteCoverLetter = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    CoverLetterGeneration::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Cover letter deleted.';
    if ($this->viewingId === $id) {
        $this->viewingId = null;
    }
};

?>

<div class="space-y-8 max-w-6xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                    CAREER & AI SUITE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Cover Letter Generator
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Generate compelling, company-targeted cover letters tailored to your profile and the hiring manager's job description.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0" data-tooltip="Generate an AI-tailored cover letter for a job opening" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            <span>Write Cover Letter</span>
        </button>
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

    @php
        $letters = $this->getCoverLetters();
        $activeLetter = $viewingId ? $letters->firstWhere('id', $viewingId) : null;
    @endphp

    @if($letters->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">No Cover Letters Created</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Generate tailored, persuasive cover letters that emphasize your relevant skills for any specific target role.</p>
            <button wire:click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2 cursor-pointer" data-tooltip="Generate your first tailored cover letter">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>Generate First Cover Letter</span>
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($letters as $letter)
                <div class="glass-card glass-card-hover rounded-3xl p-6 border border-white/5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                {{ $letter->company_name }}
                            </span>
                            <button wire:click="deleteCoverLetter({{ $letter->id }})" wire:confirm="Are you sure you want to delete this cover letter?" class="p-1.5 rounded-lg bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer" data-tooltip="Delete cover letter archive">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-white font-heading">{{ $letter->job_title }}</h3>
                            <p class="text-xs text-slate-300 mt-2 line-clamp-4 leading-relaxed font-sans">
                                {{ $letter->content }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-500 text-[11px]">{{ $letter->created_at->format('M d, Y') }}</span>
                        <button wire:click="viewCoverLetter({{ $letter->id }})" class="text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1 cursor-pointer" data-tooltip="Read full letter and copy text">
                            <span>Read Full &rarr;</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- View Full Letter Modal --}}
    @if($activeLetter)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-3xl w-full max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <div>
                        <span class="text-[10px] font-mono font-bold text-yellow-400 uppercase tracking-wider">{{ $activeLetter->company_name }}</span>
                        <h3 class="text-lg font-bold font-heading text-white">{{ $activeLetter->job_title }} Cover Letter</h3>
                    </div>
                    <button wire:click="$set('viewingId', null)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close viewer">&times;</button>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 text-slate-200 text-xs sm:text-sm leading-relaxed whitespace-pre-line font-sans">
                    {{ $activeLetter->content }}
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('viewingId', null)" class="px-5 py-2.5 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs cursor-pointer" data-tooltip="Close cover letter">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Create Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        AI Cover Letter Engine
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close modal">&times;</button>
                </div>

                <form wire:submit.prevent="generateCoverLetter" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Target Job Title *</label>
                            <input type="text" wire:model="job_title" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Staff Distributed Systems Engineer" required />
                            @error('job_title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Company Name *</label>
                            <input type="text" wire:model="company_name" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. OpenAI, Stripe" required />
                            @error('company_name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Job Description *</label>
                        <textarea rows="6" wire:model="job_description" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none leading-relaxed" placeholder="Paste the job description and key expectations..." required></textarea>
                        @error('job_description') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs cursor-pointer" data-tooltip="Discard changes">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md flex items-center gap-2 cursor-pointer" data-tooltip="Run AI generation pipeline to write cover letter">
                            <span wire:loading.remove>Generate Cover Letter</span>
                            <span wire:loading>Writing Cover Letter...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

