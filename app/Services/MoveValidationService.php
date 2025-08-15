<?php
declare(strict_types=1);

namespace App\Services;

use App\Domain\Sudoku\Grid;
use App\Models\ChallengeAttempt;
use App\Models\AttemptMove;
use App\Domain\Sudoku\Contracts\ValidatorInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servizio per validazione anti-cheat delle mosse nelle sfide competitive
 */
class MoveValidationService
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    /**
     * Valida completamente un tentativo alla fine della partita
     */
    public function validateAttemptMoves(ChallengeAttempt $attempt): bool
    {
        try {
            // Carica tutte le mosse ordinate
            $moves = $attempt->moves()
                ->orderBy('move_index')
                ->get();

            if ($moves->isEmpty()) {
                Log::warning('Attempt has no moves recorded', [
                    'attempt_id' => $attempt->id,
                    'user_id' => $attempt->user_id,
                    'challenge_id' => $attempt->challenge_id,
                ]);
                return false;
            }

            // Ricrea il puzzle iniziale
            $initialGrid = $attempt->challenge->puzzle->givens;
            if (is_string($initialGrid)) {
                $initialGrid = json_decode($initialGrid, true);
            }
            
            $currentGrid = $initialGrid;
            
            // Applica ogni mossa e verifica la validità
            foreach ($moves as $moveRecord) {
                $moveData = is_array($moveRecord->payload_json) 
                    ? $moveRecord->payload_json 
                    : json_decode($moveRecord->payload_json, true);

                if (!$this->isValidMove($currentGrid, $moveData)) {
                    Log::warning('Invalid move detected', [
                        'attempt_id' => $attempt->id,
                        'move_index' => $moveRecord->move_index,
                        'move_data' => $moveData,
                    ]);
                    return false;
                }

                // Applica la mossa
                $currentGrid = $this->applyMove($currentGrid, $moveData);
            }

            // Verifica che il risultato finale corrisponda allo stato salvato
            if ($attempt->current_state) {
                $savedState = is_array($attempt->current_state) 
                    ? $attempt->current_state 
                    : json_decode($attempt->current_state, true);
                
                if (!$this->gridsMatch($currentGrid, $savedState)) {
                    Log::warning('Final grid does not match saved state', [
                        'attempt_id' => $attempt->id,
                        'computed_grid' => $currentGrid,
                        'saved_state' => $savedState,
                    ]);
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error validating attempt moves', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Sampling validation - valida solo un campione casuale di mosse
     */
    public function validateAttemptSampling(ChallengeAttempt $attempt, float $sampleRate = 0.2): bool
    {
        $moves = $attempt->moves()
            ->orderBy('move_index')
            ->get();

        if ($moves->isEmpty()) {
            return false;
        }

        // Prendi un campione casuale di mosse
        $sampleSize = max(1, (int)($moves->count() * $sampleRate));
        $sampleMoves = $moves->random($sampleSize);

        $initialGrid = $attempt->challenge->puzzle->givens;
        if (is_string($initialGrid)) {
            $initialGrid = json_decode($initialGrid, true);
        }

        // Verifica che le mosse campionate siano valide nel loro contesto
        foreach ($sampleMoves as $moveRecord) {
            $moveData = is_array($moveRecord->payload_json) 
                ? $moveRecord->payload_json 
                : json_decode($moveRecord->payload_json, true);

            // Ricrea lo stato della griglia fino a questa mossa
            $gridAtMove = $this->reconstructGridAtMove($attempt, $moveRecord->move_index);
            
            if (!$this->isValidMove($gridAtMove, $moveData)) {
                Log::warning('Invalid move detected in sampling', [
                    'attempt_id' => $attempt->id,
                    'move_index' => $moveRecord->move_index,
                    'move_data' => $moveData,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se una singola mossa è valida nel contesto della griglia
     */
    public function isValidMove(array $grid, array $moveData): bool
    {
        // Verifica struttura del dato mossa
        if (!isset($moveData['type'])) {
            return false;
        }

        switch ($moveData['type']) {
            case 'set_value':
                return $this->validateSetValueMove($grid, $moveData);
            
            case 'clear_value':
                return $this->validateClearValueMove($grid, $moveData);
            
            case 'set_candidates':
                return $this->validateSetCandidatesMove($grid, $moveData);
            
            default:
                Log::warning('Unknown move type', ['type' => $moveData['type']]);
                return false;
        }
    }

    /**
     * Valida una mossa di impostazione valore
     */
    private function validateSetValueMove(array $grid, array $moveData): bool
    {
        if (!isset($moveData['row'], $moveData['col'], $moveData['value'])) {
            return false;
        }

        $row = $moveData['row'];
        $col = $moveData['col'];
        $value = $moveData['value'];

        // Verifica coordinate valide
        if ($row < 0 || $row > 8 || $col < 0 || $col > 8) {
            return false;
        }

        // Verifica valore valido
        if ($value < 1 || $value > 9) {
            return false;
        }

        // Verifica che la cella non sia già occupata da un "given"
        $initialGrid = $moveData['initial_grid'] ?? $grid;
        if (isset($initialGrid[$row][$col]) && $initialGrid[$row][$col] !== null) {
            return false; // Non si può modificare una cella given
        }

        // Verifica che il valore non crei conflitti (opzionale - potrebbe essere una mossa errata intenzionale)
        $gridObject = Grid::fromArray($grid);
        return $this->validator->canPlaceValue($gridObject, $row, $col, $value);
    }

    /**
     * Valida una mossa di cancellazione valore
     */
    private function validateClearValueMove(array $grid, array $moveData): bool
    {
        if (!isset($moveData['row'], $moveData['col'])) {
            return false;
        }

        $row = $moveData['row'];
        $col = $moveData['col'];

        // Verifica coordinate valide
        if ($row < 0 || $row > 8 || $col < 0 || $col > 8) {
            return false;
        }

        // Verifica che non sia una cella given
        $initialGrid = $moveData['initial_grid'] ?? $grid;
        if (isset($initialGrid[$row][$col]) && $initialGrid[$row][$col] !== null) {
            return false;
        }

        return true;
    }

    /**
     * Valida una mossa di impostazione candidati
     */
    private function validateSetCandidatesMove(array $grid, array $moveData): bool
    {
        if (!isset($moveData['row'], $moveData['col'], $moveData['candidates'])) {
            return false;
        }

        $row = $moveData['row'];
        $col = $moveData['col'];
        $candidates = $moveData['candidates'];

        // Verifica coordinate valide
        if ($row < 0 || $row > 8 || $col < 0 || $col > 8) {
            return false;
        }

        // Verifica che candidates sia un array di numeri validi
        if (!is_array($candidates)) {
            return false;
        }

        foreach ($candidates as $candidate) {
            if (!is_int($candidate) || $candidate < 1 || $candidate > 9) {
                return false;
            }
        }

        return true;
    }

    /**
     * Applica una mossa alla griglia
     */
    private function applyMove(array $grid, array $moveData): array
    {
        switch ($moveData['type']) {
            case 'set_value':
                $grid[$moveData['row']][$moveData['col']] = $moveData['value'];
                break;
            
            case 'clear_value':
                $grid[$moveData['row']][$moveData['col']] = null;
                break;
            
            // Per i candidati non modifichiamo la griglia principale
            case 'set_candidates':
                // I candidati sono metadata, non modificano la griglia risolta
                break;
        }

        return $grid;
    }

    /**
     * Ricostruisce lo stato della griglia fino a una specifica mossa
     */
    private function reconstructGridAtMove(ChallengeAttempt $attempt, int $moveIndex): array
    {
        $moves = $attempt->moves()
            ->where('move_index', '<=', $moveIndex)
            ->orderBy('move_index')
            ->get();

        $initialGrid = $attempt->challenge->puzzle->givens;
        if (is_string($initialGrid)) {
            $initialGrid = json_decode($initialGrid, true);
        }
        
        $currentGrid = $initialGrid;

        foreach ($moves as $moveRecord) {
            $moveData = is_array($moveRecord->payload_json) 
                ? $moveRecord->payload_json 
                : json_decode($moveRecord->payload_json, true);
            
            $currentGrid = $this->applyMove($currentGrid, $moveData);
        }

        return $currentGrid;
    }

    /**
     * Verifica se due griglie sono identiche
     */
    private function gridsMatch(array $grid1, array $grid2): bool
    {
        if (count($grid1) !== count($grid2)) {
            return false;
        }

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if (($grid1[$row][$col] ?? null) !== ($grid2[$row][$col] ?? null)) {
                    return false;
                }
            }
        }

        return true;
    }
}


