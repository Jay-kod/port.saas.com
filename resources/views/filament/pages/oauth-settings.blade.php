<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">

        {{-- Top Intro Banner --}}
        <x-filament::section>
            <x-slot name="heading">
                OAuth 2.0 Social Logins
            </x-slot>
            <x-slot name="description">
                Configure one-click login for GitHub and Google. Credentials entered here are encrypted and stored directly in your database.
            </x-slot>
            <x-slot name="headerEnd">
                <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                    Save OAuth Settings
                </x-filament::button>
            </x-slot>
        </x-filament::section>

        {{-- 2-Column Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; align-items: start;">

            {{-- 1. GitHub Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 1.25rem;">🐙</span>
                            <span>GitHub OAuth</span>
                        </div>
                        @if($this->isGithubConfigured())
                            <x-filament::badge color="success">Connected</x-filament::badge>
                        @else
                            <x-filament::badge color="warning">Not Configured</x-filament::badge>
                        @endif
                    </div>
                </x-slot>
                
                <x-slot name="description">
                    One-click sign-in for developers
                </x-slot>

                {{-- Setup Instructions --}}
                <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 700; font-size: 0.75rem; color: #059669; text-transform: uppercase; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <span>📖</span> Setup Instructions
                    </div>
                    <ol style="font-size: 0.8125rem; line-height: 1.5; color: #334155; margin: 0; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>
                            Go to <a href="https://github.com/settings/developers" target="_blank" style="color: #059669; font-weight: 700; text-decoration: underline;">GitHub Developer Settings &rarr; OAuth Apps</a> and click <strong>New OAuth App</strong>.
                        </li>
                        <li>
                            Application name: <code style="padding: 0.15rem 0.4rem; border-radius: 0.25rem; background: #0f172a; color: #34d399; font-family: monospace; font-size: 0.75rem;">DevFolio.AI</code>
                        </li>
                        <li>
                            <span style="font-weight: 600;">Homepage URL:</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getAppUrl() }}" style="width: 100%; font-family: monospace; font-size: 0.75rem; padding: 0.4rem 0.6rem; border-radius: 0.375rem; background: #0f172a; border: 1px solid #334155; color: #38bdf8;">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" style="padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; background: #1e293b; color: #ffffff; border: 1px solid #334155; cursor: pointer; white-space: nowrap;">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            <span style="font-weight: 600;">Authorization callback URL:</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getGithubCallbackUrl() }}" style="width: 100%; font-family: monospace; font-size: 0.75rem; padding: 0.4rem 0.6rem; border-radius: 0.375rem; background: #0f172a; border: 1px solid #334155; color: #34d399; font-weight: 700;">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGithubCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" style="padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; background: #1e293b; color: #ffffff; border: 1px solid #334155; cursor: pointer; white-space: nowrap;">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            Click <strong>Register application</strong>, generate a <strong>Client Secret</strong>, and paste them below:
                        </li>
                    </ol>
                </div>

                {{-- Inputs --}}
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">GitHub Client ID</label>
                        <input
                            type="text"
                            wire:model="github_client_id"
                            placeholder="e.g. Iv1.8a2b3c4d5e6f7g8h"
                            style="width: 100%; border-radius: 0.5rem; background: #0f172a; border: 1px solid #334155; padding: 0.55rem 0.75rem; font-size: 0.8125rem; font-family: monospace; color: #f8fafc;"
                        />
                    </div>

                    <div x-data="{ show: false }">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">GitHub Client Secret</label>
                        <div style="position: relative;">
                            <input
                                :type="show ? 'text' : 'password'"
                                wire:model="github_client_secret"
                                placeholder="Paste generated Client Secret"
                                style="width: 100%; border-radius: 0.5rem; background: #0f172a; border: 1px solid #334155; padding: 0.55rem 2.5rem 0.55rem 0.75rem; font-size: 0.8125rem; font-family: monospace; color: #f8fafc;"
                            />
                            <button type="button" @click="show = !show" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 0.85rem;">
                                <span x-show="!show">👁️</span>
                                <span x-show="show">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.25rem;">
                        <label style="font-size: 0.8125rem; color: #475569; font-weight: 600; cursor: pointer;" for="gh_enable_box">Enable GitHub Sign-In</label>
                        <input id="gh_enable_box" type="checkbox" wire:model="github_is_enabled" style="width: 1.15rem; height: 1.15rem; accent-color: #10b981; cursor: pointer;">
                    </div>
                </div>
            </x-filament::section>

            {{-- 2. Google Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 1.25rem;">🌐</span>
                            <span>Google Identity (OAuth 2.0)</span>
                        </div>
                        @if($this->isGoogleConfigured())
                            <x-filament::badge color="success">Connected</x-filament::badge>
                        @else
                            <x-filament::badge color="warning">Not Configured</x-filament::badge>
                        @endif
                    </div>
                </x-slot>

                <x-slot name="description">
                    One-click sign-in with Google Accounts
                </x-slot>

                {{-- Setup Instructions --}}
                <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 700; font-size: 0.75rem; color: #d97706; text-transform: uppercase; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <span>📖</span> Setup Instructions
                    </div>
                    <ol style="font-size: 0.8125rem; line-height: 1.5; color: #334155; margin: 0; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>
                            Open <a href="https://console.cloud.google.com/apis/credentials" target="_blank" style="color: #d97706; font-weight: 700; text-decoration: underline;">Google Cloud Console &rarr; Credentials</a>.
                        </li>
                        <li>
                            Click <strong>Create Credentials</strong> &rarr; <strong>OAuth client ID</strong> (Application type: <strong>Web application</strong>).
                        </li>
                        <li>
                            <span style="font-weight: 600;">Authorized JavaScript origins:</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getAppUrl() }}" style="width: 100%; font-family: monospace; font-size: 0.75rem; padding: 0.4rem 0.6rem; border-radius: 0.375rem; background: #0f172a; border: 1px solid #334155; color: #38bdf8;">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" style="padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; background: #1e293b; color: #ffffff; border: 1px solid #334155; cursor: pointer; white-space: nowrap;">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            <span style="font-weight: 600;">Authorized redirect URIs:</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;" x-data="{ copied: false }">
                                <input type="text" readonly value="{{ $this->getGoogleCallbackUrl() }}" style="width: 100%; font-family: monospace; font-size: 0.75rem; padding: 0.4rem 0.6rem; border-radius: 0.375rem; background: #0f172a; border: 1px solid #334155; color: #fbbf24; font-weight: 700;">
                                <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGoogleCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" style="padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; background: #1e293b; color: #ffffff; border: 1px solid #334155; cursor: pointer; white-space: nowrap;">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                </button>
                            </div>
                        </li>
                        <li>
                            Click <strong>Create</strong>, copy the generated <strong>Client ID</strong> and <strong>Client Secret</strong>, and paste below:
                        </li>
                    </ol>
                </div>

                {{-- Inputs --}}
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">Google Client ID</label>
                        <input
                            type="text"
                            wire:model="google_client_id"
                            placeholder="e.g. 123456789-abcdefg.apps.googleusercontent.com"
                            style="width: 100%; border-radius: 0.5rem; background: #0f172a; border: 1px solid #334155; padding: 0.55rem 0.75rem; font-size: 0.8125rem; font-family: monospace; color: #f8fafc;"
                        />
                    </div>

                    <div x-data="{ show: false }">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">Google Client Secret</label>
                        <div style="position: relative;">
                            <input
                                :type="show ? 'text' : 'password'"
                                wire:model="google_client_secret"
                                placeholder="Paste generated Client Secret"
                                style="width: 100%; border-radius: 0.5rem; background: #0f172a; border: 1px solid #334155; padding: 0.55rem 2.5rem 0.55rem 0.75rem; font-size: 0.8125rem; font-family: monospace; color: #f8fafc;"
                            />
                            <button type="button" @click="show = !show" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 0.85rem;">
                                <span x-show="!show">👁️</span>
                                <span x-show="show">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.25rem;">
                        <label style="font-size: 0.8125rem; color: #475569; font-weight: 600; cursor: pointer;" for="google_enable_box">Enable Google Sign-In</label>
                        <input id="google_enable_box" type="checkbox" wire:model="google_is_enabled" style="width: 1.15rem; height: 1.15rem; accent-color: #f59e0b; cursor: pointer;">
                    </div>
                </div>
            </x-filament::section>

        </div>

        {{-- Bottom Submit Button --}}
        <div style="display: flex; justify-content: flex-end; padding-top: 1rem;">
            <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                Save OAuth Settings to Database
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
