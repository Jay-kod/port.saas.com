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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #000000;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .super-admin-glow {
            background: radial-gradient(circle at 50% 0%, rgba(225, 29, 72, 0.18), transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(159, 18, 57, 0.10), transparent 40%);
        }
        .glass-card-dark {
            background: rgba(12, 12, 12, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(244, 63, 94, 0.18);
        }
        .glass-card-dark-hover {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-dark-hover:hover {
            border-color: rgba(244, 63, 94, 0.45);
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -10px rgba(225, 29, 72, 0.30);
        }
    </style>
</head>
<body class="bg-black text-gray-100 selection:bg-rose-600 selection:text-white" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

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

    <!-- Sidebar (Clima Super Admin Master Control: Red & AMOLED Black) -->
    <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:w-20': sidebarCollapsed }" 
           class="w-64 fixed inset-y-0 left-0 z-50 glass-card-dark border-r border-rose-950/70 bg-black/95 transition-all duration-300 ease-in-out lg:translate-x-0 flex flex-col h-screen overflow-hidden">
        
        <!-- Sidebar Header / Master Control Logo -->
        <div class="h-16 flex items-center border-b border-rose-950/70 transition-all duration-300" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-6'">
            <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr from-rose-600 via-red-600 to-rose-700 flex items-center justify-center shadow-lg shadow-rose-600/40 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5 text-white font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div x-show="!sidebarCollapsed" class="flex flex-col">
                    <span class="text-base font-black font-heading tracking-wider text-white whitespace-nowrap">
                        SUPER ADMIN
                    </span>
                    <span class="text-[9px] font-mono tracking-widest text-rose-500 uppercase font-bold">
                        ROOT CONTROL
                    </span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-rose-400 hover:text-white shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5">
            <div class="px-3 mb-2" x-show="!sidebarCollapsed">
                <span class="text-[10px] uppercase font-bold tracking-wider text-rose-500/80 font-mono">Master Operations</span>
            </div>

            <!-- Master Dashboard -->
            <a href="{{ route('super-admin.dashboard') }}" 
               :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
               class="flex items-center py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('super-admin.dashboard') ? 'bg-rose-500/15 text-rose-300 border border-rose-500/30 shadow-md shadow-rose-950/50' : 'text-slate-400 hover:bg-rose-950/30 hover:text-rose-200' }} transition-all"
               title="Platform Health">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('super-admin.dashboard') ? 'text-rose-400' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-semibold">Master Control</span>
            </a>

            <!-- Other Dashboards (Open in Tab without Interference) -->
            <div class="pt-4 mt-4 border-t border-rose-950/70">
                <div class="px-3 mb-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Tenant Workspaces</span>
                </div>

                <a href="{{ route('dashboard') }}" 
                   target="_blank"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-900 hover:text-emerald-400 transition-all group"
                   title="User Dashboard (New Tab)">
                    <svg class="w-5 h-5 shrink-0 text-slate-500 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                        <span>User Dashboard</span>
                        <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </span>
                </a>

                <a href="{{ route('agency') }}" 
                   target="_blank"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-900 hover:text-yellow-400 transition-all group"
                   title="Agency Workspace (New Tab)">
                    <svg class="w-5 h-5 shrink-0 text-slate-500 group-hover:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                        <span>Agency Hub</span>
                        <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </span>
                </a>

                <a href="/admin/{{ auth()->user()->defaultTenant?->id ?? 1 }}" 
                   target="_blank"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition-all group"
                   title="Content Studio (New Tab)">
                    <svg class="w-5 h-5 shrink-0 text-slate-500 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap flex items-center justify-between flex-1">
                        <span>Content Studio</span>
                        <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </span>
                </a>
            </div>
        </nav>

        <!-- Sidebar Footer (Sign out) -->
        <div class="p-4 border-t border-rose-950/70 bg-black">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="block w-full">
                @csrf
                <button type="submit" 
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-4'" 
                        class="w-full flex items-center justify-center text-sm font-medium py-2.5 rounded-xl border border-rose-950 bg-rose-950/30 text-rose-300 hover:bg-rose-900/60 hover:text-white transition-all shadow-sm font-mono" 
                        title="Sign Out">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

        <!-- Top Header (Red & AMOLED Black with Hamburger & Title) -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 border-b border-rose-950/70 glass-card-dark bg-black/90 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button @click="window.innerWidth < 1024 ? sidebarOpen = true : sidebarCollapsed = !sidebarCollapsed" 
                        class="text-rose-400 hover:text-white focus:outline-none p-2 -ml-2 rounded-xl hover:bg-rose-950/50 transition-colors"
                        title="Toggle Sidebar">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Header Title -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-xs font-mono tracking-wider">SUPER ADMIN</span>
                        <h1 class="text-base sm:text-lg font-bold font-heading text-white tracking-tight hidden sm:inline">
                            Master Control Center
                        </h1>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/30 font-mono">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        ROOT / TIER 0
                    </span>
                </div>
            </div>

            <!-- Top Right Section -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 pl-2 border-l border-rose-950">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-rose-600 to-red-700 flex items-center justify-center text-white font-bold text-xs shadow-md shadow-rose-900/50 font-mono">
                        SA
                    </div>
                    <span class="text-sm font-semibold text-rose-300 hidden md:inline">{{ auth()->user()->name ?? 'Super Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="py-6 border-t border-rose-950/70 text-center text-xs text-rose-500/60 bg-black mt-auto font-mono">
            DevFolio Master Operations &bull; Root Privileges Activated &bull; Zero Interference Architecture
        </footer>
    </div>

</body>
</html>
