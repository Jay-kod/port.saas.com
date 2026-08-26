<?php

use App\Models\Experience;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$experiences = computed(fn () => Experience::query()->orderByDesc('start_date')->get());

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
                About {{ $this->profile?->full_name }}
            </h1>
            <p class="font-hero-sub font-semibold" style="color: var(--color-primary);">
                {{ $this->profile?->headline }}
            </p>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-bold transition shadow-sm shrink-0 inline-flex items-center justify-center" style="background: var(--color-primary); color: #000000;">About</a>
            <a href="{{ $projectsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ $skillsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ $certsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ $contactRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Bio Card --}}
    <div class="p-6 sm:p-10 rounded-3xl border space-y-4" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <h2 class="font-h2" style="color: var(--color-text);">Background & Biography</h2>
        <p class="font-body max-w-[72ch] leading-relaxed whitespace-pre-line" style="color: var(--color-text-muted);">
            {{ $this->profile?->bio }}
        </p>
    </div>

    {{-- Experience Timeline --}}
    <div class="space-y-6">
        <h2 class="font-h2" style="color: var(--color-text);">
            Career & Work Experience
        </h2>

        <div class="space-y-4">
            @forelse ($this->experiences as $experience)
                <div class="p-6 sm:p-8 rounded-3xl border transition duration-200 hover:shadow-lg"
                     style="background: var(--color-surface); border-color: var(--color-border);">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="font-h3" style="color: var(--color-text);">{{ $experience->title }}</h3>
                            <p class="font-caption font-semibold mt-0.5" style="color: var(--color-primary);">{{ $experience->company }}</p>
                        </div>
                        <span class="font-caption px-3 py-1 rounded-full w-fit mt-1 sm:mt-0 font-medium"
                              style="background: var(--color-background); color: var(--color-text-muted);">
                            {{ $experience->start_date?->format('M Y') }} – {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}
                        </span>
                    </div>
                    @if ($experience->description)
                        <p class="mt-4 font-body leading-relaxed max-w-[72ch]" style="color: var(--color-text-muted);">
                            {{ $experience->description }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center rounded-3xl border font-caption" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                    No experience records listed.
                </div>
            @endforelse
        </div>
    </div>
</div>
