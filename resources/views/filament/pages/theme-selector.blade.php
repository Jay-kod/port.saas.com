<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Mode Selector & Save Header --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Default Appearance Mode</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Choose how your public portfolio appears by default before visitors switch manually.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        wire:click="$set('themeModeDefault', 'system')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $themeModeDefault === 'system' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        🖥 System
                    </button>
                    <button
                        type="button"
                        wire:click="$set('themeModeDefault', 'dark')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $themeModeDefault === 'dark' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        🌙 Dark
                    </button>
                    <button
                        type="button"
                        wire:click="$set('themeModeDefault', 'light')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $themeModeDefault === 'light' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        ☀️ Light
                    </button>
                </div>

                <button
                    type="button"
                    wire:click="save"
                    class="py-2 px-5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-md shadow-amber-500/20 transition flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Preferences
                </button>
            </div>
        </div>

        {{-- Main Theme Grid and Preview Split --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Theme Catalog Cards --}}
            <div class="lg:col-span-7 space-y-4">
                <h4 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Available Themes ({{ count($this->themes) }})</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($this->themes as $theme)
                        @php
                            $colors = $theme->colors;
                            $dark = is_array($colors) && isset($colors['dark']) ? $colors['dark'] : (is_array($colors) ? $colors : []);
                            $light = is_array($colors) && isset($colors['light']) && is_array($colors['light']) ? $colors['light'] : [];
                            $isSelected = $selectedThemeId === $theme->id;
                        @endphp

                        <div
                            wire:click="selectTheme({{ $theme->id }})"
                            class="p-5 rounded-2xl cursor-pointer transition-all duration-200 border-2 {{ $isSelected ? 'border-amber-500 bg-amber-50/20 dark:bg-amber-950/20 shadow-md ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-700 shadow-sm' }} flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-base font-bold text-gray-900 dark:text-white">{{ $theme->name }}</h5>
                                    @if($isSelected)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white uppercase tracking-wider">
                                            Selected
                                        </span>
                                    @endif
                                </div>

                                {{-- Dark Mode Swatch --}}
                                <div class="mt-2">
                                    <span class="text-[10px] uppercase font-semibold text-gray-400">Dark Palette</span>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        @foreach(['background', 'surface', 'primary', 'secondary', 'accent'] as $t)
                                            @if(isset($dark[$t]))
                                                <div
                                                    class="w-6 h-6 rounded-md border border-black/20 shadow-sm"
                                                    style="background-color: {{ $dark[$t] }}"
                                                    title="{{ ucfirst($t) }}: {{ $dark[$t] }}"
                                                ></div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Light Mode Swatch --}}
                                @if(!empty($light))
                                    <div class="mt-3">
                                        <span class="text-[10px] uppercase font-semibold text-gray-400">Light Palette</span>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            @foreach(['background', 'surface', 'primary', 'secondary', 'accent'] as $t)
                                                @if(isset($light[$t]))
                                                    <div
                                                        class="w-6 h-6 rounded-md border border-gray-300/60 shadow-sm"
                                                        style="background-color: {{ $light[$t] }}"
                                                        title="{{ ucfirst($t) }}: {{ $light[$t] }}"
                                                    ></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                                <span class="text-gray-400 font-mono">{{ $theme->slug }}</span>
                                <span class="font-bold {{ $isSelected ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500' }}">
                                    {{ $isSelected ? '✓ Previewing' : 'Click to preview' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Live Portfolio Preview --}}
            <div class="lg:col-span-5 sticky top-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Live Preview</h4>
                    <a
                        href="{{ $this->previewUrl }}"
                        target="_blank"
                        class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1"
                    >
                        Open In New Tab &nearr;
                    </a>
                </div>

                <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 shadow-xl bg-gray-950 aspect-[9/16] sm:aspect-[4/5] w-full relative">
                    <div class="h-7 bg-gray-900 border-b border-gray-800 px-3 flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></div>
                        <span class="text-[10px] text-gray-400 font-mono ml-2 truncate">{{ $this->previewUrl }}</span>
                    </div>
                    <iframe
                        src="{{ $this->previewUrl }}"
                        class="w-full h-[calc(100%-1.75rem)] border-0"
                        title="Live Theme Preview"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
