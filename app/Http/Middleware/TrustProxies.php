<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*'; // Trust all proxies - use specific IPs in production for security

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Force HTTPS detection for auth routes in production
        if (app()->environment('production')) {
            // Additional HTTPS detection for problematic hosting environments
            if ($this->shouldForceHttps($request)) {
                $request->server->set('HTTPS', 'on');
                $request->server->set('SERVER_PORT', 443);
                $request->server->set('HTTP_X_FORWARDED_PROTO', 'https');
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine if we should force HTTPS for this request
     */
    protected function shouldForceHttps($request): bool
    {
        $authRoutes = [
            'login', 'register', 'password.request', 'password.reset',
            'password.email', 'password.update', 'two-factor.login'
        ];

        $currentRoute = $request->route()?->getName();
        
        return in_array($currentRoute, $authRoutes) ||
               str_starts_with($request->path(), 'login') ||
               str_starts_with($request->path(), 'register') ||
               str_starts_with($request->path(), 'forgot-password') ||
               str_starts_with($request->path(), 'reset-password');
    }
}
