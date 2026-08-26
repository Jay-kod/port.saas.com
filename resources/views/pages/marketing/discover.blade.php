<?php

use App\Models\Profile;
use App\Models\Skill;
use function Livewire\Volt\{state, computed};

state([
    'search' => '',
    'selectedCategory' => '',
]);

$profiles = computed(function () {
    $query = Profile::query()
        ->where('is_published', true)
        ->where('is_discoverable', true)
        ->with(['skills', 'projects', 'theme'])
        ->latest();

    if (! empty($this->search)) {
        $term = '%' . $this->search . '%';
        $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', $term)
                ->orWhere('headline', 'like', $term)
                ->orWhere('bio', 'like', $term)
                ->orWhere('location', 'like', $term)
                ->orWhereHas('skills', fn ($sq) => $sq->where('name', 'like', $term));
        });
    }

    if (! empty($this->selectedCategory)) {
        $query->whereHas('skills', fn ($sq) => $sq->where('category', $this->selectedCategory));
    }

    return $query->paginate(12);
});

$categories = computed(function () {
    return ['Frontend', 'Backend', 'DevOps', 'Mobile', 'Database', 'Full-Stack'];
});

?>

<div class="min-h-screen text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 flex flex-col justify-between overflow-x-hidden w-full font-body">
    <x-marketing-header />

    {{-- Hero & Search Header --}}
    <section class="relative section-marketing px-4 sm:px-6 w-full">
        <div class="max-w-5xl mx-auto text-center space-y-4">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full font-caption font-bold bg-amber-500/10 border border-amber-500/30 text-amber-500 dark:text-amber-400 mb-2">
                <span>🌐</span>
                <span>Verified Developer Directory</span>
            </div>

            <h1 class="font-h1 text-gray-900 dark:text-white">
                Discover world-class <br class="hidden sm:inline" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 via-emerald-500 to-cyan-500 dark:from-amber-400 dark:via-emerald-400 dark:to-cyan-400">
                    software engineers & designers
                </span>
            </h1>

            <p class="font-hero-sub text-gray-600 dark:text-gray-400 max-w-[72ch] mx-auto px-2">
                Explore portfolios, verifiable project implementations, and verified developer skillsets built on DevFolio.
            </p>

            {{-- Search & Filter Controls --}}
            <div class="pt-6 max-w-2xl mx-auto space-y-4">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name, skill (e.g. React, Laravel), role or location..."
                        class="w-full pl-5 pr-12 min-h-[48px] rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90 font-body text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 shadow-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    />
                    @if($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute right-4 top-3 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white text-base cursor-pointer min-h-[24px]" data-tooltip="Clear search query">✕</button>
                    @endif
                </div>

                {{-- Category Tags (≥44px Touch Targets) --}}
                <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="$set('selectedCategory', '')"
                        class="min-h-[44px] px-4 rounded-xl font-caption font-bold transition cursor-pointer inline-flex items-center justify-center {{ empty($selectedCategory) ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                        data-tooltip="Show all developer profiles"
                    >
                        All Developers
                    </button>
                    @foreach($this->categories as $cat)
                        <button
                            type="button"
                            wire:click="$set('selectedCategory', '{{ $cat }}')"
                            class="min-h-[44px] px-4 rounded-xl font-caption font-bold transition cursor-pointer inline-flex items-center justify-center {{ $selectedCategory === $cat ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                            data-tooltip="Filter directory by {{ $cat }}"
                        >
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Developer Profiles Grid --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full">
        @if($this->profiles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($this->profiles as $profile)
                    <div class="p-6 sm:p-8 rounded-3xl bg-white/60 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 hover:border-amber-500/40 backdrop-blur-sm transition flex flex-col justify-between group shadow-lg">
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="font-h3 text-gray-900 dark:text-white group-hover:text-amber-500 dark:group-hover:text-amber-400 transition">
                                        {{ $profile->full_name }}
                                    </h2>
                                    <p class="font-caption font-semibold text-amber-600 dark:text-amber-400/90 mt-0.5">
                                        {{ $profile->headline }}
                                    </p>
                                </div>

                                @if($profile->location)
                                    <span class="font-caption text-gray-600 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-xl shrink-0">
                                        📍 {{ $profile->location }}
                                    </span>
                                @endif
                            </div>

                            @if($profile->bio)
                                <p class="font-body text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed">
                                    {{ $profile->bio }}
                                </p>
                            @endif

                            {{-- Skills Chips --}}
                            @if($profile->skills->count() > 0)
                                <div class="flex flex-wrap gap-2 pt-2">
                                    @foreach($profile->skills->take(4) as $skill)
                                        <span class="px-3 py-1 rounded-lg font-caption font-bold bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 text-gray-700 dark:text-gray-300">
                                            {{ $skill->name }}
                                        </span>
                                    @endforeach
                                    @if($profile->skills->count() > 4)
                                        <span class="px-2.5 py-1 rounded-lg font-caption text-gray-600 dark:text-gray-500 font-semibold">
                                            +{{ $profile->skills->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-800/60 flex items-center justify-between font-caption">
                            <span class="text-gray-600 dark:text-gray-500 font-medium">
                                {{ $profile->projects->count() }} {{ \Illuminate\Support\Str::plural('Project', $profile->projects->count()) }}
                            </span>

                            <a
                                href="{{ route('tenant.home', ['slug' => $profile->slug]) }}"
                                class="inline-flex items-center gap-1 font-bold text-amber-500 dark:text-amber-400 group-hover:text-amber-400 group-hover:translate-x-0.5 transition cursor-pointer min-h-[44px]"
                                data-tooltip="Open {{ $profile->full_name }}'s public portfolio"
                            >
                                <span>View Portfolio</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12">
                {{ $this->profiles->links() }}
            </div>
        @else
            <div class="p-16 rounded-3xl border border-dashed border-gray-300 dark:border-gray-800 text-center max-w-lg mx-auto space-y-4">
                <div class="text-4xl">🔍</div>
                <h2 class="font-h3 text-gray-900 dark:text-white">No Developers Found</h2>
                <p class="font-body text-gray-600 dark:text-gray-500">
                    No discoverable profiles matched your search criteria. Try a different search term or category filter.
                </p>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-gray-800/80 py-12 sm:py-20 px-4 sm:px-6 bg-gray-50 dark:bg-gray-950 font-caption text-gray-600 dark:text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                &copy; {{ date('Y') }} DevFolio AI Platform.
            </div>
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-6 gap-y-2">
                <a href="{{ route('home') }}" class="hover:text-gray-900 dark:hover:text-gray-400 cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Return to DevFolio Homepage">Home</a>
                <a href="{{ route('discover') }}" class="text-amber-500 dark:text-amber-400 font-bold cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Browse public developer directory">Discover</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-900 dark:hover:text-gray-400 cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="View pricing tiers and features">Pricing</a>
                <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-500 hover:text-emerald-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Developer Workspace">Developer Login</a>
                <a href="{{ route('agency.login') }}" target="_blank" rel="noopener noreferrer" class="text-teal-500 hover:text-teal-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Agency Hub">Agency Hub</a>
                <a href="{{ route('super-admin.login') }}" target="_blank" rel="noopener noreferrer" class="text-amber-500 hover:text-amber-400 font-semibold transition cursor-pointer min-h-[44px] inline-flex items-center" data-tooltip="Sign in to Super Admin Portal">Super Admin</a>
            </div>
        </div>
    </footer>
</div>
