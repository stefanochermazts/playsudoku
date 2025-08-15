<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class LocalizedAuth extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            $locale = null;
            
            // First: Try to get locale from route parameter (most reliable)
            if ($request->route() && $request->route()->hasParameter('locale')) {
                $routeLocale = $request->route()->parameter('locale');
                if (is_string($routeLocale) && in_array($routeLocale, ['it', 'en'])) {
                    $locale = $routeLocale;
                }
            }
            
            // Second: Try to get locale from URL path
            if (!$locale) {
                $pathSegments = explode('/', trim($request->getPathInfo(), '/'));
                if (!empty($pathSegments[0]) && in_array($pathSegments[0], ['it', 'en'])) {
                    $locale = $pathSegments[0];
                }
            }
            
            // Third: Try to get locale from app/session
            if (!$locale) {
                $appLocale = app()->getLocale();
                if (in_array($appLocale, ['it', 'en'])) {
                    $locale = $appLocale;
                }
            }
            
            // If we have a valid locale, use localized login
            if ($locale && in_array($locale, ['it', 'en'])) {
                return route('localized.login', ['locale' => $locale]);
            }
            
            // Fallback to non-localized login
            return route('login');
        }
        
        return null;
    }
}
