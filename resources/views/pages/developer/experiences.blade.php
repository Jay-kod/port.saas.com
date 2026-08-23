<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Experience;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Career Experience Timeline');

state([
    'showModal' => false,
    'editingId' => null,
    'company' => '',
    'title' => '',
    'location' => '',
    'start_date' => '',
    'end_date' => '',
    'is_current' => false,
    'description' => '',
    'sort_order' => 0,
    'savedMessage' => '',
]);

rules([
    'company' => 'required|string|max:255',
    'title' => 'required|string|max:255',
    'location' => 'nullable|string|max:255',
    'start_date' => 'required|date',
    'end_date' => 'nullable|date',
    'is_current' => 'boolean',
    'description' => 'nullable|string|max:5000',
    'sort_order' => 'integer',
]);

$getExperiences = function () {
    $profile = Auth::user()?->profile;
    return $profile ? Experience::where('profile_id', $profile->id)->orderBy('start_date', 'desc')->get() : collect();
};

$openCreateModal = function () {
    $this->reset(['editingId', 'company', 'title', 'location', 'start_date', 'end_date', 'is_current', 'description', 'sort_order']);
    $this->showModal = true;
};

$openEditModal = function ($id) {
    $profile = Auth::user()?->profile;
    $exp = Experience::where('profile_id', $profile?->id)->findOrFail($id);

    $this->editingId = $exp->id;
    $this->company = $exp->company;
    $this->title = $exp->title;
    $this->location = $exp->location ?? '';
    $this->start_date = $exp->start_date ? $exp->start_date->format('Y-m-d') : '';
    $this->end_date = $exp->end_date ? $exp->end_date->format('Y-m-d') : '';
    $this->is_current = (bool) $exp->is_current;
    $this->description = $exp->description ?? '';
    $this->sort_order = (int) $exp->sort_order;
    $this->showModal = true;
};

$saveExperience = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $this->validate();

    $data = [
        'profile_id' => $profile->id,
        'company' => $this->company,
        'title' => $this->title,
        'location' => $this->location ?: null,
        'start_date' => $this->start_date,
        'end_date' => $this->is_current ? null : ($this->end_date ?: null),
        'is_current' => (bool) $this->is_current,
        'description' => $this->description,
        'sort_order' => (int) $this->sort_order,
    ];

    if ($this->editingId) {
        $exp = Experience::where('profile_id', $profile->id)->findOrFail($this->editingId);
        $exp->update($data);
        $this->savedMessage = 'Experience record updated successfully!';
    } else {
        Experience::create($data);
        $this->savedMessage = 'Experience record created successfully!';
    }

    $this->showModal = false;
    $this->reset(['editingId', 'company', 'title', 'location', 'start_date', 'end_date', 'is_current', 'description', 'sort_order']);
};

$deleteExperience = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    Experience::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Experience record deleted.';
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
                Experience Timeline
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage your career history, company roles, leadership impact, and technical accomplishments.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Experience</span>
        </button>
    </div>

    {{-- Feedback Banner --}}
    @if($savedMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between font-mono animate-fadeIn">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $savedMessage }}</span>
            </div>
            <button wire:click="$set('savedMessage', '')" class="text-slate-400 hover:text-white">&times;</button>
        </div>
    @endif

    @php
        $experiences = $this->getExperiences();
    @endphp

    {{-- Timeline View --}}
    @if($experiences->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">No Experience History Added</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Add your previous software engineering roles, company impact, and responsibilities to power your AI resume generations.</p>
            <button wire:click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add First Role</span>
            </button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($experiences as $exp)
                <div class="glass-card glass-card-hover rounded-3xl p-6 sm:p-7 border border-white/5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-white font-heading">{{ $exp->title }}</h3>
                                @if($exp->is_current)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        CURRENT
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs font-semibold text-emerald-400 flex items-center gap-2">
                                <span>{{ $exp->company }}</span>
                                @if($exp->location)
                                    <span class="text-slate-600">&bull;</span>
                                    <span class="text-slate-400 font-normal">{{ $exp->location }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono text-slate-400 bg-slate-900 px-3 py-1.5 rounded-xl border border-slate-800">
                                {{ $exp->start_date?->format('M Y') }} &mdash; {{ $exp->is_current ? 'Present' : ($exp->end_date?->format('M Y') ?: 'Present') }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button wire:click="openEditModal({{ $exp->id }})" class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button wire:click="deleteExperience({{ $exp->id }})" wire:confirm="Are you sure you want to delete this experience record?" class="p-2 rounded-xl bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($exp->description)
                        <div class="text-xs text-slate-300 leading-relaxed pt-2 border-t border-white/5 whitespace-pre-line">
                            {{ $exp->description }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        {{ $editingId ? 'Edit Experience' : 'Add Experience Record' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>

                <form wire:submit.prevent="saveExperience" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Job Title / Role *</label>
                            <input type="text" wire:model="title" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Lead Backend Engineer" required />
                            @error('title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Company / Organization *</label>
                            <input type="text" wire:model="company" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Stripe, Acme Corp" required />
                            @error('company') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Location</label>
                        <input type="text" wire:model="location" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="San Francisco, CA or Remote" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Start Date *</label>
                            <input type="date" wire:model="start_date" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" required />
                            @error('start_date') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">End Date</label>
                            <input type="date" wire:model="end_date" {{ $is_current ? 'disabled' : '' }} class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none {{ $is_current ? 'opacity-40 cursor-not-allowed' : '' }}" />
                        </div>
                    </div>

                    <label class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="is_current" class="rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                        <span class="text-xs font-semibold text-white">I currently work here</span>
                    </label>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Role Description & Achievements</label>
                        <textarea rows="4" wire:model="description" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="Describe key systems built, metrics moved, and tech stacks utilized..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md">
                            {{ $editingId ? 'Update Experience' : 'Save Experience' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
