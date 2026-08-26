<?php

use App\Services\CurrentProfileResolver;
use Illuminate\Support\Facades\RateLimiter;
use function Livewire\Volt\{computed, state};

$profile = computed(fn () => app(CurrentProfileResolver::class)->resolve());

state([
    'senderName' => '',
    'senderEmail' => '',
    'senderMessage' => '',
    'sent' => false,
    'rateLimited' => false,
]);

$sendMessage = function () {
    $ip = request()->ip() ?: '127.0.0.1';
    $key = 'contact-form:' . $ip;

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $this->rateLimited = true;
        return;
    }

    $this->validate([
        'senderName' => 'required|string|max:255',
        'senderEmail' => 'required|email|max:255',
        'senderMessage' => 'required|string|max:2000',
    ]);

    RateLimiter::hit($key, 600); // 10 minutes

    $this->sent = true;
    $this->senderName = '';
    $this->senderEmail = '';
    $this->senderMessage = '';
};

?>

@php
    $slugParam = config('saas.mode') && $this->profile ? ['slug' => $this->profile->slug] : [];
    $homeRoute = config('saas.mode') && $this->profile ? route('tenant.home', $slugParam) : route('home');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 w-full overflow-x-hidden">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight" style="color: var(--color-text);">
                Contact {{ $this->profile?->full_name }}
            </h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--color-primary);">
                Send a direct project inquiry or connect across professional channels.
            </p>
        </div>

        <div class="mt-6 pt-4 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ route('about', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ route('projects', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ route('skills', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ route('certificates', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-medium hover:opacity-80 transition shrink-0" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ route('contact', $slugParam) }}" class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm shrink-0" style="background: var(--color-primary); color: #000000;">Contact</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8">
        {{-- Contact Form Column --}}
        <div class="md:col-span-7 space-y-4">
            @if($this->rateLimited)
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs sm:text-sm font-semibold">
                    Too many messages sent. Please wait a few minutes before trying again.
                </div>
            @elseif($this->sent)
                <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold text-center space-y-2">
                    <span class="text-3xl">✨</span>
                    <p>Thank you! Your message has been sent successfully.</p>
                </div>
            @else
                <form wire:submit="sendMessage" class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border space-y-4 shadow-lg"
                      style="background-color: var(--color-surface); border-color: var(--color-border);">
                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color: var(--color-text);">Your Name *</label>
                        <input
                            type="text"
                            wire:model="senderName"
                            required
                            placeholder="Alex Morgan"
                            class="w-full rounded-xl border p-3 text-xs sm:text-sm focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color: var(--color-text);">Your Email *</label>
                        <input
                            type="email"
                            wire:model="senderEmail"
                            required
                            placeholder="alex@company.com"
                            class="w-full rounded-xl border p-3 text-xs sm:text-sm focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5" style="color: var(--color-text);">Message *</label>
                        <textarea
                            wire:model="senderMessage"
                            rows="4"
                            required
                            placeholder="Hello, I came across your portfolio and would love to connect regarding..."
                            class="w-full rounded-xl border p-3 text-xs sm:text-sm focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full sm:w-auto py-3 px-6 rounded-xl text-xs sm:text-sm font-bold shadow-md transition hover:scale-105 cursor-pointer"
                        style="background: var(--color-primary); color: #000000;"
                        data-tooltip="Send message to developer"
                    >
                        Send Message &rarr;
                    </button>
                </form>
            @endif
        </div>

        {{-- Direct Channels Column --}}
        <div class="md:col-span-5 p-6 sm:p-8 rounded-2xl sm:rounded-3xl border space-y-5 h-fit shadow-lg"
             style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
            <h3 class="font-bold text-base sm:text-lg" style="color: var(--color-text);">Direct Channels</h3>

            <div class="space-y-3.5 text-xs sm:text-sm">
                @if ($this->profile?->email)
                    <div class="p-3.5 rounded-xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="text-[10px] uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Email</p>
                        <a href="mailto:{{ $this->profile->email }}" class="font-semibold underline hover:opacity-80 break-all" style="color: var(--color-primary);">
                            {{ $this->profile->email }}
                        </a>
                    </div>
                @endif

                @if ($this->profile?->phone)
                    <div class="p-3.5 rounded-xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="text-[10px] uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Phone</p>
                        <p class="font-semibold" style="color: var(--color-text);">{{ $this->profile->phone }}</p>
                    </div>
                @endif

                @if ($this->profile?->location)
                    <div class="p-3.5 rounded-xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="text-[10px] uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Location</p>
                        <p class="font-semibold" style="color: var(--color-text);">{{ $this->profile->location }}</p>
                    </div>
                @endif
            </div>

            @if ($this->profile?->social_links)
                <div class="pt-4 border-t space-y-2" style="border-color: var(--color-border);">
                    <p class="text-[10px] uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Social Links</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->profile->social_links as $platform => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold border hover:opacity-80 transition"
                               style="background: var(--color-background); border-color: var(--color-border); color: var(--color-primary);">
                                {{ ucfirst($platform) }} &nearr;
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
