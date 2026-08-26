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

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden font-body">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-10 rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-3">
            <h1 class="font-h1" style="color: var(--color-text);">
                Skills & Technical Expertise
            </h1>
            <p class="font-hero-sub font-semibold" style="color: var(--color-primary);">
                Programming languages, frameworks, developer tools, and cloud platforms.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ $projectsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ $skillsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-bold transition shadow-sm shrink-0 inline-flex items-center justify-center" style="background: var(--color-primary); color: #000000;">Skills</a>
            <a href="{{ $certsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ $contactRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Skills Grid --}}
    <div class="p-6 sm:p-10 rounded-3xl border space-y-6"
         style="background: var(--color-surface); border-color: var(--color-border);">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8">
            @forelse ($this->skills as $skill)
                <div class="space-y-2 p-4 sm:p-5 rounded-2xl" style="background: var(--color-background);">
                    <div class="flex justify-between font-caption font-bold">
                        <span style="color: var(--color-text);">{{ $skill->name }}</span>
                        <span style="color: var(--color-primary);">{{ $skill->proficiency }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full overflow-hidden" style="background: rgba(255, 255, 255, 0.08);">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width: {{ $skill->proficiency }}%; background: var(--color-primary);"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 font-caption" style="color: var(--color-text-muted);">
                    No skills recorded.
                </div>
            @endforelse
        </div>
    </div>
</div>
