<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

layout('layouts.dashboard');
title('Projects Showcase Studio');

state([
    'showModal' => false,
    'editingId' => null,
    'title' => '',
    'slug' => '',
    'summary' => '',
    'description' => '',
    'tech_stack_input' => '',
    'repo_url' => '',
    'live_url' => '',
    'is_featured' => false,
    'sort_order' => 0,
    'savedMessage' => '',
]);

rules([
    'title' => 'required|string|max:255',
    'slug' => 'required|string|max:255',
    'summary' => 'nullable|string|max:500',
    'description' => 'nullable|string|max:5000',
    'tech_stack_input' => 'nullable|string|max:500',
    'repo_url' => 'nullable|url|max:255',
    'live_url' => 'nullable|url|max:255',
    'is_featured' => 'boolean',
    'sort_order' => 'integer',
]);

$getProjects = function () {
    $profile = Auth::user()?->profile;
    return $profile ? Project::where('profile_id', $profile->id)->orderBy('sort_order')->orderBy('created_at', 'desc')->get() : collect();
};

$openCreateModal = function () {
    $this->reset(['editingId', 'title', 'slug', 'summary', 'description', 'tech_stack_input', 'repo_url', 'live_url', 'is_featured', 'sort_order']);
    $this->showModal = true;
};

$openEditModal = function ($id) {
    $profile = Auth::user()?->profile;
    $project = Project::where('profile_id', $profile?->id)->findOrFail($id);

    $this->editingId = $project->id;
    $this->title = $project->title;
    $this->slug = $project->slug;
    $this->summary = $project->summary ?? '';
    $this->description = $project->description ?? '';
    $this->tech_stack_input = is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : '';
    $this->repo_url = $project->repo_url ?? '';
    $this->live_url = $project->live_url ?? '';
    $this->is_featured = (bool) $project->is_featured;
    $this->sort_order = (int) $project->sort_order;
    $this->showModal = true;
};

$saveProject = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    if (! $this->slug && $this->title) {
        $this->slug = Str::slug($this->title);
    }

    $this->validate();

    $techStackArray = array_values(array_filter(array_map('trim', explode(',', $this->tech_stack_input))));

    $data = [
        'profile_id' => $profile->id,
        'title' => $this->title,
        'slug' => Str::slug($this->slug),
        'summary' => $this->summary,
        'description' => $this->description,
        'tech_stack' => $techStackArray,
        'repo_url' => $this->repo_url ?: null,
        'live_url' => $this->live_url ?: null,
        'is_featured' => (bool) $this->is_featured,
        'sort_order' => (int) $this->sort_order,
    ];

    if ($this->editingId) {
        $project = Project::where('profile_id', $profile->id)->findOrFail($this->editingId);
        $project->update($data);
        $this->savedMessage = 'Project updated successfully!';
    } else {
        Project::create($data);
        $this->savedMessage = 'Project created successfully!';
    }

    $this->showModal = false;
    $this->reset(['editingId', 'title', 'slug', 'summary', 'description', 'tech_stack_input', 'repo_url', 'live_url', 'is_featured', 'sort_order']);
};

$deleteProject = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    Project::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Project deleted successfully.';
};

?>

<div class="space-y-8 max-w-6xl">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    PORTFOLIO STUDIO
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Projects Showcase Studio
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Showcase your production apps, open-source repositories, live deployments, and architecture stacks.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0" data-tooltip="Add a new project to your portfolio showcase" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add New Project</span>
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
        $projects = $this->getProjects();
    @endphp

    {{-- Projects Grid --}}
    @if($projects->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">No Projects Added Yet</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Highlight your key engineering projects, live demos, and GitHub repositories to impress hiring managers.</p>
            <button wire:click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2 cursor-pointer" data-tooltip="Add your first engineering project">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Create First Project</span>
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                <div class="glass-card glass-card-hover rounded-3xl p-6 flex flex-col justify-between space-y-5 border border-white/5">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if($project->is_featured)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-yellow-500/10 text-yellow-400 border border-yellow-500/30" data-tooltip="Featured on portfolio homepage">
                                        FEATURED
                                    </span>
                                @endif
                                <span class="text-[10px] font-mono text-slate-500">Order: {{ $project->sort_order }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button wire:click="openEditModal({{ $project->id }})" class="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer" data-tooltip="Edit project details, tech stack, and links">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button wire:click="deleteProject({{ $project->id }})" wire:confirm="Are you sure you want to delete this project?" class="p-1.5 rounded-lg bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors cursor-pointer" data-tooltip="Permanently delete project from showcase">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-base font-bold font-heading text-white">{{ $project->title }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $project->summary ?: Str::limit($project->description, 90) }}</p>
                        </div>

                        @if(!empty($project->tech_stack))
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach((array) $project->tech_stack as $tech)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-[10px] font-mono text-emerald-400">
                                        {{ $tech }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3 font-mono">
                            @if($project->repo_url)
                                <a href="{{ $project->repo_url }}" target="_blank" class="text-slate-400 hover:text-white flex items-center gap-1" data-tooltip="Open GitHub source code repository">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" /></svg>
                                    <span>Code</span>
                                </a>
                            @endif
                            @if($project->live_url)
                                <a href="{{ $project->live_url }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 flex items-center gap-1 font-semibold" data-tooltip="Open live deployment in a new tab">
                                    <span>Demo &rarr;</span>
                                </a>
                            @endif
                        </div>

                        @if(Auth::user()?->profile?->slug)
                            <a href="{{ url('/' . Auth::user()->profile->slug . '/projects/' . $project->slug) }}" target="_blank" class="text-[11px] text-slate-500 hover:text-slate-300" data-tooltip="View dedicated project page on your portfolio">
                                View &nearr;
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        {{ $editingId ? 'Edit Project' : 'Add New Project' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close modal">&times;</button>
                </div>

                <form wire:submit.prevent="saveProject" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Project Title *</label>
                            <input type="text" wire:model="title" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Distributed Task Queue" required />
                            @error('title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Slug *</label>
                            <input type="text" wire:model="slug" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs focus:border-emerald-500 focus:outline-none" placeholder="distributed-task-queue" required />
                            @error('slug') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Short Summary</label>
                        <input type="text" wire:model="summary" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="Brief 1-sentence synopsis of what the project solves" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Tech Stack (comma-separated)</label>
                        <input type="text" wire:model="tech_stack_input" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="Laravel, Livewire, Redis, TailwindCSS, PostgreSQL" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">GitHub Repository URL</label>
                            <input type="url" wire:model="repo_url" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="https://github.com/..." />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Live Demo URL</label>
                            <input type="url" wire:model="live_url" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="https://demo.example.com" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Full Description</label>
                        <textarea rows="4" wire:model="description" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="Detailed architectural highlights, features, and engineering decisions..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <label class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3 cursor-pointer" data-tooltip="Feature this project on your main portfolio hero/highlights">
                            <input type="checkbox" wire:model="is_featured" class="rounded border-slate-700 text-emerald-500 focus:ring-emerald-500" />
                            <span class="text-xs font-semibold text-white">Feature on Portfolio Home</span>
                        </label>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Sort Order</label>
                            <input type="number" wire:model="sort_order" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="0" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs cursor-pointer" data-tooltip="Discard changes">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Save and commit project details">
                            {{ $editingId ? 'Update Project' : 'Create Project' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

