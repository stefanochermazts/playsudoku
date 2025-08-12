<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Determine if we're in a localized route
                $locale = app()->getLocale();
                $redirectUrl = route('dashboard');
                
                // Check if we're in a localized context
                if ($request->route() && $request->route()->hasParameter('locale')) {
                    $redirectUrl = route('localized.dashboard', ['locale' => $locale]);
                }
                
                return redirect($redirectUrl);
            }
        }

        return $next($request);
    }
}
