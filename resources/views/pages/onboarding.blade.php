<?php

use App\Models\Profile;
use App\Models\Theme;
use Illuminate\Support\Str;
use function Livewire\Volt\{computed, mount, rules, state};

state([
    'step' => 1,
    'slug' => '',
    'full_name' => '',
    'headline' => '',
    'bio' => '',
    'location' => '',
    'selected_theme_id' => null,
    'is_published' => true,
    'saved' => false,
]);

mount(function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->to('/admin/login');
    }

    $account = $user->accounts()->first();
    $profile = $account?->profiles()->first();

    if ($profile) {
        $this->slug = $profile->slug ?: Str::slug($user->name ?: 'portfolio');
        $this->full_name = $profile->full_name ?: $user->name;
        $this->headline = $profile->headline ?: '';
        $this->bio = $profile->bio ?: '';
        $this->location = $profile->location ?: '';
        $this->is_published = (bool) $profile->is_published;
    } else {
        $this->full_name = $user->name;
        $this->slug = Str::slug($user->name ?: 'portfolio');
    }

    $activeTheme = Theme::query()->where('is_active', true)->first() ?? Theme::query()->first();
    $this->selected_theme_id = $activeTheme?->id;
});

$themes = computed(fn () => Theme::all());

$profile = computed(function () {
    $user = auth()->user();
    $account = $user?->accounts()->first();
    return $account?->profiles()->first();
});

$nextStep = function () {
    if ($this->step === 1) {
        $this->validate([
            'slug' => ['required', 'string', 'min:2', 'max:50', 'alpha_dash'],
        ]);

        // Validate uniqueness if changing slug
        $existing = Profile::query()
            ->where('slug', $this->slug)
            ->when($this->profile, fn ($q) => $q->where('id', '!=', $this->profile->id))
            ->exists();

        if ($existing) {
            $this->addError('slug', 'This URL slug is already taken. Please choose another.');
            return;
        }
    } elseif ($this->step === 2) {
        $this->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'headline' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);
    }

    $this->step = min(4, $this->step + 1);
};

$prevStep = function () {
    $this->step = max(1, $this->step - 1);
};

$saveAndFinish = function () {
    $this->validate([
        'slug' => ['required', 'string', 'min:2', 'max:50', 'alpha_dash'],
        'full_name' => ['required', 'string', 'max:100'],
        'headline' => ['nullable', 'string', 'max:150'],
        'bio' => ['nullable', 'string', 'max:1000'],
        'location' => ['nullable', 'string', 'max:100'],
    ]);

    $profile = $this->profile;
    if ($profile) {
        $profile->update([
            'slug' => $this->slug,
            'full_name' => $this->full_name,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'location' => $this->location,
            'theme_id' => $this->selected_theme_id,
            'is_published' => true,
        ]);
    }

    if ($this->selected_theme_id) {
        Theme::query()->where('id', '!=', $this->selected_theme_id)->update(['is_active' => false]);
        Theme::query()->where('id', $this->selected_theme_id)->update(['is_active' => true]);
    }

    $this->saved = true;
    return redirect()->to('/admin');
};

$skipToDashboard = function () {
    return redirect()->to('/admin');
};

?>

