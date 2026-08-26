<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Actions & Overview --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Track and manage your job applications across each interview stage.
                </p>
            </div>

            <button
                type="button"
                wire:click="openAddModal('saved')"
                class="min-h-[44px] inline-flex items-center justify-center gap-2 py-2.5 px-6 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md shadow-amber-500/20 transition cursor-pointer"
            >
                <span>+ Add Application</span>
            </button>
        </div>

        {{-- 5-Column Kanban Board Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start">
            @foreach($this->columns as $statusKey => $column)
                <div class="bg-gray-100/70 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-800 rounded-2xl p-3.5 space-y-3 min-h-[500px] flex flex-col">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                {{ $column['title'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $column['badge_color'] }}">
                                {{ count($column['items']) }}
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="openAddModal('{{ $statusKey }}')"
                            class="text-gray-400 hover:text-amber-500 text-sm font-bold transition p-1"
                            title="Add to {{ $column['title'] }}"
                        >
                            +
                        </button>
                    </div>

                    {{-- Cards List --}}
                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[700px]">
                        @forelse($column['items'] as $app)
                            <div class="p-4 rounded-xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700/80 shadow-sm hover:shadow-md transition space-y-2.5 group">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h5 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                            {{ $app->role }}
                                        </h5>
                                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-0.5">
                                            {{ $app->company }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition">
                                        <button
                                            type="button"
                                            wire:click="editApplication({{ $app->id }})"
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1"
                                            title="Edit"
                                        >
                                            ✎
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteApplication({{ $app->id }})"
                                            class="text-gray-400 hover:text-rose-500 p-1"
                                            title="Delete"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>

                                @if($app->salary_range)
                                    <div class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        💰 {{ $app->salary_range }}
                                    </div>
                                @endif

                                @if($app->notes)
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $app->notes }}
                                    </p>
                                @endif

                                @if($app->applied_at)
                                    <p class="text-[10px] text-gray-400">
                                        Applied: {{ $app->applied_at->format('M d, Y') }}
                                    </p>
                                @endif

                                {{-- Quick Status Transition Controls --}}
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-[10px]">
                                    <span class="text-gray-400">Move:</span>
                                    <div class="flex gap-1 flex-wrap justify-end">
                                        @if($statusKey !== 'saved')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'saved')" class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200">Save</button>
                                        @endif
                                        @if($statusKey !== 'applied')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'applied')" class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 hover:bg-blue-200">Apply</button>
                                        @endif
                                        @if($statusKey !== 'interviewing')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'interviewing')" class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 hover:bg-amber-200">Interview</button>
                                        @endif
                                        @if($statusKey !== 'offer')
                                            <button type="button" wire:click="updateStatus({{ $app->id }}, 'offer')" class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200">Offer</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 rounded-xl border border-dashed border-gray-300 dark:border-gray-800 text-center text-gray-400 text-xs">
                                No applications
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add / Edit Application Modal --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-800">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $editingId ? 'Edit Job Application' : 'Add New Application' }}
                        </h4>
                        <button type="button" wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            ✕
                        </button>
                    </div>

                    <form wire:submit="saveApplication" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Company *</label>
                                <input type="text" wire:model="company" required placeholder="e.g. Google, Acme" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Role Title *</label>
                                <input type="text" wire:model="role" required placeholder="e.g. Staff Backend Engineer" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Status</label>
                                <select wire:model="status" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white">
                                    <option value="saved">Bookmarked</option>
                                    <option value="applied">Applied</option>
                                    <option value="interviewing">Interviewing</option>
                                    <option value="offer">Offer Received</option>
                                    <option value="rejected">Archived / Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Salary Range</label>
                                <input type="text" wire:model="salary_range" placeholder="e.g. $140k - $160k" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Job URL</label>
                            <input type="url" wire:model="job_url" placeholder="https://careers.company.com/..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Notes & Interview Steps</label>
                            <textarea wire:model="notes" rows="3" placeholder="Recruiter name, technical interview rounds, key requirements..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="$set('showModal', false)" class="py-2 px-4 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                                Cancel
                            </button>
                            <button type="submit" class="py-2.5 px-6 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md transition">
                                {{ $editingId ? 'Save Changes' : 'Create Application' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
