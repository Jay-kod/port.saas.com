<div class="w-full max-w-6xl mx-auto rounded-3xl overflow-hidden shadow-2xl border border-emerald-900/40 bg-gray-950 grid grid-cols-1 lg:grid-cols-12 min-h-[680px]">
    {{-- Left Side: Emerald Gradient Showcase & Step Cards --}}
    <div class="lg:col-span-6 p-8 sm:p-12 flex flex-col justify-between relative overflow-hidden" 
         style="background: radial-gradient(circle at 20% 20%, #064e3b 0%, #022c22 50%, #03130d 100%);">
        
        {{-- Ambient Glowing Orb --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Brand Logo --}}
        <div class="relative z-10">
            <a href="/" class="inline-flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 text-gray-950 flex items-center justify-center font-black text-base shadow-lg shadow-emerald-500/20">
                    ⚡
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-emerald-400">.AI</span></span>
            </a>
        </div>

        {{-- Middle Hero Text --}}
        <div class="my-10 relative z-10 space-y-4">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Get Started<br>with Us
            </h1>
            <p class="text-sm sm:text-base text-emerald-200/80 max-w-md leading-relaxed">
                Complete these easy steps to register your account and launch your developer career platform.
            </p>
        </div>

        {{-- Bottom Step Cards (Exact 3-Card layout from design) --}}
        <div class="grid grid-cols-3 gap-3 relative z-10">
            {{-- Step 1 (Active White Card) --}}
            <div class="p-4 rounded-2xl bg-white text-gray-950 shadow-xl flex flex-col justify-between min-h-[110px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-gray-950 text-white font-bold text-xs flex items-center justify-center">
                    1
                </div>
                <span class="text-xs font-bold leading-tight">
                    Sign up your<br>account
                </span>
            </div>

            {{-- Step 2 (Frosted Emerald Glass Card) --}}
            <div class="p-4 rounded-2xl bg-emerald-900/30 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[110px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-xs flex items-center justify-center border border-emerald-500/30">
                    2
                </div>
                <span class="text-xs font-semibold leading-tight text-emerald-200/90">
                    Set up your<br>workspace
                </span>
            </div>

            {{-- Step 3 (Frosted Emerald Glass Card) --}}
            <div class="p-4 rounded-2xl bg-emerald-900/30 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[110px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-xs flex items-center justify-center border border-emerald-500/30">
                    3
                </div>
                <span class="text-xs font-semibold leading-tight text-emerald-200/90">
                    Launch your<br>portfolio
                </span>
            </div>
        </div>
    </div>

    {{-- Right Side: Dark Elegant Registration Form --}}
    <div class="lg:col-span-6 p-8 sm:p-12 flex flex-col justify-center bg-gray-950 border-t lg:border-t-0 lg:border-l border-gray-800/80">
        <div class="w-full max-w-md mx-auto space-y-6">
            {{-- Form Header --}}
            <div class="space-y-1.5 text-center lg:text-left">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Sign Up Account
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">
                    Enter your personal data, or <a href="/admin/login" class="font-semibold text-emerald-400 hover:text-emerald-300 underline">sign in to existing account</a>.
                </p>
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

            {{-- Filament Registration Form --}}
            <div class="filament-custom-auth-form">
                {{ $this->content }}
            </div>

            {{-- Switch to Login Link --}}
            <div class="text-center pt-3 border-t border-gray-900">
                <p class="text-xs text-gray-400">
                    Already have an account? 
                    <a href="/admin/login" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline transition ml-1">
                        Log in here &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
