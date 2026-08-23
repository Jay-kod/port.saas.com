<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve.tenant' => \App\Http\Middleware\ResolveTenantFromSlug::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
        $middleware->redirectTo(guests: function (Request $request) {
            if ($request->is('super-admin*') || $request->is('admin*')) {
                return route('super-admin.login');
            }
            if ($request->is('agency*')) {
                return route('agency.login');
            }
            return route('developer.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
