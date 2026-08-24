<?php

use App\Models\Project;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{state, computed};

state(['slug' => null, 'projectSlug' => null]);

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$project = computed(fn () => Project::query()->where('slug', $this->projectSlug ?: $this->slug)->firstOrFail());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">{{ $this->project->title }}</h1>
    <p class="mt-2" style="color: var(--color-text-muted)">{{ $this->project->summary }}</p>
    <p class="mt-6">{{ $this->project->description }}</p>

    @if ($this->project->tech_stack)
        <div class="mt-6 flex gap-2 flex-wrap">
            @foreach ($this->project->tech_stack as $tech)
                <span class="text-xs px-2 py-1 rounded" style="background: var(--color-surface); border: 1px solid var(--color-border)">{{ $tech }}</span>
            @endforeach
        </div>
    @endif

    <div class="mt-6 flex gap-4">
        @if ($this->project->repo_url)
            <a href="{{ $this->project->repo_url }}" class="underline cursor-pointer" target="_blank" rel="noopener" data-tooltip="View source code on external repository">Repository</a>
        @endif
        @if ($this->project->live_url)
            <a href="{{ $this->project->live_url }}" class="underline cursor-pointer" target="_blank" rel="noopener" data-tooltip="Open live deployment in new tab">Live demo</a>
        @endif
    </div>

    <a href="{{ route('projects') }}" class="underline mt-10 inline-block cursor-pointer" data-tooltip="Return to projects showcase">&larr; Back to projects</a>
</div>
