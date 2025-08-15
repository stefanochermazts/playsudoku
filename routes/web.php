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
    })->middleware(['localized-auth', 'verified'])
      ->name('localized.dashboard');

    Route::get('profile', function ($locale) {
        return view('profile');
    })->middleware(['localized-auth'])
      ->name('localized.profile');
    
    // Sudoku Training (pubblico - accessibile senza login)
    Route::get('/sudoku/training', function ($locale) {
        return app(\App\Http\Controllers\SudokuDemoController::class)->index();
    })->name('localized.sudoku.training');
    
    Route::get('/sudoku/training/play', function ($locale) {
        return app(\App\Http\Controllers\SudokuDemoController::class)->play();
    })->name('localized.sudoku.play');
    
    Route::get('/sudoku/training/analyzer', function ($locale) {
        return app(\App\Http\Controllers\SudokuDemoController::class)->analyzer();
    })->name('localized.sudoku.analyzer');
    
    // Pagine di supporto (pubblico - accessibili senza login)
    Route::get('/contact', function ($locale) {
        return view('contact');
    })->name('localized.contact');
    
    Route::get('/help', function ($locale) {
        return view('help');
    })->name('localized.help');
    
    // Legal pages
    Route::get('/privacy', function ($locale) {
        return view('legal.privacy');
    })->name('localized.privacy');
    
    Route::get('/cookie-policy', function ($locale) {
        return view('legal.cookie-policy');
    })->name('localized.cookie-policy');
    
    Route::get('/terms', function ($locale) {
        return view('legal.terms');
    })->name('localized.terms');
    
    // Sfide localizzate (area riservata)
    Route::middleware(['localized-auth'])->group(function () {
        Route::get('/challenges', function ($locale) {
            return view('challenges.index');
        })->name('localized.challenges.index');
        
        Route::get('/challenges/{challenge}/play', function ($locale, $challenge) {
            // Verifica che il challenge esista
            $challengeModel = \App\Models\Challenge::find($challenge);
            if (!$challengeModel) {
                abort(404, 'Challenge not found');
            }
            
            // Configura SEO meta tags per questa challenge
            $metaService = app(\App\Services\MetaService::class);
            $metaService->setChallenge($challengeModel);
            
            return view('challenges.play', [
                'challengeId' => $challenge,
                'metaService' => $metaService
            ]);
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
        
        // Friends page
        Route::get('/friends', [App\Http\Controllers\FriendshipController::class, 'index'])->name('localized.friends.index');
        
        // Friends ranking
        Route::get('/friends/ranking', [App\Http\Controllers\FriendsRankingController::class, 'index'])->name('localized.friends.ranking');
        Route::get('/friends/compare/{friend}', [App\Http\Controllers\FriendsRankingController::class, 'compare'])->name('localized.friends.compare');
        
        // Activity Feed
        Route::get('/activity', [App\Http\Controllers\ActivityFeedController::class, 'index'])->name('localized.activity.index');
        
        // Clubs pages
        Route::get('/clubs', [App\Http\Controllers\ClubController::class, 'index'])->name('localized.clubs.index');
        Route::get('/clubs/explore', [App\Http\Controllers\ClubController::class, 'explore'])->name('localized.clubs.explore');
        Route::get('/clubs/create', [App\Http\Controllers\ClubController::class, 'create'])->name('localized.clubs.create');
        Route::post('/clubs', [App\Http\Controllers\ClubController::class, 'store'])->name('localized.clubs.store');
        Route::get('/clubs/{club}', [App\Http\Controllers\ClubController::class, 'show'])->name('localized.clubs.show');
        Route::get('/clubs/{club}/edit', [App\Http\Controllers\ClubController::class, 'edit'])->name('localized.clubs.edit');
        Route::put('/clubs/{club}', [App\Http\Controllers\ClubController::class, 'update'])->name('localized.clubs.update');
        
        // Club membership management
        Route::post('/clubs/{club}/join', [App\Http\Controllers\ClubController::class, 'joinClub'])->name('localized.clubs.join');
        Route::post('/clubs/{club}/leave', [App\Http\Controllers\ClubController::class, 'leaveClub'])->name('localized.clubs.leave');
        Route::get('/clubs/{club}/invite', [App\Http\Controllers\ClubController::class, 'showInviteForm'])->name('localized.clubs.invite.form');
        Route::post('/clubs/{club}/invite', [App\Http\Controllers\ClubController::class, 'sendInvites'])->name('localized.clubs.invite.send');
        Route::post('/clubs/{club}/invite/{membership}/accept', [App\Http\Controllers\ClubController::class, 'acceptInvite'])->name('localized.clubs.invite.accept');
        Route::post('/clubs/{club}/invite/{membership}/decline', [App\Http\Controllers\ClubController::class, 'declineInvite'])->name('localized.clubs.invite.decline');
        
        // Privacy settings
        Route::get('/settings/privacy', [App\Http\Controllers\PrivacyController::class, 'index'])->name('localized.privacy.index');
        Route::post('/settings/privacy', [App\Http\Controllers\PrivacyController::class, 'update'])->name('localized.privacy.update');
        
        // API routes per preferenze utente (localizzate)
        Route::prefix('api/preferences')->group(function () {
            Route::post('/theme', [App\Http\Controllers\UserPreferencesController::class, 'updateTheme'])->name('localized.api.preferences.theme');
            Route::get('/', [App\Http\Controllers\UserPreferencesController::class, 'getPreferences'])->name('localized.api.preferences.get');
            Route::post('/accessibility', [App\Http\Controllers\UserPreferencesController::class, 'updateAccessibility'])->name('localized.api.preferences.accessibility');
        });
        
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
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\AdminController::class, 'impersonate'])->name('users.impersonate');
    
    // Consent management (GDPR)
    Route::get('/consents', [App\Http\Controllers\Admin\ConsentController::class, 'index'])->name('consents.index');
    Route::get('/consents/{consent}', [App\Http\Controllers\Admin\ConsentController::class, 'show'])->name('consents.show');
    Route::post('/consents/{consent}/withdraw', [App\Http\Controllers\Admin\ConsentController::class, 'withdraw'])->name('consents.withdraw');
    Route::get('/consents/statistics', [App\Http\Controllers\Admin\ConsentController::class, 'statistics'])->name('consents.statistics');
    Route::post('/consents/export', [App\Http\Controllers\Admin\ConsentController::class, 'export'])->name('consents.export');
    Route::post('/consents/cleanup', [App\Http\Controllers\Admin\ConsentController::class, 'cleanup'])->name('consents.cleanup');
    Route::get('/users/{user}/consents', [App\Http\Controllers\Admin\ConsentController::class, 'userConsents'])->name('users.consents');
    
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

    // Moderation & Anti-cheat
    Route::prefix('moderation')->name('moderation.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ModerationController::class, 'dashboard'])->name('dashboard');
        Route::get('/flagged', [App\Http\Controllers\Admin\ModerationController::class, 'flaggedAttempts'])->name('flagged');
        Route::get('/attempts/{attempt}', [App\Http\Controllers\Admin\ModerationController::class, 'showAttempt'])->name('attempts.show');
        Route::post('/attempts/{attempt}/approve', [App\Http\Controllers\Admin\ModerationController::class, 'approveAttempt'])->name('attempts.approve');
        Route::post('/attempts/{attempt}/reject', [App\Http\Controllers\Admin\ModerationController::class, 'rejectAttempt'])->name('attempts.reject');
        Route::post('/challenges/{challenge}/reopen', [App\Http\Controllers\Admin\ModerationController::class, 'reopenChallenge'])->name('challenges.reopen');
        Route::post('/challenges/{challenge}/analyze', [App\Http\Controllers\Admin\ModerationController::class, 'analyzeChallengeAnomalies'])->name('challenges.analyze');
        Route::get('/export/suspicious', [App\Http\Controllers\Admin\ModerationController::class, 'exportSuspiciousAttempts'])->name('export.suspicious');
    });
});

