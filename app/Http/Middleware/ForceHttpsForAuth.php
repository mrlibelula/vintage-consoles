<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsForAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply in production
        if (!app()->environment('production')) {
            return $next($request);
        }

        // Force HTTPS for authentication routes
        if ($this->isAuthRoute($request)) {
            // Force the scheme to HTTPS for URL generation
            URL::forceScheme('https');
            
            // Set server variables to ensure Laravel detects HTTPS
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);
            $request->server->set('REQUEST_SCHEME', 'https');
            
            // Set forwarded headers for proxy environments
            if (!$request->header('X-Forwarded-Proto')) {
                $request->headers->set('X-Forwarded-Proto', 'https');
            }
            if (!$request->header('X-Forwarded-Port')) {
                $request->headers->set('X-Forwarded-Port', '443');
            }
            
            // If the request is not secure and we're dealing with a GET request,
            // redirect to HTTPS version
            if (!$request->isSecure() && $request->isMethod('GET')) {
                $httpsUrl = 'https://' . $request->getHost() . $request->getRequestUri();
                return redirect($httpsUrl, 301);
            }
        }

        return $next($request);
    }

    /**
     * Determine if this is an authentication-related route
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = [
            'login', 'register', 'password.request', 'password.reset',
            'password.email', 'password.update', 'password.confirm',
            'two-factor.login', 'verification.notice', 'verification.verify'
        ];

        $currentRoute = $request->route()?->getName();
        
        // Check by route name
        if (in_array($currentRoute, $authRoutes)) {
            return true;
        }

        // Check by path
        $authPaths = [
            'login', 'register', 'forgot-password', 'reset-password',
            'email/verify', 'user/confirm-password', 'two-factor-challenge'
        ];

        foreach ($authPaths as $path) {
            if (str_starts_with($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }
}
