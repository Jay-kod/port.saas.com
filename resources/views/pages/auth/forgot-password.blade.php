<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use function Livewire\Volt\{layout, rules, state};

layout('layouts.auth', ['title' => 'Reset Password | DevFolio.AI']);

state([
    'email' => '',
    'status' => null,
]);

rules([
    'email' => ['required', 'string', 'email'],
]);

$sendResetLink = function () {
    $this->validate();

    // Check if user exists
    $user = User::where('email', $this->email)->first();

    if ($user) {
        $status = Password::sendResetLink(['email' => $this->email]);
        $this->status = 'If an account exists with that email address, a password reset link has been dispatched.';
    } else {
        // Obscure whether account exists for security
        $this->status = 'If an account exists with that email address, a password reset link has been dispatched.';
    }
};
?>

<div class="w-full min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gray-950 text-gray-100 selection:bg-amber-500 selection:text-gray-950 relative overflow-hidden">
    {{-- Ambient Glowing Accents --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 space-y-6">
        {{-- Brand Header --}}
        <div class="text-center space-y-2">
            <a href="/" class="inline-flex items-center gap-2.5 group cursor-pointer" data-tooltip="Return to DevFolio Homepage">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center font-black text-xl shadow-lg shadow-amber-500/10 group-hover:scale-105 transition-transform">
                    ⚡
                </div>
                <span class="font-extrabold text-xl tracking-tight text-white">DevFolio<span class="text-amber-400">.AI</span></span>
            </a>
            <h1 class="text-2xl font-extrabold text-white tracking-tight pt-2">
                Reset your password
            </h1>
            <p class="text-xs sm:text-sm text-gray-400 max-w-xs mx-auto">
                Enter your account email address and we'll send you a link to reset your password.
            </p>
        </div>

        {{-- Card Container --}}
        <div class="p-6 sm:p-8 rounded-3xl bg-gray-900/80 border border-gray-800 shadow-2xl backdrop-blur-2xl space-y-5">
            @if ($status)
                <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-emerald-200">Reset Request Dispatched</p>
                        <p class="text-[11px] text-emerald-300/90 mt-0.5">{{ $status }}</p>
                    </div>
                </div>
            @endif

            <form wire:submit="sendResetLink" class="space-y-4">
                {{-- Email Address --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-gray-300">
                        Account Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        placeholder="you@domain.com"
                        required
                        autofocus
                        class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-white placeholder-gray-500 text-sm transition outline-none"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-gray-950 font-extrabold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-500/35 transition transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex items-center justify-center gap-2"
                    data-tooltip="Send password recovery email instructions"
                >
                    <span wire:loading.remove>Send Reset Instructions &rarr;</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-gray-950" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Dispatching...</span>
                    </span>
                </button>
            </form>

            {{-- Portal Return Links --}}
            <div class="pt-4 border-t border-gray-800/80 space-y-2 text-center text-xs">
                <div class="text-gray-400">Return to sign in:</div>
                <div class="flex items-center justify-center gap-3 text-xs">
                    <a href="{{ route('developer.login') }}" class="font-semibold text-emerald-400 hover:text-emerald-300 transition cursor-pointer" data-tooltip="Return to Developer Login">
                        Developer Portal
                    </a>
                    <span class="text-gray-700">&bull;</span>
                    <a href="{{ route('agency.login') }}" class="font-semibold text-teal-400 hover:text-teal-300 transition cursor-pointer" data-tooltip="Return to Agency Login">
                        Agency Hub
                    </a>
                    <span class="text-gray-700">&bull;</span>
                    <a href="{{ route('super-admin.login') }}" class="font-semibold text-amber-400 hover:text-amber-300 transition cursor-pointer" data-tooltip="Return to Super Admin Login">
                        Super Admin
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-[11px] text-gray-600">
            &copy; {{ date('Y') }} DevFolio AI Platform. All rights reserved.
        </div>
    </div>
</div>
