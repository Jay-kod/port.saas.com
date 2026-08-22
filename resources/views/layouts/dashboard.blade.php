<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">

    <!-- Header (Green & Yellow Theme) -->
    <header class="fixed top-0 inset-x-0 h-16 bg-green-700 text-white z-50 flex items-center px-4 shadow-md border-b-4 border-yellow-400">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 mr-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <h1 class="text-xl font-bold tracking-tight">@yield('title', 'Dashboard')</h1>
        
        <div class="ml-auto flex items-center space-x-4">
            <span class="text-sm font-medium">{{ auth()->user()->name ?? 'User' }}</span>
        </div>
    </header>

    <!-- Sidebar & Content Wrapper -->
    <div class="flex h-screen pt-16">
        
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-16'" 
            class="fixed inset-y-0 left-0 pt-16 bg-white border-r border-gray-200 transition-all duration-300 z-40 overflow-y-auto shadow-sm"
        >
            <nav class="flex flex-col p-2 space-y-1">
                <!-- Dashboard Link -->
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 rounded-md hover:bg-green-50 hover:text-green-700 group transition-colors">
                    <svg class="w-6 h-6 flex-shrink-0 text-gray-500 group-hover:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-3 font-medium whitespace-nowrap" x-show="sidebarOpen">Dashboard</span>
                </a>

                <!-- Agency Link -->
                <a href="{{ route('agency') }}" class="flex items-center p-2 rounded-md hover:bg-green-50 hover:text-green-700 group transition-colors">
                    <svg class="w-6 h-6 flex-shrink-0 text-gray-500 group-hover:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="ml-3 font-medium whitespace-nowrap" x-show="sidebarOpen">Agency</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main 
            :class="sidebarOpen ? 'ml-64' : 'ml-16'" 
            class="flex-1 transition-all duration-300 p-6 bg-gray-50 overflow-y-auto"
        >
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
