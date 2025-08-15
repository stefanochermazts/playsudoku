<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// This file is included both in the main routes and in the localized route group
// We need to determine the context and register appropriate routes

// Check the current route group context
$routeGroupStack = app('router')->getGroupStack();
$isInLocalizedGroup = false;

// Check if we're inside a localized route group
foreach ($routeGroupStack as $group) {
    if (isset($group['prefix']) && str_contains($group['prefix'], '{locale}')) {
        $isInLocalizedGroup = true;
        break;
    }
}

if ($isInLocalizedGroup) {
    // Localized auth routes (when included from the localized route group)
    Route::middleware('guest')->group(function () {
        Volt::route('register', 'pages.auth.register')
            ->name('localized.register');

        Volt::route('login', 'pages.auth.login')
            ->name('localized.login');

        Volt::route('forgot-password', 'pages.auth.forgot-password')
            ->name('localized.password.request');

        Volt::route('reset-password/{token}', 'pages.auth.reset-password')
            ->name('localized.password.reset');
    });
} else {
    // Non-localized auth routes (fallback)
    Route::middleware('guest')->group(function () {
        Volt::route('register', 'pages.auth.register')
            ->name('register');

        Volt::route('login', 'pages.auth.login')
            ->name('login');

        Volt::route('forgot-password', 'pages.auth.forgot-password')
            ->name('password.request');

        Volt::route('reset-password/{token}', 'pages.auth.reset-password')
            ->name('password.reset');
    });
}

Route::middleware('auth')->group(function () use ($isInLocalizedGroup) {
    if ($isInLocalizedGroup) {
        // Localized auth routes for authenticated users
        Volt::route('verify-email', 'pages.auth.verify-email')
            ->name('localized.verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('localized.verification.verify');

        Route::post('email/verification-notification', function () {
            request()->user()->sendEmailVerificationNotification();
            return back()->with('message', __('verification.sent'));
        })->middleware(['throttle:6,1'])->name('localized.verification.send');

        Volt::route('confirm-password', 'pages.auth.confirm-password')
            ->name('localized.password.confirm');
    } else {
        // Non-localized auth routes for authenticated users  
        Volt::route('verify-email', 'pages.auth.verify-email')
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('email/verification-notification', function () {
            request()->user()->sendEmailVerificationNotification();
            return back()->with('message', __('verification.sent'));
        })->middleware(['throttle:6,1'])->name('verification.send');

        Volt::route('confirm-password', 'pages.auth.confirm-password')
            ->name('password.confirm');
    }
    
    // Logout route with context-aware naming
    Route::post('logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        // Redirect to localized home if available
        $locale = app()->getLocale();
        return redirect()->to(url('/' . $locale));
    })->name($isInLocalizedGroup ? 'localized.logout' : 'logout');
});
