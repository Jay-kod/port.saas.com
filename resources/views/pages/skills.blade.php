<?php

use App\Models\Skill;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$skills = computed(fn () => Skill::query()->orderByDesc('proficiency')->get());

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

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 w-full overflow-x-hidden">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                Skills & Technical Expertise
            </h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--color-primary);">
                Programming languages, frameworks, developer tools, and cloud platforms.
            </p>
        </div>

        <div class="mt-6 pt-4 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ $projectsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ $skillsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0" style="background: var(--color-primary); color: #000000;">Skills</a>
            <a href="{{ $certsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ $contactRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Skills Grid --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border space-y-6"
         style="background: var(--color-surface); border-color: var(--color-border);">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
            @forelse ($this->skills as $skill)
                <div class="space-y-1.5 p-3.5 rounded-xl" style="background: var(--color-background);">
                    <div class="flex justify-between text-xs sm:text-sm font-bold">
                        <span style="color: var(--color-text);">{{ $skill->name }}</span>
                        <span style="color: var(--color-primary);">{{ $skill->proficiency }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full overflow-hidden" style="background: rgba(255, 255, 255, 0.08);">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width: {{ $skill->proficiency }}%; background: var(--color-primary);"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-xs sm:text-sm" style="color: var(--color-text-muted);">
                    No skills recorded.
                </div>
            @endforelse
        </div>
    </div>
</div>
