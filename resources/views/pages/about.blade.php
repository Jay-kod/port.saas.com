<?php

use App\Models\Experience;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$experiences = computed(fn () => Experience::query()->orderByDesc('start_date')->get());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">About {{ $this->profile?->full_name }}</h1>
    <p class="mt-4">{{ $this->profile?->bio }}</p>

    <h2 class="text-xl font-semibold mt-10">Experience</h2>
    <div class="mt-4 space-y-6">
        @foreach ($this->experiences as $experience)
            <div>
                <p class="font-semibold">{{ $experience->title }} — {{ $experience->company }}</p>
                <p class="text-sm" style="color: var(--color-text-muted)">
                    {{ $experience->start_date?->format('M Y') }} –
                    {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}
                </p>
                <p class="mt-1">{{ $experience->description }}</p>
            </div>
        @endforeach
    </div>

    <a href="{{ route('home') }}" class="underline mt-10 inline-block">&larr; Back</a>
</div>
