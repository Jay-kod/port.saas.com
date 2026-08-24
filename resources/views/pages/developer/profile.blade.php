<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

layout('layouts.dashboard');
title('Profile & Bio Studio');

state([
    'full_name' => fn () => Auth::user()?->profile?->full_name ?? Auth::user()?->name ?? '',
    'headline' => fn () => Auth::user()?->profile?->headline ?? '',
    'bio' => fn () => Auth::user()?->profile?->bio ?? '',
    'email' => fn () => Auth::user()?->profile?->email ?? Auth::user()?->email ?? '',
    'phone' => fn () => Auth::user()?->profile?->phone ?? '',
    'location' => fn () => Auth::user()?->profile?->location ?? '',
    'slug' => fn () => Auth::user()?->profile?->slug ?? '',
    'meta_description' => fn () => Auth::user()?->profile?->meta_description ?? '',
    'is_published' => fn () => (bool) (Auth::user()?->profile?->is_published ?? false),
    'is_discoverable' => fn () => (bool) (Auth::user()?->profile?->is_discoverable ?? true),
    'github' => fn () => Auth::user()?->profile?->social_links['github'] ?? '',
    'linkedin' => fn () => Auth::user()?->profile?->social_links['linkedin'] ?? '',
    'twitter' => fn () => Auth::user()?->profile?->social_links['twitter'] ?? '',
    'website' => fn () => Auth::user()?->profile?->social_links['website'] ?? '',
    'savedMessage' => '',
]);

rules([
    'full_name' => 'required|string|max:255',
    'headline' => 'nullable|string|max:255',
    'bio' => 'nullable|string|max:5000',
    'email' => 'nullable|email|max:255',
    'phone' => 'nullable|string|max:50',
    'location' => 'nullable|string|max:255',
    'slug' => 'required|string|max:100|alpha_dash',
    'meta_description' => 'nullable|string|max:300',
    'is_published' => 'boolean',
    'is_discoverable' => 'boolean',
    'github' => 'nullable|string|max:255',
    'linkedin' => 'nullable|string|max:255',
    'twitter' => 'nullable|string|max:255',
    'website' => 'nullable|string|max:255',
]);

$save = function () {
    $this->validate();

    $user = Auth::user();
    $profile = $user?->profile;

    if (! $profile) {
        $account = $user->defaultTenant ?? $user->accounts()->first();
        $profile = Profile::create([
            'user_id' => $user->id,
            'account_id' => $account?->id ?? 1,
            'slug' => Str::slug($this->slug ?: $this->full_name),
            'full_name' => $this->full_name,
        ]);
    }

    // Check slug uniqueness
    $slugClean = Str::slug($this->slug);
    $existing = Profile::where('slug', $slugClean)->where('id', '!=', $profile->id)->first();
    if ($existing) {
        $slugClean = $slugClean . '-' . rand(100, 999);
        $this->slug = $slugClean;
    }

    $socialLinks = array_filter([
        'github' => trim($this->github),
        'linkedin' => trim($this->linkedin),
        'twitter' => trim($this->twitter),
        'website' => trim($this->website),
    ]);

    $profile->update([
        'full_name' => $this->full_name,
        'headline' => $this->headline,
        'bio' => $this->bio,
        'email' => $this->email,
        'phone' => $this->phone,
        'location' => $this->location,
        'slug' => $slugClean,
        'meta_description' => $this->meta_description,
        'is_published' => (bool) $this->is_published,
        'is_discoverable' => (bool) $this->is_discoverable,
        'social_links' => $socialLinks,
    ]);

    $this->savedMessage = 'Profile changes saved successfully!';
};

?>

<div class="space-y-8 max-w-5xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    PORTFOLIO STUDIO
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Profile & Bio Editor
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage your public headline, personal bio, contact info, social handles, and SEO visibility.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($this->slug && $this->is_published)
                <a href="{{ url('/' . $this->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 text-xs font-semibold transition-all flex items-center gap-2" data-tooltip="Open your public live portfolio in a new tab" data-tooltip-pos="bottom">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>View Live Site</span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            @endif
            <button wire:click="save" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer" data-tooltip="Save all bio, social handles, and SEO visibility updates" data-tooltip-pos="bottom">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>Save Profile</span>
            </button>
        </div>
    </div>

    {{-- Feedback Banner --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white cursor-pointer" data-tooltip="Dismiss notification">&times;</button>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        {{-- Section 1: Core Bio & Identity --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Personal Identity & Public Headline</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Full Name *</label>
                    <input type="text" wire:model="full_name" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="e.g. Alex Morgan" required />
                    @error('full_name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Custom URL Slug *</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 rounded-l-xl bg-slate-950 border border-r-0 border-slate-800 text-slate-500 text-xs font-mono">/</span>
                        <input type="text" wire:model="slug" class="w-full px-4 py-2.5 rounded-r-xl bg-slate-900/90 border border-slate-800 text-emerald-400 font-mono text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="alex-morgan" required />
                    </div>
                    @error('slug') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">Professional Headline</label>
                <input type="text" wire:model="headline" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="e.g. Senior Full-Stack Engineer & Distributed Systems Architect" />
                @error('headline') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">About Me / Bio</label>
                <textarea rows="4" wire:model="bio" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors leading-relaxed" placeholder="Write a brief overview of your background, passions, and core engineering philosophy..."></textarea>
                @error('bio') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Section 2: Contact & Location --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Contact & Social Presence</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Public Contact Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="alex@example.com" />
                    @error('email') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Phone (Optional)</label>
                    <input type="text" wire:model="phone" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="+1 (555) 000-0000" />
                    @error('phone') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Location / Base</label>
                    <input type="text" wire:model="location" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="San Francisco, CA (or Remote)" />
                    @error('location') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">GitHub Profile</label>
                    <input type="text" wire:model="github" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors font-mono" placeholder="https://github.com/..." />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">LinkedIn Profile</label>
                    <input type="text" wire:model="linkedin" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors font-mono" placeholder="https://linkedin.com/in/..." />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Twitter / X</label>
                    <input type="text" wire:model="twitter" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors font-mono" placeholder="https://x.com/..." />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Personal Website / Blog</label>
                    <input type="text" wire:model="website" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors font-mono" placeholder="https://alexmorgan.dev" />
                </div>
            </div>
        </div>

        {{-- Section 3: Publishing & SEO --}}
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
            <h3 class="text-base font-bold font-heading text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Visibility & Discoverability</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/30 transition-all flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="is_published" class="mt-1 rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                    <div>
                        <div class="text-xs font-bold text-white">Publish Portfolio Publicly</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">When checked, your portfolio is live at <span class="text-emerald-400 font-mono">/{{ $slug ?: 'your-slug' }}</span></div>
                    </div>
                </label>

                <label class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/30 transition-all flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="is_discoverable" class="mt-1 rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                    <div>
                        <div class="text-xs font-bold text-white">List in Developer Directory</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Feature your profile in the public <span class="text-emerald-400 font-mono">/discover</span> talent index.</div>
                    </div>
                </label>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">SEO & OpenGraph Meta Description</label>
                <input type="text" wire:model="meta_description" class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none transition-colors" placeholder="e.g. Senior Software Engineer specializing in high-concurrency microservices and modern frontend architecture." />
                @error('meta_description') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="save" type="button" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>Save All Profile Changes</span>
            </button>
        </div>
    </form>
</div>
