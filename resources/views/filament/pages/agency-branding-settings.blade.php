<x-filament-panels::page>
    <div class="space-y-6 max-w-3xl">
        {{-- Plan Gating Notice if not Agency Plan --}}
        @if(! $this->canUseWhiteLabel())
            <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center font-bold text-xl">
                        🔒
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Agency White-Labeling</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            White-label branding and removing the "Powered by DevFolio" badge is exclusive to the Agency plan.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('filament.admin.pages.billing-settings', ['tenant' => $this->getAccount() ?? 1]) }}"
                    class="py-2.5 px-5 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 shadow-md shadow-amber-500/20 transition whitespace-nowrap"
                >
                    Upgrade to Agency &rarr;
                </a>
            </div>
        @endif

        {{-- White-Label Form --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">White-Label Customization</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Replace platform branding with your agency or coaching organization details.
            </p>

            <form wire:submit="saveBranding" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Agency / Brand Name
                    </label>
                    <input
                        type="text"
                        wire:model="custom_brand_name"
                        placeholder="e.g. Apex Talent Bootcamp"
                        {{ ! $this->canUseWhiteLabel() ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white disabled:opacity-50"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Custom Logo Image URL
                    </label>
                    <input
                        type="url"
                        wire:model="custom_logo_path"
                        placeholder="https://example.com/logo.png"
                        {{ ! $this->canUseWhiteLabel() ? 'disabled' : '' }}
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm p-2.5 text-gray-900 dark:text-white disabled:opacity-50"
                    />
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="hide_platform_branding"
                            {{ ! $this->canUseWhiteLabel() ? 'disabled' : '' }}
                            class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500 border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 disabled:opacity-50"
                        />
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white block">
                                Hide "Powered by DevFolio" Badge
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 block">
                                Removes all platform branding and back-links from your public client portfolios.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex justify-end">
                    <button
                        type="submit"
                        {{ ! $this->canUseWhiteLabel() ? 'disabled' : '' }}
                        class="py-2.5 px-6 rounded-xl text-xs font-bold text-gray-950 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 shadow-md transition"
                    >
                        Save Branding Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
