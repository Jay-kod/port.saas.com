<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">
        {{-- GDPR Data Portability & Export --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold text-xl">
                    📦
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">GDPR Data Subject Export</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Download a machine-readable JSON archive containing all your portfolio profiles, experiences, projects, skills, resume generations, cover letters, and job tracking records.
                    </p>
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="button"
                    wire:click="exportData"
                    class="py-2.5 px-5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-md shadow-blue-500/20 transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Data Archive (JSON)
                </button>
            </div>
        </div>

        {{-- GDPR Account Deletion (Owner Only) --}}
        @if($this->isOwner())
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-rose-200 dark:border-rose-900/40 shadow-sm space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold text-xl">
                        ⚠️
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-rose-600 dark:text-rose-400">Delete Account & Data (Right to be Forgotten)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Permanently delete this account and completely erase all associated profiles, custom domains, projects, resume generations, and billing subscriptions. This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="pt-2 max-w-md space-y-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Type <span class="font-mono font-bold text-rose-500">DELETE</span> to confirm:
                    </label>
                    <div class="flex items-center gap-3">
                        <input
                            type="text"
                            wire:model="deleteConfirmation"
                            placeholder="DELETE"
                            class="flex-1 rounded-xl border border-rose-300 dark:border-rose-900 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white"
                        />
                        <button
                            type="button"
                            wire:click="deleteAccount"
                            wire:confirm="Are you sure you want to permanently delete your account and all data?"
                            class="py-2.5 px-5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-md shadow-rose-500/20 transition whitespace-nowrap"
                        >
                            Delete Account Forever
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
