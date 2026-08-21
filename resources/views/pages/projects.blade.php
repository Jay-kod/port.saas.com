<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$projects = computed(fn () => Project::query()->orderBy('sort_order')->get());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">Projects</h1>

    <div class="mt-6 grid gap-6">
        @foreach ($this->projects as $project)
            <a href="{{ route('projects.show', $project->slug) }}" class="block p-4 rounded border" style="border-color: var(--color-border); background: var(--color-surface)">
                <p class="font-semibold">{{ $project->title }}</p>
                <p class="text-sm mt-1" style="color: var(--color-text-muted)">{{ $project->summary }}</p>
            </a>
        @endforeach
    </div>

    <a href="{{ route('home') }}" class="underline mt-10 inline-block">&larr; Back</a>
</div>
