<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Puzzle;
use App\Models\Challenge;

test('user can complete a sudoku challenge end-to-end', function () {
    $user = User::factory()->create();
    
    // Puzzle molto semplice - quasi completato per velocizzare il test
    $puzzle = Puzzle::factory()->create([
        'givens' => [
            [5, 3, 4, 6, 7, 8, 9, 1, 2],
            [6, 7, 2, 1, 9, 5, 3, 4, 8],
            [1, 9, 8, 3, 4, 2, 5, 6, 7],
            [8, 5, 9, 7, 6, 1, 4, 2, 3],
            [4, 2, 6, 8, 5, 3, 7, 9, 1],
            [7, 1, 3, 9, 2, 4, 8, 5, 6],
            [9, 6, 1, 5, 3, 7, 2, 8, 4],
            [2, 8, 7, 4, 1, 9, 6, 3, 5],
            [3, 4, 5, 2, 8, 6, 1, 7, 0] // Solo l'ultima cella vuota
        ],
        'solution' => [
            [5, 3, 4, 6, 7, 8, 9, 1, 2],
            [6, 7, 2, 1, 9, 5, 3, 4, 8],
            [1, 9, 8, 3, 4, 2, 5, 6, 7],
            [8, 5, 9, 7, 6, 1, 4, 2, 3],
            [4, 2, 6, 8, 5, 3, 7, 9, 1],
            [7, 1, 3, 9, 2, 4, 8, 5, 6],
            [9, 6, 1, 5, 3, 7, 2, 8, 4],
            [2, 8, 7, 4, 1, 9, 6, 3, 5],
            [3, 4, 5, 2, 8, 6, 1, 7, 9]
        ],
        'difficulty' => 'easy'
    ]);
    
    $challenge = Challenge::create([
        'puzzle_id' => $puzzle->id,
        'type' => 'daily',
        'status' => 'active',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
        'created_by' => $user->id,
        'visibility' => 'public'
    ]);

    $this->browse(function (Browser $browser) use ($user, $challenge) {
        $browser->loginAs($user)
                ->visit("/it/challenges/{$challenge->id}/play")
                
                // Verifica che la sfida sia caricata
                ->assertSee('Daily Challenge')
                ->assertSee('Timer')
                
                // Verifica che il timer sia partito
                ->assertDontSee('00:00:00')
                
                // Clicca sull'ultima cella vuota (8,8)
                ->click('[data-cell="8-8"]')
                ->assertPresent('[data-cell="8-8"].selected')
                
                // Inserisce il numero corretto (9)
                ->keys('[data-cell="8-8"]', '9')
                
                // Verifica che il numero sia inserito
                ->waitFor('[data-cell="8-8"]:contains("9")', 2)
                
                // Attende il completamento automatico
                ->waitForText('Congratulazioni!', 5)
                ->assertSee('Sfida completata!')
                
                // Verifica che sia mostrato il tempo
                ->assertSee('Tempo')
                ->assertSee('secondi')
                
                // Verifica che ci sia un link alla classifica
                ->assertSee('Classifica')
                ->click('a:contains("Classifica")')
                
                // Verifica redirect alla leaderboard
                ->waitForLocation('/leaderboard')
                ->assertSee('Leaderboard')
                ->assertSee($user->name);
    });
})->group('dusk', 'completion');

test('challenge shows proper validation and error handling', function () {
    $user = User::factory()->create();
    $puzzle = Puzzle::factory()->create([
        'givens' => [
            [5, 3, 0, 0, 7, 0, 0, 0, 0],
            [6, 0, 0, 1, 9, 5, 0, 0, 0],
            [0, 9, 8, 0, 0, 0, 0, 6, 0],
            [8, 0, 0, 0, 6, 0, 0, 0, 3],
            [4, 0, 0, 8, 0, 3, 0, 0, 1],
            [7, 0, 0, 0, 2, 0, 0, 0, 6],
            [0, 6, 0, 0, 0, 0, 2, 8, 0],
            [0, 0, 0, 4, 1, 9, 0, 0, 5],
            [0, 0, 0, 0, 8, 0, 0, 7, 9]
        ],
        'solution' => [
            [5, 3, 4, 6, 7, 8, 9, 1, 2],
            [6, 7, 2, 1, 9, 5, 3, 4, 8],
            [1, 9, 8, 3, 4, 2, 5, 6, 7],
            [8, 5, 9, 7, 6, 1, 4, 2, 3],
            [4, 2, 6, 8, 5, 3, 7, 9, 1],
            [7, 1, 3, 9, 2, 4, 8, 5, 6],
            [9, 6, 1, 5, 3, 7, 2, 8, 4],
            [2, 8, 7, 4, 1, 9, 6, 3, 5],
            [3, 4, 5, 2, 8, 6, 1, 7, 9]
        ],
        'difficulty' => 'normal'
    ]);
    
    $challenge = Challenge::create([
        'puzzle_id' => $puzzle->id,
        'type' => 'daily',
        'status' => 'active',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
        'created_by' => $user->id,
        'visibility' => 'public'
    ]);

    $this->browse(function (Browser $browser) use ($user, $challenge) {
        $browser->loginAs($user)
                ->visit("/it/challenges/{$challenge->id}/play")
                
                // Clicca su una cella vuota
                ->click('[data-cell="0-2"]')
                
                // Inserisce un numero che crea un conflitto (5 - stesso della riga)
                ->keys('[data-cell="0-2"]', '5')
                
                // Verifica che appaia un indicatore di errore
                ->waitFor('[data-cell="0-2"].error, .error-indicator', 3)
                
                // Verifica che il contatore errori si aggiorni
                ->assertSee('Errori:')
                
                // Corregge l'errore inserendo il numero giusto
                ->keys('[data-cell="0-2"]', '4')
                
                // Verifica che l'errore sparisca
                ->waitUntilMissing('[data-cell="0-2"].error', 2)
                
                // Test undo/redo functionality se disponibile
                ->assertPresent('button:contains("Annulla"), [aria-label*="Undo"], [title*="Undo"]');
    });
})->group('dusk', 'validation');

test('challenge respects time limits and expiration', function () {
    $user = User::factory()->create();
    $puzzle = Puzzle::factory()->create();
    
    // Challenge scaduto
    $expiredChallenge = Challenge::create([
        'puzzle_id' => $puzzle->id,
        'type' => 'daily',
        'status' => 'active',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subDay(),
        'created_by' => $user->id,
        'visibility' => 'public'
    ]);

    $this->browse(function (Browser $browser) use ($user, $expiredChallenge) {
        $browser->loginAs($user)
                ->visit("/it/challenges/{$expiredChallenge->id}/play")
                
                // Verifica che mostri un messaggio di scadenza
                ->assertSee('scaduta', 'expired', 'terminata')
                
                // Verifica che il board non sia interattivo
                ->assertMissing('.sudoku-grid [data-cell]:not([disabled])')
                
                // Può ancora vedere la soluzione/risultati
                ->assertSee('Soluzione', 'Risultati', 'Leaderboard');
    });
})->group('dusk', 'time-limits');
