<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">
        {{-- Invite Member Card --}}
        @if($this->isOwner())
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Invite Team Member</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Add collaborators, editors, or clients to manage portfolio data and resumes together.
                </p>

                <form wire:submit="inviteMember" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input
                            type="text"
                            wire:model="inviteName"
                            required
                            placeholder="Alex Morgan"
                            class="w-full min-h-[48px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base px-3.5 text-gray-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                        <input
                            type="email"
                            wire:model="inviteEmail"
                            required
                            placeholder="alex@example.com"
                            class="w-full min-h-[48px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base px-3.5 text-gray-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Role *</label>
                        <select
                            wire:model="inviteRole"
                            class="w-full min-h-[48px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base px-3 text-gray-900 dark:text-white"
                        >
                            <option value="editor">Editor (Can edit data)</option>
                            <option value="viewer">Viewer (Read-only)</option>
                            <option value="owner">Co-Owner (Full access)</option>
                        </select>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full min-h-[48px] px-5 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md shadow-amber-500/20 transition inline-flex items-center justify-center cursor-pointer"
                        >
                            + Add Member
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Team Members List --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Account Members
            </h4>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                {{-- Account Owner Row --}}
                @if($account = $this->getAccount())
                    @if($account->owner)
                        <div class="py-3.5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-amber-500/20 text-amber-500 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($account->owner->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $account->owner->name }}
                                        <span class="text-xs font-normal text-gray-400">(You / Account Creator)</span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $account->owner->email }}</p>
                                </div>
                            </div>

                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                Primary Owner
                            </span>
                        </div>
                    @endif
                @endif

                {{-- Invited Members --}}
                @forelse($this->members as $member)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($this->isOwner())
                                <select
                                    wire:change="updateMemberRole({{ $member->id }}, $event.target.value)"
                                    class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs p-1.5 text-gray-900 dark:text-white font-semibold"
                                >
                                    <option value="owner" {{ $member->pivot->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                    <option value="editor" {{ $member->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="viewer" {{ $member->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                </select>

                                <button
                                    type="button"
                                    wire:click="removeMember({{ $member->id }})"
                                    class="p-1.5 rounded-lg text-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition font-semibold"
                                    title="Remove from Team"
                                >
                                    Remove
                                </button>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 uppercase">
                                    {{ $member->pivot->role }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                        No team members added yet. Invite your colleagues above.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
