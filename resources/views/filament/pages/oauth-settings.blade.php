<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Top Intro Banner --}}
        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-950/60 via-gray-900 to-gray-950 border border-emerald-500/20 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                        ⚡ Social Login Management
                    </div>
                    <h2 class="text-xl font-extrabold text-white tracking-tight">OAuth 2.0 Provider Credentials</h2>
                    <p class="text-xs sm:text-sm text-gray-400 max-w-2xl leading-relaxed">
                        Configure free single-click sign-in with GitHub and Google. Credentials entered here are encrypted and saved directly to the database for instant authentication.
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                {{-- 1. GitHub OAuth Configuration Card --}}
                <div class="p-6 rounded-3xl bg-gray-950 border border-gray-800 shadow-xl space-y-6 flex flex-col justify-between">
                    <div class="space-y-5">
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-gray-900 border border-gray-700/80 flex items-center justify-center text-white text-xl shadow-inner">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-white">GitHub OAuth</h3>
                                    <p class="text-xs text-gray-400">One-click sign-in for developers</p>
                                </div>
                            </div>

                            @if($this->isGithubConfigured())
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-bold">
                                    Not Configured
                                </span>
                            @endif
                        </div>

                        {{-- Step-by-Step Directions Guide --}}
                        <div class="p-4 rounded-2xl bg-gray-900/90 border border-gray-800 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                                <span>📖</span> Setup Instructions
                            </h4>
                            <ol class="text-xs text-gray-300 space-y-2 list-decimal list-inside leading-relaxed">
                                <li>
                                    Go to <a href="https://github.com/settings/developers" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-semibold underline">GitHub Developer Settings &rarr; OAuth Apps</a> and click <strong>New OAuth App</strong>.
                                </li>
                                <li>
                                    Set <strong>Application name</strong> to <code class="text-emerald-300 font-semibold">DevFolio.AI</code>.
                                </li>
                                <li class="space-y-1">
                                    <span>Set <strong>Homepage URL</strong> to:</span>
                                    <div class="flex items-center gap-2 mt-0.5" x-data="{ copied: false }">
                                        <input type="text" readonly value="{{ $this->getAppUrl() }}" class="text-[11px] font-mono py-1 px-2.5 rounded-lg bg-gray-950 border border-gray-800 text-gray-300 w-full select-all">
                                        <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-[11px] font-bold text-gray-200 transition">
                                            <span x-show="!copied">Copy</span>
                                            <span x-show="copied" class="text-emerald-400">✓</span>
                                        </button>
                                    </div>
                                </li>
                                <li class="space-y-1">
                                    <span>Set <strong>Authorization callback URL</strong> to:</span>
                                    <div class="flex items-center gap-2 mt-0.5" x-data="{ copied: false }">
                                        <input type="text" readonly value="{{ $this->getGithubCallbackUrl() }}" class="text-[11px] font-mono py-1 px-2.5 rounded-lg bg-gray-950 border border-gray-800 text-emerald-300 w-full select-all">
                                        <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGithubCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-[11px] font-bold text-gray-200 transition">
                                            <span x-show="!copied">Copy</span>
                                            <span x-show="copied" class="text-emerald-400">✓</span>
                                        </button>
                                    </div>
                                </li>
                                <li>
                                    Click <strong>Register application</strong>, generate a <strong>Client Secret</strong>, and paste them below:
                                </li>
                            </ol>
                        </div>

                        {{-- Input Fields --}}
                        <div class="space-y-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-gray-300 mb-1.5">GitHub Client ID</label>
                                <input
                                    type="text"
                                    wire:model="github_client_id"
                                    placeholder="e.g. Iv1.8a2b3c4d5e6f7g8h"
                                    class="w-full px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-sm text-gray-100 placeholder-gray-600 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-mono"
                                />
                            </div>

                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-gray-300 mb-1.5">GitHub Client Secret</label>
                                <div class="relative">
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="github_client_secret"
                                        placeholder="Paste your generated GitHub Client Secret"
                                        class="w-full px-3.5 py-2 pr-10 rounded-xl bg-gray-900 border border-gray-800 text-sm text-gray-100 placeholder-gray-600 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-mono"
                                    />
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-200 text-xs">
                                        <span x-show="!show">👁️</span>
                                        <span x-show="show">🙈</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="text-xs font-semibold text-gray-400">Enable GitHub Sign-In</label>
                                <input type="checkbox" wire:model="github_is_enabled" class="rounded border-gray-700 bg-gray-800 text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Google OAuth Configuration Card --}}
                <div class="p-6 rounded-3xl bg-gray-950 border border-gray-800 shadow-xl space-y-6 flex flex-col justify-between">
                    <div class="space-y-5">
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-gray-900 border border-gray-700/80 flex items-center justify-center text-white text-xl shadow-inner">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                                        <path fill="#EA4335" d="M12 5c1.7 0 3 .7 3.7 1.3l2.8-2.8C16.8 1.9 14.6 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.4 2.6C6.2 6.9 8.8 5 12 5z"/>
                                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                                        <path fill="#FBBC05" d="M5.3 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.6C.7 9.9 0 12.4 0 15s.7 5.1 1.9 7.4l3.4-2.6z"/>
                                        <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.2 0-5.8-2-6.7-4.9L1.9 16c1.8 3.7 5.6 6.3 10.1 6.3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-white">Google Identity (OAuth 2.0)</h3>
                                    <p class="text-xs text-gray-400">One-click sign-in with Google Accounts</p>
                                </div>
                            </div>

                            @if($this->isGoogleConfigured())
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-bold">
                                    Not Configured
                                </span>
                            @endif
                        </div>

                        {{-- Step-by-Step Directions Guide --}}
                        <div class="p-4 rounded-2xl bg-gray-900/90 border border-gray-800 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                                <span>📖</span> Setup Instructions
                            </h4>
                            <ol class="text-xs text-gray-300 space-y-2 list-decimal list-inside leading-relaxed">
                                <li>
                                    Open <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-amber-400 hover:text-amber-300 font-semibold underline">Google Cloud Console &rarr; Credentials</a>.
                                </li>
                                <li>
                                    Click <strong>Create Credentials</strong> &rarr; <strong>OAuth client ID</strong> (Application type: <strong>Web application</strong>).
                                </li>
                                <li class="space-y-1">
                                    <span>Add to <strong>Authorized JavaScript origins</strong>:</span>
                                    <div class="flex items-center gap-2 mt-0.5" x-data="{ copied: false }">
                                        <input type="text" readonly value="{{ $this->getAppUrl() }}" class="text-[11px] font-mono py-1 px-2.5 rounded-lg bg-gray-950 border border-gray-800 text-gray-300 w-full select-all">
                                        <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-[11px] font-bold text-gray-200 transition">
                                            <span x-show="!copied">Copy</span>
                                            <span x-show="copied" class="text-emerald-400">✓</span>
                                        </button>
                                    </div>
                                </li>
                                <li class="space-y-1">
                                    <span>Add to <strong>Authorized redirect URIs</strong>:</span>
                                    <div class="flex items-center gap-2 mt-0.5" x-data="{ copied: false }">
                                        <input type="text" readonly value="{{ $this->getGoogleCallbackUrl() }}" class="text-[11px] font-mono py-1 px-2.5 rounded-lg bg-gray-950 border border-gray-800 text-amber-300 w-full select-all">
                                        <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGoogleCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-[11px] font-bold text-gray-200 transition">
                                            <span x-show="!copied">Copy</span>
                                            <span x-show="copied" class="text-emerald-400">✓</span>
                                        </button>
                                    </div>
                                </li>
                                <li>
                                    Click <strong>Create</strong>, copy the generated <strong>Client ID</strong> and <strong>Client Secret</strong>, and paste them below:
                                </li>
                            </ol>
                        </div>

                        {{-- Input Fields --}}
                        <div class="space-y-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-gray-300 mb-1.5">Google Client ID</label>
                                <input
                                    type="text"
                                    wire:model="google_client_id"
                                    placeholder="e.g. 123456789-abcdefg.apps.googleusercontent.com"
                                    class="w-full px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-sm text-gray-100 placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                />
                            </div>

                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-gray-300 mb-1.5">Google Client Secret</label>
                                <div class="relative">
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        wire:model="google_client_secret"
                                        placeholder="Paste your generated Google Client Secret"
                                        class="w-full px-3.5 py-2 pr-10 rounded-xl bg-gray-900 border border-gray-800 text-sm text-gray-100 placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-200 text-xs">
                                        <span x-show="!show">👁️</span>
                                        <span x-show="show">🙈</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="text-xs font-semibold text-gray-400">Enable Google Sign-In</label>
                                <input type="checkbox" wire:model="google_is_enabled" class="rounded border-gray-700 bg-gray-800 text-amber-500 focus:ring-amber-500 w-4 h-4">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                <button
                    type="submit"
                    class="py-3 px-8 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-gray-950 font-extrabold text-sm shadow-lg shadow-emerald-500/25 transition transform hover:-translate-y-0.5 flex items-center gap-2"
                >
                    <span wire:loading.remove>💾 Save OAuth Settings to Database</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-gray-950" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
