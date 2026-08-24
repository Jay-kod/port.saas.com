<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Job Tracker (Kanban)');

state([
    'showModal' => false,
    'editingId' => null,
    'company' => '',
    'role' => '',
    'job_url' => '',
    'salary_range' => '',
    'status' => 'saved',
    'notes' => '',
    'savedMessage' => '',
]);

rules([
    'company' => 'required|string|max:255',
    'role' => 'required|string|max:255',
    'job_url' => 'nullable|url|max:255',
    'salary_range' => 'nullable|string|max:100',
    'status' => 'required|string|in:saved,applied,interviewing,offered,rejected',
    'notes' => 'nullable|string|max:5000',
]);

$getApplications = function () {
    $profile = Auth::user()?->profile;
    return $profile ? JobApplication::where('profile_id', $profile->id)->orderBy('created_at', 'desc')->get() : collect();
};

$openCreateModal = function ($prefillStatus = 'saved') {
    $this->reset(['editingId', 'company', 'role', 'job_url', 'salary_range', 'notes', 'savedMessage']);
    $this->status = $prefillStatus;
    $this->showModal = true;
};

$openEditModal = function ($id) {
    $profile = Auth::user()?->profile;
    $app = JobApplication::where('profile_id', $profile?->id)->findOrFail($id);

    $this->editingId = $app->id;
    $this->company = $app->company;
    $this->role = $app->role;
    $this->job_url = $app->job_url ?? '';
    $this->salary_range = $app->salary_range ?? '';
    $this->status = $app->status;
    $this->notes = $app->notes ?? '';
    $this->showModal = true;
};

$saveApplication = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $this->validate();

    $data = [
        'profile_id' => $profile->id,
        'company' => $this->company,
        'role' => $this->role,
        'job_url' => $this->job_url ?: null,
        'salary_range' => $this->salary_range ?: null,
        'status' => $this->status,
        'notes' => $this->notes ?: null,
    ];

    if ($this->editingId) {
        $app = JobApplication::where('profile_id', $profile->id)->findOrFail($this->editingId);
        $app->update($data);
        $this->savedMessage = 'Job application updated!';
    } else {
        JobApplication::create($data);
        $this->savedMessage = 'Job application added to board!';
    }

    $this->showModal = false;
    $this->reset(['editingId', 'company', 'role', 'job_url', 'salary_range', 'status', 'notes']);
};

$updateStatus = function ($id, $newStatus) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    $app = JobApplication::where('profile_id', $profile->id)->findOrFail($id);
    $app->update(['status' => $newStatus]);
};

$deleteApplication = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    JobApplication::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Application removed from board.';
};

?>

