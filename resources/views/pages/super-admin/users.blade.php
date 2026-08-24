<?php

use function Livewire\Volt\{state, layout, title, usesPagination, computed};
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

layout('layouts.super-admin');
title('User Management & Privileges');

usesPagination();

state([
    'search' => '',
    'roleFilter' => 'all', // all, super_admin, standard
    'successMessage' => '',
    'errorMessage' => '',
]);

$users = computed(function () {
    return User::query()
        ->with(['accounts', 'profile', 'memberAccounts'])
        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        })
        ->when($this->roleFilter === 'super_admin', function ($query) {
            $query->where('is_super_admin', true);
        })
        ->when($this->roleFilter === 'standard', function ($query) {
            $query->where('is_super_admin', false);
        })
        ->latest()
        ->paginate(12);
});

$toggleSuperAdmin = function (int $userId) {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (Auth::id() === $userId) {
        $this->errorMessage = 'Security restriction: You cannot revoke your own Super Admin root privileges.';
        return;
    }

    $targetUser = User::findOrFail($userId);
    $targetUser->is_super_admin = ! $targetUser->is_super_admin;
    $targetUser->save();

    $action = $targetUser->is_super_admin ? 'elevated to Super Administrator' : 'demoted to Standard User';
    $this->successMessage = "User '{$targetUser->name}' ({$targetUser->email}) has been successfully {$action}.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    SECURITY & IDENTITY
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-900 text-slate-400 border border-slate-800">
                    ROLE ELEVATION
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Users & Role Privileges
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage registered user accounts, promote or demote Super Admin root credentials, and audit security privileges.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-all">
                &larr; Telemetry Hub
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($successMessage)
    <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-200 text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ $successMessage }}</span>
        </div>
        <button wire:click="$set('successMessage', '')" class="text-amber-400 hover:text-white underline text-xs cursor-pointer">Dismiss</button>
    </div>
    @endif

    @if($errorMessage)
    <div class="p-4 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-200 text-xs sm:text-sm flex items-center justify-between font-mono animate-fadeIn">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>{{ $errorMessage }}</span>
        </div>
        <button wire:click="$set('errorMessage', '')" class="text-red-400 hover:text-white underline text-xs cursor-pointer">Dismiss</button>
    </div>
    @endif

    <!-- Toolbar: Search & Role Filter -->
    <div class="glass-card-dark rounded-3xl p-5 border border-amber-950/70 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search by user name or email address..." 
                       class="w-full pl-10 pr-4 py-2 rounded-xl bg-black border border-amber-950 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 font-mono">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Role Filter Pills -->
            <div class="flex items-center gap-1.5 font-mono text-xs overflow-x-auto pb-1 sm:pb-0">
                <button type="button" 
                        wire:click="$set('roleFilter', 'all')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $roleFilter === 'all' ? 'bg-amber-600 text-slate-950 shadow-md shadow-amber-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    All Users
                </button>
                <button type="button" 
                        wire:click="$set('roleFilter', 'super_admin')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $roleFilter === 'super_admin' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Super Admins
                </button>
                <button type="button" 
                        wire:click="$set('roleFilter', 'standard')"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer {{ $roleFilter === 'standard' ? 'bg-slate-800 text-white border border-slate-700' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Standard Users
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass-card-dark rounded-3xl overflow-hidden border border-amber-950/70 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs font-mono">
                <thead class="bg-black/95 border-b border-amber-950 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">User & Identity</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Super Admin Root</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Tenant Ownership</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px]">Portfolio Slug</th>
                        <th class="px-6 py-3.5 font-bold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-950/40">
                    @forelse($this->users as $userItem)
                    <tr class="hover:bg-amber-950/15 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-white text-sm">{{ $userItem->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $userItem->email }} &bull; ID: #{{ $userItem->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($userItem->is_super_admin)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 shadow-sm shadow-amber-950">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                SUPER ADMIN
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] text-slate-500 bg-slate-900 border border-slate-800">
                                Standard User
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            @if($userItem->accounts->first())
                            <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-[10px] text-slate-300">
                                {{ $userItem->accounts->first()->name }} ({{ $userItem->accounts->first()->plan_slug ?: 'free' }})
                            </span>
                            @else
                            <span class="text-slate-600">No Owned Tenant</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                            @if($userItem->profile && $userItem->profile->slug)
                            <a href="{{ url('/' . $userItem->profile->slug) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1">
                                <span>/{{ $userItem->profile->slug }}</span>
                                <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                            @else
                            <span class="text-slate-600">No profile</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <button type="button" 
                                    wire:click="toggleSuperAdmin({{ $userItem->id }})" 
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer {{ $userItem->is_super_admin ? 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30' }}">
                                {{ $userItem->is_super_admin ? 'Demote from SA' : 'Promote to SA' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">No users found matching query.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-amber-950/60 bg-black/60">
            {{ $this->users->links() }}
        </div>
    </div>
</div>
