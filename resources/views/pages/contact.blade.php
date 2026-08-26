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
    $slug = $this->profile?->slug ?? request()->route('slug') ?? request('slug');
    $slugParam = $slug ? ['slug' => $slug] : [];
    $hasSlugRoute = \Illuminate\Support\Facades\Route::has('tenant.home');
    $homeRoute = $hasSlugRoute && $slug ? route('tenant.home', $slugParam) : (Route::has('home') ? route('home') : url('/'));
    $aboutRoute = $hasSlugRoute && $slug ? route('about', $slugParam) : (Route::has('about') && !config('saas.mode') ? route('about') : '#about');
    $projectsRoute = $hasSlugRoute && $slug ? route('projects', $slugParam) : (Route::has('projects') && !config('saas.mode') ? route('projects') : '#projects');
    $skillsRoute = $hasSlugRoute && $slug ? route('skills', $slugParam) : (Route::has('skills') && !config('saas.mode') ? route('skills') : '#skills');
    $certsRoute = $hasSlugRoute && $slug ? route('certificates', $slugParam) : (Route::has('certificates') && !config('saas.mode') ? route('certificates') : '#certificates');
    $contactRoute = $hasSlugRoute && $slug ? route('contact', $slugParam) : (Route::has('contact') && !config('saas.mode') ? route('contact') : '#contact');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-16 space-y-8 sm:space-y-12 w-full overflow-x-hidden font-body">
    {{-- Header with navigation pill bar --}}
    <div class="p-6 sm:p-10 rounded-3xl border relative overflow-hidden backdrop-blur-xl transition-all shadow-xl"
         style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
        <div class="space-y-3">
            <h1 class="font-h1" style="color: var(--color-text);">
                Contact {{ $this->profile?->full_name }}
            </h1>
            <p class="font-hero-sub font-semibold" style="color: var(--color-primary);">
                Send a direct project inquiry or connect across professional channels.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="border-color: var(--color-border);">
            <a href="{{ $homeRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Overview</a>
            <a href="{{ $aboutRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">About</a>
            <a href="{{ $projectsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Projects</a>
            <a href="{{ $skillsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Skills</a>
            <a href="{{ $certsRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-medium hover:opacity-80 transition shrink-0 inline-flex items-center justify-center" style="color: var(--color-text); background: var(--color-background);">Certificates</a>
            <a href="{{ $contactRoute }}" class="min-h-[44px] px-5 rounded-xl font-nav font-bold transition shadow-sm shrink-0 inline-flex items-center justify-center" style="background: var(--color-primary); color: #000000;">Contact</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">
        {{-- Contact Form Column --}}
        <div class="md:col-span-7 space-y-4">
            @if($this->rateLimited)
                <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 font-body font-semibold">
                    Too many messages sent. Please wait a few minutes before trying again.
                </div>
            @elseif($this->sent)
                <div class="p-8 rounded-3xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-body font-semibold text-center space-y-3">
                    <span class="text-4xl">✨</span>
                    <h2 class="font-h3 text-emerald-400">Message Delivered!</h2>
                    <p class="font-body text-emerald-300/90">Thank you! Your message has been sent directly to the developer.</p>
                </div>
            @else
                <form wire:submit="sendMessage" class="p-6 sm:p-8 rounded-3xl border space-y-5 shadow-lg"
                      style="background-color: var(--color-surface); border-color: var(--color-border);">
                    <div>
                        <label class="block font-caption font-bold mb-2" style="color: var(--color-text);">Your Name *</label>
                        <input
                            type="text"
                            wire:model="senderName"
                            required
                            placeholder="Alex Morgan"
                            class="w-full min-h-[48px] rounded-2xl border px-4 font-body text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border);"
                        />
                    </div>

                    <div>
                        <label class="block font-caption font-bold mb-2" style="color: var(--color-text);">Your Email *</label>
                        <input
                            type="email"
                            wire:model="senderEmail"
                            required
                            placeholder="alex@company.com"
                            class="w-full min-h-[48px] rounded-2xl border px-4 font-body text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border);"
                        />
                    </div>

                    <div>
                        <label class="block font-caption font-bold mb-2" style="color: var(--color-text);">Message *</label>
                        <textarea
                            wire:model="senderMessage"
                            rows="5"
                            required
                            placeholder="Hello, I came across your portfolio and would love to connect regarding..."
                            class="w-full rounded-2xl border p-4 font-body text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                            style="background-color: var(--color-background); border-color: var(--color-border);"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="btn-primary w-full sm:w-auto min-h-[48px] px-8 rounded-2xl shadow-md transition hover:scale-105 cursor-pointer inline-flex items-center justify-center gap-2"
                        style="background: var(--color-primary); color: #000000;"
                        data-tooltip="Send message to developer"
                    >
                        <span>Send Message</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            @endif
        </div>

        {{-- Direct Channels Column --}}
        <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl border space-y-6 h-fit shadow-lg"
             style="background: var(--color-surface); border-color: var(--color-border); color: var(--color-text);">
            <h2 class="font-h3" style="color: var(--color-text);">Direct Channels</h2>

            <div class="space-y-4 font-body">
                @if ($this->profile?->email)
                    <div class="p-4 rounded-2xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="font-caption uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Email</p>
                        <a href="mailto:{{ $this->profile->email }}" class="font-semibold underline hover:opacity-80 break-all mt-0.5 inline-block" style="color: var(--color-primary);">
                            {{ $this->profile->email }}
                        </a>
                    </div>
                @endif

                @if ($this->profile?->phone)
                    <div class="p-4 rounded-2xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="font-caption uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Phone</p>
                        <p class="font-semibold mt-0.5" style="color: var(--color-text);">{{ $this->profile->phone }}</p>
                    </div>
                @endif

                @if ($this->profile?->location)
                    <div class="p-4 rounded-2xl border" style="background: var(--color-background); border-color: var(--color-border);">
                        <p class="font-caption uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Location</p>
                        <p class="font-semibold mt-0.5" style="color: var(--color-text);">{{ $this->profile->location }}</p>
                    </div>
                @endif
            </div>

            @if ($this->profile?->social_links)
                <div class="pt-6 border-t space-y-3" style="border-color: var(--color-border);">
                    <p class="font-caption uppercase font-bold tracking-wider opacity-60" style="color: var(--color-text-muted);">Social Links</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->profile->social_links as $platform => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="min-h-[44px] px-4 rounded-xl font-caption font-bold border hover:opacity-80 transition inline-flex items-center justify-center"
                               style="background: var(--color-background); border-color: var(--color-border); color: var(--color-primary);">
                                <span>{{ ucfirst($platform) }}</span>
                                <span class="ml-1">&nearr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
