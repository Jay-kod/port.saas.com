<?php

use App\Models\Experience;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$experiences = computed(fn () => Experience::query()->orderByDesc('start_date')->get());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 w-full overflow-x-hidden">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                About {{ $this->profile?->full_name }}
            </h1>
            <p class="text-sm sm:text-base font-medium" style="color: var(--color-primary);">
                {{ $this->profile?->headline }}
            </p>
        </div>

        <div class="mt-6 pt-4 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ route('about') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0" style="background: var(--color-primary); color: #000000;">About</a>
            <a href="{{ route('projects') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ route('skills') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ route('certificates') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ route('contact') }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Contact</a>
        </div>
    </div>

    {{-- Bio Card --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border space-y-3" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">Background & Biography</h2>
        <p class="text-xs sm:text-sm leading-relaxed whitespace-pre-line" style="color: var(--color-text-muted);">
            {{ $this->profile?->bio }}
        </p>
    </div>

    {{-- Experience Timeline --}}
    <div class="space-y-4">
        <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">
            Career & Work Experience
        </h2>

        <div class="space-y-4">
            @forelse ($this->experiences as $experience)
                <div class="p-5 sm:p-6 rounded-2xl border transition duration-200 hover:shadow-lg"
                     style="background: var(--color-surface); border-color: var(--color-border);">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
                        <div>
                            <p class="font-bold text-base sm:text-lg" style="color: var(--color-text);">{{ $experience->title }}</p>
                            <p class="text-xs sm:text-sm font-semibold" style="color: var(--color-primary);">{{ $experience->company }}</p>
                        </div>
                        <span class="text-[11px] sm:text-xs px-2.5 py-1 rounded-full w-fit mt-1 sm:mt-0 font-medium"
                              style="background: var(--color-background); color: var(--color-text-muted);">
                            {{ $experience->start_date?->format('M Y') }} – {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}
                        </span>
                    </div>
                    @if ($experience->description)
                        <p class="mt-3 text-xs sm:text-sm leading-relaxed" style="color: var(--color-text-muted);">
                            {{ $experience->description }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center rounded-2xl border text-xs sm:text-sm" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                    No experience records listed.
                </div>
            @endforelse
        </div>
    </div>
</div>
