<x-filament-panels::page>
    <style>
        .oauth-icon-badge {
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }
        .oauth-svg-icon {
            width: 20px !important;
            height: 20px !important;
            max-width: 20px !important;
            max-height: 20px !important;
            display: block;
        }
        .oauth-guide-box {
            border-radius: 0.75rem;
            padding: 1rem;
            font-size: 0.8125rem;
            line-height: 1.5;
        }
        .oauth-input {
            width: 100%;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
    </style>

    <form wire:submit="save" class="space-y-6">
        {{-- Intro Section --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        ⚡ Social Login Management
                    </span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    OAuth 2.0 Provider Credentials
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-2xl">
                    Configure one-click login for GitHub and Google. Credentials entered here are encrypted and stored directly in your database.
                </p>
            </div>
            <div>
                <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                    Save OAuth Settings
                </x-filament::button>
            </div>
        </div>

        {{-- 2-Column Grid: GitHub & Google --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            {{-- 1. GitHub OAuth Card --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
                {{-- Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="oauth-icon-badge bg-gray-900 text-white dark:bg-gray-800 border border-gray-700">
                            <svg class="oauth-svg-icon fill-current" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">GitHub OAuth</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">One-click sign-in for developers</p>
                        </div>
                    </div>

                    @if($this->isGithubConfigured())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            ● Connected
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Not Configured
                        </span>
                    @endif
                </div>

                {{-- Setup Instructions Box --}}
                <div class="oauth-guide-box bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 space-y-2.5">
                    <div class="font-bold text-xs uppercase tracking-wider text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                        <span>📖</span> Setup Instructions
                    </div>
                    <ol class="text-xs text-gray-700 dark:text-gray-300 space-y-2 list-decimal list-inside">
                        <li>
                            Go to <a href="https://github.com/settings/developers" target="_blank" class="text-emerald-600 dark:text-emerald-400 font-semibold underline hover:opacity-80">GitHub Developer Settings &rarr; OAuth Apps</a> and click <strong>New OAuth App</strong>.
                        </li>
                        <li>
                            Application name: <code class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-900 text-emerald-700 dark:text-emerald-300 font-mono text-[11px]">DevFolio.AI</code>
                        </li>
                        <li class="space-y-1">
                            <span>Homepage URL:</span>
                            <div class="flex items-center gap-1.5 mt-0.5" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getAppUrl() }}" class="text-xs font-mono py-1 px-2 rounded bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 w-full select-all">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2.5 rounded bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓</span>
                                </button>
                            </div>
                        </li>
                        <li class="space-y-1">
                            <span>Authorization callback URL:</span>
                            <div class="flex items-center gap-1.5 mt-0.5" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getGithubCallbackUrl() }}" class="text-xs font-mono py-1 px-2 rounded bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-emerald-700 dark:text-emerald-400 font-bold w-full select-all">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGithubCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2.5 rounded bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            Click <strong>Register application</strong>, generate a <strong>Client Secret</strong>, and paste them below:
                        </li>
                    </ol>
                </div>

                {{-- Form Fields --}}
                <div class="space-y-3.5 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">GitHub Client ID</label>
                        <input
                            type="text"
                            wire:model="github_client_id"
                            placeholder="e.g. Iv1.8a2b3c4d5e6f7g8h"
                            class="oauth-input border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">GitHub Client Secret</label>
                        <div class="relative">
                            <input
                                :type="show ? 'text' : 'password'"
                                wire:model="github_client_secret"
                                placeholder="Paste your generated Client Secret"
                                class="oauth-input pr-10 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-200 text-xs">
                                <span x-show="!show">👁️</span>
                                <span x-show="show">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Enable GitHub Sign-In</label>
                        <input type="checkbox" wire:model="github_is_enabled" class="rounded border-gray-300 dark:border-gray-700 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                    </div>
                </div>
            </div>

            {{-- 2. Google OAuth Card --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
                {{-- Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="oauth-icon-badge bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <svg class="oauth-svg-icon" viewBox="0 0 24 24">
                                <path fill="#EA4335" d="M12 5c1.7 0 3 .7 3.7 1.3l2.8-2.8C16.8 1.9 14.6 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.4 2.6C6.2 6.9 8.8 5 12 5z"/>
                                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                                <path fill="#FBBC05" d="M5.3 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.6C.7 9.9 0 12.4 0 15s.7 5.1 1.9 7.4l3.4-2.6z"/>
                                <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.2 0-5.8-2-6.7-4.9L1.9 16c1.8 3.7 5.6 6.3 10.1 6.3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Google Identity (OAuth 2.0)</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">One-click sign-in with Google Accounts</p>
                        </div>
                    </div>

                    @if($this->isGoogleConfigured())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            ● Connected
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Not Configured
                        </span>
                    @endif
                </div>

                {{-- Setup Instructions Box --}}
                <div class="oauth-guide-box bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 space-y-2.5">
                    <div class="font-bold text-xs uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                        <span>📖</span> Setup Instructions
                    </div>
                    <ol class="text-xs text-gray-700 dark:text-gray-300 space-y-2 list-decimal list-inside">
                        <li>
                            Open <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-amber-600 dark:text-amber-400 font-semibold underline hover:opacity-80">Google Cloud Console &rarr; Credentials</a>.
                        </li>
                        <li>
                            Click <strong>Create Credentials</strong> &rarr; <strong>OAuth client ID</strong> (Application type: <strong>Web application</strong>).
                        </li>
                        <li class="space-y-1">
                            <span>Authorized JavaScript origins:</span>
                            <div class="flex items-center gap-1.5 mt-0.5" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getAppUrl() }}" class="text-xs font-mono py-1 px-2 rounded bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 w-full select-all">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2.5 rounded bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓</span>
                                </button>
                            </div>
                        </li>
                        <li class="space-y-1">
                            <span>Authorized redirect URIs:</span>
                            <div class="flex items-center gap-1.5 mt-0.5" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getGoogleCallbackUrl() }}" class="text-xs font-mono py-1 px-2 rounded bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-amber-700 dark:text-amber-400 font-bold w-full select-all">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGoogleCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="py-1 px-2.5 rounded bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" class="text-emerald-600 dark:text-emerald-400">✓</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            Click <strong>Create</strong>, copy the generated <strong>Client ID</strong> and <strong>Client Secret</strong>, and paste below:
                        </li>
                    </ol>
                </div>

                {{-- Form Fields --}}
                <div class="space-y-3.5 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Client ID</label>
                        <input
                            type="text"
                            wire:model="google_client_id"
                            placeholder="e.g. 123456789-abcdefg.apps.googleusercontent.com"
                            class="oauth-input border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        />
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Client Secret</label>
                        <div class="relative">
                            <input
                                :type="show ? 'text' : 'password'"
                                wire:model="google_client_secret"
                                placeholder="Paste your generated Client Secret"
                                class="oauth-input pr-10 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-200 text-xs">
                                <span x-show="!show">👁️</span>
                                <span x-show="show">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Enable Google Sign-In</label>
                        <input type="checkbox" wire:model="google_is_enabled" class="rounded border-gray-300 dark:border-gray-700 text-amber-600 focus:ring-amber-500 w-4 h-4">
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Action Button --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                Save OAuth Settings to Database
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
