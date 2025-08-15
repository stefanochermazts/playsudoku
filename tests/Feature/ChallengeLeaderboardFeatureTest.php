<?php

use App\Models\User;
use App\Models\Challenge;
use App\Models\Puzzle;
use App\Models\ChallengeAttempt;
use App\Services\ChallengeService;
use Carbon\Carbon;

beforeEach(function () {
    // Crea una sfida di test con dati fissi
    $givens = [
        [5,3,null,null,7,null,null,null,null],
        [6,null,null,1,9,5,null,null,null],
        [null,9,8,null,null,null,null,6,null],
        [8,null,null,null,6,null,null,null,3],
        [4,null,null,8,null,3,null,null,1],
        [7,null,null,null,2,null,null,null,6],
        [null,6,null,null,null,null,2,8,null],
        [null,null,null,4,1,9,null,null,5],
        [null,null,null,null,8,null,null,7,9]
    ];
    
    $solution = [
        [5,3,4,6,7,8,9,1,2],
        [6,7,2,1,9,5,3,4,8],
        [1,9,8,3,4,2,5,6,7],
        [8,5,9,7,6,1,4,2,3],
        [4,2,6,8,5,3,7,9,1],
        [7,1,3,9,2,4,8,5,6],
        [9,6,1,5,3,7,2,8,4],
        [2,8,7,4,1,9,6,3,5],
        [3,4,5,2,8,6,1,7,9]
    ];
    
    $this->puzzle = Puzzle::factory()->create([
        'difficulty' => 'normal',
        'givens' => $givens,
        'solution' => $solution
    ]);
    
    // Crea utenti di test (incluso admin per creare la sfida)
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->users = User::factory()->count(5)->create();
    
    $this->challenge = Challenge::create([
        'puzzle_id' => $this->puzzle->id,
        'type' => 'daily',
        'status' => 'active',
        'starts_at' => Carbon::now()->subHour(),
        'ends_at' => Carbon::now()->addHour(),
        'created_by' => $this->admin->id,
        'visibility' => 'public',
    ]);
});

test('complete challenge flow with leaderboard and tie-break rules', function () {
    // Scenario: 5 utenti completano la sfida con diversi tempi, errori e hint
    
    // User 1: Tempo medio, nessun errore, nessun hint (winner)
    $attempt1 = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[0]->id,
        'completed_at' => Carbon::now()->subMinutes(30),
        'duration_ms' => 180000, // 3 minuti in ms
        'errors_count' => 0,
        'hints_used' => 0,
        'valid' => true,
    ]);

    // User 2: Stesso tempo di User 1, ma un errore (secondo posto)
    $attempt2 = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[1]->id,
        'completed_at' => Carbon::now()->subMinutes(25),
        'duration_ms' => 180000, // 3 minuti (stesso tempo)
        'errors_count' => 1, // Più errori = posizione peggiore
        'hints_used' => 0,
        'valid' => true,
    ]);

    // User 3: Tempo migliore di tutti (primo posto assoluto)
    $attempt3 = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[2]->id,
        'completed_at' => Carbon::now()->subMinutes(35), // Completato prima (timestamp più antico)
        'duration_ms' => 120000, // 2 minuti (tempo migliore)
        'errors_count' => 0,
        'hints_used' => 1,
        'valid' => true,
    ]);

    // User 4: Stesso tempo di User 1 e 2, stessi errori di User 1, ma completato dopo
    $attempt4 = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[3]->id,
        'completed_at' => Carbon::now()->subMinutes(20), // Completato dopo
        'duration_ms' => 180000,
        'errors_count' => 0,
        'hints_used' => 0,
        'valid' => true,
    ]);

    // User 5: Tempo peggiore
    $attempt5 = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[4]->id,
        'completed_at' => Carbon::now()->subMinutes(15),
        'duration_ms' => 300000, // 5 minuti
        'errors_count' => 2,
        'hints_used' => 3,
        'valid' => true,
    ]);

    // Ottieni la classifica ordinata
    $leaderboard = $this->challenge->getLeaderboard();

    // Verifica l'ordine secondo i criteri di tie-break:
    // 1. Tempo di completamento (più basso è meglio)
    // 2. Meno errori 
    // 3. Timestamp di completamento più antico
    // 4. Meno hint usati

    expect($leaderboard)->toHaveCount(5);
    
    // Primo posto: User 3 (tempo migliore: 2 minuti)
    expect($leaderboard[0]->user_id)->toBe($this->users[2]->id);
    expect($leaderboard[0]->duration_ms)->toBe(120000);
    
    // Secondo posto: User 1 (3 min, 0 errori, completato prima di User 4)
    expect($leaderboard[1]->user_id)->toBe($this->users[0]->id);
    expect($leaderboard[1]->duration_ms)->toBe(180000);
    expect($leaderboard[1]->errors_count)->toBe(0);
    
    // Terzo posto: User 4 (3 min, 0 errori, ma completato dopo User 1)
    expect($leaderboard[2]->user_id)->toBe($this->users[3]->id);
    expect($leaderboard[2]->duration_ms)->toBe(180000);
    expect($leaderboard[2]->errors_count)->toBe(0);
    
    // Quarto posto: User 2 (3 min, ma 1 errore)
    expect($leaderboard[3]->user_id)->toBe($this->users[1]->id);
    expect($leaderboard[3]->duration_ms)->toBe(180000);
    expect($leaderboard[3]->errors_count)->toBe(1);
    
    // Quinto posto: User 5 (tempo peggiore)
    expect($leaderboard[4]->user_id)->toBe($this->users[4]->id);
    expect($leaderboard[4]->duration_ms)->toBe(300000);
});

