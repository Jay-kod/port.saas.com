<div 
    x-data="{ 
        activeRole: 'user',
        selectRole(role, email) {
            this.activeRole = role;
            if (email) {
                const emailInput = document.querySelector('input[type=\'email\'], input[name*=\'email\']');
                const passInput = document.querySelector('input[type=\'password\'], input[name*=\'password\']');
                if (emailInput) {
                    emailInput.value = email;
                    emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (passInput) {
                    passInput.value = 'password';
                    passInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    }" 
    class="w-full h-screen min-h-screen grid grid-cols-1 lg:grid-cols-12 m-0 p-0 rounded-none border-none overflow-hidden bg-gray-950"
>
    {{-- Left Side: Emerald Gradient Showcase & Feature Cards --}}
    <div class="lg:col-span-6 p-8 sm:p-10 lg:p-14 flex flex-col justify-between relative overflow-hidden h-full" 
         style="background: radial-gradient(circle at 20% 20%, #064e3b 0%, #022c22 50%, #03130d 100%);">
        
        {{-- Ambient Glowing Orb --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Brand Logo --}}
        <div class="relative z-10">
            <a href="/" class="inline-flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 text-gray-950 flex items-center justify-center font-black text-sm shadow-lg shadow-emerald-500/20">
                    ⚡
                </div>
                <span class="font-extrabold text-lg tracking-tight text-white">DevFolio<span class="text-emerald-400">.AI</span></span>
            </a>
        </div>

        {{-- Middle Hero Text --}}
        <div class="my-4 sm:my-6 relative z-10 space-y-2">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Welcome Back<br>to DevFolio
            </h1>
            <p class="text-xs sm:text-sm text-emerald-200/80 max-w-md leading-relaxed">
                Sign in to manage your developer portfolio, run AI resume tailoring, and track applications.
            </p>
        </div>

        {{-- Bottom Step Cards (Exact 3-Card layout from design) --}}
        <div class="grid grid-cols-3 gap-2.5 relative z-10">
            {{-- Card 1 (Active White Card) --}}
            <div class="p-3 rounded-2xl bg-white text-gray-950 shadow-xl flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-5 h-5 rounded-full bg-gray-950 text-white font-bold text-[10px] flex items-center justify-center">
                    🚀
                </div>
                <span class="text-[11px] font-bold leading-tight">
                    AI Resume<br>Tailoring
                </span>
            </div>

            {{-- Card 2 (Frosted Emerald Glass Card) --}}
            <div class="p-3 rounded-2xl bg-emerald-900/30 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-5 h-5 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-[10px] flex items-center justify-center border border-emerald-500/30">
                    🎨
                </div>
                <span class="text-[11px] font-semibold leading-tight text-emerald-200/90">
                    7 Handcrafted<br>Themes
                </span>
            </div>

            {{-- Card 3 (Frosted Emerald Glass Card) --}}
            <div class="p-3 rounded-2xl bg-emerald-900/30 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-5 h-5 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-[10px] flex items-center justify-center border border-emerald-500/30">
                    📊
                </div>
                <span class="text-[11px] font-semibold leading-tight text-emerald-200/90">
                    Job Tracker<br>Kanban
                </span>
            </div>
        </div>
    </div>

    {{-- Right Side: Dark Elegant Login Form --}}
    <div class="lg:col-span-6 p-6 sm:p-8 flex flex-col justify-center bg-gray-950 border-t lg:border-t-0 lg:border-l border-gray-800/80 overflow-y-auto h-full">
        <div class="w-full max-w-sm mx-auto space-y-4">
            {{-- Form Header --}}
            <div class="space-y-1 text-center lg:text-left">
                <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                    Sign In to Account
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">
                    Enter your credentials, or <a href="/admin/register" class="font-semibold text-amber-400 hover:text-amber-300 underline">create a free account</a>.
                </p>
            </div>

            {{-- Role Selection Tabs with Distinct Colors --}}
            <div class="space-y-2">
                <div class="grid grid-cols-3 gap-2 p-1.5 rounded-2xl bg-gray-900/90 border border-gray-800">
                    {{-- 1. Developer Tab (Cyan) --}}
                    <button
                        type="button"
                        @click="selectRole('user', '')"
                        :class="activeRole === 'user' 
                            ? 'bg-cyan-500/15 border-cyan-500/50 text-cyan-400 shadow-md shadow-cyan-500/10' 
                            : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                        class="py-2 px-1.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition text-xs font-bold text-center"
                    >
                        <span class="text-sm">👤</span>
                        <span>Developer</span>
                    </button>

                    {{-- 2. Admin Tab (Amber) --}}
                    <button
                        type="button"
                        @click="selectRole('admin', '')"
                        :class="activeRole === 'admin' 
                            ? 'bg-amber-500/15 border-amber-500/50 text-amber-400 shadow-md shadow-amber-500/10' 
                            : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                        class="py-2 px-1.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition text-xs font-bold text-center"
                    >
                        <span class="text-sm">🏢</span>
                        <span>Admin</span>
                    </button>

                    {{-- 3. Super Admin Tab (Purple) --}}
                    <button
                        type="button"
                        @click="selectRole('super_admin', 'admin@example.com')"
                        :class="activeRole === 'super_admin' 
                            ? 'bg-purple-500/15 border-purple-500/50 text-purple-400 shadow-md shadow-purple-500/10' 
                            : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                        class="py-2 px-1.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition text-xs font-bold text-center"
                    >
                        <span class="text-sm">👑</span>
                        <span>Super Admin</span>
                    </button>
                </div>

                {{-- Role Banner --}}
                <div x-show="activeRole === 'super_admin'" class="p-2.5 rounded-xl bg-purple-950/40 border border-purple-500/30 text-[11px] text-purple-300 flex items-center justify-between">
                    <span>Super Admin: <code class="text-purple-200">admin@example.com</code></span>
                    <button type="button" @click="selectRole('super_admin', 'admin@example.com')" class="underline text-purple-400 font-bold hover:text-purple-200">Auto-fill</button>
                </div>
            </div>

            {{-- Social Login Buttons --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <button type="button" class="py-2.5 px-4 rounded-xl border border-gray-800 bg-gray-900/80 hover:bg-gray-800 text-gray-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.7 0 3 .7 3.7 1.3l2.8-2.8C16.8 1.9 14.6 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.4 2.6C6.2 6.9 8.8 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                        <path fill="#FBBC05" d="M5.3 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.6C.7 9.9 0 12.4 0 15s.7 5.1 1.9 7.4l3.4-2.6z"/>
                        <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.2 0-5.8-2-6.7-4.9L1.9 16c1.8 3.7 5.6 6.3 10.1 6.3z"/>
                    </svg>
                    <span>Google</span>
                </button>

                <button type="button" class="py-2.5 px-4 rounded-xl border border-gray-800 bg-gray-900/80 hover:bg-gray-800 text-gray-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                    </svg>
                    <span>GitHub</span>
                </button>
            </div>

            {{-- Divider --}}
            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-gray-800"></div>
                <span class="flex-shrink mx-3 text-xs text-gray-500 uppercase tracking-widest font-medium">Or</span>
                <div class="flex-grow border-t border-gray-800"></div>
            </div>

            {{-- Filament Login Form --}}
            <div class="filament-custom-auth-form">
                {{ $this->content }}
            </div>

            {{-- Switch to Register Link --}}
            <div class="text-center pt-3 border-t border-gray-900">
                <p class="text-xs text-gray-400">
                    Don't have an account? 
                    <a href="/admin/register" class="font-bold text-amber-400 hover:text-amber-300 hover:underline transition ml-1">
                        Sign up for free &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
