<?php

use App\Models\Skill;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

$skills = computed(fn () => Skill::query()->orderBy('sort_order')->get());

?>

<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold">Skills</h1>

    <div class="mt-6 space-y-4">
        @foreach ($this->skills as $skill)
            <div>
                <div class="flex justify-between text-sm">
                    <span>{{ $skill->name }}</span>
                    <span style="color: var(--color-text-muted)">{{ $skill->proficiency }}%</span>
                </div>
                <div class="w-full h-2 rounded mt-1" style="background: var(--color-surface)">
                    <div class="h-2 rounded" style="width: {{ $skill->proficiency }}%; background: var(--color-primary)"></div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('home') }}" class="underline mt-10 inline-block">&larr; Back</a>
</div>
