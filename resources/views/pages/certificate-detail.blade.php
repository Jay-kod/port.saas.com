<?php

use App\Models\Certificate;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'certSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$certificate = computed(fn () => Certificate::query()->where('slug', $this->certSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-6 w-full overflow-x-hidden">
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <a href="{{ route('certificates') }}" class="inline-flex items-center gap-1 text-xs font-semibold hover:underline mb-4 opacity-75" style="color: var(--color-primary);">
            &larr; Back to Certificates
        </a>

        <div class="space-y-3">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                {{ $this->certificate->title }}
            </h1>
            <p class="text-base sm:text-lg font-semibold" style="color: var(--color-primary);">
                Issued by {{ $this->certificate->issuer }}
            </p>
            @if ($this->certificate->issue_date)
                <p class="text-xs sm:text-sm" style="color: var(--color-text-muted);">
                    Issued: {{ $this->certificate->issue_date->format('F Y') }}
                </p>
            @endif
        </div>

        @if ($this->certificate->credential_url)
            <div class="mt-6 pt-4 border-t flex items-center" style="border-color: var(--color-border);">
                <a href="{{ $this->certificate->credential_url }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md transition hover:scale-105"
                   target="_blank" rel="noopener"
                   style="background: var(--color-primary); color: #000000;">
                    <span>Verify Credential Online</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
