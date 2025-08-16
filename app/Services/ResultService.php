<?php
declare(strict_types=1);

namespace App\Services;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\MoveLog;
use App\Domain\Sudoku\Validator;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\AttemptMove;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servizio per la validazione e gestione dei risultati delle sfide
 */
class ResultService
{
    public function __construct(
        private readonly Validator $validator
    ) {}

    /**
     * Valida e registra il completamento di una sfida
     */
    public function submitChallengeCompletion(
        User $user,
        Challenge $challenge,
        Grid $finalBoard,
        MoveLog $moveLog,
        int $durationMs,
        int $errorsCount = 0,
        int $hintsUsed = 0
    ): ChallengeAttempt {
        // Verifica che l'utente possa completare questa sfida
        if (!$challenge->isActive()) {
            throw new \InvalidArgumentException('La sfida non è più attiva');
        }

        if (!$challenge->canUserParticipate($user)) {
            throw new \InvalidArgumentException('L\'utente non può partecipare a questa sfida');
        }

        // Validazione della soluzione
        $validation = $this->validateCompletion($challenge, $finalBoard, $moveLog);
        
        if (!$validation['is_valid']) {
            throw new \InvalidArgumentException(
                'Soluzione non valida: ' . implode(', ', $validation['errors'])
            );
        }

        $attempt = DB::transaction(function () use (
            $user, $challenge, $finalBoard, $moveLog, $durationMs, $errorsCount, $hintsUsed, $validation
        ) {
            // Crea o aggiorna il tentativo
            $attempt = ChallengeAttempt::updateOrCreate(
                [
                    'challenge_id' => $challenge->id,
                    'user_id' => $user->id,
                ],
                [
                    'duration_ms' => $durationMs,
                    'errors_count' => $errorsCount,
                    'hints_used' => $hintsUsed,
                    'completed_at' => now(),
                    'valid' => $validation['is_valid'],
                ]
            );

            // Salva le mosse per replay
            $this->saveMoves($attempt, $moveLog);

            return $attempt;
        });

        // Award badges
        app(BadgeService::class)->onChallengeCompleted($user, [
            'duration_ms' => $durationMs,
            'errors_count' => $errorsCount,
            'hints_used' => $hintsUsed,
            'difficulty' => $challenge->puzzle->difficulty ?? 'normal',
            'type' => $challenge->type,
        ]);

        // Award season points (provisional placement = 1, refined later when leaderboard is resolved)
        app(SeasonService::class)->awardPointsForChallenge($user, $challenge, 1);

        return $attempt;
    }

    /**
     * Valida il completamento di una sfida
     */
    public function validateCompletion(
        Challenge $challenge,
        Grid $finalBoard,
        MoveLog $moveLog
    ): array {
        $errors = [];
        $isValid = true;

        // 1. Verifica che la board finale sia valida
        if (!$this->validator->isValid($finalBoard)) {
            $errors[] = 'La griglia finale contiene errori';
            $isValid = false;
        }

        // 2. Verifica che la board sia completa
        if (!$finalBoard->isComplete()) {
            $errors[] = 'La griglia non è completa';
            $isValid = false;
        }

        // 3. Verifica che la soluzione corrisponda
        $expectedSolution = $challenge->puzzle->getSolutionGrid();
        if (!$this->gridsMatch($finalBoard, $expectedSolution)) {
            $errors[] = 'La soluzione non corrisponde a quella attesa';
            $isValid = false;
        }

        // 4. Verifica coerenza delle mosse (se fornite)
        if ($moveLog->count() > 0) {
            $moveValidation = $this->validateMoveSequence($challenge->puzzle->toGrid(), $moveLog);
            if (!$moveValidation['is_valid']) {
                $errors = array_merge($errors, $moveValidation['errors']);
                $isValid = false;
            }
        }

        return [
            'is_valid' => $isValid,
            'errors' => $errors,
            'final_board_valid' => $this->validator->isValid($finalBoard),
            'solution_matches' => $this->gridsMatch($finalBoard, $expectedSolution),
            'moves_consistent' => $moveLog->count() === 0 || $this->validateMoveSequence($challenge->puzzle->toGrid(), $moveLog)['is_valid'],
        ];
    }

    /**
     * Valida la sequenza di mosse
     */
    public function validateMoveSequence(Grid $initialGrid, MoveLog $moveLog): array
    {
        $errors = [];
        $currentGrid = $initialGrid;
        $isValid = true;

        try {
            // Applica ogni mossa in sequenza
            foreach ($moveLog->getMoves() as $index => $move) {
                // Verifica che la mossa sia applicabile alla griglia corrente
                $cell = $currentGrid->getCell($move->row, $move->col);
                
                // Se la cella è given, non può essere modificata
                if ($cell->isGiven) {
                    $errors[] = "Mossa {$index}: tentativo di modificare una cella given ({$move->row}, {$move->col})";
                    $isValid = false;
                    continue;
                }

                // Applica la mossa
                $currentGrid = match ($move->type) {
                    'set_value' => $currentGrid->setCell($move->row, $move->col, $move->value),
                    'clear_value' => $currentGrid->clearCell($move->row, $move->col),
                    'set_candidates' => $currentGrid->updateCellCandidates($move->row, $move->col, $move->candidates),
                    default => throw new \InvalidArgumentException("Tipo mossa non valido: {$move->type}")
                };
            }
        } catch (\Exception $e) {
            $errors[] = "Errore nell'applicazione delle mosse: " . $e->getMessage();
            $isValid = false;
        }

        return [
            'is_valid' => $isValid,
            'errors' => $errors,
            'final_grid' => $currentGrid,
        ];
    }

