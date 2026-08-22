@php
    $livewire ??= null;
    $hasNavigation = filament()->hasNavigation();
    $navigation = filament()->getNavigation();
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-50 dark:bg-gray-950 font-sans text-gray-900 dark:text-gray-100">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity 
             class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Custom Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-transform duration-300 lg:static lg:translate-x-0">
            
            <!-- Sidebar Header / Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                <a href="{{ filament()->getHomeUrl() }}" class="flex items-center gap-3">
                    <!-- Replace with your own logo -->
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-white font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <span class="text-lg font-bold">{{ filament()->getBrandName() }}</span>
                </a>
            </div>

            <!-- Tenant Menu (If applicable) -->
            @if (filament()->hasTenantMenu())
                <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <x-filament-panels::tenant-menu />
                </div>
            @endif

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @foreach ($navigation as $group)
                    @if ($group->getLabel())
                        <div class="px-3 mt-6 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ $group->getLabel() }}
                        </div>
                    @endif
                    
                    @foreach ($group->getItems() as $item)
                        <a href="{{ $item->getUrl() }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->fullUrlIs($item->getUrl().'*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-500 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}">
                            
                            @if ($icon = $item->getIcon())
                                <x-filament::icon :icon="$icon" class="w-5 h-5 opacity-75" />
                            @else
                                <span class="w-5 h-5"></span>
                            @endif
                            
                            <span>{{ $item->getLabel() }}</span>
                            
                            @if ($badge = $item->getBadge())
                                <span class="ml-auto bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ $badge }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament-panels::user-menu />
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Custom Top Header -->
            <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 bg-white/75 dark:bg-gray-900/75 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 z-30 sticky top-0">
                <div class="flex items-center gap-4">
                    <!-- Hamburger Button -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    
                    <!-- Page Title Teleport Target -->
                    <div id="custom-topbar-title" class="flex items-center">
                        <!-- Filament page title will teleport here -->
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Global Search, Theme Switcher, etc can go here -->
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-filament-panels::layout.base>
