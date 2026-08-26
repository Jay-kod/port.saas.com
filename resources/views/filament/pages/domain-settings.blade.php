<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Plan Gating Notice if on Free Tier --}}
        @if(! $this->canUseCustomDomains())
            <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center font-bold text-xl">
                        🔒
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Custom Domains are a Pro Feature</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            Upgrade to Pro Developer or Agency tier to connect your personal domain (e.g. <code>resume.yourname.com</code>).
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('filament.admin.pages.billing-settings', ['tenant' => $this->getAccount() ?? 1]) }}"
                    class="py-2.5 px-5 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md shadow-amber-500/20 transition whitespace-nowrap"
                >
                    Upgrade to Pro &rarr;
                </a>
            </div>
        @endif

        {{-- Add Domain Form --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Connect a Custom Domain</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 mb-4">
                Serve your portfolio at your own subdomain or apex domain with automated SSL.
            </p>

            <form wire:submit="addDomain" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 text-sm">
                        https://
                    </div>
                    <input
                        type="text"
                        wire:model="newDomain"
                        placeholder="portfolio.yourname.com"
                        {{ ! $this->canUseCustomDomains() ? 'disabled' : '' }}
                        class="w-full pl-20 pr-4 min-h-[48px] rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-base text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                </div>

                <button
                    type="submit"
                    {{ ! $this->canUseCustomDomains() ? 'disabled' : '' }}
                    class="min-h-[48px] px-8 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-amber-500/20 transition inline-flex items-center justify-center cursor-pointer"
                >
                    Connect Domain
                </button>
            </form>
        </div>

        {{-- Domains List --}}
        <div class="space-y-4">
            <h4 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Connected Domains ({{ count($this->domains) }})</h4>

            @forelse($this->domains as $domain)
                <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $domain->isVerified() ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }} flex items-center justify-center font-bold text-lg">
                                {{ $domain->isVerified() ? '✓' : '⏱' }}
                            </div>
                            <div>
                                <h5 class="text-base font-bold text-gray-900 dark:text-white font-mono flex items-center gap-2">
                                    {{ $domain->domain }}
                                    @if($domain->isVerified())
                                        <a href="https://{{ $domain->domain }}" target="_blank" class="text-xs font-normal text-amber-600 hover:underline font-sans">
                                            Visit Site &nearr;
                                        </a>
                                    @endif
                                </h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Added on {{ $domain->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($domain->isVerified())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    Active & Verified
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="verifyDomain({{ $domain->id }})"
                                    class="min-h-[44px] px-5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-sm transition inline-flex items-center justify-center cursor-pointer"
                                >
                                    Verify DNS Now
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click="removeDomain({{ $domain->id }})"
                                class="min-h-[44px] px-4 rounded-xl text-xs font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition inline-flex items-center justify-center cursor-pointer"
                                title="Disconnect Domain"
                            >
                                Disconnect
                            </button>
                        </div>
                    </div>

                    {{-- DNS Configuration Guide if Pending --}}
                    @if(! $domain->isVerified())
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs space-y-3">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">
                                To complete verification, add the following DNS record to your domain provider:
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 font-mono">
                                <div class="p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-400 block">TYPE</span>
                                    <span class="text-gray-900 dark:text-white font-bold">TXT</span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-400 block">HOST / NAME</span>
                                    <span class="text-gray-900 dark:text-white font-bold">@ or {{ $domain->domain }}</span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-400 block">VALUE / TARGET</span>
                                    <span class="text-amber-600 dark:text-amber-400 font-bold truncate block" title="{{ $domain->verification_token }}">{{ $domain->verification_token }}</span>
                                </div>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Additionally point a <strong>CNAME</strong> record to <code>cname.devfolio.ai</code> or an <strong>A</strong> record to our edge cluster. DNS changes can take up to a few minutes to propagate.
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No custom domains connected yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
