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
    Route::get('/', function ($locale) {
        return view('home');
    })->name('localized.home');

    Route::get('dashboard', function ($locale) {
        return view('dashboard-livewire');
    })->middleware(['auth', 'verified'])
      ->name('localized.dashboard');

    Route::get('profile', function ($locale) {
        return view('profile');
    })->middleware(['auth'])
      ->name('localized.profile');
    
    // Sfide localizzate (area riservata)
    Route::middleware(['auth'])->group(function () {
        Route::get('/challenges', function ($locale) {
            return view('challenges.index');
        })->name('localized.challenges.index');
        
        Route::get('/challenges/{challenge}/play', function ($locale, $challenge) {
            // Verifica che il challenge esista
            $challengeModel = \App\Models\Challenge::find($challenge);
            if (!$challengeModel) {
                abort(404, 'Challenge not found');
            }
            
            return view('challenges.play', ['challengeId' => $challenge]);
        })->name('localized.challenges.play');
        
        // Leaderboard localizzata (spostata dentro il gruppo auth)
        Route::get('/leaderboard/{challenge}', function($locale, $challenge) {
            return app(\App\Http\Controllers\LeaderboardController::class)->show(request(), $challenge);
        })->name('localized.leaderboard.show');
        
        // Export leaderboard CSV
        Route::get('/leaderboard/{challenge}/export', function($locale, $challenge) {
            return app(\App\Http\Controllers\LeaderboardController::class)->exportCsv(request(), $challenge);
        })->name('localized.leaderboard.export');
        
        // Daily Board routes
        Route::get('/daily-board', [\App\Http\Controllers\DailyBoardController::class, 'index'])
            ->name('localized.daily-board.index');
        Route::get('/daily-board/archive', [\App\Http\Controllers\DailyBoardController::class, 'archive'])
            ->name('localized.daily-board.archive');
        Route::get('/daily-board/{date}', [\App\Http\Controllers\DailyBoardController::class, 'show'])
            ->name('localized.daily-board.show');
            
        // Weekly Board routes
        Route::get('/weekly-board', [\App\Http\Controllers\WeeklyBoardController::class, 'index'])
            ->name('localized.weekly-board.index');
        Route::get('/weekly-board/archive', [\App\Http\Controllers\WeeklyBoardController::class, 'archive'])
            ->name('localized.weekly-board.archive');
        Route::get('/weekly-board/{week}', [\App\Http\Controllers\WeeklyBoardController::class, 'show'])
            ->name('localized.weekly-board.show');
        
        // altre rotte protette
    });
    
    // Include auth routes with locale prefix
    require __DIR__.'/auth.php';
});

// Redirect dashboard senza locale a dashboard localizzato
Route::get('dashboard', function (Request $request) {
    $supported = (array) config('app.supported_locales', ['en', 'it']);
    
    // Prova a ottenere locale dalla sessione
    $sessionLocale = $request->session()->get('locale');
    if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
        return redirect()->to(url('/'.$sessionLocale.'/dashboard'));
    }
    
    // Fallback su Accept-Language
    $preferred = 'en';
    $accept = (string) $request->header('Accept-Language', '');
    if (preg_match('/\bit\b/i', $accept)) {
        $preferred = 'it';
    }
    
    if (! in_array($preferred, $supported, true)) {
        $preferred = $supported[0] ?? 'en';
    }
    
    return redirect()->to(url('/'.$preferred.'/dashboard'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Redirect profile senza locale a profile localizzato
Route::get('profile', function (Request $request) {
    $supported = (array) config('app.supported_locales', ['en', 'it']);
    
    // Prova a ottenere locale dalla sessione
    $sessionLocale = $request->session()->get('locale');
    if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
        return redirect()->to(url('/'.$sessionLocale.'/profile'));
    }
    
    // Fallback su Accept-Language
    $preferred = 'en';
    $accept = (string) $request->header('Accept-Language', '');
    if (preg_match('/\bit\b/i', $accept)) {
        $preferred = 'it';
    }
    
    if (! in_array($preferred, $supported, true)) {
        $preferred = $supported[0] ?? 'en';
    }
    
    return redirect()->to(url('/'.$preferred.'/profile'));
})->middleware(['auth'])->name('profile');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [App\Http\Controllers\Admin\AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'showUser'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'destroyUser'])->name('users.destroy');
    
    // Challenge management
    Route::get('/challenges', [App\Http\Controllers\Admin\AdminController::class, 'challenges'])->name('challenges');
    Route::get('/challenges/create', [App\Http\Controllers\Admin\AdminController::class, 'createChallenge'])->name('challenges.create');
    Route::post('/challenges', [App\Http\Controllers\Admin\AdminController::class, 'storeChallenge'])->name('challenges.store');
    Route::get('/challenges/{challenge}', [App\Http\Controllers\Admin\AdminController::class, 'showChallenge'])->name('challenges.show');
    Route::get('/challenges/{challenge}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editChallenge'])->name('challenges.edit');
    Route::put('/challenges/{challenge}', [App\Http\Controllers\Admin\AdminController::class, 'updateChallenge'])->name('challenges.update');
    Route::delete('/challenges/{challenge}', [App\Http\Controllers\Admin\AdminController::class, 'destroyChallenge'])->name('challenges.destroy');
    
    // Puzzle management
    Route::get('/puzzles', [App\Http\Controllers\Admin\AdminController::class, 'puzzles'])->name('puzzles');
    Route::get('/puzzles/generate', [App\Http\Controllers\Admin\AdminController::class, 'generatePuzzles'])->name('puzzles.generate');
    Route::post('/puzzles/generate', [App\Http\Controllers\Admin\AdminController::class, 'storeGeneratedPuzzles'])->name('puzzles.store');
    Route::delete('/puzzles/{puzzle}', [App\Http\Controllers\Admin\AdminController::class, 'destroyPuzzle'])->name('puzzles.destroy');
});

