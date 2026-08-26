<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'projectSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$project = computed(fn () => Project::query()->where('slug', $this->projectSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-6 w-full overflow-x-hidden">
    {{-- Header Card --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <a href="{{ route('projects') }}" class="inline-flex items-center gap-1 text-xs font-semibold hover:underline mb-4 opacity-75" style="color: var(--color-primary);">
            &larr; Back to Projects
        </a>

        <div class="space-y-3">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                {{ $this->project->title }}
            </h1>
            <p class="text-sm sm:text-base leading-relaxed" style="color: var(--color-text-muted);">
                {{ $this->project->summary }}
            </p>
        </div>

        {{-- Tech Stack Pills --}}
        @if ($this->project->tech_stack)
            <div class="mt-6 flex gap-2 flex-wrap pt-4 border-t" style="border-color: var(--color-border);">
                @foreach (is_array($this->project->tech_stack) ? $this->project->tech_stack : explode(',', $this->project->tech_stack) as $tech)
                    <span class="text-xs px-3 py-1 rounded-lg font-medium" style="background: var(--color-background); border: 1px solid var(--color-border); color: var(--color-text);">
                        {{ trim($tech) }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Action Links --}}
        <div class="mt-6 flex flex-wrap gap-3">
            @if ($this->project->live_url)
                <a href="{{ $this->project->live_url }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md transition hover:scale-105"
                   target="_blank" rel="noopener"
                   style="background: var(--color-primary); color: #000000;">
                    <span>Live Demo</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif

            @if ($this->project->repo_url)
                <a href="{{ $this->project->repo_url }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold border transition hover:opacity-80"
                   target="_blank" rel="noopener"
                   style="background: var(--color-background); border-color: var(--color-border); color: var(--color-text);">
                    <span>GitHub Repository</span>
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Detailed Description Card --}}
    @if ($this->project->description)
        <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border space-y-4"
             style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
            <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">
                Project Overview & Architecture
            </h2>
            <div class="text-xs sm:text-sm leading-relaxed whitespace-pre-line" style="color: var(--color-text-muted);">
                {{ $this->project->description }}
            </div>
        </div>
    @endif
</div>