    /**
     * Calcola il ranking per tie-break
     * Criterio: (1) meno errori, (2) timestamp più antico, (3) meno hint
     */
    public function calculateTieBreakRanking(ChallengeAttempt $attempt): array
    {
        return [
            'duration_ms' => $attempt->duration_ms,
            'errors_count' => $attempt->errors_count,
            'completed_at_timestamp' => $attempt->completed_at->timestamp,
            'hints_used' => $attempt->hints_used,
        ];
    }

    /**
     * Confronta due tentativi per determinare il vincitore
     * Ritorna: -1 se attempt1 vince, 1 se attempt2 vince, 0 se pari
     */
    public function compareAttempts(ChallengeAttempt $attempt1, ChallengeAttempt $attempt2): int
    {
        // 1. Confronta durata (minore è meglio)
        if ($attempt1->duration_ms !== $attempt2->duration_ms) {
            return $attempt1->duration_ms <=> $attempt2->duration_ms;
        }

        // 2. Confronta errori (meno è meglio)
        if ($attempt1->errors_count !== $attempt2->errors_count) {
            return $attempt1->errors_count <=> $attempt2->errors_count;
        }

        // 3. Confronta timestamp completamento (più antico è meglio)
        if ($attempt1->completed_at->timestamp !== $attempt2->completed_at->timestamp) {
            return $attempt1->completed_at->timestamp <=> $attempt2->completed_at->timestamp;
        }

        // 4. Confronta hint usati (meno è meglio)
        return $attempt1->hints_used <=> $attempt2->hints_used;
    }

    /**
     * Ottiene il miglior tentativo di un utente per una sfida
     */
    public function getBestAttemptForUser(User $user, Challenge $challenge): ?ChallengeAttempt
    {
        $attempts = $challenge->attempts()
            ->where('user_id', $user->id)
            ->valid()
            ->completed()
            ->get();

        if ($attempts->isEmpty()) {
            return null;
        }

        return $attempts->reduce(function ($best, $current) {
            if ($best === null) {
                return $current;
            }

            return $this->compareAttempts($current, $best) < 0 ? $current : $best;
        });
    }

    /**
     * Verifica se due griglie sono identiche
     */
    private function gridsMatch(Grid $grid1, Grid $grid2): bool
    {
        $array1 = $grid1->toArray();
        $array2 = $grid2->toArray();

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($array1[$row][$col] !== $array2[$row][$col]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Salva le mosse di un tentativo per replay
     */
    private function saveMoves(ChallengeAttempt $attempt, MoveLog $moveLog): void
    {
        // Rimuovi mosse esistenti (in caso di re-submit)
        $attempt->moves()->delete();

        $moves = [];
        foreach ($moveLog->getMoves() as $index => $move) {
            $moves[] = AttemptMove::fromDomainMove($attempt->id, $index, $move);
        }

        if (!empty($moves)) {
            AttemptMove::insert(array_map(fn($move) => $move->toArray(), $moves));
        }
    }

    /**
     * Ricostruisce il MoveLog da un tentativo salvato
     */
    public function reconstructMoveLog(ChallengeAttempt $attempt): MoveLog
    {
        $moveLog = new MoveLog();

        $savedMoves = $attempt->moves()->orderedByIndex()->get();
        
        foreach ($savedMoves as $savedMove) {
            $domainMove = $savedMove->toDomainMove();
            $moveLog->addMove($domainMove);
        }

        return $moveLog;
    }

    /**
     * Verifica l'integrità di un tentativo
     */
    public function verifyAttemptIntegrity(ChallengeAttempt $attempt): array
    {
        $issues = [];

        // Verifica che esistano le mosse se il tentativo è completato
        if ($attempt->isCompleted() && $attempt->moves()->count() === 0) {
            $issues[] = 'Tentativo completato senza mosse registrate';
        }

        // Verifica che la durata sia ragionevole
        if ($attempt->duration_ms !== null) {
            $minDuration = 10000; // 10 secondi minimo
            $maxDuration = 24 * 60 * 60 * 1000; // 24 ore massimo

            if ($attempt->duration_ms < $minDuration) {
                $issues[] = "Durata troppo breve: {$attempt->duration_ms}ms";
            }

            if ($attempt->duration_ms > $maxDuration) {
                $issues[] = "Durata troppo lunga: {$attempt->duration_ms}ms";
            }
        }

        // Verifica che il numero di errori sia ragionevole
        if ($attempt->errors_count > 81) { // Non può sbagliare più celle di quelle disponibili
            $issues[] = "Numero di errori irrealistico: {$attempt->errors_count}";
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
        ];
    }
}
