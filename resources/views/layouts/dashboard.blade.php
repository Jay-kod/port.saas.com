<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DevFolio') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .dashboard-glow {
            background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.12), transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(234, 179, 8, 0.08), transparent 40%);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card-hover {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-hover:hover {
            border-color: rgba(74, 222, 128, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -10px rgba(16, 185, 129, 0.15);
        }
    </style>
</head>
<body class="bg-slate-950 selection:bg-emerald-500 selection:text-white" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden" 
         @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar (Clima-style dark glass with Green/Yellow accents) -->
    <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:w-20': sidebarCollapsed }" 
           class="w-64 fixed inset-y-0 left-0 z-50 glass-card border-r border-white/5 bg-slate-950/95 transition-all duration-300 ease-in-out lg:translate-x-0 flex flex-col h-screen overflow-hidden">
        
        <!-- Sidebar Header / Logo -->
        <div class="h-16 flex items-center border-b border-white/5 transition-all duration-300" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-6'">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr from-emerald-500 to-yellow-500 flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5 text-slate-950 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span x-show="!sidebarCollapsed" class="text-xl font-extrabold font-heading tracking-tight bg-gradient-to-r from-emerald-400 via-yellow-300 to-white bg-clip-text text-transparent whitespace-nowrap">
                    DevFolio
                </span>
            </a>
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-slate-400 hover:text-white shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5">
            <div class="px-3 mb-2" x-show="!sidebarCollapsed">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Workspace</span>
            </div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
               class="flex items-center py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm shadow-emerald-500/10' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }} transition-all"
               title="Dashboard">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Dashboard</span>
            </a>

            <!-- Agency Workspace -->
            <a href="{{ route('agency') }}" 
               :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
               class="flex items-center py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('agency') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }} transition-all"
               title="Agency">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('agency') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Agency Workspace</span>
            </a>

            <div class="pt-4 mt-4 border-t border-white/5">
                <div class="px-3 mb-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Portfolio & Settings</span>
                </div>

                <!-- Admin Management Panel -->
                <a href="/admin/{{ auth()->user()->defaultTenant?->id ?? 1 }}" 
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                   title="Content Management">
                    <svg class="w-5 h-5 shrink-0 text-yellow-400/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Content Studio</span>
                </a>

                <!-- Onboarding Wizard -->
                <a href="{{ route('onboarding') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                   title="Onboarding Wizard">
                    <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Setup Wizard</span>
                </a>
            </div>

            @if(auth()->user()?->is_super_admin)
            <div class="pt-4 mt-4 border-t border-rose-500/20">
                <div class="px-3 mb-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-rose-400">Master Control</span>
                </div>

                <a href="{{ route('super-admin.dashboard') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2.5 rounded-xl text-sm font-medium bg-rose-500/10 text-rose-300 border border-rose-500/20 hover:bg-rose-500/20 transition-all"
                   title="Super Admin">
                    <svg class="w-5 h-5 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-semibold">Super Admin</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- Sidebar Footer (User info & Sign out) -->
        <div class="p-4 border-t border-white/5 bg-slate-950/60">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="block w-full">
                @csrf
                <button type="submit" 
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-4'" 
                        class="w-full flex items-center justify-center text-sm font-medium py-2.5 rounded-xl border border-slate-800 bg-slate-900/80 text-slate-300 hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-300 transition-all shadow-sm" 
                        title="Sign Out">
                    <svg class="w-4 h-4 shrink-0 text-slate-400 group-hover:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-medium">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" class="flex flex-col min-h-screen transition-all duration-300">
        <div :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'" class="dashboard-glow flex-1 absolute inset-0 z-[-1] hidden lg:block transition-all duration-300"></div>
        <div class="dashboard-glow flex-1 absolute inset-0 z-[-1] lg:hidden"></div>

        <!-- Top Header (Clima-style with hamburger and page title) -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 border-b border-white/5 glass-card bg-slate-950/80 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button @click="window.innerWidth < 1024 ? sidebarOpen = true : sidebarCollapsed = !sidebarCollapsed" 
                        class="text-slate-300 hover:text-white focus:outline-none p-2 -ml-2 rounded-xl hover:bg-white/5 transition-colors"
                        title="Toggle Sidebar">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Page Title -->
                <h1 class="text-lg font-bold font-heading text-white tracking-tight">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <!-- Top Right Section -->
            <div class="flex items-center gap-3">
                <!-- Live Portfolio Link Badge -->
                @php
                    $userProfile = auth()->user()?->profile;
                @endphp
                @if($userProfile && $userProfile->is_published)
                <a href="{{ url('/' . $userProfile->slug) }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Portfolio</span>
                    <svg class="w-3.5 h-3.5 ml-0.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
                @endif

                <!-- User Profile Pill -->
                <div class="flex items-center gap-2 pl-2 border-l border-white/10">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-500 to-yellow-500 flex items-center justify-center text-slate-950 font-bold text-xs shadow-md">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-slate-200 hidden md:inline">{{ auth()->user()->name ?? 'User' }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="py-6 border-t border-white/5 text-center text-xs text-slate-500 bg-slate-950/50 mt-auto">
            DevFolio SaaS Platform &bull; AI-Powered Portfolio & Resume Suite
        </footer>
    </div>

</body>
</html>
