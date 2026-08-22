<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Super Admin')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-black text-gray-100" x-data="{ sidebarOpen: false }">

    <!-- Header (Red & Amoled Black Theme) -->
    <header class="fixed top-0 inset-x-0 h-16 bg-black text-white z-50 flex items-center px-4 shadow-lg border-b border-red-600">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 mr-4 rounded-md hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-red-600">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <h1 class="text-xl font-bold tracking-tight text-red-500">@yield('title', 'Super Admin')</h1>
        
        <div class="ml-auto flex items-center space-x-4">
            <span class="text-sm font-medium text-red-400">Super Admin: {{ auth()->user()->name ?? 'Admin' }}</span>
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs bg-red-950 hover:bg-red-900 text-red-300 border border-red-800 px-3 py-1.5 rounded font-semibold transition-colors">
                    Sign Out
                </button>
            </form>
        </div>
    </header>

    <!-- Sidebar & Content Wrapper -->
    <div class="flex h-screen pt-16">
        
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-16'" 
            class="fixed inset-y-0 left-0 pt-16 bg-black border-r border-red-900 transition-all duration-300 z-40 overflow-y-auto shadow-md"
        >
            <nav class="flex flex-col p-2 space-y-1">
                <!-- Super Admin Dashboard Link -->
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center p-2 rounded-md hover:bg-red-950 hover:text-red-400 group transition-colors">
                    <svg class="w-6 h-6 flex-shrink-0 text-red-700 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span class="ml-3 font-medium whitespace-nowrap" x-show="sidebarOpen">System Health</span>
                </a>

                <!-- User Dashboard Link -->
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 rounded-md hover:bg-red-950 hover:text-red-400 group transition-colors">
                    <svg class="w-6 h-6 flex-shrink-0 text-red-700 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-3 font-medium whitespace-nowrap" x-show="sidebarOpen">User Dashboard</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main 
            :class="sidebarOpen ? 'ml-64' : 'ml-16'" 
            class="flex-1 transition-all duration-300 p-6 bg-black overflow-y-auto"
        >
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
