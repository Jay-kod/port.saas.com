<?php

use App\Models\Certificate;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'certSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$certificate = computed(fn () => Certificate::query()->where('slug', $this->certSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden font-body">
    <div class="p-6 sm:p-10 rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        @php
            $slug = $this->profile?->slug ?? request()->route('slug') ?? request('slug');
            $slugParam = $slug ? ['slug' => $slug] : [];
            $backRoute = Route::has('certificates') ? route('certificates', $slugParam) : url('/certificates');
        @endphp
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-1.5 font-nav font-bold hover:underline mb-6 min-h-[44px]" style="color: var(--color-primary);">
            <span>&larr;</span>
            <span>Back to Certificates</span>
        </a>

        <div class="space-y-3">
            <h1 class="font-h1" style="color: var(--color-text);">
                {{ $this->certificate->title }}
            </h1>
            <p class="font-hero-sub font-semibold" style="color: var(--color-primary);">
                Issued by {{ $this->certificate->issuer }}
            </p>
            @if ($this->certificate->issue_date)
                <p class="font-caption" style="color: var(--color-text-muted);">
                    Issued: {{ $this->certificate->issue_date->format('F Y') }}
                </p>
            @endif
        </div>

        @if ($this->certificate->credential_url)
            <div class="mt-8 pt-6 border-t flex items-center" style="border-color: var(--color-border);">
                <a href="{{ $this->certificate->credential_url }}"
                   class="btn-primary min-h-[48px] px-8 rounded-2xl shadow-lg transition hover:scale-105 inline-flex items-center justify-center gap-2"
                   target="_blank" rel="noopener"
                   style="background: var(--color-primary); color: #000000;">
                    <span>Verify Credential Online</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
