<?php

namespace App\Providers;

use App\Models\Account;
use App\Services\CurrentProfileResolver;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind CurrentProfileResolver
        $this->app->singleton(CurrentProfileResolver::class, function ($app) {
            return new CurrentProfileResolver();
        });

        // Bind custom LoginResponse for Filament
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Account::class);

        \Illuminate\Support\Facades\RateLimiter::for('contact-form', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinutes(10, 5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('ai-generation', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
