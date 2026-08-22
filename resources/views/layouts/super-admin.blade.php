<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-black text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DevFolio') }} - @yield('title', 'Super Admin Master Control')</title>

    <!-- Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --panel-accent: #D97706;
            --panel-accent-dark: #F59E0B;
            --panel-accent-glow: rgba(245, 158, 11, 0.18);
            --panel-accent-subtle: rgba(245, 158, 11, 0.10);
            --panel-badge-text: #fbbf24;
            --panel-badge-border: rgba(245, 158, 11, 0.30);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #000000;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .super-admin-glow {
            background: radial-gradient(circle at 50% 0%, var(--panel-accent-glow), transparent 50%),
                        radial-gradient(circle at 100% 100%, var(--panel-accent-subtle), transparent 40%);
        }
        .glass-card-dark {
            background: rgba(12, 12, 12, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--panel-badge-border);
        }
        .glass-card-dark-hover {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-dark-hover:hover {
            border-color: var(--panel-accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -10px var(--panel-accent-glow);
        }
        .active-nav-pill-amber {
            background-color: var(--panel-accent-subtle) !important;
            color: var(--panel-badge-text) !important;
            border: 1px solid var(--panel-badge-border) !important;
            box-shadow: 0 4px 12px -2px var(--panel-accent-glow) !important;
        }
        .active-nav-pill-amber svg {
            color: var(--panel-badge-text) !important;
        }
        /* Custom scrollbar for sidebar */
        aside nav::-webkit-scrollbar {
            width: 4px;
        }
        aside nav::-webkit-scrollbar-track {
            background: transparent;
        }
        aside nav::-webkit-scrollbar-thumb {
            background: rgba(245, 158, 11, 0.2);
            border-radius: 4px;
        }
        aside nav::-webkit-scrollbar-thumb:hover {
            background: rgba(245, 158, 11, 0.4);
        }
    </style>
</head>
<body class="bg-black text-gray-100 selection:bg-amber-500 selection:text-slate-950" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    @php
        $tenantId = auth()->user()?->defaultTenant?->id ?? auth()->user()?->accounts->first()?->id ?? 1;
    @endphp

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 lg:hidden" 
         @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar (Clima Super Admin Master Control: Amber & AMOLED Black) -->
    <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:w-20': sidebarCollapsed }" 
           class="w-64 fixed inset-y-0 left-0 z-50 glass-card-dark border-r border-amber-950/70 bg-black/95 transition-all duration-300 ease-in-out lg:translate-x-0 flex flex-col h-screen overflow-hidden">
        
        <!-- Sidebar Header / Master Control Logo -->
        <div class="h-20 flex flex-col justify-center border-b border-amber-950/70 transition-all duration-300" :class="sidebarCollapsed ? 'items-center px-0' : 'px-5'">
            <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr from-amber-600 via-orange-500 to-amber-400 flex items-center justify-center shadow-lg shadow-amber-600/30 group-hover:scale-105 transition-transform duration-200 border border-amber-500/40">
                    <svg class="w-5 h-5 text-slate-950 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div x-show="!sidebarCollapsed" class="flex flex-col">
                    <span class="text-base font-black font-heading tracking-wider text-white whitespace-nowrap">
                        SUPER ADMIN
                    </span>
                    <span class="text-[9px] font-mono tracking-widest text-amber-400 uppercase font-bold">
                        ROOT CONTROL
                    </span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-amber-400 hover:text-white shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-4">
            
            <!-- SECTION 1: MASTER ROOT OPERATIONS -->
            <div>
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400/80 font-mono">Master Operations</span>
                </div>
                <div class="space-y-1">
                    <!-- Master Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium {{ request()->routeIs('super-admin.dashboard') ? 'active-nav-pill-amber font-bold' : 'text-slate-400 hover:bg-amber-950/30 hover:text-amber-200' }} transition-all"
                       title="Platform Health">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-bold">Master Control</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 2: MODERATION & GLOBAL CATALOGS -->
            <div class="pt-2 border-t border-amber-950/70">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400/80 font-mono">Platform Catalogs</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Portfolio Reports Moderation -->
                    <a href="/admin/{{ $tenantId }}/portfolio-reports" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Moderation Queue">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Moderation Queue</span>
                    </a>

                    <!-- Global Themes -->
                    <a href="/admin/{{ $tenantId }}/themes" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Global Themes">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Themes Catalog</span>
                    </a>

                    <!-- Global Templates -->
                    <a href="/admin/{{ $tenantId }}/templates" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Resume Templates">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Templates Catalog</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 3: TENANT WORKSPACES -->
            <div class="pt-2 border-t border-amber-950/70">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Tenant Workspaces</span>
                </div>
                <div class="space-y-0.5">
                    <!-- User Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       target="_blank"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-emerald-400 transition-all group"
                       title="User Dashboard (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                            <span>User Dashboard</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>

                    <!-- Agency Hub -->
                    <a href="{{ route('agency') }}" 
                       target="_blank"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-yellow-400 transition-all group"
                       title="Agency Workspace (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                            <span>Agency Hub</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>

                    <!-- Content Studio -->
                    <a href="/admin/{{ $tenantId }}" 
                       target="_blank"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition-all group"
                       title="Content Studio (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                            <span>Content Studio</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer (Sign out) -->
        <div class="p-3 border-t border-amber-950/70 bg-black">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="block w-full">
                @csrf
                <button type="submit" 
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-3'" 
                        class="w-full flex items-center justify-center text-xs font-medium py-2 rounded-xl border border-amber-950 bg-amber-950/30 text-amber-300 hover:bg-amber-900/60 hover:text-white transition-all shadow-sm font-mono" 
                        title="Sign Out">
                    <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-bold">Terminate Session</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" class="flex flex-col min-h-screen transition-all duration-300">
        <div :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'" class="super-admin-glow flex-1 absolute inset-0 z-[-1] hidden lg:block transition-all duration-300"></div>
        <div class="super-admin-glow flex-1 absolute inset-0 z-[-1] lg:hidden"></div>

        <!-- Top Header (Amber & AMOLED Black with Hamburger & Title) -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 border-b border-amber-950/70 glass-card-dark bg-black/90 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button @click="window.innerWidth < 1024 ? sidebarOpen = true : sidebarCollapsed = !sidebarCollapsed" 
                        class="text-amber-400 hover:text-white focus:outline-none p-2 -ml-2 rounded-xl hover:bg-amber-950/50 transition-colors"
                        title="Toggle Sidebar">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Header Title -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-amber-600 text-slate-950 font-black text-xs font-mono tracking-wider">SUPER ADMIN</span>
                        <h1 class="text-base sm:text-lg font-bold font-heading text-white tracking-tight hidden sm:inline">
                            Master Control Center
                        </h1>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30 font-mono">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        ROOT / TIER 0
                    </span>
                </div>
            </div>

            <!-- Top Right Section -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 pl-2 border-l border-amber-950">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-600 to-orange-600 flex items-center justify-center text-slate-950 font-bold text-xs shadow-md shadow-amber-900/50 font-mono">
                        SA
                    </div>
                    <span class="text-sm font-semibold text-amber-300 hidden md:inline">{{ auth()->user()->name ?? 'Super Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="py-6 border-t border-amber-950/70 text-center text-xs text-amber-500/60 bg-black mt-auto font-mono">
            DevFolio Master Operations &bull; Root Privileges Activated &bull; Zero Interference Architecture
        </footer>
    </div>

</body>
</html>
