<x-filament-panels::page>
    @php
        $stats = $this->getUsageStats();
        $plan = $this->getPlanDetails();
        $account = $this->getAccount();
        $plans = config('plans', []);
    @endphp

    <div class="space-y-6">
        {{-- Current Subscription & Usage Header Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Current Plan Card --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Current Plan</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $plan['name'] ?? ucfirst($account?->plan_slug ?? 'Free') }}
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $plan['name'] ?? 'Free Tier' }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @if(($account?->plan_slug ?? 'free') === 'free')
                            Starter tier for building your developer portfolio.
                        @elseif($account?->plan_slug === 'pro')
                            Pro Developer tier with unlimited AI & custom domain.
                        @else
                            Agency tier with multi-profile and team tools.
                        @endif
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $account?->stripe_id ? 'Active Stripe Customer' : 'Self-serve Account' }}
                    </span>
                    @if($account?->stripe_id)
                        <button
                            type="button"
                            wire:click="redirectToPortal"
                            class="inline-flex items-center text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline"
                        >
                            Stripe Portal &rarr;
                        </button>
                    @endif
                </div>
            </div>

            {{-- AI Usage Meter Card --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm md:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Monthly AI Generation Meter</span>
                        @if($stats['is_byok'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                BYOK Active (Unlimited)
                            </span>
                        @elseif($stats['is_unlimited'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                Unlimited Quota
                            </span>
                        @else
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $stats['used'] }} / {{ $stats['limit'] }} Used
                            </span>
                        @endif
                    </div>

                    <div class="flex items-baseline justify-between mt-2">
                        <div class="text-3xl font-extrabold text-gray-900 dark:text-white">
                            @if($stats['is_unlimited'])
                                &infin; <span class="text-sm font-normal text-gray-500 dark:text-gray-400">generations available</span>
                            @else
                                {{ $stats['remaining'] }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">left this month</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $stats['used'] }} created this period
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    @if(! $stats['is_unlimited'])
                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3 mt-4 overflow-hidden">
                            <div
                                class="h-3 rounded-full transition-all duration-500 {{ $stats['percentage'] >= 100 ? 'bg-rose-500' : ($stats['percentage'] >= 66 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                style="width: {{ $stats['percentage'] }}%"
                            ></div>
                        </div>
                    @else
                        <div class="w-full bg-emerald-100 dark:bg-emerald-950/40 rounded-full h-3 mt-4 overflow-hidden">
                            <div class="h-3 rounded-full bg-emerald-500 w-full"></div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>
                        @if($stats['is_byok'])
                            Using your custom AI API key configured in AI Settings.
                        @elseif(! $stats['is_unlimited'])
                            Resets at the start of each billing period.
                        @else
                            Pro subscription unlocks continuous AI resume revisions.
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Available Plan Tiers --}}
        <div>
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Available Plans</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Free Plan --}}
                <div class="rounded-2xl p-6 bg-white dark:bg-gray-900 border {{ ($account?->plan_slug ?? 'free') === 'free' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h5 class="text-xl font-bold text-gray-900 dark:text-white">Free</h5>
                            @if(($account?->plan_slug ?? 'free') === 'free')
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Active</span>
                            @endif
                        </div>
                        <div class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">$0 <span class="text-sm font-normal text-gray-500">/ forever</span></div>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2">&check; 1 Portfolio Profile</li>
                            <li class="flex items-center gap-2">&check; 3 AI Resume generations/mo</li>
                            <li class="flex items-center gap-2">&check; GitHub auto-sync</li>
                            <li class="flex items-center gap-2">&check; Core portfolio themes</li>
                            <li class="flex items-center gap-2 text-gray-400">&cross; Custom domain</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        @if(($account?->plan_slug ?? 'free') === 'free')
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                                Current Plan
                            </button>
                        @else
                            <button
                                wire:click="upgradeToPlan('free')"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
                            >
                                Downgrade to Free
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Pro Plan --}}
                <div class="rounded-2xl p-6 bg-white dark:bg-gray-900 border {{ $account?->plan_slug === 'pro' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-gradient-to-l from-amber-500 to-amber-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-bl-xl">
                        Popular
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <h5 class="text-xl font-bold text-gray-900 dark:text-white">Pro Developer</h5>
                            @if($account?->plan_slug === 'pro')
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Active</span>
                            @endif
                        </div>
                        <div class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">$12 <span class="text-sm font-normal text-gray-500">/ month</span></div>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2">&check; 1 Portfolio Profile</li>
                            <li class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-semibold">&check; Unlimited AI Resumes</li>
                            <li class="flex items-center gap-2">&check; Custom Domain support</li>
                            <li class="flex items-center gap-2">&check; Remove platform branding</li>
                            <li class="flex items-center gap-2">&check; All premium themes</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        @if($account?->plan_slug === 'pro')
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                                Current Plan
                            </button>
                        @else
                            <button
                                wire:click="upgradeToPlan('pro')"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-md shadow-amber-500/20 transition"
                            >
                                Upgrade to Pro
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Agency Plan --}}
                <div class="rounded-2xl p-6 bg-white dark:bg-gray-900 border {{ $account?->plan_slug === 'agency' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h5 class="text-xl font-bold text-gray-900 dark:text-white">Agency / Team</h5>
                            @if($account?->plan_slug === 'agency')
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Active</span>
                            @endif
                        </div>
                        <div class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">$49 <span class="text-sm font-normal text-gray-500">/ month</span></div>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2 font-semibold text-amber-600 dark:text-amber-400">&check; Unlimited Profiles</li>
                            <li class="flex items-center gap-2">&check; Unlimited AI Resumes</li>
                            <li class="flex items-center gap-2">&check; White-label client portals</li>
                            <li class="flex items-center gap-2">&check; Multiple custom domains</li>
                            <li class="flex items-center gap-2">&check; Priority support & SLA</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        @if($account?->plan_slug === 'agency')
                            <button disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                                Current Plan
                            </button>
                        @else
                            <button
                                wire:click="upgradeToPlan('agency')"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-gray-900 dark:text-white bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
                            >
                                Upgrade to Agency
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
