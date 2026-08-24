<?php

use function Livewire\Volt\{state, layout, title, computed};
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

layout('layouts.dashboard');
title('Team & Seat Management');

state([
    'inviteEmail' => '',
    'inviteRole' => 'editor',
    'showInviteModal' => false,
    'successMessage' => '',
    'errorMessage' => '',
]);

$account = computed(function () {
    $user = Auth::user();
    return (session('active_tenant_id') ? Account::find(session('active_tenant_id')) : null)
        ?? $user?->accounts()->first()
        ?? $user?->memberAccounts()->first();
});

$members = computed(function () {
    return $this->account ? $this->account->members()->get() : collect();
});

$ownerUser = computed(function () {
    return $this->account?->owner;
});

$seatLimit = 10;
$usedSeats = computed(function () {
    return $this->members->count() + 1; // +1 for owner
});

$inviteMember = function () {
    $this->errorMessage = '';
    $this->successMessage = '';

    if (! $this->account) {
        $this->errorMessage = 'No active agency account found.';
        return;
    }

    if ($this->usedSeats >= $this->seatLimit) {
        $this->errorMessage = 'Seat limit reached. Your agency tier accommodates up to 10 active team seats.';
        return;
    }

    $this->validate([
        'inviteEmail' => ['required', 'email'],
        'inviteRole' => ['required', 'in:owner,editor,viewer'],
    ]);

    $existingUser = User::where('email', $this->inviteEmail)->first();

    if (! $existingUser) {
        // Automatically provision an invited member user account with default credentials
        $nameParts = explode('@', $this->inviteEmail);
        $name = ucwords(str_replace(['.', '_', '-'], ' ', $nameParts[0]));
        $existingUser = User::create([
            'name' => $name,
            'email' => $this->inviteEmail,
            'password' => bcrypt('Password123!'),
        ]);
    }

    if ($existingUser->id === $this->account->owner_user_id) {
        $this->errorMessage = 'This user is already the agency account owner.';
        return;
    }

    if ($this->account->members()->where('user_id', $existingUser->id)->exists()) {
        // Update existing member role
        $this->account->members()->updateExistingPivot($existingUser->id, [
            'role' => $this->inviteRole,
        ]);
        $this->successMessage = "Updated role for '{$existingUser->email}' to {$this->inviteRole}.";
    } else {
        // Attach member
        $this->account->members()->attach($existingUser->id, [
            'role' => $this->inviteRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->successMessage = "Team seat invitation sent to '{$existingUser->email}' as {$this->inviteRole}!";
    }

    $this->reset(['inviteEmail', 'inviteRole']);
    $this->showInviteModal = false;
};

$updateRole = function ($userId, $newRole) {
    if (! in_array($newRole, ['owner', 'editor', 'viewer'])) return;
    $this->account?->members()->updateExistingPivot($userId, ['role' => $newRole]);
    $this->successMessage = "Member role updated successfully.";
};

$removeMember = function ($userId) {
    $this->account?->members()->detach($userId);
    $this->successMessage = "Member seat removed from agency workspace.";
};

?>

<div class="space-y-8 max-w-7xl mx-auto pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20">
                    TEAM COLLABORATION
                </span>
                <span class="text-xs text-slate-500 font-mono">SEAT OCCUPANCY</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-white tracking-tight">
                Agency Team & Seats Manager
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Collaborate with agency designers, developers, and project managers across client portfolios.
            </p>
        </div>

        <button type="button" wire:click="$set('showInviteModal', true)" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 via-teal-400 to-cyan-400 hover:opacity-95 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-teal-950/40 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span>+ Invite Team Member</span>
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

    {{-- Seat Capacity Bar Card --}}
    <div class="glass-card rounded-3xl p-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-mono uppercase text-slate-400 font-semibold">Seat Utilization</span>
                <div class="text-2xl font-extrabold font-heading text-white mt-1">
                    {{ $this->usedSeats }} of {{ $this->seatLimit }} Seats Active
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Your agency tier includes up to 10 concurrent team collaborator seats.</p>
            </div>

            <div class="w-full sm:w-64 space-y-2">
                <div class="flex justify-between text-xs font-mono">
                    <span class="text-teal-400 font-bold">{{ (int)round(($this->usedSeats / $this->seatLimit) * 100) }}% Occupied</span>
                    <span class="text-slate-400">{{ $this->seatLimit - $this->usedSeats }} Seats Left</span>
                </div>
                <div class="w-full bg-slate-900 border border-slate-800 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-teal-500 to-cyan-400 h-full rounded-full transition-all duration-500" style="width: {{ ($this->usedSeats / $this->seatLimit) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Members Roster Table --}}
    <div class="glass-card rounded-3xl overflow-hidden border border-white/10">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold font-heading text-white">Active Agency Roster</h3>
                <p class="text-xs text-slate-400">Team members with delegated access to client portfolios.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $this->usedSeats }} Members</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-900/80 border-b border-white/5 text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Team Member</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Email</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Role / Tier</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px]">Permissions</th>
                        <th class="px-6 py-3.5 font-semibold uppercase tracking-wider text-[10px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    {{-- Agency Owner (Fixed) --}}
                    @if($this->ownerUser)
                        <tr class="bg-teal-950/20">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-teal-500/20 border border-teal-500/40 text-teal-300 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($this->ownerUser->name ?: 'O', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-2">
                                            <span>{{ $this->ownerUser->name }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-teal-500/30 text-teal-300 border border-teal-500/40 font-bold">PRIMARY OWNER</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400">Account Creator</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-300">
                                {{ $this->ownerUser->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                    OWNER
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                Full Control (Billing & Settings)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-slate-500 italic">
                                Permanent Seat
                            </td>
                        </tr>
                    @endif

                    {{-- Other Team Members --}}
                    @forelse($this->members as $member)
                        @php
                            $role = $member->pivot->role ?? 'editor';
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($member->name ?: 'M', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $member->name }}</div>
                                        <div class="text-[10px] text-slate-400">Joined {{ $member->pivot->created_at ? $member->pivot->created_at->diffForHumans() : 'recently' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-300">
                                {{ $member->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold
                                    {{ $role === 'owner' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : '' }}
                                    {{ $role === 'editor' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : '' }}
                                    {{ $role === 'viewer' ? 'bg-slate-800 text-slate-400 border border-slate-700' : '' }}
                                ">
                                    {{ strtoupper($role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                {{ $role === 'owner' ? 'Full Control' : ($role === 'editor' ? 'Client & Content Management' : 'Read-Only Analytics') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <select wire:change="updateRole({{ $member->id }}, $event.target.value)" class="px-2 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 text-[11px] focus:outline-none">
                                    <option value="owner" {{ $role === 'owner' ? 'selected' : '' }}>Owner</option>
                                    <option value="editor" {{ $role === 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="viewer" {{ $role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                </select>
                                <button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="Revoke team access for this member?" class="text-rose-400 hover:text-rose-300 font-semibold cursor-pointer">
                                    Revoke
                                </button>
                            </td>
                        </tr>
                    @empty
                        @if(!$this->ownerUser)
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">
                                    No additional team members invited yet.
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ROLE PERMISSIONS MATRIX --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-6">
        <div>
            <h3 class="text-lg font-bold font-heading text-white">Role Capabilities & Permissions Matrix</h3>
            <p class="text-xs text-slate-400 mt-0.5">Granular access control boundaries defined across agency tiers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-slate-950/70 border border-teal-500/20 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-teal-400 uppercase">Agency Owner</span>
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                </div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-teal-400">&check;</span> Manage Stripe billing & invoices</li>
                    <li class="flex items-center gap-2"><span class="text-teal-400">&check;</span> Provision & delete client portfolios</li>
                    <li class="flex items-center gap-2"><span class="text-teal-400">&check;</span> Invite & revoke team member seats</li>
                    <li class="flex items-center gap-2"><span class="text-teal-400">&check;</span> Configure white-label branding</li>
                    <li class="flex items-center gap-2"><span class="text-teal-400">&check;</span> Connect custom apex domains</li>
                </ul>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950/70 border border-cyan-500/20 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-cyan-400 uppercase">Agency Editor</span>
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                </div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-rose-400">&times;</span> No access to billing/plans</li>
                    <li class="flex items-center gap-2"><span class="text-cyan-400">&check;</span> Edit projects, skills & bios</li>
                    <li class="flex items-center gap-2"><span class="text-cyan-400">&check;</span> Generate AI tailored resumes</li>
                    <li class="flex items-center gap-2"><span class="text-cyan-400">&check;</span> Publish / unpublish portfolios</li>
                    <li class="flex items-center gap-2"><span class="text-rose-400">&times;</span> Cannot remove other members</li>
                </ul>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-slate-400 uppercase">Agency Viewer</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-center gap-2"><span class="text-rose-400">&times;</span> No access to billing or settings</li>
                    <li class="flex items-center gap-2"><span class="text-slate-400">&check;</span> Read-only portfolio previews</li>
                    <li class="flex items-center gap-2"><span class="text-slate-400">&check;</span> View multi-client analytics</li>
                    <li class="flex items-center gap-2"><span class="text-rose-400">&times;</span> Cannot edit or delete assets</li>
                    <li class="flex items-center gap-2"><span class="text-rose-400">&times;</span> Cannot generate AI documents</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- INVITE MODAL --}}
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-md w-full border border-teal-500/30 bg-slate-950 shadow-2xl relative space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-white">Invite Team Collaborator</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Grant access to manage client developer portfolios.</p>
                    </div>
                    <button type="button" wire:click="$set('showInviteModal', false)" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="inviteMember" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Collaborator Email *</label>
                        <input type="email" wire:model="inviteEmail" placeholder="colleague@agency.com" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none" />
                        @error('inviteEmail') <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Assigned Role</label>
                        <select wire:model="inviteRole" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none">
                            <option value="editor">Editor (Can edit client portfolios & AI resumes)</option>
                            <option value="viewer">Viewer (Read-only analytics & previews)</option>
                            <option value="owner">Owner (Full access including billing)</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" wire:click="$set('showInviteModal', false)" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-teal-950/50">
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
