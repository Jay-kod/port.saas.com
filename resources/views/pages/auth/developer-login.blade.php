<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\{layout, mount, rules, state};

layout('layouts.auth', ['title' => 'Developer Sign In | DevFolio.AI']);

state([
    'email' => '',
    'password' => '',
    'remember' => true,
]);

rules([
    'email' => ['required', 'string', 'email'],
    'password' => ['required', 'string'],
]);

mount(function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }
        if ($user->isAgencyUser()) {
            return redirect()->route('agency');
        }
        return redirect()->route('dashboard');
    }
});

$fillDemo = function () {
    $this->email = 'developer@example.com';
    $this->password = 'password';
};

$login = function () {
    $this->validate();

    if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    session()->regenerate();

    $user = Auth::user();

    // Check if user has completed onboarding / has profile
    $profile = $user->profile ?? $user->accounts()->first()?->profiles()->first();
    if (! $profile) {
        return redirect()->to('/onboarding');
    }

    // Sanitize intended URL to avoid redirecting back into login loops
    $intended = session()->pull('url.intended');
    if ($intended && ! Str::contains($intended, ['/login', '/logout', '/register', 'password'])) {
        return redirect()->to($intended);
    }

    return redirect()->to(route('dashboard'));
};
?>

<div class="w-full min-h-screen grid grid-cols-1 md:grid-cols-12 m-0 p-0 bg-gray-950 text-gray-100 selection:bg-emerald-500 selection:text-white">
    {{-- Left Showcase Column: Emerald Brand Experience --}}
    <div class="md:col-span-6 p-6 sm:p-10 lg:p-14 flex flex-col justify-between relative overflow-hidden min-h-[38vh] md:min-h-screen"
         style="background: radial-gradient(circle at 20% 20%, #064e3b 0%, #022c22 50%, #03130d 100%);">
        
        {{-- Ambient Glowing Accents --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Brand Header --}}
        <div class="relative z-10 flex items-center justify-between">
            <a href="/" class="inline-flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 text-gray-950 flex items-center justify-center font-black text-base shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                    ⚡
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-emerald-400">.AI</span></span>
            </a>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Developer Portal</span>
            </div>
        </div>

        {{-- Hero Copy --}}
        <div class="my-6 md:my-auto relative z-10 space-y-3">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Empower Your<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200">Developer Portfolio</span>
            </h1>
            <p class="text-xs sm:text-sm text-emerald-200/80 max-w-md leading-relaxed">
                Sign in to manage your public developer portfolio, tailor high-impact ATS resumes with AI, and track job applications in real time.
            </p>
        </div>

        {{-- Feature Highlights --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 relative z-10 mt-4 md:mt-0">
            <div class="p-3.5 rounded-2xl bg-white/95 text-gray-950 shadow-xl flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-gray-950 text-white font-bold text-xs flex items-center justify-center">
                    🚀
                </div>
                <span class="text-xs font-bold leading-tight">
                    AI Resume<br>Tailoring
                </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-xs flex items-center justify-center border border-emerald-500/40">
                    🎨
                </div>
                <span class="text-xs font-semibold leading-tight text-emerald-200/90">
                    7 Handcrafted<br>Themes
                </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-emerald-800/80 text-emerald-300 font-bold text-xs flex items-center justify-center border border-emerald-500/40">
                    📊
                </div>
                <span class="text-xs font-semibold leading-tight text-emerald-200/90">
                    Job Tracker<br>Kanban
                </span>
            </div>
        </div>
    </div>

    {{-- Right Form Column: Dark Sleek Emerald Login --}}
    <div class="md:col-span-6 p-6 sm:p-10 lg:p-14 flex flex-col justify-between bg-gray-950 border-t md:border-t-0 md:border-l border-gray-800/80">
        <div class="w-full max-w-md mx-auto my-auto space-y-5">
            {{-- Form Header --}}
            <div class="space-y-1.5 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Portfolio Owner Portal
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Developer Sign In
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">
                    Enter your credentials or use the instant demo button below.
                </p>
            </div>

            {{-- 1-Click Demo Credentials Card --}}
            <div class="p-3.5 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 flex items-center justify-between gap-3 transition hover:border-emerald-500/50">
                <div class="space-y-0.5 text-xs">
                    <div class="font-bold text-emerald-300 flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>Demo Developer Account</span>
                    </div>
                    <div class="text-gray-400 font-mono text-[11px]">
                        developer@example.com
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="fillDemo"
                    onclick="autofillForm('developer@example.com', 'password', 'email', 'password')"
                    class="py-1.5 px-3 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                >
                    <span>Auto-fill</span>
                    <span>&rarr;</span>
                </button>
            </div>

            {{-- Social Auth Buttons --}}
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('social.redirect', 'google') }}" class="py-2.5 px-3 rounded-xl border border-gray-800 bg-gray-900/90 hover:bg-gray-850 hover:border-gray-700 text-gray-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.7 0 3 .7 3.7 1.3l2.8-2.8C16.8 1.9 14.6 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.4 2.6C6.2 6.9 8.8 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                        <path fill="#FBBC05" d="M5.3 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.6C.7 9.9 0 12.4 0 15s.7 5.1 1.9 7.4l3.4-2.6z"/>
                        <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.2 0-5.8-2-6.7-4.9L1.9 16c1.8 3.7 5.6 6.3 10.1 6.3z"/>
                    </svg>
                    <span>Google</span>
                </a>

                <a href="{{ route('social.redirect', 'github') }}" class="py-2.5 px-3 rounded-xl border border-gray-800 bg-gray-900/90 hover:bg-gray-850 hover:border-gray-700 text-gray-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                    </svg>
                    <span>GitHub</span>
                </a>
            </div>

            {{-- Divider --}}
            <div class="relative flex items-center">
                <div class="flex-grow border-t border-gray-800"></div>
                <span class="flex-shrink mx-3 text-[11px] text-gray-500 uppercase tracking-widest font-semibold">Or with email</span>
                <div class="flex-grow border-t border-gray-800"></div>
            </div>

            {{-- Livewire Login Form --}}
            <form wire:submit="login" class="space-y-4">
                {{-- Email Address --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-gray-300">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        placeholder="you@domain.com"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-gray-900 border border-gray-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white placeholder-gray-500 text-sm transition outline-none"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5" x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold text-gray-300">
                            Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline transition">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            type="password"
                            id="password"
                            wire:model="password"
                            placeholder="••••••••"
                            required
                            class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-gray-900 border border-gray-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white placeholder-gray-500 text-sm transition outline-none"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-200 focus:outline-none cursor-pointer"
                            aria-label="Toggle password visibility"
                        >
                            {{-- Eye Open (when password is masked: showPassword is false) --}}
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- Eye Slash (when password is unmasked: showPassword is true) --}}
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="remember"
                            class="w-4 h-4 rounded bg-gray-900 border-gray-700 text-emerald-500 focus:ring-emerald-500/30 focus:ring-offset-gray-950"
                        />
                        <span class="text-xs text-gray-400">Remember this device</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-gray-950 font-extrabold text-sm shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/35 transition transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove>Sign In to Developer Workspace &rarr;</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-gray-950" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Authenticating...
                    </span>
                </button>
            </form>

            {{-- Register Prompt --}}
            <div class="text-center pt-2">
                <p class="text-xs text-gray-400">
                    Don't have a developer portfolio yet?
                    <a href="/admin/register" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline transition ml-1">
                        Create one for free &rarr;
                    </a>
                </p>
            </div>
        </div>

        {{-- Footer Cross-Portal Navigation Switcher --}}
        <div class="pt-6 mt-6 border-t border-gray-900">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-gray-500">
                <span>Looking for a different portal?</span>
                <div class="flex items-center gap-3">
                    <a href="/agency/login" class="inline-flex items-center gap-1 text-teal-400 hover:text-teal-300 hover:underline transition font-medium">
                        <span>🏢 Agency Hub</span>
                    </a>
                    <span>&bull;</span>
                    <a href="/super-admin/login" class="inline-flex items-center gap-1 text-amber-400 hover:text-amber-300 hover:underline transition font-medium">
                        <span>👑 Super Admin</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