test('challenge leaderboard with invalid attempts', function () {
    // User con tentativo valido
    $validAttempt = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[0]->id,
        'completed_at' => Carbon::now()->subMinutes(10),
        'duration_ms' => 180000,
        'valid' => true,
    ]);

    // User con tentativo invalido (non deve apparire in classifica)
    $invalidAttempt = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[1]->id,
        'completed_at' => Carbon::now()->subMinutes(5),
        'duration_ms' => 120000, // Tempo migliore ma invalido
        'valid' => false,
    ]);

    // User con tentativo incompleto (non deve apparire in classifica)
    $incompleteAttempt = ChallengeAttempt::create([
        'challenge_id' => $this->challenge->id,
        'user_id' => $this->users[2]->id,
        'completed_at' => null, // Non completato
        'duration_ms' => null,
        'valid' => true,
    ]);

    $leaderboard = $this->challenge->getLeaderboard();

    // Solo il tentativo valido e completato deve essere presente
    expect($leaderboard)->toHaveCount(1);
    expect($leaderboard[0]->user_id)->toBe($this->users[0]->id);
    expect($leaderboard[0]->valid)->toBe(true);
    expect($leaderboard[0]->completed_at)->not->toBeNull();
});

test('challenge leaderboard performance with many participants', function () {
    // Crea 50 partecipanti per testare le performance (meno di 100 per velocità nei test)
    $manyUsers = User::factory()->count(50)->create();
    
    foreach ($manyUsers as $index => $user) {
        ChallengeAttempt::create([
            'challenge_id' => $this->challenge->id,
            'user_id' => $user->id,
            'completed_at' => Carbon::now()->subMinutes(100 - $index),
            'duration_ms' => (120 + $index) * 1000, // Tempi crescenti
            'errors_count' => $index % 3, // Vari numeri di errori
            'hints_used' => $index % 5, // Vari numeri di hint
            'valid' => true,
        ]);
    }

    $startTime = microtime(true);
    $leaderboard = $this->challenge->getLeaderboard();
    $executionTime = microtime(true) - $startTime;

    // Verifica che la query sia efficiente (< 200ms per i test)
    expect($executionTime)->toBeLessThan(0.2);
    expect($leaderboard)->toHaveCount(50);
    
    // Verifica l'ordinamento corretto
    for ($i = 0; $i < 49; $i++) {
        expect($leaderboard[$i]->duration_ms)
            ->toBeLessThanOrEqual($leaderboard[$i + 1]->duration_ms);
    }
});

test('challenge leaderboard with pagination', function () {
    // Crea 25 partecipanti
    $users = User::factory()->count(25)->create();
    
    foreach ($users as $index => $user) {
        ChallengeAttempt::create([
            'challenge_id' => $this->challenge->id,
            'user_id' => $user->id,
            'completed_at' => Carbon::now()->subMinutes(50 - $index),
            'duration_ms' => (120 + $index) * 1000,
            'valid' => true,
        ]);
    }

    // Test paginazione (primo gruppo di 10)
    $leaderboard = $this->challenge->getLeaderboard(10, 0);
    expect($leaderboard)->toHaveCount(10);
    
    // Verifica che i primi 10 siano i migliori
    expect($leaderboard[0]->duration_ms)->toBe(120000);
    expect($leaderboard[9]->duration_ms)->toBe(129000);

    // Test secondo gruppo (offset 10)
    $secondPage = $this->challenge->getLeaderboard(10, 10);
    expect($secondPage)->toHaveCount(10);
    expect($secondPage[0]->duration_ms)->toBe(130000);
    expect($secondPage[9]->duration_ms)->toBe(139000);
});
