<?php
declare(strict_types=1);

namespace App\Services;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use App\Models\Challenge;
use App\Models\Puzzle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Servizio per la gestione delle sfide Sudoku
 */
class ChallengeService
{
    public function __construct(
        private readonly Generator $generator,
        private readonly Validator $validator
    ) {}

    /**
     * Crea una nuova sfida daily
     */
    public function createDailyChallenge(
        string $difficulty = 'normal',
        ?Carbon $startDate = null
    ): Challenge {
        $startDate = $startDate ?? now();
        $seed = $this->generateDailySeed($startDate);
        
        // Verifica se esiste già una sfida daily per questa data
        $existingChallenge = Challenge::where('type', 'daily')
            ->whereDate('starts_at', $startDate->toDateString())
            ->first();
            
        if ($existingChallenge) {
            return $existingChallenge;
        }

        $puzzle = $this->getOrCreatePuzzle($seed, $difficulty);

        return DB::transaction(function () use ($puzzle, $startDate) {
            return Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'daily',
                'starts_at' => $startDate->startOfDay(),
                'ends_at' => $startDate->endOfDay(),
                'visibility' => 'public',
                'status' => 'scheduled',
                'created_by' => $this->getSystemUserId(),
            ]);
        });
    }

    /**
     * Crea una nuova sfida weekly
     */
    public function createWeeklyChallenge(
        string $difficulty = 'hard',
        ?Carbon $startDate = null
    ): Challenge {
        $startDate = $startDate ?? now()->startOfWeek();
        $seed = $this->generateWeeklySeed($startDate);
        
        // Verifica se esiste già una sfida weekly per questa settimana
        $existingChallenge = Challenge::where('type', 'weekly')
            ->whereBetween('starts_at', [$startDate, $startDate->copy()->endOfWeek()])
            ->first();
            
        if ($existingChallenge) {
            return $existingChallenge;
        }

        $puzzle = $this->getOrCreatePuzzle($seed, $difficulty);

        return DB::transaction(function () use ($puzzle, $startDate) {
            return Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'weekly',
                'starts_at' => $startDate->startOfWeek(),
                'ends_at' => $startDate->endOfWeek(),
                'visibility' => 'public',
                'status' => 'scheduled',
                'created_by' => $this->getSystemUserId(),
            ]);
        });
    }

    /**
     * Crea una sfida custom (solo admin)
     */
    public function createCustomChallenge(
        User $creator,
        int $puzzleId,
        Carbon $startsAt,
        Carbon $endsAt,
        string $visibility = 'public'
    ): Challenge {
        // Policy: solo admin può creare sfide custom pubbliche
        if ($visibility === 'public' && !$creator->isAdmin()) {
            throw new \InvalidArgumentException('Solo gli admin possono creare sfide custom pubbliche');
        }

        $puzzle = Puzzle::findOrFail($puzzleId);

        return DB::transaction(function () use ($puzzle, $creator, $startsAt, $endsAt, $visibility) {
            return Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'custom',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'visibility' => $visibility,
                'status' => 'scheduled',
                'created_by' => $creator->id,
            ]);
        });
    }

    /**
     * Attiva le sfide programmate
     */
    public function activateScheduledChallenges(): int
    {
        $now = now();
        
        return DB::transaction(function () use ($now) {
            return Challenge::where('status', 'scheduled')
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>', $now)
                ->update(['status' => 'active']);
        });
    }

    /**
     * Completa le sfide scadute
     */
    public function completeExpiredChallenges(): int
    {
        $now = now();
        
        return DB::transaction(function () use ($now) {
            return Challenge::whereIn('status', ['scheduled', 'active'])
                ->where('ends_at', '<=', $now)
                ->update(['status' => 'completed']);
        });
    }

    /**
     * Ottiene le sfide attive
     */
    public function getActiveChallenges(): Collection
    {
        return Cache::remember('active_challenges', 300, function () {
            return Challenge::active()
                ->public()
                ->with(['puzzle', 'creator'])
                ->orderBy('type')
                ->orderBy('ends_at')
                ->get();
        });
    }

    /**
     * Ottiene le sfide disponibili per un utente
     */
    public function getAvailableChallenges(User $user): Collection
    {
        return Challenge::active()
            ->public()
            ->whereDoesntHave('attempts', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereNotNull('completed_at');
            })
            ->with(['puzzle'])
            ->orderBy('type')
            ->orderBy('ends_at')
            ->get();
    }

    /**
     * Verifica se un utente può partecipare a una sfida
     */
    public function canUserParticipate(User $user, Challenge $challenge): bool
    {
        if (!$challenge->isActive()) {
            return false;
        }

        if ($challenge->visibility === 'private' && $challenge->created_by !== $user->id) {
            return false;
        }

        // Verifica se l'utente ha già completato la sfida
        return !$challenge->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    /**
     * Ottiene o crea un puzzle con seed e difficoltà specifici
     */
    public function getOrCreatePuzzle(int $seed, string $difficulty): Puzzle
    {
        // Prima cerca se esiste già
        $existingPuzzle = Puzzle::where('seed', $seed)
            ->where('difficulty', $difficulty)
            ->first();

        if ($existingPuzzle) {
            return $existingPuzzle;
        }

        // Crea un nuovo puzzle
        return DB::transaction(function () use ($seed, $difficulty) {
            $puzzle = $this->generator->generatePuzzleWithDifficulty($seed, $difficulty);
            $solution = $this->generator->generateCompleteGrid($seed);

            return Puzzle::create([
                'seed' => $seed,
                'givens' => $puzzle->toArray(),
                'solution' => $solution->toArray(),
                'difficulty' => $difficulty,
            ]);
        });
    }

    /**
     * Genera seed deterministico per sfida daily
     */
    private function generateDailySeed(Carbon $date): int
    {
        $dateString = $date->toDateString(); // YYYY-MM-DD
        return abs(crc32("daily_" . $dateString)) % 999999 + 1000;
    }

    /**
     * Genera seed deterministico per sfida weekly
     */
    private function generateWeeklySeed(Carbon $date): int
    {
        $year = $date->year;
        $week = $date->weekOfYear;
        return abs(crc32("weekly_{$year}_w{$week}")) % 999999 + 1000;
    }

    /**
     * Ottiene l'ID dell'utente sistema per sfide automatiche
     */
    private function getSystemUserId(): int
    {
        // Cerca un utente admin per le sfide di sistema
        $systemUser = User::where('role', 'admin')->first();
        
        if (!$systemUser) {
            throw new \RuntimeException('Nessun utente admin trovato per creare sfide di sistema');
        }

        return $systemUser->id;
    }

    /**
     * Invalida la cache delle sfide attive
     */
    public function clearActiveChallengesCache(): void
    {
        Cache::forget('active_challenges');
    }

    /**
     * Statistiche sfide
     */
    public function getChallengeStats(): array
    {
        return Cache::remember('challenge_stats', 3600, function () {
            return [
                'total_challenges' => Challenge::count(),
                'active_challenges' => Challenge::active()->count(),
                'daily_challenges' => Challenge::byType('daily')->count(),
                'weekly_challenges' => Challenge::byType('weekly')->count(),
                'custom_challenges' => Challenge::byType('custom')->count(),
                'total_participants' => DB::table('challenge_attempts')
                    ->distinct('user_id')
                    ->count(),
            ];
        });
    }
}
