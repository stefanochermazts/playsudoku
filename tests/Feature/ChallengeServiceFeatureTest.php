<?php

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use App\Models\Challenge;
use App\Models\User;
use App\Services\ChallengeService;
use Carbon\Carbon;

beforeEach(function () {
    $validator = new Validator();
    $generator = new Generator($validator);
    $this->challengeService = new ChallengeService($generator, $validator);

    // Crea utenti di test
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user']);
});

test('creates daily challenge successfully', function () {
    $startDate = Carbon::now();
    
    $challenge = $this->challengeService->createDailyChallenge('normal', $startDate);

    expect($challenge)->toBeInstanceOf(Challenge::class)
        ->and($challenge->type)->toBe('daily')
        ->and($challenge->puzzle->difficulty)->toBe('normal')
        ->and($challenge->visibility)->toBe('public')
        ->and($challenge->status)->toBe('scheduled');
});

test('creates weekly challenge successfully', function () {
    $startDate = Carbon::now()->startOfWeek();
    
    $challenge = $this->challengeService->createWeeklyChallenge('hard', $startDate);

    expect($challenge)->toBeInstanceOf(Challenge::class)
        ->and($challenge->type)->toBe('weekly')
        ->and($challenge->puzzle->difficulty)->toBe('hard');
});

test('admin can create public custom challenge', function () {
    $puzzle = \App\Models\Puzzle::factory()->create();
    $startsAt = Carbon::now()->addHour();
    $endsAt = Carbon::now()->addHours(2);

    $challenge = $this->challengeService->createCustomChallenge(
        $this->admin,
        $puzzle->id,
        $startsAt,
        $endsAt,
        'public'
    );

    expect($challenge)->toBeInstanceOf(Challenge::class)
        ->and($challenge->type)->toBe('custom')
        ->and($challenge->visibility)->toBe('public')
        ->and($challenge->created_by)->toBe($this->admin->id);
});

test('user cannot create public custom challenge', function () {
    $puzzle = \App\Models\Puzzle::factory()->create();
    $startsAt = Carbon::now()->addHour();
    $endsAt = Carbon::now()->addHours(2);

    expect(fn() => $this->challengeService->createCustomChallenge(
        $this->user,
        $puzzle->id,
        $startsAt,
        $endsAt,
        'public'
    ))->toThrow(\InvalidArgumentException::class, 'Solo gli admin possono creare sfide custom pubbliche');
});

test('service integration works end to end', function () {
    // Test integrazione completa: crea sfida, attiva, e verifica stato
    $challenge = $this->challengeService->createDailyChallenge('easy');
    
    expect($challenge->status)->toBe('scheduled');
    
    // Forza la sfida ad essere eleggibile per l'attivazione
    $challenge->update([
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addHour(),
    ]);
    
    // Attiva le sfide programmate
    $activatedCount = $this->challengeService->activateScheduledChallenges();
    
    expect($activatedCount)->toBeGreaterThan(0)
        ->and($challenge->fresh()->status)->toBe('active');
    
    // Verifica che l'utente possa partecipare
    $canParticipate = $this->challengeService->canUserParticipate($this->user, $challenge->fresh());
    expect($canParticipate)->toBeTrue();
});
