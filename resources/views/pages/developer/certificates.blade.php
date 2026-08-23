<?php

use function Livewire\Volt\{state, layout, title, rules};
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

layout('layouts.dashboard');
title('Certificates & Accreditations');

state([
    'showModal' => false,
    'editingId' => null,
    'title' => '',
    'slug' => '',
    'issuer' => '',
    'issue_date' => '',
    'credential_url' => '',
    'sort_order' => 0,
    'savedMessage' => '',
]);

rules([
    'title' => 'required|string|max:255',
    'slug' => 'required|string|max:255',
    'issuer' => 'required|string|max:255',
    'issue_date' => 'nullable|date',
    'credential_url' => 'nullable|url|max:255',
    'sort_order' => 'integer',
]);

$getCertificates = function () {
    $profile = Auth::user()?->profile;
    return $profile ? Certificate::where('profile_id', $profile->id)->orderBy('issue_date', 'desc')->get() : collect();
};

$openCreateModal = function () {
    $this->reset(['editingId', 'title', 'slug', 'issuer', 'issue_date', 'credential_url', 'sort_order']);
    $this->showModal = true;
};

$openEditModal = function ($id) {
    $profile = Auth::user()?->profile;
    $cert = Certificate::where('profile_id', $profile?->id)->findOrFail($id);

    $this->editingId = $cert->id;
    $this->title = $cert->title;
    $this->slug = $cert->slug;
    $this->issuer = $cert->issuer;
    $this->issue_date = $cert->issue_date ? $cert->issue_date->format('Y-m-d') : '';
    $this->credential_url = $cert->credential_url ?? '';
    $this->sort_order = (int) $cert->sort_order;
    $this->showModal = true;
};

$saveCertificate = function () {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    if (! $this->slug && $this->title) {
        $this->slug = Str::slug($this->title);
    }

    $this->validate();

    $data = [
        'profile_id' => $profile->id,
        'title' => $this->title,
        'slug' => Str::slug($this->slug),
        'issuer' => $this->issuer,
        'issue_date' => $this->issue_date ?: null,
        'credential_url' => $this->credential_url ?: null,
        'sort_order' => (int) $this->sort_order,
    ];

    if ($this->editingId) {
        $cert = Certificate::where('profile_id', $profile->id)->findOrFail($this->editingId);
        $cert->update($data);
        $this->savedMessage = 'Certificate updated successfully!';
    } else {
        Certificate::create($data);
        $this->savedMessage = 'Certificate added successfully!';
    }

    $this->showModal = false;
    $this->reset(['editingId', 'title', 'slug', 'issuer', 'issue_date', 'credential_url', 'sort_order']);
};

$deleteCertificate = function ($id) {
    $profile = Auth::user()?->profile;
    if (! $profile) return;

    Certificate::where('profile_id', $profile->id)->where('id', $id)->delete();
    $this->savedMessage = 'Certificate deleted.';
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
                Certificates & Accreditations
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Display verified cloud certifications, professional licenses, and course accreditations.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-emerald-950/50 flex items-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Certificate</span>
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
        $certificates = $this->getCertificates();
    @endphp

    @if($certificates->isEmpty())
        <div class="glass-card rounded-3xl p-12 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <h3 class="text-lg font-bold text-white font-heading">No Certificates Added Yet</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">Add your AWS, Google Cloud, Kubernetes, or other engineering credentials to demonstrate verified domain expertise.</p>
            <button wire:click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs transition-all shadow-md inline-flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add First Certificate</span>
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($certificates as $cert)
                <div class="glass-card glass-card-hover rounded-3xl p-6 border border-white/5 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $cert->issuer }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button wire:click="openEditModal({{ $cert->id }})" class="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button wire:click="deleteCertificate({{ $cert->id }})" wire:confirm="Are you sure you want to delete this certificate?" class="p-1.5 rounded-lg bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <h3 class="text-base font-bold text-white font-heading">{{ $cert->title }}</h3>
                    </div>

                    <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-mono text-[11px]">
                            {{ $cert->issue_date ? 'Issued: ' . $cert->issue_date->format('M Y') : 'Active Credential' }}
                        </span>

                        @if($cert->credential_url)
                            <a href="{{ $cert->credential_url }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                                <span>Verify &rarr;</span>
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
            <div class="glass-card bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-6">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <h3 class="text-lg font-bold font-heading text-white">
                        {{ $editingId ? 'Edit Certificate' : 'Add Certificate' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>

                <form wire:submit.prevent="saveCertificate" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Certificate Name *</label>
                        <input type="text" wire:model="title" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. AWS Certified Solutions Architect" required />
                        @error('title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Slug *</label>
                        <input type="text" wire:model="slug" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs focus:border-emerald-500 focus:outline-none" placeholder="aws-solutions-architect" required />
                        @error('slug') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Issuing Organization *</label>
                        <input type="text" wire:model="issuer" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" placeholder="e.g. Amazon Web Services, Google, Linux Foundation" required />
                        @error('issuer') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Issue Date</label>
                        <input type="date" wire:model="issue_date" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Verification URL</label>
                        <input type="url" wire:model="credential_url" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-emerald-500 focus:outline-none font-mono" placeholder="https://www.credly.com/..." />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shadow-md">
                            {{ $editingId ? 'Update Certificate' : 'Save Certificate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
