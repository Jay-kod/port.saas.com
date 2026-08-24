<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Profile;
use App\Models\Account;
use App\Models\Theme;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Client Portfolios Manager');

state([
    'showModal' => false,
    'editingProfileId' => null,
    'fullName' => '',
    'headline' => '',
    'slug' => '',
    'bio' => '',
    'themeId' => 1,
    'isPublished' => false,
    'search' => '',
    'successMessage' => '',
    'errorMessage' => '',
]);

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$profiles = computed(function () {
    if (! $this->account) return collect();
    $query = $this->account->profiles()->latest();
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('full_name', 'like', "%{$this->search}%")
              ->orWhere('headline', 'like', "%{$this->search}%")
              ->orWhere('slug', 'like', "%{$this->search}%");
        });
    }
    return $query->get();
});

$themes = computed(function () {
    return Theme::all();
});

$activeProfile = computed(function () {
    if (session('active_profile_id')) {
        return Profile::find(session('active_profile_id'));
    }
    return $this->account?->profiles()->first();
});

$openCreateModal = function () {
    $this->reset(['editingProfileId', 'fullName', 'headline', 'slug', 'bio', 'themeId', 'isPublished']);
    $this->themeId = 1;
    $this->showModal = true;
};

$openEditModal = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->findOrFail($profileId);
    $this->editingProfileId = $profile->id;
    $this->fullName = $profile->full_name;
    $this->headline = $profile->headline;
    $this->slug = $profile->slug;
    $this->bio = $profile->bio;
    $this->themeId = $profile->theme_id ?: 1;
    $this->isPublished = (bool)$profile->is_published;
    $this->showModal = true;
};

$saveClient = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->account) {
        $this->errorMessage = 'No active agency account found.';
        return;
    }

    if (! $this->editingProfileId && ! $this->account->canCreateProfile()) {
        $this->errorMessage = 'Profile limit reached for current plan tier.';
        return;
    }

    $validated = $this->validate([
        'fullName' => ['required', 'string', 'max:255'],
        'headline' => ['nullable', 'string', 'max:255'],
        'slug' => ['nullable', 'string', 'max:100', 'alpha_dash'],
        'bio' => ['nullable', 'string'],
        'themeId' => ['required', 'integer'],
        'isPublished' => ['boolean'],
    ]);

    $validThemeId = Theme::find($this->themeId)?->id;

    if ($this->editingProfileId) {
        $profile = Profile::where('account_id', $this->account->id)->findOrFail($this->editingProfileId);
        
        $slug = !empty($this->slug) ? Str::slug($this->slug) : $profile->slug;
        if ($slug !== $profile->slug && Profile::where('slug', $slug)->where('id', '!=', $profile->id)->exists()) {
            $this->errorMessage = 'This URL slug is already taken by another portfolio.';
            return;
        }

        $profile->update([
            'full_name' => $this->fullName,
            'headline' => $this->headline ?: 'Software Engineer',
            'slug' => $slug,
            'bio' => $this->bio,
            'theme_id' => $validThemeId,
            'is_published' => $this->isPublished,
        ]);

        $this->successMessage = "Client profile '{$profile->full_name}' updated successfully!";
    } else {
        $slug = !empty($this->slug) 
            ? Str::slug($this->slug) 
            : Str::slug($this->fullName) . '-' . Str::random(4);

        $baseSlug = $slug;
        $counter = 1;
        while (Profile::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $profile = Profile::create([
            'account_id' => $this->account->id,
            'user_id' => Auth::id(),
            'full_name' => $this->fullName,
            'headline' => $this->headline ?: 'Software Engineer',
            'slug' => $slug,
            'bio' => $this->bio,
            'theme_id' => $validThemeId,
            'theme_mode_default' => 'dark',
            'is_published' => $this->isPublished,
        ]);

        session(['active_profile_id' => $profile->id]);
        $this->successMessage = "Client profile '{$profile->full_name}' created successfully!";
    }

    $this->showModal = false;
};

$togglePublish = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->findOrFail($profileId);
    $profile->update(['is_published' => !$profile->is_published]);
    $this->successMessage = "Profile '{$profile->full_name}' status changed to " . ($profile->is_published ? 'Live' : 'Draft');
};

$switchProfile = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->findOrFail($profileId);
    session(['active_profile_id' => $profile->id]);
    $this->successMessage = "Active client context switched to '{$profile->full_name}'. Developer studios will now edit this client's content.";
};

