<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Getting Started Checklist</span>
                        @if($percentage === 100)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                Ready to share 🎉
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                {{ $completedCount }} / {{ $totalCount }} completed
                            </span>
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Follow these steps to customize your profile, add your projects, and share your work.
                    </p>
                </div>

                <div class="w-full sm:w-48">
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                        <span>Setup progress</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                @foreach($items as $item)
                    <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $item['completed'] ? 'border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/10 dark:border-emerald-500/20' : 'border-gray-200 bg-white dark:bg-gray-800/60 dark:border-gray-700/60' }} transition">
                        <div class="mt-0.5 flex-shrink-0">
                            @if($item['completed'])
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                    <x-filament::icon icon="heroicon-m-check" class="w-4 h-4" />
                                </div>
                            @else
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center text-xs font-medium text-gray-400">
                                    {{ $loop->iteration }}
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-sm font-semibold {{ $item['completed'] ? 'text-gray-900 dark:text-white' : 'text-gray-900 dark:text-white' }}">
                                    {{ $item['title'] }}
                                </h4>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                {{ $item['description'] }}
                            </p>
                            <div class="mt-2.5">
                                <a href="{{ $item['url'] }}" class="inline-flex items-center text-xs font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 gap-1">
                                    <span>{{ $item['action'] }}</span>
                                    <x-filament::icon icon="heroicon-m-arrow-right" class="w-3 h-3" />
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
