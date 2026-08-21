<?php

use App\Models\Certificate;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$certificates = computed(fn () => Certificate::query()->orderBy('sort_order')->get());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">Certificates</h1>

    <div class="mt-6 grid gap-4">
        @foreach ($this->certificates as $certificate)
            <a href="{{ route('certificates.show', $certificate->slug) }}" class="block p-4 rounded border" style="border-color: var(--color-border); background: var(--color-surface)">
                <p class="font-semibold">{{ $certificate->title }}</p>
                <p class="text-sm" style="color: var(--color-text-muted)">{{ $certificate->issuer }}</p>
            </a>
        @endforeach
    </div>

    <a href="{{ route('home') }}" class="underline mt-10 inline-block">&larr; Back</a>
</div>
