    {{-- Top Navigation --}}
    <header class="border-b border-gray-200 dark:border-gray-800/80 bg-white/60 dark:bg-gray-950/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-18 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center font-black text-lg sm:text-xl shadow-lg shadow-amber-500/5">
                    ⚡
                </div>
                <span class="font-extrabold text-lg sm:text-xl tracking-tight text-gray-900 dark:text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-8">
                <nav class="flex items-center gap-8 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-amber-500 dark:text-amber-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition font-medium">Home</a>
                    @if(request()->routeIs('home'))
                        <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition font-medium">Features</a>
                    @else
                        <a href="{{ route('home') }}#features" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition font-medium">Features</a>
                    @endif
                    <a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'text-amber-500 dark:text-amber-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition font-medium">Pricing</a>
                    <a href="{{ route('discover') }}" class="{{ request()->routeIs('discover') ? 'text-amber-500 dark:text-amber-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition font-medium">Discover</a>
                </nav>

                <a href="/admin/login" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                    <span>Get Started</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <button
                type="button"
                id="mobile-menu-btn"
                class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-800/60 transition"
                aria-label="Toggle navigation menu"
                onclick="document.getElementById('mobile-menu').classList.toggle('translate-x-full'); document.getElementById('mobile-menu-overlay').classList.toggle('hidden');"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>

    {{-- Mobile Slide-out Menu --}}
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] hidden" onclick="document.getElementById('mobile-menu').classList.add('translate-x-full'); this.classList.add('hidden');"></div>
    <div id="mobile-menu" class="fixed top-0 right-0 w-72 h-full bg-white dark:bg-gray-950 border-l border-gray-200 dark:border-gray-800/80 z-[70] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-800/60">
            <span class="font-extrabold text-lg text-gray-900 dark:text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            <button
                type="button"
                class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-800/60 transition"
                onclick="document.getElementById('mobile-menu').classList.add('translate-x-full'); document.getElementById('mobile-menu-overlay').classList.add('hidden');"
                aria-label="Close menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex flex-col gap-1 p-4 flex-1">
            <a href="{{ route('home') }}" class="px-4 py-3 rounded-xl {{ request()->routeIs('home') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} font-medium transition">Home</a>
            @if(request()->routeIs('home'))
                <a href="#features" onclick="document.getElementById('mobile-menu').classList.add('translate-x-full'); document.getElementById('mobile-menu-overlay').classList.add('hidden');" class="px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 transition hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white font-medium">Features</a>
            @else
                <a href="{{ route('home') }}#features" class="px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 transition hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white font-medium">Features</a>
            @endif
            <a href="{{ route('pricing') }}" class="px-4 py-3 rounded-xl {{ request()->routeIs('pricing') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} font-medium transition">Pricing</a>
            <a href="{{ route('discover') }}" class="px-4 py-3 rounded-xl {{ request()->routeIs('discover') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} font-medium transition">Discover</a>
        </nav>
        <div class="p-4 border-t border-gray-200 dark:border-gray-800/60">
            <a href="/admin/login" class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                <span>Get Started</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
