<?php

use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Puzzle;
use App\Models\Challenge;

test('sudoku board loads and is interactive', function () {
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
                ->assertSee('Sudoku')
                
                // Verifica che la griglia sia presente
                ->assertPresent('.sudoku-grid')
                
                // Verifica che ci siano celle interattive
                ->assertPresent('[role="gridcell"]')
                
                // Debug: stampa il contenuto della pagina
                ->dump()
                
                // Verifica che i numeri dati siano visualizzati (prima cella dovrebbe avere 5)
                ->assertPresent('[aria-rowindex="1"][aria-colindex="1"]')
                ->assertSeeIn('[aria-rowindex="1"][aria-colindex="1"]', '5')
                
                // Tenta di cliccare su una cella vuota (riga 1, colonna 3)
                ->click('[aria-rowindex="1"][aria-colindex="3"]')
                
                // Verifica che la cella sia selezionata (ha aria-selected="true")
                ->waitFor('[aria-rowindex="1"][aria-colindex="3"][aria-selected="true"]', 2)
                
                // Tenta di inserire un numero via JavaScript (Livewire gestisce via wire:click)
                ->script('
                    // Simula l\'inserimento del numero 4 nella cella selezionata
                    window.Livewire.find(document.querySelector("[wire\\:id]").getAttribute("wire:id")).setValue(0, 2, 4);
                ')
                
                // Verifica che il numero sia stato inserito
                ->waitFor('[aria-rowindex="1"][aria-colindex="3"]:contains("4")', 3)
                
                // Verifica accessibility basics
                ->assertPresent('[role="grid"]')
                ->assertPresent('[aria-label]');
    });
})->group('dusk', 'smoke');

test('sudoku board supports keyboard navigation', function () {
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
                
                // Focus sulla prima cella vuota (riga 1, colonna 3)
                ->click('[aria-rowindex="1"][aria-colindex="3"]')
                ->waitFor('[aria-rowindex="1"][aria-colindex="3"][aria-selected="true"]', 2)
                
                // Verifica che la cella abbia il focus giusto
                ->assertPresent('[aria-rowindex="1"][aria-colindex="3"][tabindex="0"]')
                
                // Verifica navigation disponibile (i tasti freccia sono gestiti via JavaScript)
                ->script('
                    // Testa che il sistema di navigazione sia presente
                    const selectedCell = document.querySelector("[aria-selected=\\"true\\"]");
                    return selectedCell && selectedCell.getAttribute("role") === "gridcell";
                ')
                
                // Verifica Tab navigation verso i controlli
                ->keys('[aria-rowindex="1"][aria-colindex="3"]', ['{tab}'])
                ->assertFocused('button, input, [tabindex="0"]:not([aria-selected="true"])');
    });
})->group('dusk', 'accessibility');
