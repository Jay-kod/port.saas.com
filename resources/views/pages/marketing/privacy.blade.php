<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('layouts.app');
title('Privacy Policy — DevFolio');

?>

<div class="min-h-screen bg-slate-950 text-slate-100 py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-12">
        <div class="space-y-4 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-bold text-amber-500 hover:text-amber-400">
                &larr; Back to DevFolio Home
            </a>
            <h1 class="text-4xl font-extrabold tracking-tight">Privacy Policy</h1>
            <p class="text-sm text-slate-400">Effective date: August 2026</p>
        </div>

        <div class="prose prose-invert max-w-none space-y-8 text-sm leading-relaxed text-slate-300">
            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">1. Information We Collect</h2>
                <p>
                    We collect account profile details (name, email), portfolio data you provide (work history, skills, portfolio projects), and usage telemetry necessary for rate limiting and billing metering.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">2. AI Processing & Data Privacy</h2>
                <p>
                    When using AI features (Resume Tailoring, Cover Letter Generation, Resume Parsing), text submitted is processed strictly to generate your requested documents. Your private data is never used to train generalized foundation models without explicit consent.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">3. GDPR Compliance & Data Rights</h2>
                <p>
                    Under GDPR and international privacy frameworks, you have the right to access, export, or permanently delete your personal information at any time directly through the Privacy & Data dashboard.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-white">4. Cookies & Local Storage</h2>
                <p>
                    We use essential cookies and browser local storage strictly for authentication sessions and user-selected interface preferences (such as Light/Dark mode).
                </p>
            </section>
        </div>
    </div>
</div>
