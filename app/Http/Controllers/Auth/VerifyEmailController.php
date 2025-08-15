<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Get the locale from the request
        $locale = $request->route('locale') ?? app()->getLocale();
        
        // Determine the correct dashboard route
        $dashboardRoute = $this->getDashboardRoute($locale);
        
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($dashboardRoute.'?verified=1')
                ->with('status', __('auth.Email verified successfully'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($dashboardRoute.'?verified=1')
            ->with('status', __('auth.Welcome! Your email has been verified.'));
    }
    
    /**
     * Get the appropriate dashboard route based on locale.
     */
    private function getDashboardRoute(string $locale): string
    {
        if ($locale !== config('app.fallback_locale') && Route::has('localized.dashboard')) {
            return route('localized.dashboard', ['locale' => $locale], false);
        }
        
        return route('dashboard', absolute: false);
    }
}
