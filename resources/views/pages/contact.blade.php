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

<div class="max-w-3xl mx-auto px-6 py-16 space-y-8">
    <div class="space-y-2">
        <h1 class="text-3xl font-bold">Contact {{ $this->profile?->full_name }}</h1>
        <p class="text-sm opacity-75">Send a direct inquiry or reach out through social platforms.</p>
    </div>

    @if($this->rateLimited)
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-sm font-semibold">
            Too many messages sent. Please wait a few minutes before trying again.
        </div>
    @elseif($this->sent)
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
            Thank you! Your message has been sent successfully.
        </div>
    @else
        <form wire:submit="sendMessage" class="space-y-4 p-6 rounded-2xl border" style="background-color: var(--color-surface); border-color: var(--color-border);">
            <div>
                <label class="block text-xs font-semibold mb-1" style="color: var(--color-text);">Your Name *</label>
                <input
                    type="text"
                    wire:model="senderName"
                    required
                    placeholder="Jordan Lee"
                    class="w-full rounded-xl border p-2.5 text-sm"
                    style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1" style="color: var(--color-text);">Your Email *</label>
                <input
                    type="email"
                    wire:model="senderEmail"
                    required
                    placeholder="jordan@example.com"
                    class="w-full rounded-xl border p-2.5 text-sm"
                    style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1" style="color: var(--color-text);">Message *</label>
                <textarea
                    wire:model="senderMessage"
                    rows="4"
                    required
                    placeholder="Hello, I would love to collaborate on..."
                    class="w-full rounded-xl border p-2.5 text-sm"
                    style="background-color: var(--color-background); border-color: var(--color-border); color: var(--color-text);"
                ></textarea>
            </div>

            <button
                type="submit"
                class="py-2.5 px-6 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md transition cursor-pointer"
                data-tooltip="Send message to developer"
            >
                Send Message &rarr;
            </button>
        </form>
    @endif

    <div class="pt-6 border-t space-y-2 text-sm" style="border-color: var(--color-border);">
        @if ($this->profile?->email)
            <p>Direct Email: <a href="mailto:{{ $this->profile->email }}" class="underline font-semibold" style="color: var(--color-primary);" data-tooltip="Compose email to {{ $this->profile->email }}">{{ $this->profile->email }}</a></p>
        @endif
        @if ($this->profile?->phone)
            <p>Phone: {{ $this->profile->phone }}</p>
        @endif
        @foreach ($this->profile?->social_links ?? [] as $platform => $url)
            <p>{{ ucfirst($platform) }}: <a href="{{ $url }}" class="underline font-semibold" target="_blank" rel="noopener" style="color: var(--color-primary);" data-tooltip="Visit external {{ ucfirst($platform) }} profile">{{ $url }}</a></p>
        @endforeach
    </div>

    <a href="{{ route('home') }}" class="underline mt-8 inline-block text-xs cursor-pointer" data-tooltip="Return to developer portfolio home">&larr; Back to Portfolio</a>
</div>
