<x-filament-panels::page.simple>
    <div 
        x-data="{ 
            activeRole: 'user',
            selectRole(role, email) {
                this.activeRole = role;
                if (email) {
                    const emailInput = document.querySelector('input[type=\'email\'], input[name*=\'email\']');
                    const passInput = document.querySelector('input[type=\'password\'], input[name*=\'password\']');
                    if (emailInput) {
                        emailInput.value = email;
                        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (passInput) {
                        passInput.value = 'password';
                        passInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        }" 
        class="space-y-6"
    >
        {{-- Role Tabs Header --}}
        <div class="space-y-2.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 text-center">
                Select Your Access Portal
            </p>
            
            <div class="grid grid-cols-3 gap-2 p-1.5 rounded-2xl bg-gray-900/80 border border-gray-800 backdrop-blur-md">
                {{-- 1. Developer / User Tab (Cyan Theme) --}}
                <button
                    type="button"
                    @click="selectRole('user', '')"
                    :class="activeRole === 'user' 
                        ? 'bg-cyan-500/15 border-cyan-500/50 text-cyan-400 shadow-md shadow-cyan-500/10' 
                        : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                    class="py-2.5 px-2 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all duration-200 text-xs font-bold text-center"
                >
                    <span class="text-base">👤</span>
                    <span>Developer</span>
                </button>

                {{-- 2. Account Admin / Agency Tab (Amber Theme) --}}
                <button
                    type="button"
                    @click="selectRole('admin', '')"
                    :class="activeRole === 'admin' 
                        ? 'bg-amber-500/15 border-amber-500/50 text-amber-400 shadow-md shadow-amber-500/10' 
                        : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                    class="py-2.5 px-2 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all duration-200 text-xs font-bold text-center"
                >
                    <span class="text-base">🏢</span>
                    <span>Admin</span>
                </button>

                {{-- 3. Super Admin Tab (Purple Theme) --}}
                <button
                    type="button"
                    @click="selectRole('super_admin', 'admin@example.com')"
                    :class="activeRole === 'super_admin' 
                        ? 'bg-purple-500/15 border-purple-500/50 text-purple-400 shadow-md shadow-purple-500/10' 
                        : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'"
                    class="py-2.5 px-2 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all duration-200 text-xs font-bold text-center"
                >
                    <span class="text-base">👑</span>
                    <span>Super Admin</span>
                </button>
            </div>
        </div>

        {{-- Role Context Banner --}}
        <div>
            {{-- Developer User Context Banner --}}
            <div x-show="activeRole === 'user'" x-transition:enter="transition ease-out duration-200" class="p-3.5 rounded-2xl bg-cyan-950/40 border border-cyan-500/30 text-cyan-200 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        Developer Workspace
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-semibold">User Portal</span>
                </div>
                <p class="text-xs text-cyan-300/80 leading-relaxed">
                    Sign in to manage your personal portfolio, AI resumes, job application Kanban tracker, and theme styling.
                </p>
            </div>

            {{-- Account Admin Context Banner --}}
            <div x-show="activeRole === 'admin'" x-transition:enter="transition ease-out duration-200" class="p-3.5 rounded-2xl bg-amber-950/40 border border-amber-500/30 text-amber-200 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Agency & Tenant Admin
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-semibold">Admin Portal</span>
                </div>
                <p class="text-xs text-amber-300/80 leading-relaxed">
                    Access team collaboration, multi-client portfolios, Stripe billing, custom domains, and white-label branding.
                </p>
            </div>

            {{-- Super Admin Context Banner --}}
            <div x-show="activeRole === 'super_admin'" x-transition:enter="transition ease-out duration-200" class="p-3.5 rounded-2xl bg-purple-950/40 border border-purple-500/30 text-purple-200 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                        Platform Super Admin
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-semibold">Master Console</span>
                </div>
                <p class="text-xs text-purple-300/80 leading-relaxed">
                    Full platform oversight across all tenants, moderation reports, global templates, and system controls.
                </p>
                <div class="pt-1 flex items-center justify-between text-[11px] text-purple-400/90 border-t border-purple-500/20">
                    <span>Default: <code class="bg-purple-900/50 px-1 py-0.5 rounded text-purple-200">admin@example.com</code></span>
                    <button 
                        type="button" 
                        @click="selectRole('super_admin', 'admin@example.com')" 
                        class="underline hover:text-purple-200 font-semibold"
                    >
                        Auto-fill
                    </button>
                </div>
            </div>
        </div>

        {{-- Filament Form Content --}}
        <div>
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page.simple>
