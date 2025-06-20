<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\URL;

class FortifyServiceProvider extends ServiceProvider
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
        // Force HTTPS for Fortify routes in production
        if ($this->app->environment('production')) {
            Fortify::loginView(function () {
                URL::forceScheme('https');
                return view('auth.login');
            });

            Fortify::registerView(function () {
                URL::forceScheme('https');
                return view('auth.register');
            });

            Fortify::requestPasswordResetLinkView(function () {
                URL::forceScheme('https');
                return view('auth.forgot-password');
            });

            Fortify::resetPasswordView(function ($request) {
                URL::forceScheme('https');
                return view('auth.reset-password', ['request' => $request]);
            });
        } else {
            // Development views without forcing HTTPS
            Fortify::loginView(function () {
                return view('auth.login');
            });

            Fortify::registerView(function () {
                return view('auth.register');
            });

            Fortify::requestPasswordResetLinkView(function () {
                return view('auth.forgot-password');
            });

            Fortify::resetPasswordView(function ($request) {
                return view('auth.reset-password', ['request' => $request]);
            });
        }

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
