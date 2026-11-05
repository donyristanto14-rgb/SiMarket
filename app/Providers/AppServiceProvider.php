<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($trustedProxies = config('app.trusted_proxies')) {
            TrustProxies::at($trustedProxies);
        }

        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');

            return Limit::perMinute(5)->by(
                $request->ip().'|'.(is_string($email) ? Str::lower($email) : '')
            );
        });

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
