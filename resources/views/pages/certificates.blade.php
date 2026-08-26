<?php

use App\Models\Certificate;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$certificates = computed(fn () => Certificate::query()->orderBy('sort_order')->get());

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
                Certifications & Accreditations
            </h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--color-primary);">
                Professional licenses, cloud certifications, and technical accomplishments.
            </p>
        </div>

        <div class="mt-6 pt-4 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ $projectsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ $skillsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ $certsRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0" style="background: var(--color-primary); color: #000000;">Certificates</a>
            <a href="{{ $contactRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Certificates Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        @forelse ($this->certificates as $certificate)
            <a href="{{ route('certificates.show', $certificate->slug) }}"
               class="p-5 sm:p-6 rounded-2xl border transition duration-200 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between group"
               style="background: var(--color-surface); border-color: var(--color-border);">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-base sm:text-lg group-hover:underline" style="color: var(--color-text);">
                            {{ $certificate->title }}
                        </h3>
                        <svg class="w-4 h-4 opacity-50 group-hover:opacity-100 shrink-0 transition" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold" style="color: var(--color-primary);">
                        {{ $certificate->issuer }}
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t flex items-center justify-between text-[11px]" style="border-color: var(--color-border); color: var(--color-text-muted);">
                    <span>{{ $certificate->issue_date?->format('M Y') ?? 'Verified' }}</span>
                    <span class="underline font-semibold" style="color: var(--color-primary);">View Credential &rarr;</span>
                </div>
            </a>
        @empty
            <div class="col-span-full p-8 text-center rounded-2xl border text-xs sm:text-sm" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                No certificates recorded yet.
            </div>
        @endforelse
    </div>
</div>
