<?php

use App\Models\Certificate;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Services\CurrentProfileResolver;
use function Livewire\Volt\{computed};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());
$featuredProjects = computed(fn () => Project::query()->orderBy('sort_order')->take(4)->get());
$topSkills = computed(fn () => Skill::query()->orderByDesc('proficiency')->take(8)->get());
$recentExperiences = computed(fn () => Experience::query()->orderByDesc('start_date')->take(3)->get());
$certificatesCount = computed(fn () => Certificate::query()->count());

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden">
    @if ($this->profile)
        {{-- Hero Header Card --}}
        <div class="p-6 sm:p-10 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
             style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
            
            {{-- Background Subtle Glow --}}
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full blur-3xl pointer-events-none opacity-20"
                 style="background: var(--color-primary);"></div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-8 text-center sm:text-left relative z-10">
                @if ($this->profile->avatar_path)
                    <img src="{{ Storage::url($this->profile->avatar_path) }}" alt="{{ $this->profile->full_name }}"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl sm:rounded-3xl object-cover border-2 shadow-lg shrink-0"
                         style="border-color: var(--color-primary);">
                @else
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl sm:rounded-3xl flex items-center justify-center text-3xl font-extrabold text-white shadow-lg shrink-0"
                         style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary, #3b82f6));">
                        {{ strtoupper(substr($this->profile->full_name ?? 'D', 0, 1)) }}
                    </div>
                @endif

                <div class="space-y-2.5 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                            {{ $this->profile->full_name }}
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/15 border border-emerald-500/30 text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Available</span>
                        </span>
            {{-- Profile Header Layout --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold"
                         style="background: var(--color-background); border: 1px solid var(--color-border); color: var(--color-primary);">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background: var(--color-primary);"></span>
                        <span>Available for Opportunities</span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight" style="color: var(--color-text);">
                        {{ $this->profile?->full_name }}
                    </h1>
                    <p class="text-base sm:text-xl font-medium" style="color: var(--color-primary);">
                        {{ $this->profile?->headline }}
                    </p>
                    @if ($this->profile?->location)
                        <p class="text-xs sm:text-sm flex items-center gap-1.5 pt-1" style="color: var(--color-text-muted);">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $this->profile->location }}</span>
                        </p>
                    @endif
            {{-- Navigation Pill Bar (Scrollable on mobile) --}}
            <div class="mt-8 pt-6 border-t flex items-center justify-center sm:justify-start gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none"
                 style="border-color: var(--color-border);">
                <a href="{{ route('home') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0"
                   style="background: var(--color-primary); color: #000000;">
                    Overview
                </a>
                <a href="{{ route('about') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0"
                   style="color: var(--color-text); background: var(--color-background);">
                    About
                </a>
                <a href="{{ route('projects') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0"
                   style="color: var(--color-text); background: var(--color-background);">
                    Projects
                </a>
                <a href="{{ route('skills') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0"
                   style="color: var(--color-text); background: var(--color-background);">
                    Skills
                </a>
                <a href="{{ route('certificates') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0"
                   style="color: var(--color-text); background: var(--color-background);">
                    Certificates
                </a>
                <a href="{{ route('contact') }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0"
                   style="color: var(--color-text); background: var(--color-background);">
                    Contact
                </a>
            </div>
        </div>

        {{-- Featured Projects Grid --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">
                    Featured Projects
                </h2>
                <a href="{{ route('projects') }}" class="text-xs sm:text-sm font-semibold hover:underline" style="color: var(--color-primary);">
                    View All &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                @forelse ($this->featuredProjects as $project)
                    <a href="{{ route('projects.show', $project->slug) }}"
                       class="p-5 sm:p-6 rounded-2xl border transition duration-200 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between group"
                       style="background: var(--color-surface); border-color: var(--color-border);">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-base sm:text-lg group-hover:underline" style="color: var(--color-text);">
                                    {{ $project->title }}
                                </h3>
                                <svg class="w-4 h-4 opacity-50 group-hover:opacity-100 shrink-0 transition" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </div>
                            <p class="text-xs sm:text-sm line-clamp-2 leading-relaxed" style="color: var(--color-text-muted);">
                                {{ $project->summary }}
                            </p>
                        </div>

                        @if ($project->tech_stack)
                            <div class="flex flex-wrap gap-1.5 mt-4 pt-3 border-t" style="border-color: var(--color-border);">
                                @foreach (is_array($project->tech_stack) ? $project->tech_stack : explode(',', $project->tech_stack) as $tag)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium" style="background: var(--color-background); color: var(--color-text-muted);">
                                        {{ trim($tag) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center rounded-2xl border text-xs sm:text-sm" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                        No projects showcased yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Experience & Skills 2-Column Responsive Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            {{-- Experience Timeline Preview --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">
                        Recent Experience
                    </h2>
                    <a href="{{ route('about') }}" class="text-xs sm:text-sm font-semibold hover:underline" style="color: var(--color-primary);">
                        Full Timeline &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($this->recentExperiences as $exp)
                        <div class="p-4 sm:p-5 rounded-2xl border" style="background: var(--color-surface); border-color: var(--color-border);">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-bold text-sm sm:text-base" style="color: var(--color-text);">{{ $exp->title }}</p>
                                    <p class="text-xs font-semibold" style="color: var(--color-primary);">{{ $exp->company }}</p>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded-full shrink-0" style="background: var(--color-background); color: var(--color-text-muted);">
                                    {{ $exp->start_date?->format('Y') }} – {{ $exp->is_current ? 'Present' : $exp->end_date?->format('Y') }}
                                </span>
                            </div>
                            @if ($exp->description)
                                <p class="text-xs mt-2 line-clamp-2 leading-relaxed" style="color: var(--color-text-muted);">
                                    {{ $exp->description }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center rounded-2xl border text-xs" style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text-muted);">
                            No experience entries listed yet.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Top Skills Preview --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--color-text);">
                        Core Competencies
                    </h2>
                    <a href="{{ route('skills') }}" class="text-xs sm:text-sm font-semibold hover:underline" style="color: var(--color-primary);">
                        All Skills &rarr;
                    </a>
                </div>

                <div class="p-5 sm:p-6 rounded-2xl border space-y-3.5" style="background: var(--color-surface); border-color: var(--color-border);">
                    @forelse ($this->topSkills as $skill)
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span style="color: var(--color-text);">{{ $skill->name }}</span>
                                <span style="color: var(--color-text-muted);">{{ $skill->proficiency }}%</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full overflow-hidden" style="background: var(--color-background);">
                                <div class="h-full rounded-full transition-all duration-500"
                                     style="width: {{ $skill->proficiency }}%; background: var(--color-primary);"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-center" style="color: var(--color-text-muted);">No skills added yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Contact Callout Banner --}}
        <div class="p-6 sm:p-8 rounded-2xl border text-center space-y-3 relative overflow-hidden"
             style="background: var(--color-surface); border-color: var(--color-border);">
            <h3 class="text-lg sm:text-xl font-extrabold" style="color: var(--color-text);">
                Want to collaborate or discuss an opportunity?
            </h3>
            <p class="text-xs sm:text-sm max-w-md mx-auto" style="color: var(--color-text-muted);">
                Feel free to reach out directly through the inquiry form or connect on social platforms.
            </p>
            <div class="pt-2">
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md transition hover:scale-105"
                   style="background: var(--color-primary); color: #000000;">
                    <span>Get in Touch</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    @else
        <div class="p-12 text-center rounded-3xl border space-y-4" style="background: var(--color-surface); border-color: var(--color-border);">
            <p class="text-xl font-bold">No profile published yet.</p>
            <p class="text-sm text-gray-400">Log in to the dashboard to set up and publish your portfolio.</p>
            <a href="/login" class="inline-block px-6 py-2.5 rounded-xl text-sm font-bold bg-amber-500 text-gray-950">
                Log In to Workspace &rarr;
            </a>
        </div>
    @endif
</div>
