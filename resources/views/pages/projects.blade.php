<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$projects = computed(fn () => Project::query()->orderBy('sort_order')->get());

?>

@php
    $slugParam = config('saas.mode') && $this->profile ? ['slug' => $this->profile->slug] : [];
    $homeRoute = config('saas.mode') && $this->profile ? route('tenant.home', $slugParam) : route('home');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 w-full overflow-x-hidden">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                Projects & Engineering Showcase
            </h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--color-primary);">
                Explore open-source work, full-stack applications, and technical architectures.
            </p>
        </div>

        <div class="mt-6 pt-4 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ route('about', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ route('projects', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0" style="background: var(--color-primary); color: #000000;">Projects</a>
            <a href="{{ route('skills', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ route('certificates', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ route('contact', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Projects Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        @forelse ($this->projects as $project)
            <a href="{{ route('projects.show', $project->slug) }}"
               class="p-5 sm:p-6 rounded-2xl border transition duration-200 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between group"
               style="background: var(--color-surface); border-color: var(--color-border);">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-base sm:text-lg group-hover:underline" style="color: var(--color-text);">
                            {{ $project->title }}
                        </h3>
                        <svg class="w-4 h-4 opacity-50 group-hover:opacity-100 shrink-0 transition" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </div>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--color-text-muted);">
                        {{ $project->summary }}
                    </p>
                </div>

                @if ($project->tech_stack)
                    <div class="flex flex-wrap gap-1.5 mt-4 pt-3 border-t" style="border-color: var(--color-border);">
                        @foreach (is_array($project->tech_stack) ? $project->tech_stack : explode(',', $project->tech_stack) as $tag)
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-medium" style="background: var(--color-background); color: var(--color-text-muted);">
                                {{ trim($tag) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </a>
        @empty
            <div class="col-span-full p-8 text-center rounded-2xl border text-xs sm:text-sm" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                No projects found.
            </div>
        @endforelse
    </div>
</div>
