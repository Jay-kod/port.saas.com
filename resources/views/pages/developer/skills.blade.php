<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Skills Matrix');

state([
    'showModal' => false,
    'editingId' => null,
    'name' => '',
    'category' => 'Backend',
    'proficiency' => 85,
    'icon' => '',
    'sort_order' => 0,
    'savedMessage' => '',
]);

rules([
    'name' => 'required|string|max:255',
    'category' => 'required|string|max:100',
    'proficiency' => 'nullable|integer|min:0|max:100',
    'icon' => 'nullable|string|max:100',
    'sort_order' => 'integer',
]);

$getSkills = function () {
    $profile = Auth::user()?->profile;
    return $profile ? Skill::where('profile_id', $profile->id)->orderBy('category')->orderBy('sort_order')->orderBy('name')->get() : collect();
};

$openCreateModal = function ($prefillCategory = 'Backend') {
    $this->reset(['editingId', 'name', 'icon', 'sort_order']);
    $this->category = $prefillCategory;
    $this->proficiency = 85;
    $this->showModal = true;
};

$openEditModal = function ($id) {
    $profile = Auth::user()?->profile;
    $skill = Skill::where('profile_id', $profile?->id)->findOrFail($id);

    $this->editingId = $skill->id;
    $this->name = $skill->name;
    $this->category = $skill->category ?: 'Backend';
    $this->proficiency = $skill->proficiency ?? 85;
    $this->icon = $skill->icon ?? '';
    $this->sort_order = (int) $skill->sort_order;
    $this->showModal = true;
};

$saveSkill = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $this->validate();

    $data = [
        'profile_id' => $profile->id,
        'name' => $this->name,
        'category' => $this->category,
        'proficiency' => $this->proficiency ? (int) $this->proficiency : null,
        'icon' => $this->icon ?: null,
        'sort_order' => (int) $this->sort_order,
    ];

    if ($this->editingId) {
        $skill = Skill::where('profile_id', $profile->id)->findOrFail($this->editingId);
        $skill->update($data);
        $this->savedMessage = 'Skill updated successfully!';
    } else {
        Skill::create($data);
        $this->savedMessage = 'Skill added successfully!';
    }

    $this->showModal = false;
    $this->reset(['editingId', 'name', 'category', 'proficiency', 'icon', 'sort_order']);
};

$deleteSkill = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    Skill::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Skill deleted.';
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
                Technical Skills Matrix
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Organize your engineering competencies across languages, frameworks, infrastructure, and databases.
            </p>
        </div>

        <button wire:click="openCreateModal('Backend')" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0" data-tooltip="Add a technical competency or framework" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Skill</span>
        </button>
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

    @php
        $skills = $this->getSkills();
        $groupedSkills = $skills->groupBy('category');
    @endphp

    @if($skills->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">No Skills Listed Yet</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Add your core technical competencies (e.g. PHP, Laravel, TypeScript, Docker, PostgreSQL) to display on your public portfolio.</p>
            <button wire:click="openCreateModal('Backend')" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2 cursor-pointer" data-tooltip="Add your first engineering skill">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add First Skill</span>
            </button>
        </div>
    @else
        <div class="space-y-6">
            @foreach($groupedSkills as $categoryName => $catSkills)
                <div class="glass-card rounded-3xl p-6 sm:p-7 border border-white/5 space-y-4">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <h3 class="text-base font-bold text-white font-heading">{{ $categoryName ?: 'General' }}</h3>
                            <span class="text-xs font-mono text-slate-500">({{ count($catSkills) }})</span>
                        </div>
                        <button wire:click="openCreateModal('{{ $categoryName }}')" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1 cursor-pointer" data-tooltip="Add a skill under {{ $categoryName }}">
                            <span>+ Add to {{ $categoryName }}</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($catSkills as $skill)
                            <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/40 transition-all flex flex-col justify-between space-y-2 group">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">{{ $skill->name }}</span>
                                    <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openEditModal({{ $skill->id }})" class="p-1 text-slate-400 hover:text-white cursor-pointer" data-tooltip="Edit skill name and proficiency">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button wire:click="deleteSkill({{ $skill->id }})" wire:confirm="Are you sure you want to delete this skill?" class="p-1 text-slate-400 hover:text-rose-400 cursor-pointer" data-tooltip="Remove skill from portfolio">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>

                                @if($skill->proficiency)
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                                            <span>Mastery</span>
                                            <span class="text-emerald-400">{{ $skill->proficiency }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full" style="width: {{ $skill->proficiency }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        {{ $editingId ? 'Edit Skill' : 'Add Technical Skill' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close modal">&times;</button>
                </div>

                <form wire:submit.prevent="saveSkill" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Skill Name *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Laravel, Docker, PostgreSQL" required />
                        @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Category *</label>
                        <select wire:model="category" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none">
                            <option value="Backend">Backend & APIs</option>
                            <option value="Frontend">Frontend & Mobile</option>
                            <option value="DevOps & Cloud">DevOps & Cloud</option>
                            <option value="Databases & Storage">Databases & Storage</option>
                            <option value="AI & Machine Learning">AI & Machine Learning</option>
                            <option value="Tools & Architecture">Tools & Architecture</option>
                        </select>
                        @error('category') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-semibold text-slate-300">Proficiency Level</label>
                            <span class="text-xs font-mono font-bold text-emerald-400">{{ $proficiency }}%</span>
                        </div>
                        <input type="range" min="10" max="100" step="5" wire:model.live="proficiency" class="w-full accent-emerald-500 cursor-pointer" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Sort Order</label>
                        <input type="number" wire:model="sort_order" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="0" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs cursor-pointer" data-tooltip="Discard changes">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Save skill to portfolio">
                            {{ $editingId ? 'Update Skill' : 'Save Skill' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