// Stop impersonation (accessibile a qualunque utente autenticato)
Route::post('/impersonation/stop', [App\Http\Controllers\Admin\AdminController::class, 'stopImpersonate'])
    ->middleware(['localized-auth'])
    ->name('impersonation.stop');

// SEO: Sitemap and robots.txt
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-static.xml', [App\Http\Controllers\SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemap-challenges.xml', [App\Http\Controllers\SitemapController::class, 'challenges'])->name('sitemap.challenges');
Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

// Route demo Sudoku (accessibili a tutti)
Route::get('/sudoku/demo', [App\Http\Controllers\SudokuDemoController::class, 'index'])->name('sudoku.demo');
Route::get('/sudoku/play', [App\Http\Controllers\SudokuDemoController::class, 'play'])->name('sudoku.play');
Route::get('/sudoku/analyzer', [App\Http\Controllers\SudokuDemoController::class, 'analyzer'])->name('sudoku.analyzer');

// Redirect route area riservata senza locale a route localizzate
Route::middleware(['localized-auth'])->group(function () {
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

// API routes per preferenze utente
Route::middleware(['auth'])->prefix('api/preferences')->group(function () {
    Route::post('/theme', [App\Http\Controllers\UserPreferencesController::class, 'updateTheme'])->name('api.preferences.theme');
    Route::get('/', [App\Http\Controllers\UserPreferencesController::class, 'getPreferences'])->name('api.preferences.get');
    Route::post('/accessibility', [App\Http\Controllers\UserPreferencesController::class, 'updateAccessibility'])->name('api.preferences.accessibility');
});

// API routes per amicizie
Route::middleware(['auth'])->prefix('api/friends')->group(function () {
    Route::get('/search', [App\Http\Controllers\FriendshipController::class, 'search'])->name('api.friends.search');
    Route::post('/request', [App\Http\Controllers\FriendshipController::class, 'sendRequest'])->name('api.friends.request');
    Route::post('/accept/{friendship}', [App\Http\Controllers\FriendshipController::class, 'acceptRequest'])->name('api.friends.accept');
    Route::post('/decline/{friendship}', [App\Http\Controllers\FriendshipController::class, 'declineRequest'])->name('api.friends.decline');
    Route::delete('/remove/{friend}', [App\Http\Controllers\FriendshipController::class, 'removeFriend'])->name('api.friends.remove');
    Route::post('/block/{userToBlock}', [App\Http\Controllers\FriendshipController::class, 'blockUser'])->name('api.friends.block');
    Route::get('/mutual/{user}', [App\Http\Controllers\FriendshipController::class, 'mutualFriends'])->name('api.friends.mutual');
});

// API routes per club
Route::middleware(['auth'])->prefix('api/clubs')->group(function () {
    Route::post('/join/{club}', [App\Http\Controllers\ClubController::class, 'join'])->name('api.clubs.join');
    Route::post('/accept-invite/{membership}', [App\Http\Controllers\ClubController::class, 'acceptInvite'])->name('api.clubs.accept-invite');
    Route::post('/decline-invite/{membership}', [App\Http\Controllers\ClubController::class, 'declineInvite'])->name('api.clubs.decline-invite');
});

// API routes per preferenze utente
Route::middleware(['auth'])->prefix('api/user')->group(function () {
    Route::post('/preferences', function(Illuminate\Http\Request $request) {
        $validatedData = $request->validate([
            'theme' => 'required|in:light,dark',
        ]);
        
        // Per ora ritorna semplicemente successo
        // TODO: Salvare le preferenze del tema nel database quando sarà aggiunta la colonna
        
        return response()->json(['success' => true, 'theme' => $validatedData['theme']]);
    })->name('api.user.preferences');
});

// Profili pubblici utenti
Route::get('/users/{user}/profile', [App\Http\Controllers\UserProfileController::class, 'show'])->name('users.profile.show');

// Include auth routes for non-localized fallback
require __DIR__.'/auth.php';
