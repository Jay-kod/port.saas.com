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
    @livewireStyles

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });

        window.toggleDashboardSidebar = function() {
            const isMobile = window.innerWidth < 1024;
            const sidebar = document.getElementById('dashboard-sidebar');
            const mainShell = document.getElementById('dashboard-main-shell');
            const overlay = document.getElementById('mobile-sidebar-overlay');
            
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
                    localStorage.setItem('devfolio_sidebar_collapsed', isCollapsed ? '1' : '0');
                } catch (e) {}
            }
        };

        window.closeMobileSidebar = function() {
            const sidebar = document.getElementById('dashboard-sidebar');
            const overlay = document.getElementById('mobile-sidebar-overlay');
            if (sidebar) sidebar.classList.remove('is-open');
            if (overlay) overlay.style.display = 'none';
        };

        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem('devfolio_sidebar_collapsed') === '1') {
                    const sidebar = document.getElementById('dashboard-sidebar');
                    const mainShell = document.getElementById('dashboard-main-shell');
                    if (sidebar) sidebar.classList.add('is-collapsed');
                    if (mainShell) mainShell.classList.add('is-collapsed');
                }
            } catch (e) {}
        });
    </script>

    @php
        $user = auth()->user();
        $account = (session('active_tenant_id') ? \App\Models\Account::find(session('active_tenant_id')) : null)
            ?? $user?->accounts()->first()
            ?? $user?->memberAccounts()->first();
        $tenantId = $account?->id ?? 1;
        $userProfile = $user?->profile;
        $userRole = $account ? $account->getUserRole($user) : 'owner';
        $isAgencyRoute = request()->routeIs('agency*');
        $isAgencyPlan = ($account?->plan_slug === 'agency');

        // Initials calculation for top-right user menu
        $userInitials = 'U';
        if ($user && $user->name) {
            $nameParts = preg_split('/\s+/', trim($user->name));
            if (count($nameParts) >= 2) {
                $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
            } else {
                $userInitials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0]))));
            }
        }

        if ($userRole === 'editor' || $userRole === 'viewer') {
            $panelType = 'member';
            $panelRole = $userRole;
            $panelTitle = 'Team Member';
            $panelBadge = ucfirst($userRole) . ' Seat';
            $panelColor = 'slate';
            $accent = '#475569'; // Slate blue
            $accentDark = '#64748B';
            $glowRgba = 'rgba(100, 116, 139, 0.16)';
            $subtleRgba = 'rgba(100, 116, 139, 0.12)';
            $badgeText = '#94a3b8';
            $badgeBorder = 'rgba(100, 116, 139, 0.30)';
            $logoGradient = 'from-slate-600 via-slate-500 to-slate-400';
            $selectionBg = 'selection:bg-slate-600 selection:text-white';
        } elseif ($isAgencyRoute || ($isAgencyPlan && $userRole === 'owner')) {
            $panelType = 'agency';
            $panelRole = 'agency_owner';
            $panelTitle = 'Agency Hub';
            $panelBadge = 'Agency Owner';
            $panelColor = 'teal';
            $accent = '#0D9488'; // Teal
            $accentDark = '#14B8A6';
            $glowRgba = 'rgba(20, 184, 166, 0.16)';
            $subtleRgba = 'rgba(20, 184, 166, 0.12)';
            $badgeText = '#2dd4bf';
            $badgeBorder = 'rgba(20, 184, 166, 0.30)';
            $logoGradient = 'from-teal-500 via-teal-400 to-cyan-300';
            $selectionBg = 'selection:bg-teal-500 selection:text-slate-950';
        } else {
            $panelType = 'owner';
            $panelRole = 'owner';
            $panelTitle = 'Portfolio Studio';
            $panelBadge = 'Portfolio Owner';
            $panelColor = 'green';
            $accent = '#16A34A'; // Green
            $accentDark = '#22C55E';
            $glowRgba = 'rgba(34, 197, 94, 0.16)';
            $subtleRgba = 'rgba(34, 197, 94, 0.12)';
            $badgeText = '#4ade80';
            $badgeBorder = 'rgba(34, 197, 94, 0.30)';
            $logoGradient = 'from-emerald-500 via-emerald-400 to-yellow-400';
            $selectionBg = 'selection:bg-emerald-500 selection:text-white';
        }
    @endphp

    <style>
        :root {
            --panel-accent: {{ $accent }};
            --panel-accent-dark: {{ $accentDark }};
            --panel-accent-glow: {{ $glowRgba }};
            --panel-accent-subtle: {{ $subtleRgba }};
            --panel-badge-text: {{ $badgeText }};
            --panel-badge-border: {{ $badgeBorder }};
        }
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .dashboard-glow {
            background: radial-gradient(circle at 50% 0%, var(--panel-accent-glow), transparent 50%),
                        radial-gradient(circle at 100% 100%, var(--panel-accent-subtle), transparent 40%);
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
            border-color: var(--panel-badge-border);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -10px var(--panel-accent-glow);
        }
        .panel-role-badge {
            background-color: var(--panel-accent-subtle);
            color: var(--panel-badge-text);
            border: 1px solid var(--panel-badge-border);
        }
        /* Ultra-Clean Modern Sidebar Navigation (Vercel / Linear Minimal Style) */
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.8125rem; /* 13px */
            font-weight: 500;
            letter-spacing: -0.01em;
            background-color: transparent;
            border: 1px solid transparent;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .sidebar-nav-item .sidebar-nav-icon {
            width: 1.125rem; /* 18px */
            height: 1.125rem;
            color: #8b949e;
            flex-shrink: 0;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        .sidebar-nav-item .sidebar-nav-label {
            white-space: nowrap;
            line-height: 1.25;
            transition: color 0.15s ease;
        }
        
        /* Hover State */
        .sidebar-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }
        .sidebar-nav-item:hover .sidebar-nav-icon {
            color: #ffffff;
        }
        .sidebar-nav-item:hover .sidebar-nav-label {
            color: #ffffff;
        }
        
        /* Active State (As in reference screenshot) */
        .sidebar-nav-item.is-active {
            background-color: #24272d !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        .sidebar-nav-item.is-active .sidebar-nav-icon {
            color: #ffffff !important;
        }
        .sidebar-nav-item.is-active .sidebar-nav-label {
            color: #ffffff !important;
        }
        
        .sidebar-nav-section-title {
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            padding: 0 0.75rem 0.25rem 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
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
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }
        /* Dashboard Layout Architecture */
        .dashboard-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dashboard-main-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: padding-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Desktop Mode (>= 1024px) */
        @media (min-width: 1024px) {
            .dashboard-sidebar {
                width: 16.5rem;
                transform: translateX(0) !important;
            }
            .dashboard-main-shell {
                padding-left: 16.5rem !important;
            }

            /* Collapsed desktop state */
            .dashboard-sidebar.is-collapsed {
                width: 5.25rem !important;
            }
            .dashboard-main-shell.is-collapsed {
                padding-left: 5.25rem !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-label,
            .dashboard-sidebar.is-collapsed .sidebar-nav-label,
            .dashboard-sidebar.is-collapsed .sidebar-nav-section-title {
                display: none !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-link,
            .dashboard-sidebar.is-collapsed .sidebar-nav-item {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                gap: 0 !important;
                width: 2.25rem !important;
                height: 2.25rem !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-header-box {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-header-box > div {
                justify-content: center !important;
                width: 100% !important;
            }
            .dashboard-sidebar.is-collapsed nav {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-footer-btn span {
                display: none !important;
            }
            .dashboard-sidebar.is-collapsed .sidebar-footer-btn {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }

        /* Mobile / Tablet Mode (< 1024px) */
        @media (max-width: 1023px) {
            .dashboard-sidebar {
                width: 18rem !important;
                max-width: 85vw !important;
                transform: translateX(-100%) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8) !important;
            }
            .dashboard-sidebar.is-open {
                transform: translateX(0) !important;
            }
            .dashboard-main-shell {
                padding-left: 0 !important;
            }
            /* Always show all labels in mobile drawer */
            .dashboard-sidebar .sidebar-label {
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
<body class="bg-slate-950 {{ $selectionBg }}" x-data="{ sidebarOpen: false, sidebarCollapsed: false, showLogoutModal: false }">

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-sidebar-overlay"
         onclick="window.closeMobileSidebar()"
         x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden" 
         @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar (Multi-tier dark glass with dynamic accent cues) -->
    <aside id="dashboard-sidebar"
           :class="{ 'is-open': sidebarOpen, 'is-collapsed': sidebarCollapsed }" 
           class="dashboard-sidebar glass-card border-r border-white/5 bg-slate-950/95 flex flex-col h-screen overflow-hidden">
        
        <!-- Sidebar Header / Logo & Role Badge -->
        <div class="sidebar-header-box h-20 flex flex-col justify-center border-b border-white/5 transition-all duration-300" :class="sidebarCollapsed ? 'items-center px-0' : 'px-5'">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-tr {{ $logoGradient }} flex items-center justify-center shadow-md shadow-black/40 group-hover:scale-105 transition-transform duration-200" style="border: 1px solid var(--panel-badge-border);">
                        <svg class="w-5 h-5 text-slate-950 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" class="sidebar-label flex flex-col">
                        <span class="text-lg font-extrabold font-heading tracking-tight bg-gradient-to-r {{ $logoGradient }} bg-clip-text text-transparent whitespace-nowrap">
                            DevFolio
                        </span>
                        <span class="text-[10px] font-mono font-bold tracking-wider uppercase" style="color: var(--panel-badge-text);">
                            {{ $panelBadge }}
                        </span>
                    </div>
                </a>
                <button type="button" onclick="window.closeMobileSidebar()" @click="sidebarOpen = false" class="ml-auto lg:hidden text-slate-400 hover:text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Sidebar Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-5">
            
            <!-- SECTION 1: WORKSPACE HUBS -->
            <div>
                <div class="sidebar-nav-section-title" x-show="!sidebarCollapsed">
                    Workspace
                </div>
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                       title="Dashboard">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Dashboard</span>
                    </a>

                    <!-- Agency Workspace -->
                    <a href="{{ route('agency') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('agency') ? 'is-active' : '' }}"
                       title="Agency Hub">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Agency Hub</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 2: PORTFOLIO CONTENT (STUDIO) -->
            <div class="pt-3 border-t border-white/5">
                <div class="sidebar-nav-section-title" x-show="!sidebarCollapsed">
                    Portfolio Studio
                </div>
                <div class="space-y-1">
                    <!-- Profile Info -->
                    <a href="{{ route('developer.profile') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.profile') ? 'is-active' : '' }}"
                       title="Profile Details">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Profile & Bio</span>
                    </a>

                    <!-- Projects -->
                    <a href="{{ route('developer.projects') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.projects') ? 'is-active' : '' }}"
                       title="Projects Showcase">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Projects</span>
                    </a>

                    <!-- Experience Timeline -->
                    <a href="{{ route('developer.experiences') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.experiences') ? 'is-active' : '' }}"
                       title="Career Experience">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Experience</span>
                    </a>

                    <!-- Skills Matrix -->
                    <a href="{{ route('developer.skills') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.skills') ? 'is-active' : '' }}"
                       title="Technical Skills">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Skills Matrix</span>
                    </a>

                    <!-- Certificates -->
                    <a href="{{ route('developer.certificates') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.certificates') ? 'is-active' : '' }}"
                       title="Accreditations">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Certificates</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 3: CAREER & AI PIPELINES -->
            <div class="pt-3 border-t border-white/5">
                <div class="sidebar-nav-section-title" x-show="!sidebarCollapsed">
                    Career & AI Suite
                </div>
                <div class="space-y-1">
                    <!-- AI Resumes -->
                    <a href="{{ route('developer.resumes') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.resumes') ? 'is-active' : '' }}"
                       title="Tailored Resumes">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">AI Resumes</span>
                    </a>

                    <!-- Cover Letters -->
                    <a href="{{ route('developer.cover-letters') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.cover-letters') ? 'is-active' : '' }}"
                       title="Cover Letters">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Cover Letters</span>
                    </a>

                    <!-- Kanban Job Tracker -->
                    <a href="{{ route('developer.job-tracker') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.job-tracker') ? 'is-active' : '' }}"
                       title="Job Application Pipeline">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Job Tracker (Kanban)</span>
                    </a>

                    <!-- Resume Import -->
                    <a href="{{ route('developer.resume-import') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.resume-import') ? 'is-active' : '' }}"
                       title="PDF Parser & Importer">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Import Resume PDF</span>
                    </a>

                    <!-- GitHub Sync -->
                    <a href="{{ route('developer.github-sync') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.github-sync') ? 'is-active' : '' }}"
                       title="GitHub Repositories">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">GitHub Sync</span>
                    </a>

                    <!-- AI BYOK Provider Settings -->
                    <a href="{{ route('developer.ai-settings') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.ai-settings') ? 'is-active' : '' }}"
                       title="OpenAI / Anthropic Keys">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">AI Settings (BYOK)</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 4: PLATFORM CONFIGURATION -->
            <div class="pt-3 border-t border-white/5">
                <div class="sidebar-nav-section-title" x-show="!sidebarCollapsed">
                    Platform Design
                </div>
                <div class="space-y-1">
                    <!-- Themes & Palettes -->
                    <a href="{{ route('developer.themes') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.themes') ? 'is-active' : '' }}"
                       title="Visual Themes & Modes">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21a4 4 0 01-4-4 4 4 0 014 4 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Theme & Mode</span>
                    </a>

                    <!-- Custom Domains -->
                    <a href="{{ route('developer.domains') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.domains') ? 'is-active' : '' }}"
                       title="Custom Apex & CNAME Domains">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Custom Domains</span>
                    </a>

                    <!-- Resume Blade Templates -->
                    <a href="{{ route('developer.templates') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.templates') ? 'is-active' : '' }}"
                       title="Resume Templates Catalog">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Resume Templates</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 5: ACCOUNT & COMPLIANCE -->
            <div class="pt-3 border-t border-white/5">
                <div class="sidebar-nav-section-title" x-show="!sidebarCollapsed">
                    Account & Settings
                </div>
                <div class="space-y-1">
                    <!-- Billing & Invoices -->
                    <a href="{{ route('developer.billing') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.billing') ? 'is-active' : '' }}"
                       title="Subscription & Invoices">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Billing & Usage</span>
                    </a>

                    <!-- Privacy & GDPR -->
                    <a href="{{ route('developer.privacy') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('developer.privacy') ? 'is-active' : '' }}"
                       title="Privacy & Data Export">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Privacy & GDPR</span>
                    </a>

                    <!-- Onboarding Setup Wizard -->
                    <a href="{{ route('onboarding') }}" 
                       class="sidebar-nav-item group {{ request()->routeIs('onboarding') ? 'is-active' : '' }}"
                       title="Setup Wizard">
                        <svg class="sidebar-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-nav-label">Setup Wizard</span>
                    </a>
                </div>
            </div>

            <!-- SECTION 6: SUPER ADMIN (If Elevated) -->
            @if(auth()->user()?->is_super_admin)
            <div class="pt-3 border-t border-rose-500/20">
                <div class="sidebar-nav-section-title text-rose-400 font-mono" x-show="!sidebarCollapsed">
                    Root Privileges
                </div>

                <a href="{{ route('super-admin.dashboard') }}" 
                    class="sidebar-nav-item group bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300"
                    title="Super Admin Master Control">
                    <svg class="sidebar-nav-icon text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="sidebar-nav-label font-bold font-mono">SUPER ADMIN</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- Sidebar Footer (User info & Sign out) -->
        <div class="p-3 border-t border-white/5 bg-slate-950/60">
            <button type="button" 
                    @click="showLogoutModal = true"
                    class="sidebar-nav-item w-full group hover:bg-red-500/10 hover:text-red-300 transition-all cursor-pointer" 
                    title="Sign Out">
                <svg class="sidebar-nav-icon group-hover:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="!sidebarCollapsed" class="sidebar-nav-label group-hover:text-red-300 font-medium">Sign Out</span>
            </button>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div id="dashboard-main-shell" :class="{ 'is-collapsed': sidebarCollapsed }" class="dashboard-main-shell flex-1 flex flex-col min-h-screen">
        <div class="dashboard-glow flex-1 absolute inset-0 z-[-1] pointer-events-none"></div>

        <!-- Top Header (Clima-style with hamburger and page title) -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 border-b border-white/5 glass-card bg-slate-950/80 backdrop-blur-md">
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Hamburger Button -->
                <button type="button"
                        id="dashboard-hamburger-btn"
                        onclick="window.toggleDashboardSidebar()"
                        @click="window.innerWidth < 1024 ? sidebarOpen = !sidebarOpen : sidebarCollapsed = !sidebarCollapsed" 
                        class="text-slate-300 hover:text-white focus:outline-none p-2 -ml-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer"
                        title="Toggle Sidebar"
                        aria-label="Toggle Navigation Sidebar">
                    <svg class="w-6 h-6 shrink-0 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Page Title -->
                <h1 class="text-base sm:text-lg font-bold font-heading text-white tracking-tight whitespace-nowrap">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <!-- Top Right Section -->
            <div class="flex items-center gap-3">
                <!-- Role Status Pill -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold panel-role-badge">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: var(--panel-accent-dark);"></span>
                    <span>{{ $panelBadge }}</span>
                </div>

                <!-- Live Portfolio Link Badge -->
                @if($userProfile && $userProfile->is_published)
                <a href="{{ url('/' . $userProfile->slug) }}" target="_blank" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Portfolio</span>
                    <svg class="w-3.5 h-3.5 ml-0.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
                @endif

                <!-- User Profile Initials Dropdown Component -->
                <div class="relative pl-2 border-l border-white/10" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            type="button" 
                            class="flex items-center gap-2.5 p-1 sm:px-2 rounded-xl hover:bg-white/5 border border-transparent hover:border-white/10 transition-all focus:outline-none group"
                            id="user-menu-button" 
                            aria-expanded="false" 
                            aria-haspopup="true">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr {{ $logoGradient }} flex items-center justify-center text-slate-950 font-bold text-xs shadow-md shadow-black/40 border border-white/20 group-hover:scale-105 transition-transform">
                            {{ $userInitials }}
                        </div>
                        <span class="text-sm font-semibold text-slate-200 hidden md:inline group-hover:text-white">{{ $user->name ?? 'User' }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                         class="absolute right-0 mt-2 w-64 rounded-2xl glass-card bg-slate-950/95 border border-white/10 shadow-2xl shadow-black/90 py-2 z-50 divide-y divide-white/5"
                         style="display: none;">
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-tr {{ $logoGradient }} flex items-center justify-center text-slate-950 font-bold text-sm shadow-md border border-white/20">
                                    {{ $userInitials }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ $user->name ?? 'User' }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ $user->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold panel-role-badge">
                                    <span class="w-1 h-1 rounded-full animate-pulse" style="background-color: var(--panel-accent-dark);"></span>
                                    {{ $panelBadge }}
                                </span>
                            </div>
                        </div>

                        <!-- Menu Items: Profile & Settings -->
                        <div class="py-1.5 px-1.5 space-y-0.5">
                            <!-- Profile Link -->
                            <a href="{{ route('developer.profile') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-xl transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 group-hover:text-emerald-400 group-hover:border-emerald-500/30 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-200 group-hover:text-white">Profile</span>
                                    <span class="text-[10px] text-slate-400">Edit your portfolio details</span>
                                </div>
                            </a>

                            <!-- Settings Link -->
                            <a href="{{ route('developer.billing') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-xl transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 group-hover:border-cyan-500/30 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-200 group-hover:text-white">Settings</span>
                                    <span class="text-[10px] text-slate-400">Billing, domains & workspace</span>
                                </div>
                            </a>
                        </div>

                        <!-- Logout Button (triggers modal) -->
                        <div class="py-1.5 px-1.5">
                            <button @click="userMenuOpen = false; showLogoutModal = true" 
                                    type="button" 
                                    class="w-full flex items-center gap-3 px-3 py-2 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-colors group text-left">
                                <div class="w-7 h-7 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 group-hover:bg-red-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="font-semibold text-red-400 group-hover:text-red-300">Sign Out</span>
                                    <span class="text-[10px] text-red-400/70">Terminate active session</span>
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
        <footer class="py-6 border-t border-white/5 text-center text-xs text-slate-500 bg-slate-950/50 mt-auto">
            DevFolio SaaS Platform &bull; AI-Powered Portfolio & Resume Suite
        </footer>
    </div>

    <!-- Custom Logout Verification Modal -->
    <div x-show="showLogoutModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" 
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
             class="relative w-full max-w-md p-6 rounded-3xl glass-card bg-slate-900/95 border border-white/10 shadow-2xl shadow-black/90 overflow-hidden">
            
            <!-- Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none" style="background-color: var(--panel-accent-glow);"></div>

            <div class="relative flex flex-col items-center text-center">
                <!-- Icon -->
                <div class="w-14 h-14 rounded-2xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 mb-4 shadow-lg shadow-red-500/10 animate-pulse">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>

                <!-- Title & Description -->
                <h3 class="text-xl font-bold font-heading text-white tracking-tight">
                    Confirm Sign Out
                </h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-400">
                    Are you sure you want to log out? Your active authentication session will be securely terminated and you will need to log back in to access this dashboard.
                </p>

                <!-- User Pill inside Modal -->
                <div class="mt-4 w-full py-2 px-3 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-center gap-2 text-xs text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Active account: <strong class="text-white">{{ $user->email ?? 'User' }}</strong></span>
                </div>

                <!-- Modal Action Buttons -->
                <div class="mt-6 grid grid-cols-2 gap-3 w-full">
                    <button type="button" 
                            @click="showLogoutModal = false"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition-all">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 border border-red-500/40 shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
