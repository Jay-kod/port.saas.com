<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'projectSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$project = computed(fn () => Project::query()->where('slug', $this->projectSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden font-body">
    {{-- Header Card --}}
    <div class="p-6 sm:p-10 rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        @php
            $slug = $this->profile?->slug ?? request()->route('slug') ?? request('slug');
            $slugParam = $slug ? ['slug' => $slug] : [];
            $backRoute = Route::has('projects') ? route('projects', $slugParam) : url('/projects');
        @endphp
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-1.5 font-nav font-bold hover:underline mb-6 min-h-[44px]" style="color: var(--color-primary);">
            <span>&larr;</span>
            <span>Back to Projects</span>
        </a>

        <div class="space-y-4">
            <h1 class="font-h1" style="color: var(--color-text);">
                {{ $this->project->title }}
            </h1>
            <p class="font-hero-sub leading-relaxed max-w-[72ch]" style="color: var(--color-text-muted);">
                {{ $this->project->summary }}
            </p>
        </div>

        {{-- Tech Stack Pills --}}
        @if ($this->project->tech_stack)
            <div class="mt-8 flex gap-2 flex-wrap pt-6 border-t" style="border-color: var(--color-border);">
                @foreach (is_array($this->project->tech_stack) ? $this->project->tech_stack : explode(',', $this->project->tech_stack) as $tech)
                    <span class="font-caption px-3 py-1.5 rounded-xl font-semibold" style="background: var(--color-background); border: 1px solid var(--color-border); color: var(--color-text);">
                        {{ trim($tech) }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Action Links (≥44px Touch Targets & 48px Height) --}}
        <div class="mt-8 flex flex-wrap gap-4">
            @if ($this->project->live_url)
                <a href="{{ $this->project->live_url }}"
                   class="btn-primary min-h-[48px] px-8 rounded-2xl shadow-lg transition hover:scale-105 inline-flex items-center justify-center gap-2"
                   target="_blank" rel="noopener"
                   style="background: var(--color-primary); color: #000000;">
                    <span>Live Demo</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif

            @if ($this->project->repo_url)
                <a href="{{ $this->project->repo_url }}"
                   class="btn-secondary min-h-[48px] px-8 rounded-2xl border transition hover:opacity-80 inline-flex items-center justify-center gap-2"
                   target="_blank" rel="noopener"
                   style="background: var(--color-background); border-color: var(--color-border); color: var(--color-text);">
                    <span>GitHub Repository</span>
                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Detailed Description Card --}}
    @if ($this->project->description)
        <div class="p-6 sm:p-10 rounded-3xl border space-y-4"
             style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
            <h2 class="font-h2" style="color: var(--color-text);">
                Project Overview & Architecture
            </h2>
            <div class="font-body leading-relaxed max-w-[72ch] whitespace-pre-line" style="color: var(--color-text-muted);">
                {{ $this->project->description }}
            </div>
        </div>
    @endif
</div>
