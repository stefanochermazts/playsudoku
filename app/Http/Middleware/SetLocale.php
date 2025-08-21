<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = (array) config('app.supported_locales', ['en']);

        $routeLocale = $request->route('locale');
        $sessionLocale = null;

        // Safely try to get session locale
        try {
            if ($request->hasSession()) {
                $sessionLocale = $request->session()->get('locale');
            }
        } catch (\Exception $e) {
            // Session not available, continue without it
            $sessionLocale = null;
        }

        $locale = 'en';
        if (is_string($routeLocale) && in_array($routeLocale, $supportedLocales, true)) {
            $locale = $routeLocale;
            // Safely try to set session locale
            try {
                if ($request->hasSession()) {
                    $request->session()->put('locale', $locale);
                }
            } catch (\Exception $e) {
                // Session not available, continue without it
            }
        } elseif (is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)) {
            $locale = $sessionLocale;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}


