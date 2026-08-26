<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$projects = computed(fn () => Project::query()->orderBy('sort_order')->get());

?>

@php
    $slug = $this->profile?->slug ?? request()->route('slug') ?? request('slug');
    $slugParam = $slug ? ['slug' => $slug] : [];
    $hasSlugRoute = \Illuminate\Support\Facades\Route::has('tenant.home');
    $homeRoute = $hasSlugRoute && $slug ? route('tenant.home', $slugParam) : (Route::has('home') ? route('home') : url('/'));
    $aboutRoute = $hasSlugRoute && $slug ? route('about', $slugParam) : (Route::has('about') && !config('saas.mode') ? route('about') : '#about');
    $projectsRoute = $hasSlugRoute && $slug ? route('projects', $slugParam) : (Route::has('projects') && !config('saas.mode') ? route('projects') : '#projects');
    $skillsRoute = $hasSlugRoute && $slug ? route('skills', $slugParam) : (Route::has('skills') && !config('saas.mode') ? route('skills') : '#skills');
    $certsRoute = $hasSlugRoute && $slug ? route('certificates', $slugParam) : (Route::has('certificates') && !config('saas.mode') ? route('certificates') : '#certificates');
    $contactRoute = $hasSlugRoute && $slug ? route('contact', $slugParam) : (Route::has('contact') && !config('saas.mode') ? route('contact') : '#contact');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden font-body">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-10 rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-3">
            <h1 class="font-h1" style="color: var(--color-text);">
                Projects & Engineering Showcase
            </h1>
            <p class="font-hero-sub font-semibold" style="color: var(--color-primary);">
                Explore open-source work, full-stack applications, and technical architectures.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ $projectsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-bold transition shadow-sm shrink-0 inline-flex items-center justify-center" style="background: var(--color-primary); color: #000000;">Projects</a>
            <a href="{{ $skillsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ $certsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ $contactRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Projects Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8">
        @forelse ($this->projects as $project)
            @php
                $projRoute = Route::has('projects.show') ? route('projects.show', $hasSlugRoute && $slug ? ['slug' => $slug, 'projectSlug' => $project->slug] : ['slug' => $project->slug]) : '#';
            @endphp
            <a href="{{ $projRoute }}"
               class="p-6 sm:p-8 rounded-3xl border transition duration-200 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between group"
               style="background: var(--color-surface); border-color: var(--color-border);">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-h3 group-hover:underline" style="color: var(--color-text);">
                            {{ $project->title }}
                        </h2>
                        <svg class="w-5 h-5 opacity-60 group-hover:opacity-100 shrink-0 transition" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </div>
                    <p class="font-body text-gray-600 dark:text-gray-400 leading-relaxed" style="color: var(--color-text-muted);">
                        {{ $project->summary }}
                    </p>
                </div>

                @if ($project->tech_stack)
                    <div class="flex flex-wrap gap-2 mt-6 pt-4 border-t" style="border-color: var(--color-border);">
                        @foreach (is_array($project->tech_stack) ? $project->tech_stack : explode(',', $project->tech_stack) as $tag)
                            <span class="px-2.5 py-1 rounded-lg font-caption font-semibold" style="background: var(--color-background); color: var(--color-text-muted);">
                                {{ trim($tag) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </a>
        @empty
            <div class="col-span-full p-8 text-center rounded-3xl border font-caption" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                No projects found.
            </div>
        @endforelse
    </div>
</div>
