<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\{layout, mount, rules, state};

layout('layouts.auth', ['title' => 'Super Admin Console Sign In | DevFolio.AI']);

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
    $this->email = 'admin@example.com';
    $this->password = 'password';
};

$login = function () {
    $this->validate();

    if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $user = Auth::user();

    // Strict Super Admin Verification
    if (! $user->isSuperAdmin()) {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => 'Access Restricted: This master console is strictly reserved for Platform Super Administrators. Please sign in via the Developer or Agency portal.',
        ]);
    }

    session()->regenerate();

    $intended = session()->pull('url.intended');
    if ($intended && ! Str::contains($intended, ['/login', '/logout', '/register', 'password'])) {
        return redirect()->to($intended);
    }

    return redirect()->to(route('super-admin.dashboard'));
};
?>

<div class="w-full min-h-screen grid grid-cols-1 md:grid-cols-12 m-0 p-0 bg-gray-950 text-gray-100 selection:bg-amber-500 selection:text-gray-950">
    {{-- Left Showcase Column: Amber Security Break Experience --}}
    <div class="md:col-span-6 p-6 sm:p-10 lg:p-14 flex flex-col justify-between relative overflow-hidden min-h-[38vh] md:min-h-screen"
         style="background: radial-gradient(circle at 20% 20%, #78350f 0%, #451a03 50%, #1c0a00 100%);">
        
        {{-- Ambient Glowing Accents --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-orange-600/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Brand Header with Warning Badge --}}
        <div class="relative z-10 flex items-center justify-between">
            <a href="/" class="inline-flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-orange-500 text-gray-950 flex items-center justify-center font-black text-base shadow-lg shadow-amber-500/25 group-hover:scale-105 transition-transform">
                    👑
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            </a>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 border border-amber-500/40 text-amber-300 backdrop-blur-md shadow-lg shadow-amber-500/10">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>ROOT ADMIN ZONE</span>
            </div>
        </div>

        {{-- Hero Copy --}}
        <div class="my-6 md:my-auto relative z-10 space-y-3">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Master Platform<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-orange-300">Control Console</span>
            </h1>
            <p class="text-xs sm:text-sm text-amber-200/80 max-w-md leading-relaxed">
                High-privilege platform management interface. Root operators can audit all tenants, manage custom domain routing, monitor AI usage quotas, and enforce platform security policies.
            </p>
        </div>

        {{-- Feature Highlights --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 relative z-10 mt-4 md:mt-0">
            <div class="p-3.5 rounded-2xl bg-white/95 text-gray-950 shadow-xl flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-gray-950 text-white font-bold text-xs flex items-center justify-center">
                    🔒
                </div>
                <span class="text-xs font-bold leading-tight">
                    Audit Trail<br>Active
                </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-950/40 border border-amber-500/30 text-amber-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-amber-800/80 text-amber-300 font-bold text-xs flex items-center justify-center border border-amber-500/40">
                    🌐
                </div>
                <span class="text-xs font-semibold leading-tight text-amber-200/90">
                    Platform-Wide<br>Telemetry
                </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-950/40 border border-amber-500/30 text-amber-100 backdrop-blur-md flex flex-col justify-between min-h-[90px] transform transition hover:scale-[1.02]">
                <div class="w-6 h-6 rounded-full bg-amber-800/80 text-amber-300 font-bold text-xs flex items-center justify-center border border-amber-500/40">
                    🛡️
                </div>
                <span class="text-xs font-semibold leading-tight text-amber-200/90">
                    Abuse &<br>Moderation
                </span>
            </div>
        </div>
    </div>

    {{-- Right Form Column: Dark Amber Security Console Login --}}
    <div class="md:col-span-6 p-6 sm:p-10 lg:p-14 flex flex-col justify-between bg-gray-950 border-t md:border-t-0 md:border-l border-gray-800/80">
        <div class="w-full max-w-md mx-auto my-auto space-y-5">
            {{-- Form Header --}}
            <div class="space-y-1.5 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 uppercase tracking-wider mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    High-Privilege Security Zone
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Super Admin Sign In
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">
                    Sign in with root administrator credentials. All actions are logged.
                </p>
            </div>

            {{-- 1-Click Demo Credentials Card --}}
            <div class="p-3.5 rounded-2xl bg-amber-950/30 border border-amber-500/30 flex items-center justify-between gap-3 transition hover:border-amber-500/50">
                <div class="space-y-0.5 text-xs">
                    <div class="font-bold text-amber-300 flex items-center gap-1.5">
                        <span>👑</span>
                        <span>Super Admin Account</span>
                    </div>
                    <div class="text-gray-400 font-mono text-[11px]">
                        admin@example.com
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="fillDemo"
                    onclick="autofillForm('admin@example.com', 'password', 'admin_email', 'admin_password')"
                    class="py-1.5 px-3 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                >
                    <span>Auto-fill</span>
                    <span>&rarr;</span>
                </button>
            </div>

            {{-- Livewire & Standard POST Login Form --}}
            <form method="POST" action="{{ route('super-admin.login.submit') }}" wire:submit="login" class="space-y-4">
                @csrf

                {{-- Email Address --}}
                <div class="space-y-1.5">
                    <label for="admin_email" class="block text-xs font-semibold text-gray-300">
                        Admin Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="admin_email"
                        wire:model="email"
                        placeholder="admin@devfolio.ai"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-gray-900 border border-gray-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-white placeholder-gray-500 text-sm transition outline-none"
                    />
                    @error('email')
                        <div class="p-2.5 rounded-lg bg-red-950/40 border border-red-500/40 text-red-300 text-xs mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5" x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between">
                        <label for="admin_password" class="block text-xs font-semibold text-gray-300">
                            Root Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300 hover:underline transition">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            id="admin_password"
                            wire:model="password"
                            placeholder="••••••••"
                            required
                            class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-gray-900 border border-gray-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-white placeholder-gray-500 text-sm transition outline-none"
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
                            name="remember"
                            value="1"
                            wire:model="remember"
                            class="w-4 h-4 rounded bg-gray-900 border-gray-700 text-amber-500 focus:ring-amber-500/30 focus:ring-offset-gray-950"
                        />
                        <span class="text-xs text-gray-400">Remember admin session</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-gray-950 font-extrabold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-500/35 transition transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove>Sign In to Master Control &rarr;</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-gray-950" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying Root Privileges...
                    </span>
                </button>
            </form>

            {{-- Security Notice --}}
            <div class="p-3 rounded-xl bg-amber-950/20 border border-amber-500/20 text-center">
                <p class="text-[11px] text-amber-300/80 leading-relaxed">
                    ⚠️ Restricted access. Only accounts with global super-admin permissions can authenticate to this console.
                </p>
            </div>
        </div>

        {{-- Footer Cross-Portal Navigation Switcher --}}
        <div class="pt-6 mt-6 border-t border-gray-900">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-gray-500">
                <span>Looking for user workspaces?</span>
                <div class="flex items-center gap-3">
                    <a href="/developer/login" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 hover:underline transition font-medium">
                        <span>👤 Developer Portal</span>
                    </a>
                    <span>&bull;</span>
                    <a href="/agency/login" class="inline-flex items-center gap-1 text-teal-400 hover:text-teal-300 hover:underline transition font-medium">
                        <span>🏢 Agency Hub</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