<div class="min-h-screen flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8" style="background: radial-gradient(circle at top right, rgba(0, 255, 156, 0.05), transparent 40%), var(--color-background, #0a0e14);">
    <div class="max-w-2xl mx-auto w-full">
        {{-- Header & Step Indicator --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mb-4 shadow-lg shadow-amber-500/5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Set Up Your Portfolio
            </h1>
            <p class="mt-2 text-sm text-gray-400">
                Let's get your public profile customized in just a few quick steps.
            </p>

            {{-- Progress bar & steps --}}
            <div class="mt-8">
                <div class="flex items-center justify-between max-w-xs mx-auto">
                    @foreach(['URL', 'Bio', 'Theme', 'Launch'] as $idx => $stepName)
                        @php $stepNum = $idx + 1; @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 {{ $step === $stepNum ? 'bg-amber-500 text-gray-950 ring-4 ring-amber-500/20 shadow-md shadow-amber-500/20' : ($step > $stepNum ? 'bg-emerald-500 text-white' : 'bg-gray-800 text-gray-400 border border-gray-700') }}">
                                @if($step > $stepNum)
                                    ✓
                                @else
                                    {{ $stepNum }}
                                @endif
                            </div>
                            <span class="text-xs mt-1.5 font-medium {{ $step === $stepNum ? 'text-amber-400' : 'text-gray-500' }}">
                                {{ $stepName }}
                            </span>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-2 {{ $step > $stepNum ? 'bg-emerald-500' : 'bg-gray-800' }} transition-colors duration-300"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Wizard Card --}}
        <div class="bg-gray-900/80 backdrop-blur-xl border border-gray-800/80 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/50 transition-all">
            {{-- STEP 1: Slug Selection --}}
            @if ($step === 1)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-white">Choose your portfolio URL</h2>
                        <p class="text-sm text-gray-400 mt-1">This is the unique web address where employers and clients can find you.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-300">Username / Slug</label>
                        <div class="relative flex rounded-xl border border-gray-700 bg-gray-950/60 overflow-hidden focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500">
                            <span class="inline-flex items-center px-4 text-xs font-mono text-gray-500 bg-gray-900/80 border-r border-gray-800 select-none">
                                {{ url('/') }}/
                            </span>
                            <input
                                wire:model.live.debounce.300ms="slug"
                                type="text"
                                class="flex-1 min-w-0 block w-full px-4 py-3 bg-transparent text-white font-mono text-sm focus:outline-none placeholder-gray-600"
                                placeholder="your-name"
                            />
                        </div>
                        @error('slug')
                            <p class="text-xs text-rose-400 mt-1 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="p-4 rounded-xl bg-gray-950/40 border border-gray-800/60">
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="text-emerald-400 font-semibold">Live Preview:</span>
                            <code class="text-amber-300 font-mono">{{ url('/') }}/{{ $slug ?: 'your-name' }}</code>
                        </div>
                    </div>
                </div>

            {{-- STEP 2: Basic Info --}}
            @elseif ($step === 2)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-white">Tell us about yourself</h2>
                        <p class="text-sm text-gray-400 mt-1">Provide your name and a brief overview of your skills and background.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Full Name *</label>
                            <input
                                wire:model="full_name"
                                type="text"
                                class="mt-1.5 block w-full rounded-xl border border-gray-700 bg-gray-950/60 px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 placeholder-gray-600"
                                placeholder="e.g. Alex Doe"
                            />
                            @error('full_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">Professional Headline</label>
                            <input
                                wire:model="headline"
                                type="text"
                                class="mt-1.5 block w-full rounded-xl border border-gray-700 bg-gray-950/60 px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 placeholder-gray-600"
                                placeholder="e.g. Full-Stack Engineer | AI & Cloud Specialist"
                            />
                            @error('headline') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">Location</label>
                            <input
                                wire:model="location"
                                type="text"
                                class="mt-1.5 block w-full rounded-xl border border-gray-700 bg-gray-950/60 px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 placeholder-gray-600"
                                placeholder="e.g. San Francisco, CA (or Remote)"
                            />
                            @error('location') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">Short Bio</label>
                            <textarea
                                wire:model="bio"
                                rows="3"
                                class="mt-1.5 block w-full rounded-xl border border-gray-700 bg-gray-950/60 px-4 py-2.5 text-white text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 placeholder-gray-600"
                                placeholder="Briefly describe what you build, your passions, and what roles you are open to..."
                            ></textarea>
                            @error('bio') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

            {{-- STEP 3: Starter Theme --}}
            @elseif ($step === 3)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-white">Pick a starter theme</h2>
                        <p class="text-sm text-gray-400 mt-1">Select the look and feel for your public portfolio. You can switch this anytime.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                        @foreach ($this->themes as $t)
                            <div
                                wire:click="$set('selected_theme_id', {{ $t->id }})"
                                class="cursor-pointer relative flex flex-col p-4 rounded-2xl border transition-all {{ $selected_theme_id === $t->id ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/20 shadow-lg shadow-amber-500/5' : 'border-gray-800 bg-gray-950/40 hover:border-gray-700' }}"
                            >
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-semibold text-sm text-white">{{ $t->name }}</span>
                                    @if ($selected_theme_id === $t->id)
                                        <span class="w-5 h-5 rounded-full bg-amber-500 text-gray-950 flex items-center justify-center text-xs font-bold">✓</span>
                                    @endif
                                </div>

                                {{-- Color Swatches --}}
                                @if (is_array($t->colors))
                                    <div class="flex items-center gap-1.5 mt-auto pt-2">
                                        @foreach (['background', 'surface', 'primary', 'secondary', 'accent'] as $colorKey)
                                            @if (isset($t->colors[$colorKey]))
                                                <span class="w-5 h-5 rounded-full border border-gray-700/80 shadow-inner" style="background-color: {{ $t->colors[$colorKey] }}" title="{{ $colorKey }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- STEP 4: Ready to Launch --}}
            @elseif ($step === 4)
                <div class="space-y-6 text-center py-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-white">You're all set!</h2>
                        <p class="text-sm text-gray-400 mt-2 max-w-md mx-auto">
                            We've saved your profile preferences. Clicking below will publish your portfolio and open your admin control center.
                        </p>
                    </div>

                    <div class="bg-gray-950/60 border border-gray-800 rounded-2xl p-4 text-left max-w-md mx-auto space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Name:</span>
                            <span class="text-white font-medium">{{ $full_name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Public URL:</span>
                            <code class="text-amber-400 font-mono">{{ url('/') }}/{{ $slug }}</code>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Status:</span>
                            <span class="text-emerald-400 font-medium">Published & Active</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Navigation Buttons --}}
            <div class="mt-8 pt-6 border-t border-gray-800/80 flex items-center justify-between">
                @if ($step > 1)
                    <button
                        wire:click="prevStep"
                        type="button"
                        class="px-4 py-2.5 rounded-xl border border-gray-700 bg-gray-800/60 hover:bg-gray-800 text-sm font-medium text-gray-300 transition"
                    >
                        Back
                    </button>
                @else
                    <button
                        wire:click="skipToDashboard"
                        type="button"
                        class="text-xs text-gray-500 hover:text-gray-400 transition"
                    >
                        Skip to Dashboard →
                    </button>
                @endif

                @if ($step < 4)
                    <button
                        wire:click="nextStep"
                        type="button"
                        class="ml-auto inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-semibold transition shadow-md shadow-amber-500/20"
                    >
                        <span>Continue</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <button
                        wire:click="saveAndFinish"
                        type="button"
                        class="ml-auto inline-flex items-center gap-1.5 px-8 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-semibold transition shadow-lg shadow-emerald-500/20"
                    >
                        <span>Complete Setup & Open Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
