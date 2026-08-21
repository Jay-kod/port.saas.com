<?php

use App\Models\Certificate;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'certSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$certificate = computed(fn () => Certificate::query()->where('slug', $this->certSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">{{ $this->certificate->title }}</h1>
    <p class="mt-2" style="color: var(--color-text-muted)">{{ $this->certificate->issuer }}</p>
    <p class="mt-2 text-sm">Issued {{ $this->certificate->issue_date?->format('M Y') }}</p>

    @if ($this->certificate->credential_url)
        <a href="{{ $this->certificate->credential_url }}" class="underline mt-4 inline-block" target="_blank" rel="noopener">View credential</a>
    @endif

    <a href="{{ route('certificates') }}" class="underline mt-10 inline-block block">&larr; Back to certificates</a>
</div>
