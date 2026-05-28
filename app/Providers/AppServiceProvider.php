<?php

namespace App\Providers;

use App\Services\GameRepository;
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
        $this->app->singleton(GameRepository::class);
        $this->app->singleton(\App\Services\AppFontService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Comprehensive HTTPS fix for production
        if ($this->app->environment('production')) {
            // Force HTTPS for all URL generation
            URL::forceScheme('https');
            
            // Fix domain/subdomain issues that cause browser warnings
            $appUrl = env('APP_URL');
            if ($appUrl && str_starts_with($appUrl, 'https://')) {
                $domain = parse_url($appUrl, PHP_URL_HOST);
                if ($domain) {
                    URL::forceRootUrl($appUrl);
                }
            }
            
            // Additional security headers to prevent mixed content warnings
            if (request()->isSecure()) {
                config(['session.secure' => true]);
                config(['session.cookie' => config('session.cookie').'_secure']);
            }
        }
    }
}
