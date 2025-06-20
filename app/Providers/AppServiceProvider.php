<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

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
        Schema::defaultStringLength(191);

        // Only force HTTPS URLs if explicitly in production and secure
        if ($this->app->environment('production') && request()->isSecure()) {
            URL::forceScheme('https');
        }
        
        // Alternative approach if using a load balancer/proxy:
        // if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        //     URL::forceScheme('https');
        // }
    }
}
