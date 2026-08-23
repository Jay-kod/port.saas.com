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
            <div class="hidden md:flex items-center gap-6 lg:gap-8">
                <nav class="flex items-center gap-6 lg:gap-8 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition">Home</a>
                    @if(request()->routeIs('home'))
                        <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition">Features</a>
                    @else
                        <a href="{{ route('home') }}#features" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition">Features</a>
                    @endif
                    <a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition">Pricing</a>
                    <a href="{{ route('discover') }}" class="{{ request()->routeIs('discover') ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} transition">Discover</a>
                </nav>

                {{-- Dynamic Get Started Dropdown Launcher --}}
                <div class="relative inline-block text-left" id="get-started-dropdown-container">
                    <button
                        type="button"
                        id="get-started-dropdown-btn"
                        onclick="toggleGetStartedDropdown(event)"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-extrabold transition-all duration-200 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer select-none"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span>Get Started</span>
                        <svg id="get-started-dropdown-arrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown Menu with 3 Portals --}}
                    <div
                        id="get-started-dropdown-menu"
                        class="hidden absolute right-0 mt-2.5 w-80 sm:w-88 rounded-2xl bg-gray-950/95 border border-gray-800 shadow-2xl backdrop-blur-2xl p-2.5 z-50 space-y-1.5 transition-all duration-200 origin-top-right"
                        role="menu"
                    >
                        <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-widest text-gray-400 border-b border-gray-800/80 flex items-center justify-between">
                            <span>Choose Your Workspace Portal</span>
                            <span class="text-amber-400 text-xs">⚡</span>
                        </div>

                        {{-- 1. Developer Sign In & Workspace --}}
                        <a
                            href="{{ route('developer.login') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-start gap-3 p-3 rounded-xl bg-gray-900/40 hover:bg-emerald-950/50 border border-gray-800/60 hover:border-emerald-500/40 transition-all duration-200 group"
                            role="menuitem"
                        >
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-110 transition-transform shadow-md shadow-emerald-500/10">
                                👤
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-extrabold text-white text-xs group-hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                                        <span>Developer Workspace</span>
                                        <svg class="w-3 h-3 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold uppercase tracking-wider">Default</span>
                                </div>
                                <p class="text-[11px] text-gray-400 group-hover:text-gray-300 leading-tight mt-0.5">
                                    Personal portfolio, AI resume tailoring & job applications
                                </p>
                            </div>
                        </a>

                        {{-- 2. Agency Hub --}}
                        <a
                            href="{{ route('agency.login') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-start gap-3 p-3 rounded-xl bg-gray-900/40 hover:bg-teal-950/50 border border-gray-800/60 hover:border-teal-500/40 transition-all duration-200 group"
                            role="menuitem"
                        >
                            <div class="w-8 h-8 rounded-lg bg-teal-500/15 border border-teal-500/30 text-teal-400 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-110 transition-transform shadow-md shadow-teal-500/10">
                                🏢
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-extrabold text-white text-xs group-hover:text-teal-300 transition-colors flex items-center gap-1.5">
                                        <span>Agency & Teams Hub</span>
                                        <svg class="w-3 h-3 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-teal-500/20 text-teal-300 font-bold uppercase tracking-wider">Agency</span>
                                </div>
                                <p class="text-[11px] text-gray-400 group-hover:text-gray-300 leading-tight mt-0.5">
                                    Multi-client portfolios, white-labeling & team permissions
                                </p>
                            </div>
                        </a>

                        {{-- 3. Super Admin --}}
                        <a
                            href="{{ route('super-admin.login') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-start gap-3 p-3 rounded-xl bg-gray-900/40 hover:bg-amber-950/50 border border-gray-800/60 hover:border-amber-500/40 transition-all duration-200 group"
                            role="menuitem"
                        >
                            <div class="w-8 h-8 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-110 transition-transform shadow-md shadow-amber-500/10">
                                👑
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-extrabold text-white text-xs group-hover:text-amber-300 transition-colors flex items-center gap-1.5">
                                        <span>Master Super Admin</span>
                                        <svg class="w-3 h-3 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold uppercase tracking-wider">Root</span>
                                </div>
                                <p class="text-[11px] text-gray-400 group-hover:text-gray-300 leading-tight mt-0.5">
                                    Global platform operations, custom domains & moderation
                                </p>
                            </div>
                        </a>

                        {{-- Footer Action --}}
                        <div class="pt-2 mt-1 border-t border-gray-900 px-3 py-1.5 flex items-center justify-between text-[11px] text-gray-400 bg-gray-900/40 rounded-xl">
                            <span>New developer?</span>
                            <a href="/admin/register" target="_blank" rel="noopener noreferrer" class="font-bold text-amber-400 hover:text-amber-300 hover:underline transition">
                                Create Free Account &rarr;
                            </a>
                        </div>
                    </div>
                </div>
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
        <nav class="flex flex-col gap-1 p-4 flex-1 overflow-y-auto">
            <a href="{{ route('home') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('home') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 font-bold' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} text-sm font-medium transition">Home</a>
            @if(request()->routeIs('home'))
                <a href="#features" onclick="document.getElementById('mobile-menu').classList.add('translate-x-full'); document.getElementById('mobile-menu-overlay').classList.add('hidden');" class="px-4 py-2.5 rounded-xl text-gray-600 dark:text-gray-300 transition hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white text-sm font-medium">Features</a>
            @else
                <a href="{{ route('home') }}#features" class="px-4 py-2.5 rounded-xl text-gray-600 dark:text-gray-300 transition hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white text-sm font-medium">Features</a>
            @endif
            <a href="{{ route('pricing') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('pricing') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 font-bold' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} text-sm font-medium transition">Pricing</a>
            <a href="{{ route('discover') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('discover') ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 font-bold' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white' }} text-sm font-medium transition">Discover</a>

            <div class="pt-4 mt-2 border-t border-gray-200 dark:border-gray-800/60">
                <div class="px-4 pb-2 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                    Choose Workspace Portal
                </div>
                <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                    <span class="flex items-center gap-2"><span>👤</span><span>Developer Workspace</span></span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <a href="{{ route('agency.login') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/40 transition">
                    <span class="flex items-center gap-2"><span>🏢</span><span>Agency & Teams Hub</span></span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <a href="{{ route('super-admin.login') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition">
                    <span class="flex items-center gap-2"><span>👑</span><span>Master Super Admin</span></span>
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </nav>
        <div class="p-4 border-t border-gray-200 dark:border-gray-800/60">
            <a href="{{ route('developer.login') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-bold transition shadow-lg shadow-amber-500/20">
                <span>Get Started</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    {{-- Dropdown Toggle Script --}}
    <script>
        function toggleGetStartedDropdown(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const menu = document.getElementById('get-started-dropdown-menu');
            const arrow = document.getElementById('get-started-dropdown-arrow');
            const btn = document.getElementById('get-started-dropdown-btn');
            if (!menu) return;

            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                if (arrow) arrow.classList.add('rotate-180');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }

        document.addEventListener('click', function(event) {
            const container = document.getElementById('get-started-dropdown-container');
            const menu = document.getElementById('get-started-dropdown-menu');
            const arrow = document.getElementById('get-started-dropdown-arrow');
            const btn = document.getElementById('get-started-dropdown-btn');

            if (container && !container.contains(event.target)) {
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    if (arrow) arrow.classList.remove('rotate-180');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
            }
        });
    </script>
