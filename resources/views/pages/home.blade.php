<?php

use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    @if ($this->profile)
        <h1 class="text-4xl font-bold" style="color: var(--color-primary)">{{ $this->profile->full_name }}</h1>
        <p class="mt-2 text-xl" style="color: var(--color-text-muted)">{{ $this->profile->headline }}</p>
        <p class="mt-6">{{ $this->profile->bio }}</p>

        <nav class="mt-10 flex gap-4 flex-wrap">
            <a href="{{ route('about') }}" class="underline">About</a>
            <a href="{{ route('projects') }}" class="underline">Projects</a>
            <a href="{{ route('skills') }}" class="underline">Skills</a>
            <a href="{{ route('certificates') }}" class="underline">Certificates</a>
            <a href="{{ route('contact') }}" class="underline">Contact</a>
        </nav>
    @else
        <p>No profile has been set up yet. Visit <a href="/admin" class="underline">/admin</a> to get started.</p>
    @endif
</div>