// Route demo Sudoku (accessibili a tutti)
Route::get('/sudoku/demo', [App\Http\Controllers\SudokuDemoController::class, 'index'])->name('sudoku.demo');
Route::get('/sudoku/play', [App\Http\Controllers\SudokuDemoController::class, 'play'])->name('sudoku.play');

// Redirect route area riservata senza locale a route localizzate
Route::middleware(['auth'])->group(function () {
    // Redirect challenges senza locale
    Route::get('/challenges', function (Request $request) {
        $supported = (array) config('app.supported_locales', ['en', 'it']);
        
        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            return redirect()->to(url('/'.$sessionLocale.'/challenges'));
        }
        
        $preferred = 'en';
        $accept = (string) $request->header('Accept-Language', '');
        if (preg_match('/\bit\b/i', $accept)) {
            $preferred = 'it';
        }
        
        if (! in_array($preferred, $supported, true)) {
            $preferred = $supported[0] ?? 'en';
        }
        
        return redirect()->to(url('/'.$preferred.'/challenges'));
    })->name('challenges.index');
    
    Route::get('/challenges/{challenge}/play', function (Request $request, $challenge) {
        $supported = (array) config('app.supported_locales', ['en', 'it']);
        
        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            return redirect()->to(url('/'.$sessionLocale.'/challenges/'.$challenge.'/play'));
        }
        
        $preferred = 'en';
        $accept = (string) $request->header('Accept-Language', '');
        if (preg_match('/\bit\b/i', $accept)) {
            $preferred = 'it';
        }
        
        if (! in_array($preferred, $supported, true)) {
            $preferred = $supported[0] ?? 'en';
        }
        
        return redirect()->to(url('/'.$preferred.'/challenges/'.$challenge.'/play'));
    })->name('challenges.play');
    
    // Redirect leaderboard senza locale - TEMPORANEAMENTE COMMENTATO PER DEBUG
    /*
    Route::get('/leaderboard/{challenge?}', function (Request $request, $challenge = null) {
        $supported = (array) config('app.supported_locales', ['en', 'it']);
        
        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            $suffix = $challenge ? '/leaderboard/'.$challenge : '/leaderboard';
            return redirect()->to(url('/'.$sessionLocale.$suffix));
        }
        
        $preferred = 'en';
        $accept = (string) $request->header('Accept-Language', '');
        if (preg_match('/\bit\b/i', $accept)) {
            $preferred = 'it';
        }
        
        if (! in_array($preferred, $supported, true)) {
            $preferred = $supported[0] ?? 'en';
        }
        
        $suffix = $challenge ? '/leaderboard/'.$challenge : '/leaderboard';
        return redirect()->to(url('/'.$preferred.$suffix));
    })->name('leaderboard.redirect');
    */
});

// Route di test
Route::get('/test-basic', [App\Http\Controllers\SimpleTestController::class, 'test']);
Route::get('/test-livewire', [App\Http\Controllers\SimpleTestController::class, 'livewireTest']);

// Include auth routes for non-localized fallback
require __DIR__.'/auth.php';