<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                    CAREER & AI SUITE
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Job Application Tracker
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                5-stage visual Kanban pipeline to track opportunities from wishlist to signed offer.
            </p>
        </div>

        <button wire:click="openCreateModal('saved')" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0" data-tooltip="Add a new job application opportunity" data-tooltip-pos="bottom">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Track New Job</span>
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
        $apps = $this->getApplications();
        $columns = [
            'saved' => ['label' => 'Wishlist', 'color' => 'slate', 'border' => 'border-slate-800'],
            'applied' => ['label' => 'Applied', 'color' => 'blue', 'border' => 'border-blue-900/40'],
            'interviewing' => ['label' => 'Interviewing', 'color' => 'yellow', 'border' => 'border-yellow-900/40'],
            'offered' => ['label' => 'Offer Received', 'color' => 'emerald', 'border' => 'border-emerald-900/40'],
            'rejected' => ['label' => 'Archived', 'color' => 'rose', 'border' => 'border-rose-900/40'],
        ];
    @endphp

    {{-- Kanban Board Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach($columns as $statusKey => $colMeta)
            @php
                $colApps = $apps->where('status', $statusKey);
            @endphp
            <div class="flex flex-col bg-slate-900/50 rounded-3xl p-4 border {{ $colMeta['border'] }} min-h-[500px] space-y-3">
                <div class="flex items-center justify-between px-1 pb-2 border-b border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-{{ $colMeta['color'] }}-400"></span>
                        <h3 class="text-xs font-bold font-mono uppercase text-slate-300">{{ $colMeta['label'] }}</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-950 text-slate-400 border border-white/5">
                        {{ count($colApps) }}
                    </span>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto">
                    @forelse($colApps as $item)
                        <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/5 hover:border-emerald-500/40 transition-all group">
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-emerald-400 uppercase tracking-wider">{{ $item->company }}</span>
                                    <h4 class="text-xs font-bold text-white font-heading mt-0.5">{{ $item->role }}</h4>
                                </div>
                                <div class="flex items-center gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="openEditModal({{ $item->id }})" class="p-1 text-slate-400 hover:text-white cursor-pointer" data-tooltip="Edit opportunity details and notes">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="deleteApplication({{ $item->id }})" class="p-1 text-slate-400 hover:text-rose-400 cursor-pointer" data-tooltip="Delete opportunity from board">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>

                            @if($item->salary_range)
                                <div class="text-[10px] font-mono text-yellow-400">
                                    {{ $item->salary_range }}
                                </div>
                            @endif

                            @if($item->notes)
                                <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ $item->notes }}
                                </p>
                            @endif

                            <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[10px] font-mono">
                                @if($item->job_url)
                                    <a href="{{ $item->job_url }}" target="_blank" class="text-slate-400 hover:text-emerald-400 flex items-center gap-0.5" data-tooltip="Open original job posting">
                                        <span>Link</span>
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                @else
                                    <span class="text-slate-600">&mdash;</span>
                                @endif

                                <select wire:change="updateStatus({{ $item->id }}, $event.target.value)" class="bg-slate-950 border border-slate-800 text-[10px] rounded-lg px-2 py-0.5 text-slate-300 focus:outline-none cursor-pointer" data-tooltip="Move opportunity to stage">
                                    <option value="saved" {{ $item->status === 'saved' ? 'selected' : '' }}>Wishlist</option>
                                    <option value="applied" {{ $item->status === 'applied' ? 'selected' : '' }}>Applied</option>
                                    <option value="interviewing" {{ $item->status === 'interviewing' ? 'selected' : '' }}>Interview</option>
                                    <option value="offered" {{ $item->status === 'offered' ? 'selected' : '' }}>Offer</option>
                                    <option value="rejected" {{ $item->status === 'rejected' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-[11px] text-slate-600 font-mono">
                            No jobs in {{ strtolower($colMeta['label']) }}
                        </div>
                    @endforelse
                </div>

                <button wire:click="openCreateModal('{{ $statusKey }}')" class="w-full py-2 rounded-xl bg-slate-950/60 hover:bg-slate-900 border border-dashed border-white/10 text-slate-400 hover:text-white text-xs font-mono transition-colors flex items-center justify-center gap-1 cursor-pointer" data-tooltip="Add job to {{ $colMeta['label'] }}">
                    <span>+ Add</span>
                </button>
            </div>
        @endforeach
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        {{ $editingId ? 'Edit Tracked Job' : 'Track New Opportunity' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg cursor-pointer" data-tooltip="Close modal">&times;</button>
                </div>

                <form wire:submit.prevent="saveApplication" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Company Name *</label>
                        <input type="text" wire:model="company" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Linear, OpenAI, Stripe" required />
                        @error('company') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Role / Position *</label>
                        <input type="text" wire:model="role" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Staff Backend Engineer" required />
                        @error('role') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Pipeline Stage *</label>
                        <select wire:model="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none">
                            <option value="saved">Wishlist / Saved</option>
                            <option value="applied">Applied</option>
                            <option value="interviewing">Interviewing</option>
                            <option value="offered">Offer Received</option>
                            <option value="rejected">Archived / Rejected</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Salary Range</label>
                            <input type="text" wire:model="salary_range" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="$180k - $220k" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Job URL</label>
                            <input type="url" wire:model="job_url" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="https://..." />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Notes & Interview Steps</label>
                        <textarea rows="3" wire:model="notes" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="Recruiter contacts, interview rounds, salary expectations..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs cursor-pointer" data-tooltip="Discard changes">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md cursor-pointer" data-tooltip="Save opportunity to board">
                            {{ $editingId ? 'Update Opportunity' : 'Save Opportunity' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
