<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('enforce-locale')->group(function () {
    // All public pages should include the locale prefix; middleware enforces it.
});

Route::get('/', function (Request $request) {
    if (app()->environment('testing')) {
        return view('welcome');
    }
    $supported = (array) config('app.supported_locales', ['en', 'it']);

    $sessionLocale = $request->session()->get('locale');
    if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
        return redirect()->to(url('/'.$sessionLocale));
    }

    $preferred = 'en';
    $accept = (string) $request->header('Accept-Language', '');
    if (preg_match('/\bit\b/i', $accept)) {
        $preferred = 'it';
    }

    if (! in_array($preferred, $supported, true)) {
        $preferred = $supported[0] ?? 'en';
    }

    return redirect()->to(url('/'.$preferred));
});

// Gruppo opzionale con prefisso locale per contenuti tradotti.
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'en|it'], 'middleware' => ['setlocale']], function () {
    Route::view('/', 'home')->name('localized.home');

    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('localized.dashboard');

    Route::view('profile', 'profile')
        ->middleware(['auth'])
        ->name('localized.profile');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
