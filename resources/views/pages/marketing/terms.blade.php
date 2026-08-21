<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('layouts.app');
title('Terms of Service — DevFolio');

?>

<div class="min-h-screen bg-slate-950 text-slate-100 py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-12">
        <div class="space-y-4 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-bold text-amber-500 hover:text-amber-400">
                &larr; Back to DevFolio Home
            </a>
            <h1 class="text-4xl font-extrabold tracking-tight">Terms of Service</h1>
            <p class="text-sm text-slate-400">Effective date: August 2026</p>
        </div>

        <div class="prose prose-invert max-w-none space-y-8 text-sm leading-relaxed text-slate-300">
            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">1. Acceptance of Terms</h2>
                <p>
                    By creating an account or accessing the DevFolio platform, you agree to comply with these Terms of Service. If you are using DevFolio on behalf of an organization or educational bootcamp, you agree to these terms on behalf of that entity.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">2. Permitted Use & Content Ownership</h2>
                <p>
                    You retain full intellectual property ownership of all resume text, projects, credentials, and portfolios published or generated through the service. You may not publish content that infringes upon third-party rights, contains malicious code, or promotes unlawful activities.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">3. Subscriptions & Billing</h2>
                <p>
                    Paid subscriptions (Pro and Agency tiers) are billed in advance on a recurring monthly or annual basis. You may cancel your subscription at any time via the customer billing portal.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">4. Termination & Account Deletion</h2>
                <p>
                    You have the right to terminate your account and request complete data deletion at any time through your account privacy settings.
                </p>
            </section>
        </div>
    </div>
</div>
