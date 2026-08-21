<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .oauth-settings-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }
        .oauth-hero-card {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.4) 0%, rgba(17, 24, 39, 0.95) 50%, rgba(3, 7, 18, 0.98) 100%);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 1.25rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .oauth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 1.5rem;
            align-items: start;
        }
        .oauth-card {
            background-color: #0d131f;
            border: 1px solid #1f293d;
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .oauth-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1a2333;
        }
        .oauth-badge-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            min-height: 40px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #161f30;
            border: 1px solid #28354d;
        }
        .oauth-svg-icon {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            max-width: 20px !important;
            max-height: 20px !important;
            display: block;
        }
        .oauth-guide {
            background-color: #090e17;
            border: 1px solid #1a2436;
            border-radius: 0.875rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .oauth-url-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }
        .oauth-url-input {
            width: 100%;
            background-color: #05080f;
            border: 1px solid #1e293b;
            color: #38bdf8;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            padding: 0.4rem 0.65rem;
            border-radius: 0.5rem;
        }
        .oauth-copy-btn {
            background-color: #1e293b;
            color: #f1f5f9;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #334155;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .oauth-copy-btn:hover {
            background-color: #334155;
            color: #ffffff;
        }
        .oauth-field-group {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .oauth-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 0.25rem;
            display: block;
        }
        .oauth-text-input {
            width: 100%;
            background-color: #070b12;
            border: 1px solid #1e293b;
            color: #f8fafc;
            font-size: 0.8125rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            padding: 0.6rem 0.85rem;
            border-radius: 0.625rem;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .oauth-text-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 1px #10b981;
        }
        .oauth-submit-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #030712;
            font-weight: 800;
            font-size: 0.875rem;
            padding: 0.75rem 1.75rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .oauth-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.45);
        }
    </style>

    <div class="oauth-settings-container">
        {{-- Hero Header Card --}}
        <div class="oauth-hero-card">
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; width: fit-content; padding: 0.25rem 0.75rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                    ⚡ Social Login Management
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; margin: 0;">OAuth 2.0 Provider Credentials</h2>
                <p style="font-size: 0.8125rem; color: #94a3b8; margin: 0; max-width: 42rem;">
                    Configure instant one-click login for GitHub and Google. Credentials entered here are encrypted and stored directly in your database.
                </p>
            </div>
            <div>
                <button type="button" wire:click="save" class="oauth-submit-btn">
                    <span>💾 Save Settings</span>
                </button>
            </div>
        </div>

        <form wire:submit="save" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="oauth-grid">

                {{-- 1. GitHub OAuth Configuration Card --}}
                <div class="oauth-card">
                    {{-- Header --}}
                    <div class="oauth-card-header">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="oauth-badge-icon">
                                <svg class="oauth-svg-icon" fill="#f8fafc" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.9375rem; font-weight: 800; color: #ffffff;">GitHub OAuth</div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">One-click sign-in for developers</div>
                            </div>
                        </div>

                        @if($this->isGithubConfigured())
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;">
                                ● Connected
                            </span>
                        @else
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24;">
                                Not Configured
                            </span>
                        @endif
                    </div>

                    {{-- Step-by-Step Directions Guide --}}
                    <div class="oauth-guide">
                        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #34d399; display: flex; align-items: center; gap: 0.35rem;">
                            <span>📖</span> Setup Instructions
                        </div>
                        <ol style="font-size: 0.75rem; color: #cbd5e1; margin: 0; padding-left: 1.15rem; display: flex; flex-direction: column; gap: 0.5rem; line-height: 1.45;">
                            <li>
                                Go to <a href="https://github.com/settings/developers" target="_blank" style="color: #34d399; font-weight: 700; text-decoration: underline;">GitHub Developer Settings &rarr; OAuth Apps</a> and click <strong>New OAuth App</strong>.
                            </li>
                            <li>
                                Application name: <code style="padding: 0.1rem 0.35rem; border-radius: 0.25rem; background: #020617; color: #6ee7b7; font-family: monospace;">DevFolio.AI</code>
                            </li>
                            <li>
                                <span>Homepage URL:</span>
                                <div class="oauth-url-row" x-data="{ copied: false }">
                                    <input type="text" readonly value="{{ $this->getAppUrl() }}" class="oauth-url-input select-all">
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="oauth-copy-btn">
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                    </button>
                                </div>
                            </li>
                            <li>
                                <span>Authorization callback URL:</span>
                                <div class="oauth-url-row" x-data="{ copied: false }">
                                    <input type="text" readonly value="{{ $this->getGithubCallbackUrl() }}" class="oauth-url-input select-all" style="color: #34d399; font-weight: 700;">
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGithubCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="oauth-copy-btn">
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

                    {{-- Form Fields --}}
                    <div class="oauth-field-group">
                        <div>
                            <label class="oauth-label">GitHub Client ID</label>
                            <input
                                type="text"
                                wire:model="github_client_id"
                                placeholder="e.g. Iv1.8a2b3c4d5e6f7g8h"
                                class="oauth-text-input"
                            />
                        </div>

                        <div x-data="{ show: false }">
                            <label class="oauth-label">GitHub Client Secret</label>
                            <div style="position: relative;">
                                <input
                                    :type="show ? 'text' : 'password'"
                                    wire:model="github_client_secret"
                                    placeholder="Paste your generated Client Secret"
                                    class="oauth-text-input"
                                    style="padding-right: 2.5rem;"
                                />
                                <button type="button" @click="show = !show" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 0.85rem;">
                                    <span x-show="!show">👁️</span>
                                    <span x-show="show">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.25rem;">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; cursor: pointer;" for="gh_enabled">Enable GitHub Sign-In</label>
                            <input id="gh_enabled" type="checkbox" wire:model="github_is_enabled" style="width: 1.1rem; height: 1.1rem; accent-color: #10b981; cursor: pointer;">
                        </div>
                    </div>
                </div>

                {{-- 2. Google OAuth Configuration Card --}}
                <div class="oauth-card">
                    {{-- Header --}}
                    <div class="oauth-card-header">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="oauth-badge-icon">
                                <svg class="oauth-svg-icon" viewBox="0 0 24 24">
                                    <path fill="#EA4335" d="M12 5c1.7 0 3 .7 3.7 1.3l2.8-2.8C16.8 1.9 14.6 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.4 2.6C6.2 6.9 8.8 5 12 5z"/>
                                    <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                                    <path fill="#FBBC05" d="M5.3 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.6C.7 9.9 0 12.4 0 15s.7 5.1 1.9 7.4l3.4-2.6z"/>
                                    <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.2 0-5.8-2-6.7-4.9L1.9 16c1.8 3.7 5.6 6.3 10.1 6.3z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.9375rem; font-weight: 800; color: #ffffff;">Google Identity (OAuth 2.0)</div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">One-click sign-in with Google Accounts</div>
                            </div>
                        </div>

                        @if($this->isGoogleConfigured())
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;">
                                ● Connected
                            </span>
                        @else
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24;">
                                Not Configured
                            </span>
                        @endif
                    </div>

                    {{-- Step-by-Step Directions Guide --}}
                    <div class="oauth-guide">
                        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #fbbf24; display: flex; align-items: center; gap: 0.35rem;">
                            <span>📖</span> Setup Instructions
                        </div>
                        <ol style="font-size: 0.75rem; color: #cbd5e1; margin: 0; padding-left: 1.15rem; display: flex; flex-direction: column; gap: 0.5rem; line-height: 1.45;">
                            <li>
                                Open <a href="https://console.cloud.google.com/apis/credentials" target="_blank" style="color: #fbbf24; font-weight: 700; text-decoration: underline;">Google Cloud Console &rarr; Credentials</a>.
                            </li>
                            <li>
                                Click <strong>Create Credentials</strong> &rarr; <strong>OAuth client ID</strong> (Application type: <strong>Web application</strong>).
                            </li>
                            <li>
                                <span>Authorized JavaScript origins:</span>
                                <div class="oauth-url-row" x-data="{ copied: false }">
                                    <input type="text" readonly value="{{ $this->getAppUrl() }}" class="oauth-url-input select-all">
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $this->getAppUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="oauth-copy-btn">
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" style="color: #34d399;">✓ Copied</span>
                                    </button>
                                </div>
                            </li>
                            <li>
                                <span>Authorized redirect URIs:</span>
                                <div class="oauth-url-row" x-data="{ copied: false }">
                                    <input type="text" readonly value="{{ $this->getGoogleCallbackUrl() }}" class="oauth-url-input select-all" style="color: #fbbf24; font-weight: 700;">
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $this->getGoogleCallbackUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)" class="oauth-copy-btn">
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

                    {{-- Form Fields --}}
                    <div class="oauth-field-group">
                        <div>
                            <label class="oauth-label">Google Client ID</label>
                            <input
                                type="text"
                                wire:model="google_client_id"
                                placeholder="e.g. 123456789-abcdefg.apps.googleusercontent.com"
                                class="oauth-text-input"
                            />
                        </div>

                        <div x-data="{ show: false }">
                            <label class="oauth-label">Google Client Secret</label>
                            <div style="position: relative;">
                                <input
                                    :type="show ? 'text' : 'password'"
                                    wire:model="google_client_secret"
                                    placeholder="Paste your generated Client Secret"
                                    class="oauth-text-input"
                                    style="padding-right: 2.5rem;"
                                />
                                <button type="button" @click="show = !show" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 0.85rem;">
                                    <span x-show="!show">👁️</span>
                                    <span x-show="show">🙈</span>
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.25rem;">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; cursor: pointer;" for="google_enabled">Enable Google Sign-In</label>
                            <input id="google_enabled" type="checkbox" wire:model="google_is_enabled" style="width: 1.1rem; height: 1.1rem; accent-color: #f59e0b; cursor: pointer;">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Action Bar --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #1e293b;">
                <button type="submit" class="oauth-submit-btn">
                    <span>💾 Save OAuth Settings to Database</span>
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
