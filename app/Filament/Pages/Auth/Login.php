<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string|Htmlable
    {
        return 'DevFolio Portal Sign In';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString('Sign in to access your <strong>Portfolio Workspace</strong>, <strong>Team Dashboard</strong>, or <strong>Admin Console</strong>.');
    }
}
