<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        // Clear any stale intended URLs that might redirect back to root admin panel
        session()->forget('url.intended');

        if (! $user) {
            return redirect()->to('/admin/login');
        }

        // 1. Super Admin -> Super Admin Master Control
        if ($user->is_super_admin || $user->email === 'admin@example.com') {
            return redirect()->to(route('super-admin.dashboard'));
        }

        // 2. Agency Owner or Team Member -> Agency Client Hub
        $isAgency = $user->accounts()->where('plan_slug', 'agency')->exists()
            || $user->memberAccounts()->where('plan_slug', 'agency')->exists()
            || $user->email === 'agency@example.com';

        if ($isAgency) {
            return redirect()->to(route('agency'));
        }

        // 3. Developer / Portfolio Owner / Default User -> User Dashboard
        return redirect()->to(route('dashboard'));
    }
}
