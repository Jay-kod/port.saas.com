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
        /* Custom scrollbar for sidebar */
        aside nav::-webkit-scrollbar {
            width: 4px;
        }
        aside nav::-webkit-scrollbar-track {
            background: transparent;
        }
        aside nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        aside nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-slate-950 selection:bg-emerald-500 selection:text-white" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    @php
        $tenantId = auth()->user()?->defaultTenant?->id ?? auth()->user()?->accounts->first()?->id ?? 1;
        $userProfile = auth()->user()?->profile;
    @endphp

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
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-4">
            
            <!-- SECTION 1: WORKSPACE HUBS -->
            <div>
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Workspace</span>
                </div>
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 shadow-sm shadow-emerald-500/10' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }} transition-all"
                       title="Dashboard">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-semibold">Dashboard</span>
                    </a>

                    <!-- Agency Workspace -->
                    <a href="{{ route('agency') }}" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium {{ request()->routeIs('agency') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }} transition-all"
                       title="Agency Hub">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('agency') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Agency Hub</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 2: PORTFOLIO CONTENT (STUDIO) -->
            <div class="pt-2 border-t border-white/5">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Portfolio Studio</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Profile Info -->
                    <a href="/admin/{{ $tenantId }}/profiles" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Profile Details">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Profile & Bio</span>
                    </a>

                    <!-- Projects -->
                    <a href="/admin/{{ $tenantId }}/projects" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Projects Showcase">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Projects</span>
                    </a>

                    <!-- Experience Timeline -->
                    <a href="/admin/{{ $tenantId }}/experiences" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Career Experience">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Experience</span>
                    </a>

                    <!-- Skills Matrix -->
                    <a href="/admin/{{ $tenantId }}/skills" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Technical Skills">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Skills Matrix</span>
                    </a>

                    <!-- Certificates -->
                    <a href="/admin/{{ $tenantId }}/certificates" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Accreditations">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Certificates</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 3: AI CAREER SUITE -->
            <div class="pt-2 border-t border-white/5">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-400/80 font-mono">Career & AI Suite</span>
                </div>
                <div class="space-y-0.5">
                    <!-- AI Resume Tailor -->
                    <a href="/admin/{{ $tenantId }}/resume-generations" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-emerald-300 transition-all"
                       title="AI Resume Tailor">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">AI Resumes</span>
                    </a>

                    <!-- Cover Letter Generator -->
                    <a href="/admin/{{ $tenantId }}/cover-letter-generations" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-emerald-300 transition-all"
                       title="AI Cover Letters">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Cover Letters</span>
                    </a>

                    <!-- Job Tracker (Kanban) -->
                    <a href="/admin/{{ $tenantId }}/job-tracker" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-yellow-300 transition-all"
                       title="Job Tracker Kanban">
                        <svg class="w-4 h-4 shrink-0 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Job Tracker (Kanban)</span>
                    </a>

                    <!-- Resume PDF Importer -->
                    <a href="/admin/{{ $tenantId }}/resume-import" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Resume PDF Importer">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Import Resume PDF</span>
                    </a>

                    <!-- GitHub Sync -->
                    <a href="/admin/{{ $tenantId }}/github-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="GitHub Sync">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">GitHub Sync</span>
                    </a>

                    <!-- Custom AI Keys -->
                    <a href="/admin/{{ $tenantId }}/ai-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="AI Settings & BYOK">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">AI Settings (BYOK)</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 4: APPEARANCE & BRANDING -->
            <div class="pt-2 border-t border-white/5">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Appearance</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Theme Customizer -->
                    <a href="/admin/{{ $tenantId }}/theme-selector" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-yellow-300 transition-all"
                       title="Theme Customizer">
                        <svg class="w-4 h-4 shrink-0 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Theme & Mode</span>
                    </a>

                    <!-- Custom Domains -->
                    <a href="/admin/{{ $tenantId }}/domain-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Custom Domains">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Custom Domains</span>
                    </a>

                    <!-- Agency Branding -->
                    <a href="/admin/{{ $tenantId }}/agency-branding-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Agency Branding">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">White-Label Brand</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 5: ACCOUNT, BILLING & PRIVACY -->
            <div class="pt-2 border-t border-white/5">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Account & Settings</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Billing & Usage -->
                    <a href="/admin/{{ $tenantId }}/billing-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-emerald-400 transition-all"
                       title="Billing & Usage">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400/90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Billing & Usage</span>
                    </a>

                    <!-- Team Management -->
                    <a href="/admin/{{ $tenantId }}/team-settings" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Team Members">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Team Members</span>
                    </a>

                    <!-- Privacy & GDPR Data -->
                    <a href="/admin/{{ $tenantId }}/privacy-and-data" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Privacy & Data Export">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Privacy & GDPR</span>
                    </a>

                    <!-- Onboarding Setup Wizard -->
                    <a href="{{ route('onboarding') }}" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all"
                       title="Setup Wizard">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Setup Wizard</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 6: SUPER ADMIN (If Elevated) -->
            @if(auth()->user()?->is_super_admin)
            <div class="pt-2 border-t border-rose-500/20">
                <div class="px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-rose-400 font-mono">Root Privileges</span>
                </div>

                <a href="{{ route('super-admin.dashboard') }}" 
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                   class="flex items-center py-2 rounded-xl text-xs font-medium bg-rose-500/10 text-rose-300 border border-rose-500/30 hover:bg-rose-500/20 transition-all font-mono"
                   title="Super Admin Master Control">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap font-bold">SUPER ADMIN</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- Sidebar Footer (User info & Sign out) -->
        <div class="p-3 border-t border-white/5 bg-slate-950/60">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="block w-full">
                @csrf
                <button type="submit" 
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-3'" 
                        class="w-full flex items-center justify-center text-xs font-medium py-2 rounded-xl border border-slate-800 bg-slate-900/80 text-slate-300 hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-300 transition-all shadow-sm" 
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
