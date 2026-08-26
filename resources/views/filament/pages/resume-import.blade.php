<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">
        @if($step === 1)
            {{-- Step 1: Upload & Extract --}}
            <div class="p-8 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upload Your Existing Resume</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Our AI extracts your experience, skills, bio, and projects from your PDF or plain text resume and maps them directly into your portfolio.
                    </p>
                </div>

                <form wire:submit="parseResume" class="space-y-6">
                    {{-- PDF Upload Input --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                            PDF Resume File
                        </label>
                        <input
                            type="file"
                            wire:model="resumeFile"
                            accept="application/pdf"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-gray-950 hover:file:bg-amber-400 file:cursor-pointer border border-gray-300 dark:border-gray-700 rounded-xl p-2 bg-gray-50 dark:bg-gray-800/50"
                        />
                        <div wire:loading wire:target="resumeFile" class="text-xs text-amber-500 mt-2 font-medium">
                            Uploading PDF...
                        </div>
                    </div>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-200 dark:border-gray-800"></div>
                        <span class="flex-shrink mx-4 text-xs font-bold text-gray-400 uppercase">Or Paste Resume Text</span>
                        <div class="flex-grow border-t border-gray-200 dark:border-gray-800"></div>
                    </div>

                    {{-- Text Area Input --}}
                    <div>
                        <textarea
                            wire:model="resumeText"
                            rows="6"
                            placeholder="Paste plain text resume content here..."
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white p-3.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="min-h-[48px] inline-flex items-center justify-center gap-2 py-3 px-8 rounded-2xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md shadow-amber-500/20 transition disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="parseResume">⚡ Parse with AI</span>
                            <span wire:loading wire:target="parseResume">Parsing Resume...</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            {{-- Step 2: Review & Confirm --}}
            <div class="space-y-6">
                <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Review Extracted Resume Data</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            Verify the extracted profile details, experiences, and skills before applying them to your live portfolio.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="min-h-[44px] px-4 rounded-xl text-xs font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 inline-flex items-center cursor-pointer"
                    >
                        &larr; Start Over
                    </button>
                </div>

                {{-- Profile Info Card --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Profile Information</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 font-semibold">Full Name</label>
                            <input type="text" wire:model="parsedData.full_name" class="w-full min-h-[44px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base p-2.5 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-semibold">Headline</label>
                            <input type="text" wire:model="parsedData.headline" class="w-full min-h-[44px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base p-2.5 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-semibold">Email</label>
                            <input type="text" wire:model="parsedData.email" class="w-full min-h-[44px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base p-2.5 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-semibold">Location</label>
                            <input type="text" wire:model="parsedData.location" class="w-full min-h-[44px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base p-2.5 text-gray-900 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-semibold">Professional Bio</label>
                        <textarea wire:model="parsedData.bio" rows="3" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base p-3 text-gray-900 dark:text-white"></textarea>
                    </div>
                </div>

                {{-- Experience Review --}}
                @if(!empty($parsedData['experiences']))
                    <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Extracted Work Experience ({{ count($parsedData['experiences']) }})</h4>
                        <div class="space-y-3">
                            @foreach($parsedData['experiences'] as $index => $exp)
                                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 space-y-2">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $exp['title'] ?? '' }} at {{ $exp['company'] ?? '' }}</div>
                                    <div class="text-xs text-gray-500">{{ $exp['start_date'] ?? '' }} - {{ $exp['is_current'] ? 'Present' : ($exp['end_date'] ?? '') }}</div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300">{{ $exp['description'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Skills Review --}}
                @if(!empty($parsedData['skills']))
                    <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Extracted Skills ({{ count($parsedData['skills']) }})</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($parsedData['skills'] as $skill)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    {{ $skill['name'] ?? '' }} ({{ $skill['category'] ?? 'General' }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-4">
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="min-h-[48px] py-2.5 px-6 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition inline-flex items-center justify-center cursor-pointer"
                    >
                        Discard & Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="importParsedData"
                        class="min-h-[48px] py-3 px-8 rounded-xl text-xs font-bold text-gray-950 bg-emerald-400 hover:bg-emerald-300 shadow-md shadow-emerald-400/20 transition inline-flex items-center justify-center cursor-pointer"
                    >
                        ✓ Import All into Portfolio
                    </button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
