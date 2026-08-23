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

<div class="min-h-screen text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 flex flex-col justify-between">
    <x-marketing-header />

    {{-- Hero & Search Header --}}
    <section class="relative pt-16 pb-12 px-6">
        <div class="max-w-5xl mx-auto text-center space-y-4">

            <h1 class="text-4xl sm:text-6xl font-black text-gray-900 dark:text-white tracking-tight">
                Discover world-class <br class="hidden sm:inline" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 via-emerald-500 to-cyan-500 dark:from-amber-400 dark:via-emerald-400 dark:to-cyan-400">
                    software engineers & designers
                </span>
            </h1>

            <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Explore portfolios, verifiable project implementations, and verified developer skillsets built on DevFolio.
            </p>

            {{-- Search & Filter Controls --}}
            <div class="pt-6 max-w-2xl mx-auto space-y-4">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name, skill (e.g. React, Laravel, Docker), role or location..."
                        class="w-full pl-5 pr-12 py-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 shadow-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    />
                    @if($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute right-4 top-3.5 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white text-sm">✕</button>
                    @endif
                </div>

                {{-- Category Tags --}}
                <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="$set('selectedCategory', '')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ empty($selectedCategory) ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        All Developers
                    </button>
                    @foreach($this->categories as $cat)
                        <button
                            type="button"
                            wire:click="$set('selectedCategory', '{{ $cat }}')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $selectedCategory === $cat ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                        >
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Developer Profiles Grid --}}
    <main class="max-w-7xl mx-auto px-6 py-8 flex-1 w-full">
        @if($this->profiles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->profiles as $profile)
                    <div class="p-6 rounded-3xl bg-white/60 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 hover:border-amber-500/40 backdrop-blur-sm transition flex flex-col justify-between group shadow-lg">
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-white group-hover:text-amber-500 dark:group-hover:text-amber-400 transition">
                                        {{ $profile->full_name }}
                                    </h3>
                                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400/90 mt-0.5">
                                        {{ $profile->headline }}
                                    </p>
                                </div>

                                @if($profile->location)
                                    <span class="text-[11px] font-medium text-gray-600 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg">
                                        📍 {{ $profile->location }}
                                    </span>
                                @endif
                            </div>

                            @if($profile->bio)
                                <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed">
                                    {{ $profile->bio }}
                                </p>
                            @endif

                            {{-- Skills Chips --}}
                            @if($profile->skills->count() > 0)
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    @foreach($profile->skills->take(4) as $skill)
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 text-gray-700 dark:text-gray-300">
                                            {{ $skill->name }}
                                        </span>
                                    @endforeach
                                    @if($profile->skills->count() > 4)
                                        <span class="px-2 py-1 rounded-lg text-[10px] text-gray-600 dark:text-gray-500 font-semibold">
                                            +{{ $profile->skills->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-800/60 flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-500 font-medium">
                                {{ $profile->projects->count() }} {{ \Illuminate\Support\Str::plural('Project', $profile->projects->count()) }}
                            </span>

                            <a
                                href="{{ route('tenant.home', ['slug' => $profile->slug]) }}"
                                class="inline-flex items-center gap-1 font-bold text-amber-400 group-hover:text-amber-300 group-hover:translate-x-0.5 transition"
                            >
                                <span>View Portfolio</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $this->profiles->links() }}
            </div>
        @else
            <div class="p-16 rounded-3xl border border-dashed border-gray-300 dark:border-gray-800 text-center max-w-lg mx-auto space-y-3">
                <div class="text-3xl">🔍</div>
                <h4 class="text-base font-bold text-gray-900 dark:text-white">No Developers Found</h4>
                <p class="text-xs text-gray-600 dark:text-gray-500">
                    No discoverable profiles matched your search criteria. Try a different search term or category filter.
                </p>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-gray-800/80 py-10 px-6 bg-gray-50 dark:bg-gray-950 text-xs text-gray-600 dark:text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} DevFolio AI Platform.
            </div>
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-3 sm:gap-5">
                <a href="{{ route('home') }}" class="hover:text-gray-900 dark:hover:text-gray-400">Home</a>
                <a href="{{ route('discover') }}" class="text-amber-500 dark:text-amber-400 font-semibold">Discover</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-900 dark:hover:text-gray-400">Pricing</a>
                <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="text-emerald-500 hover:text-emerald-400 font-medium transition">Developer Login</a>
                <a href="{{ route('agency.login') }}" target="_blank" rel="noopener noreferrer" class="text-teal-500 hover:text-teal-400 font-medium transition">Agency Hub</a>
                <a href="{{ route('super-admin.login') }}" target="_blank" rel="noopener noreferrer" class="text-amber-500 hover:text-amber-400 font-medium transition">Super Admin</a>
            </div>
        </div>
    </footer>
</div>