$deleteProfile = function ($profileId) {
    $profile = Profile::where('account_id', $this->account?->id)->findOrFail($profileId);
    if ($this->account->profiles()->count() <= 1) {
        $this->errorMessage = 'Cannot delete the only profile in the agency account.';
        return;
    }

    $name = $profile->full_name;
    $profile->delete();

    if (session('active_profile_id') == $profileId) {
        session()->forget('active_profile_id');
    }

    $this->successMessage = "Client profile '{$name}' deleted successfully.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    CLIENT ROSTER
                </span>
                <span class="text-xs text-slate-500 font-mono">MULTI-TENANT DIRECTORY</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Client Portfolios Manager
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Provision, edit, switch active client context, and manage live publishing states.
            </p>
        </div>

        <button type="button" wire:click="openCreateModal" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 hover:opacity-95 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-teal-950/40 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>+ Provision Client</span>
        </button>
    </div>

    {{-- Feedback Notifications --}}
    @if($successMessage)
        <div class="p-4 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')" class="text-teal-400 hover:text-white">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    @endif

    {{-- Search & Filtering Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="relative w-full sm:w-80">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search clients, roles, slugs..." class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
        </div>
        <div class="text-xs text-slate-400 font-mono">
            Showing <strong class="text-white">{{ $this->profiles->count() }}</strong> client records
        </div>
    </div>

    {{-- Client Roster Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($this->profiles as $profile)
            @php
                $isActive = ($this->activeProfile?->id === $profile->id);
                $projectsCount = $profile->projects()->count();
                $skillsCount = $profile->skills()->count();
            @endphp
            <div class="glass-card rounded-3xl p-6 relative overflow-hidden flex flex-col justify-between space-y-6 border {{ $isActive ? 'border-teal-500/50 bg-teal-950/10' : 'border-white/5 hover:border-teal-500/30' }} transition-all group">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl {{ $isActive ? 'bg-teal-500/20 text-teal-300 border-teal-500/40' : 'bg-slate-900 text-slate-300 border-slate-800' }} border flex items-center justify-center font-bold text-base font-heading">
                                {{ strtoupper(substr($profile->full_name ?: 'C', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold font-heading text-white group-hover:text-teal-400 transition-colors">
                                    {{ $profile->full_name ?: 'Unnamed Client' }}
                                </h3>
                                <p class="text-xs text-slate-400 truncate max-w-[180px]">{{ $profile->headline ?: 'No headline' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($isActive)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">ACTIVE</span>
                            @endif
                            <button type="button" wire:click="togglePublish({{ $profile->id }})" class="cursor-pointer">
                                @if($profile->is_published)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">LIVE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700">DRAFT</span>
                                @endif
                            </button>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2 text-xs font-mono">
                        <div class="flex justify-between text-slate-400">
                            <span>Slug:</span>
                            <span class="text-white">/{{ $profile->slug }}</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Assets:</span>
                            <span class="text-slate-300">{{ $projectsCount }} Projects &bull; {{ $skillsCount }} Skills</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Theme:</span>
                            <span class="text-teal-400">{{ $profile->theme?->name ?: 'Standard' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/5 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        @if($isActive)
                            <span class="text-teal-400 font-bold flex items-center gap-1 font-mono text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> Active Workspace
                            </span>
                        @else
                            <button type="button" wire:click="switchProfile({{ $profile->id }})" class="text-slate-300 hover:text-teal-300 font-semibold flex items-center gap-1 transition-colors cursor-pointer">
                                <span>Switch Context</span>
                                <span>&rarr;</span>
                            </button>
                        @endif

                        @if($profile->is_published)
                            <a href="{{ url('/' . $profile->slug) }}" target="_blank" class="text-slate-400 hover:text-white flex items-center gap-1">
                                <span>Preview</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/5">
                        <button type="button" wire:click="openEditModal({{ $profile->id }})" class="py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 text-xs font-semibold text-center transition-all cursor-pointer">
                            Edit Details
                        </button>
                        <button type="button" wire:click="deleteProfile({{ $profile->id }})" wire:confirm="Are you sure you want to delete this client profile? All associated projects and data will be permanently removed." class="py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold text-center transition-all cursor-pointer">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-3xl p-12 text-center text-slate-500 space-y-3">
                <svg class="w-12 h-12 mx-auto text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                <p class="text-sm font-medium">No client portfolios match your filter.</p>
            </div>
        @endforelse
    </div>

    {{-- CREATE / EDIT CLIENT MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-teal-500/30 bg-slate-950 shadow-2xl relative space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-white">
                            {{ $editingProfileId ? 'Edit Client Profile' : 'Provision New Client Portfolio' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Configure client identity, custom slug, and design theme.</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="saveClient" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Client Full Name *</label>
                        <input type="text" wire:model="fullName" placeholder="e.g. David Vance" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                        @error('fullName') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Headline</label>
                        <input type="text" wire:model="headline" placeholder="e.g. Principal Distributed Systems Engineer" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Custom URL Slug</label>
                        <div class="flex items-center rounded-xl bg-slate-900 border border-slate-800 focus-within:border-teal-500 overflow-hidden">
                            <span class="px-3 text-slate-500 font-mono text-xs">saas.com/</span>
                            <input type="text" wire:model="slug" placeholder="david-vance" class="w-full py-2.5 pr-4 bg-transparent text-white text-xs focus:outline-none font-mono" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bio Summary</label>
                        <textarea wire:model="bio" rows="3" placeholder="Brief executive summary..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Design Theme</label>
                            <select wire:model="themeId" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none">
                                @foreach($this->themes as $themeItem)
                                    <option value="{{ $themeItem->id }}">{{ $themeItem->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="isPublished" class="w-4 h-4 rounded text-teal-500 bg-slate-900 border-slate-800 focus:ring-teal-500" />
                                <span class="text-xs text-white font-medium">Publish Live Immediately</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-teal-950/50">
                            {{ $editingProfileId ? 'Save Changes' : 'Provision Client' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
