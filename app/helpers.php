<?php

/**
 * Force a secure URL for authentication routes to prevent browser warnings
 */
if (!function_exists('secure_auth_url')) {
    function secure_auth_url($path = '') {
        $appUrl = env('APP_URL', config('app.url'));
        
        // Ensure HTTPS
        if (!str_starts_with($appUrl, 'https://')) {
            $appUrl = str_replace('http://', 'https://', $appUrl);
        }
        
        return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
    }
}

/**
 * Generate secure route for auth pages
 */
if (!function_exists('secure_route')) {
    function secure_route($name, $parameters = [], $absolute = true) {
        $url = route($name, $parameters, false);
        
        if (app()->environment('production')) {
            $appUrl = env('APP_URL', config('app.url'));
            
            // Force HTTPS domain
            if (!str_starts_with($appUrl, 'https://')) {
                $appUrl = str_replace('http://', 'https://', $appUrl);
            }
            
            $domain = parse_url($appUrl, PHP_URL_HOST);
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            
            if ($domain && $scheme) {
                return $scheme . '://' . $domain . $url;
            }
        }
        
        return $url;
    }
} 