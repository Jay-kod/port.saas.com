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

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });

        window.toggleSuperAdminSidebar = function() {
            const isMobile = window.innerWidth < 1024;
            const sidebar = document.getElementById('super-admin-sidebar');
            const mainShell = document.getElementById('super-admin-main-shell');
            const overlay = document.getElementById('mobile-super-admin-overlay');
            
            if (isMobile) {
                if (!sidebar) return;
                const isOpen = sidebar.classList.toggle('is-open');
                if (overlay) {
                    overlay.style.display = isOpen ? 'block' : 'none';
                }
            } else {
                if (!sidebar || !mainShell) return;
                const isCollapsed = sidebar.classList.toggle('is-collapsed');
                mainShell.classList.toggle('is-collapsed', isCollapsed);
                try {
                    localStorage.setItem('devfolio_superadmin_sidebar_collapsed', isCollapsed ? '1' : '0');
                } catch (e) {}
            }
        };

        window.closeMobileSuperAdminSidebar = function() {
            const sidebar = document.getElementById('super-admin-sidebar');
            const overlay = document.getElementById('mobile-super-admin-overlay');
            if (sidebar) sidebar.classList.remove('is-open');
            if (overlay) overlay.style.display = 'none';
        };

        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem('devfolio_superadmin_sidebar_collapsed') === '1') {
                    const sidebar = document.getElementById('super-admin-sidebar');
                    const mainShell = document.getElementById('super-admin-main-shell');
                    if (sidebar) sidebar.classList.add('is-collapsed');
                    if (mainShell) mainShell.classList.add('is-collapsed');
                }
            } catch (e) {}
        });
    </script>

    @php
        $user = auth()->user();
        $tenantId = (session('active_tenant_id') ? \App\Models\Account::find(session('active_tenant_id')) : null)?->id 
            ?? $user?->accounts()->first()?->id 
            ?? 1;
        $userProfile = $user?->profile;

        $userInitials = 'SA';
        if ($user && $user->name) {
            $nameParts = preg_split('/\s+/', trim($user->name));
            if (count($nameParts) >= 2) {
                $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
            } else {
                $userInitials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0]))));
            }
        }
    @endphp

    <style>
        :root {
            --panel-accent: #D97706;
            --panel-accent-dark: #F59E0B;
            --panel-accent-glow: rgba(245, 158, 11, 0.18);
            --panel-accent-subtle: rgba(245, 158, 11, 0.10);
            --panel-badge-text: #fbbf24;
            --panel-badge-border: rgba(245, 158, 11, 0.30);
        }
        [x-cloak] {
            display: none !important;
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
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(245, 158, 11, 0.2);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(245, 158, 11, 0.4);
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(245, 158, 11, 0.2) transparent;
        }
        /* Super Admin Layout Architecture */
        .super-admin-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .super-admin-main-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: padding-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Desktop Mode (>= 1024px) */
        @media (min-width: 1024px) {
            .super-admin-sidebar {
                width: 16rem;
                transform: translateX(0) !important;
            }
            .super-admin-main-shell {
                padding-left: 16rem !important;
            }

            /* Collapsed desktop state */
            .super-admin-sidebar.is-collapsed {
                width: 5rem !important;
            }
            .super-admin-main-shell.is-collapsed {
                padding-left: 5rem !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-label {
                display: none !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-link {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                gap: 0 !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-header-box {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-header-box > div {
                justify-content: center !important;
                width: 100% !important;
            }
            .super-admin-sidebar.is-collapsed nav {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-footer-btn span {
                display: none !important;
            }
            .super-admin-sidebar.is-collapsed .sidebar-footer-btn {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }

        /* Mobile / Tablet Mode (< 1024px) */
        @media (max-width: 1023px) {
            .super-admin-sidebar {
                width: 18rem !important;
                max-width: 85vw !important;
                transform: translateX(-100%) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8) !important;
            }
            .super-admin-sidebar.is-open {
                transform: translateX(0) !important;
            }
            .super-admin-main-shell {
                padding-left: 0 !important;
            }
            /* Always show all labels in mobile drawer */
            .super-admin-sidebar .sidebar-label {
                display: flex !important;
            }
            .sidebar-label.px-3 {
                display: block !important;
            }
        }

        /* SVG constraints */
        svg {
            max-width: 100%;
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }
        .w-3\.5 { width: 0.875rem; }
        .h-3\.5 { height: 0.875rem; }
        .w-4 { width: 1rem; }
        .h-4 { height: 1rem; }
        .w-5 { width: 1.25rem; }
        .h-5 { height: 1.25rem; }
        .w-6 { width: 1.5rem; }
        .h-6 { height: 1.5rem; }
        .w-8 { width: 2rem; }
        .h-8 { height: 2rem; }
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
    </style>
</head>
<body class="bg-black text-slate-100 selection:bg-amber-500 selection:text-black font-sans" x-data="{ sidebarOpen: false, sidebarCollapsed: false, showLogoutModal: false }">

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-super-admin-overlay"
         onclick="window.closeMobileSuperAdminSidebar()"
         x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/80 backdrop-blur-md z-40 lg:hidden" 
         @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar (Super Admin Master Control) -->
    <aside id="super-admin-sidebar"
           :class="{ 'is-open': sidebarOpen, 'is-collapsed': sidebarCollapsed }" 
           class="super-admin-sidebar glass-card-dark border-r border-amber-950/70 bg-black flex flex-col h-screen overflow-hidden">
        
        <!-- Sidebar Header (Amber Shield) -->
        <div class="sidebar-header-box h-20 flex flex-col justify-center border-b border-amber-950/70 transition-all duration-300" :class="sidebarCollapsed ? 'items-center px-0' : 'px-5'">
            <div class="flex items-center gap-3">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr from-amber-600 via-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-900/40 border border-amber-400/40 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-slate-950 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" class="sidebar-label flex flex-col">
                        <span class="text-lg font-black font-heading tracking-tight bg-gradient-to-r from-amber-400 via-amber-200 to-orange-400 bg-clip-text text-transparent uppercase font-mono">
                            MASTER
                        </span>
                        <span class="text-[10px] font-mono font-bold tracking-wider text-amber-500">
                            CONTROL CENTER
                        </span>
                    </div>
                </a>
                <button type="button" onclick="window.closeMobileSuperAdminSidebar()" @click="sidebarOpen = false" class="ml-auto lg:hidden text-amber-400 hover:text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-4 font-sans">
            <!-- SECTION 1: PLATFORM OVERVIEW -->
            <div>
                <div class="sidebar-label px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-500/80 font-mono">Root Operations</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Master Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('super-admin.dashboard') ? 'active-nav-pill-amber' : 'text-slate-300 hover:bg-amber-950/30 hover:text-amber-200' }} transition-all"
                       title="Master Overview">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('super-admin.dashboard') ? 'text-amber-400' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap font-mono">Platform Telemetry</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 2: GLOBAL AUDIT & GOVERNANCE -->
            <div class="pt-2 border-t border-amber-950/70">
                <div class="sidebar-label px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-500/80 font-mono">Platform Moderation</span>
                </div>
                <div class="space-y-0.5">
                    <!-- Abuse Reports -->
                    <a href="/admin/{{ $tenantId }}/portfolio-reports" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Moderation Queue">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap">Moderation Queue</span>
                    </a>

                    <!-- Global Themes -->
                    <a href="/admin/{{ $tenantId }}/themes" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Global Themes">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap">Themes Catalog</span>
                    </a>

                    <!-- Global Templates -->
                    <a href="/admin/{{ $tenantId }}/templates" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-amber-950/30 hover:text-amber-200 transition-all"
                       title="Resume Templates">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap">Templates Catalog</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 3: TENANT WORKSPACES -->
            <div class="pt-2 border-t border-amber-950/70">
                <div class="sidebar-label px-3 mb-1.5" x-show="!sidebarCollapsed">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 font-mono">Tenant Workspaces</span>
                </div>
                <div class="space-y-0.5">
                    <!-- User Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       target="_blank" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-emerald-400 transition-all group"
                       title="User Dashboard (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap flex items-center justify-between flex-1">
                            <span>User Dashboard</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>

                    <!-- Agency Hub -->
                    <a href="{{ route('agency') }}" 
                       target="_blank" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-yellow-400 transition-all group"
                       title="Agency Workspace (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap flex items-center justify-between flex-1">
                            <span>Agency Hub</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>

                    <!-- Content Studio -->
                    <a href="/admin/{{ $tenantId }}" 
                       target="_blank" 
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'" 
                       class="sidebar-link flex items-center py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-slate-900 hover:text-white transition-all group"
                       title="Content Studio (New Tab)">
                        <svg class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap flex items-center justify-between flex-1">
                            <span>Content Studio</span>
                            <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer (Sign out) -->
        <div class="p-3 border-t border-amber-950/70 bg-black">
            <button type="button" 
                    @click="showLogoutModal = true"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-3'" 
                    class="sidebar-footer-btn w-full flex items-center justify-center text-xs font-medium py-2 rounded-xl border border-amber-950 bg-amber-950/30 text-amber-300 hover:bg-amber-900/60 hover:text-white transition-all shadow-sm font-mono group" 
                    title="Sign Out">
                <svg class="w-4 h-4 shrink-0 text-amber-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="!sidebarCollapsed" class="sidebar-label whitespace-nowrap font-bold">Terminate Session</span>
            </button>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div id="super-admin-main-shell" :class="{ 'is-collapsed': sidebarCollapsed }" class="super-admin-main-shell flex-1 flex flex-col min-h-screen">
        <div class="super-admin-glow flex-1 absolute inset-0 z-[-1] pointer-events-none"></div>

        <!-- Top Header (Amber & AMOLED Black with Hamburger & Title) -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 border-b border-amber-950/70 glass-card-dark bg-black/90 backdrop-blur-md">
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Hamburger Button -->
                <button type="button"
                        id="super-admin-hamburger-btn"
                        onclick="window.toggleSuperAdminSidebar()"
                        @click="window.innerWidth < 1024 ? sidebarOpen = !sidebarOpen : sidebarCollapsed = !sidebarCollapsed" 
                        class="text-amber-400 hover:text-white focus:outline-none p-2 -ml-2 rounded-xl hover:bg-amber-950/50 transition-colors cursor-pointer"
                        title="Toggle Sidebar"
                        aria-label="Toggle Navigation Sidebar">
                    <svg class="w-6 h-6 shrink-0 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Header Title -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-amber-600 text-slate-950 font-black text-xs font-mono tracking-wider">SUPER ADMIN</span>
                        <h1 class="text-base sm:text-lg font-bold font-heading text-white tracking-tight hidden sm:inline whitespace-nowrap">
                            Master Control Center
                        </h1>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30 font-mono">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        ROOT / TIER 0
                    </span>
                </div>
            </div>

            <!-- Top Right Section with Interactive Initials Dropdown -->
            <div class="flex items-center gap-3">
                <div class="relative pl-2 border-l border-amber-950" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            type="button" 
                            class="flex items-center gap-2.5 p-1 sm:px-2 rounded-xl hover:bg-amber-950/30 border border-transparent hover:border-amber-500/20 transition-all focus:outline-none group"
                            id="super-admin-menu-button" 
                            aria-expanded="false" 
                            aria-haspopup="true">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-600 to-orange-600 flex items-center justify-center text-slate-950 font-bold text-xs shadow-md shadow-amber-900/50 font-mono group-hover:scale-105 transition-transform border border-amber-400/40">
                            {{ $userInitials }}
                        </div>
                        <span class="text-sm font-semibold text-amber-300 hidden md:inline group-hover:text-amber-200">{{ $user->name ?? 'Super Admin' }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen" 
                         @click.outside="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-64 rounded-2xl glass-card-dark bg-black/95 border border-amber-500/30 shadow-2xl shadow-black/90 py-2 z-50 divide-y divide-amber-950/60"
                         style="display: none;">
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-tr from-amber-600 to-orange-600 flex items-center justify-center text-slate-950 font-bold text-sm shadow-md border border-amber-400/40 font-mono">
                                    {{ $userInitials }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ $user->name ?? 'Super Admin' }}</p>
                                    <p class="text-[11px] text-amber-400/80 truncate font-mono">{{ $user->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30 font-mono">
                                    <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                                    ROOT / TIER 0
                                </span>
                            </div>
                        </div>

                        <!-- Menu Items: Profile & Settings -->
                        <div class="py-1.5 px-1.5 space-y-0.5 font-sans">
                            <!-- Profile Link -->
                            <a href="/admin/{{ $tenantId }}/profiles" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-amber-950/30 rounded-xl transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-amber-950/30 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:bg-amber-950/60 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-200 group-hover:text-white">Profile</span>
                                    <span class="text-[10px] text-slate-400">View profile configurations</span>
                                </div>
                            </a>

                            <!-- Settings Link -->
                            <a href="/admin/{{ $tenantId }}/billing-settings" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-amber-950/30 rounded-xl transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-amber-950/30 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:bg-amber-950/60 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-200 group-hover:text-white">Settings</span>
                                    <span class="text-[10px] text-slate-400">Billing, accounts & platform</span>
                                </div>
                            </a>
                        </div>

                        <!-- Logout Button (triggers modal) -->
                        <div class="py-1.5 px-1.5">
                            <button @click="userMenuOpen = false; showLogoutModal = true" 
                                    type="button" 
                                    class="w-full flex items-center gap-3 px-3 py-2 text-xs font-medium text-amber-400 hover:text-white hover:bg-red-500/10 rounded-xl transition-colors group text-left">
                                <div class="w-7 h-7 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 group-hover:bg-red-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="font-semibold text-amber-300 group-hover:text-red-300 font-mono">Terminate Session</span>
                                    <span class="text-[10px] text-amber-500/70">Sign out of master control</span>
                                </div>
                            </button>
                        </div>
                    </div>
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

    <!-- Custom Super Admin Logout Verification Modal -->
    <div x-show="showLogoutModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" 
         style="display: none;"
         @keydown.escape.window="showLogoutModal = false">
        
        <!-- Modal Card -->
        <div @click.outside="showLogoutModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-md p-6 rounded-3xl glass-card-dark bg-black/95 border border-amber-500/40 shadow-2xl shadow-amber-950/60 overflow-hidden">
            
            <!-- Ambient Amber Glow -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative flex flex-col items-center text-center font-sans">
                <!-- Icon -->
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/40 flex items-center justify-center text-amber-400 mb-4 shadow-lg shadow-amber-500/10 animate-pulse">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <!-- Title & Description -->
                <h3 class="text-xl font-bold font-heading text-white tracking-tight font-mono">
                    Terminate Root Session?
                </h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-400">
                    Are you sure you want to end your Super Admin session? Master control privileges will be securely closed and you will be returned to the sign-in portal.
                </p>

                <!-- User Pill inside Modal -->
                <div class="mt-4 w-full py-2 px-3 rounded-xl bg-amber-950/30 border border-amber-500/20 flex items-center justify-center gap-2 text-xs text-amber-300 font-mono">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Elevated Account: <strong class="text-white">{{ $user->email ?? 'Super Admin' }}</strong></span>
                </div>

                <!-- Modal Action Buttons -->
                <div class="mt-6 grid grid-cols-2 gap-3 w-full font-mono">
                    <button type="button" 
                            @click="showLogoutModal = false"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 transition-all">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 border border-amber-400/50 shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Terminate</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
