<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';
    protected static string $layout = 'filament.layouts.auth';

    public function getMaxWidth(): ?string
    {
        return '6xl';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        return 'DevFolio Portal Sign In';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString('Sign in to access your <strong>Portfolio Workspace</strong>, <strong>Team Dashboard</strong>, or <strong>Admin Console</strong>.');
    }

    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        $response = parent::authenticate();

        $user = filament()->auth()->user();

        if ($user && $user->is_super_admin) {
            redirect()->intended(route('super-admin.dashboard'));
        }

        redirect()->intended(route('dashboard'));
        
        return null;
    }
}
