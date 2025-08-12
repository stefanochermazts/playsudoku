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

    Route::view('dashboard', 'dashboard-livewire')
        ->middleware(['auth', 'verified'])
        ->name('localized.dashboard');

    Route::view('profile', 'profile')
        ->middleware(['auth'])
        ->name('localized.profile');
    
    // Sfide localizzate (area riservata)
    Route::middleware(['auth'])->group(function () {
        Route::get('/challenges', function () {
            return view('challenges.index');
        })->name('localized.challenges.index');
        
        Route::get('/challenges/{challenge}/play', function ($challengeId) {
            return view('challenges.play', ['challengeId' => $challengeId]);
        })->name('localized.challenges.play');
        
        Route::get('/leaderboard', function () {
            return view('leaderboard.index');
        })->name('localized.leaderboard.index');
    });
    
    // Include auth routes with locale prefix
    require __DIR__.'/auth.php';
});

Route::view('dashboard', 'dashboard-livewire')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
});

// Route demo Sudoku (accessibili a tutti)
Route::get('/sudoku/demo', [App\Http\Controllers\SudokuDemoController::class, 'index'])->name('sudoku.demo');
Route::get('/sudoku/play', [App\Http\Controllers\SudokuDemoController::class, 'play'])->name('sudoku.play');

// Route area riservata (con auth)
Route::middleware(['auth'])->group(function () {
    // Sfide
    Route::get('/challenges', function () {
        return view('challenges.index');
    })->name('challenges.index');
    
    Route::get('/challenges/{challenge}/play', function ($challengeId) {
        return view('challenges.play', ['challengeId' => $challengeId]);
    })->name('challenges.play');
    
    // Classifiche
    Route::get('/leaderboard', function () {
        return view('leaderboard.index');
    })->name('leaderboard.index');
});

// Route di test
Route::get('/test-basic', [App\Http\Controllers\SimpleTestController::class, 'test']);
Route::get('/test-livewire', [App\Http\Controllers\SimpleTestController::class, 'livewireTest']);

// Include auth routes for non-localized fallback
require __DIR__.'/auth.php';
