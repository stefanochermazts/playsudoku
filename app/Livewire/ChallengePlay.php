<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Sudoku\Grid;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\AttemptMove;
use Livewire\Attributes\On;
use Livewire\Component;

class ChallengePlay extends Component
{
    public Challenge $challenge;
    public ?ChallengeAttempt $attempt = null;
    public array $grid = [];
    public array $candidates = [];
    public int $selectedRow = 0;
    public int $selectedCol = 0;
    public bool $inputMode = true; // true = numbers, false = candidates
    public bool $showCandidates = true;
    public array $moveHistory = [];
    public int $currentMoveIndex = -1;
    public int $errorCount = 0;
    public int $elapsedTime = 0; // in seconds
    public bool $timerRunning = false;
    public bool $isCompleted = false;
    public bool $isReadOnly = false;
    public array $conflicts = [];

    public function mount(int $challengeId): void
    {
        $this->challenge = Challenge::with('puzzle')->findOrFail($challengeId);
        
        // Verifica accesso alla sfida
        if ($this->challenge->status !== 'active' || $this->challenge->ends_at <= now()) {
            abort(403, 'Questa sfida non è più disponibile.');
        }

        // Carica o crea tentativo
        $this->loadOrCreateAttempt();
        
        // Inizializza la griglia
        $this->initializeGrid();
        
        // Controlla se le hints sono bloccate
        if (isset($this->challenge->settings['hints_allowed']) && !$this->challenge->settings['hints_allowed']) {
            $this->showCandidates = false;
        }
    }

    public function render()
    {
        $completionPercentage = $this->calculateCompletionPercentage();
        
        return view('livewire.challenge-play', [
            'completionPercentage' => $completionPercentage,
            'timeLimit' => $this->challenge->settings['time_limit'] ?? null,
            'hintsAllowed' => $this->challenge->settings['hints_allowed'] ?? true,
        ]);
    }

    private function loadOrCreateAttempt(): void
    {
        $this->attempt = ChallengeAttempt::where('user_id', auth()->id())
            ->where('challenge_id', $this->challenge->id)
            ->first();

        if (!$this->attempt) {
            // Crea nuovo tentativo
            $this->attempt = ChallengeAttempt::create([
                'user_id' => auth()->id(),
                'challenge_id' => $this->challenge->id,
                'duration_ms' => 0,
                'errors_count' => 0,
                'hints_used' => 0,
                'valid' => true,
            ]);
        }

        // Se già completato, imposta come read-only
        if ($this->attempt->completed_at) {
            $this->isCompleted = true;
            $this->isReadOnly = true;
            $this->timerRunning = false;
        }
    }

    private function initializeGrid(): void
    {
        // Se c'è stato salvato, caricalo
        if ($this->attempt && $this->attempt->current_state) {
            $this->grid = $this->attempt->current_state;
        } else {
            // Carica dalla griglia del puzzle
            $puzzleGrid = $this->challenge->puzzle->givens;
            $this->grid = is_array($puzzleGrid) ? $puzzleGrid : json_decode($puzzleGrid, true);
        }

        // Inizializza candidati
        $this->initializeCandidates();
        
        // Seleziona la prima cella vuota
        $this->selectFirstEmptyCell();
    }

    private function initializeCandidates(): void
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] === null) {
                    $this->candidates[$row][$col] = [];
                } else {
                    $this->candidates[$row][$col] = [];
                }
            }
        }
    }

    private function selectFirstEmptyCell(): void
    {
        if ($this->isReadOnly) return;
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] === null) {
                    $this->selectedRow = $row;
                    $this->selectedCol = $col;
                    return;
                }
            }
        }
    }

    public function selectCell(int $row, int $col): void
    {
        if ($this->isReadOnly) return;
        
        $this->selectedRow = $row;
        $this->selectedCol = $col;
    }

    public function setCellValue(int $value): void
    {
        if ($this->isReadOnly) return;
        
        $row = $this->selectedRow;
        $col = $this->selectedCol;
        
        // Non modificare celle del puzzle originale
        $originalGrid = $this->challenge->puzzle->givens;
        if (is_string($originalGrid)) {
            $originalGrid = json_decode($originalGrid, true);
        }
        
        if ($originalGrid[$row][$col] !== null) {
            return;
        }

        $oldValue = $this->grid[$row][$col];
        $this->grid[$row][$col] = $value;
        
        // Avvia timer al primo input
        if (!$this->timerRunning && !$this->isCompleted) {
            $this->timerRunning = true;
        }

        // Aggiungi alla cronologia mosse
        $this->addMoveToHistory($row, $col, $oldValue, $value);
        
        // Verifica conflitti
        $this->checkForConflicts();
        
        // Salva stato
        $this->saveAttemptState();
        
        // Controlla completamento
        $this->checkCompletion();
    }

    public function toggleInputMode(): void
    {
        if ($this->challenge->settings['hints_allowed'] ?? true) {
            $this->inputMode = !$this->inputMode;
        }
    }

    public function handleKeyInput(?string $key = null): void
    {
        if (!$key || $this->isReadOnly) return;
        
        if (is_numeric($key) && $key >= '1' && $key <= '9') {
            if ($this->inputMode) {
                $this->setCellValue((int)$key);
            } else {
                $this->toggleCandidate((int)$key);
            }
        } elseif ($key === 'Backspace' || $key === 'Delete') {
            $this->setCellValue(null);
        } elseif (in_array($key, ['c', 'C'])) {
            $this->toggleInputMode();
        } elseif (in_array($key, ['u', 'U'])) {
            $this->undo();
        } elseif (in_array($key, ['r', 'R'])) {
            $this->redo();
        } elseif ($key === 'ArrowUp') {
            $this->moveSelection(-1, 0);
        } elseif ($key === 'ArrowDown') {
            $this->moveSelection(1, 0);
        } elseif ($key === 'ArrowLeft') {
            $this->moveSelection(0, -1);
        } elseif ($key === 'ArrowRight') {
            $this->moveSelection(0, 1);
        }
    }

    private function moveSelection(int $deltaRow, int $deltaCol): void
    {
        $newRow = max(0, min(8, $this->selectedRow + $deltaRow));
        $newCol = max(0, min(8, $this->selectedCol + $deltaCol));
        
        $this->selectedRow = $newRow;
        $this->selectedCol = $newCol;
    }

    private function toggleCandidate(int $number): void
    {
        if (!($this->challenge->settings['hints_allowed'] ?? true)) return;
        
        $row = $this->selectedRow;
        $col = $this->selectedCol;
        
        if ($this->grid[$row][$col] !== null) return;
        
        $candidates = &$this->candidates[$row][$col];
        
        if (in_array($number, $candidates)) {
            $candidates = array_values(array_filter($candidates, fn($n) => $n !== $number));
        } else {
            $candidates[] = $number;
            sort($candidates);
        }
    }

    private function addMoveToHistory(int $row, int $col, $oldValue, $newValue): void
    {
        // Rimuovi mosse future se siamo nel mezzo della cronologia
        if ($this->currentMoveIndex < count($this->moveHistory) - 1) {
            $this->moveHistory = array_slice($this->moveHistory, 0, $this->currentMoveIndex + 1);
        }
        
        $this->moveHistory[] = [
            'row' => $row,
            'col' => $col,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->currentMoveIndex = count($this->moveHistory) - 1;
        
        // Limita cronologia a 100 mosse
        if (count($this->moveHistory) > 100) {
            $this->moveHistory = array_slice($this->moveHistory, -100);
            $this->currentMoveIndex = count($this->moveHistory) - 1;
        }
    }

    public function undo(): void
    {
        if ($this->isReadOnly || $this->currentMoveIndex < 0) return;
        
        $move = $this->moveHistory[$this->currentMoveIndex];
        $this->grid[$move['row']][$move['col']] = $move['old_value'];
        $this->currentMoveIndex--;
        
        $this->checkForConflicts();
        $this->saveAttemptState();
    }

    public function redo(): void
    {
        if ($this->isReadOnly || $this->currentMoveIndex >= count($this->moveHistory) - 1) return;
        
        $this->currentMoveIndex++;
        $move = $this->moveHistory[$this->currentMoveIndex];
        $this->grid[$move['row']][$move['col']] = $move['new_value'];
        
        $this->checkForConflicts();
        $this->saveAttemptState();
    }

    private function checkForConflicts(): void
    {
        $this->conflicts = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $value = $this->grid[$row][$col];
                if ($value === null) continue;
                
                // Controlla conflitti riga
                for ($c = 0; $c < 9; $c++) {
                    if ($c !== $col && $this->grid[$row][$c] === $value) {
                        $this->conflicts[] = ['row' => $row, 'col' => $col];
                        $this->conflicts[] = ['row' => $row, 'col' => $c];
                    }
                }
                
                // Controlla conflitti colonna
                for ($r = 0; $r < 9; $r++) {
                    if ($r !== $row && $this->grid[$r][$col] === $value) {
                        $this->conflicts[] = ['row' => $row, 'col' => $col];
                        $this->conflicts[] = ['row' => $r, 'col' => $col];
                    }
                }
                
                // Controlla conflitti box 3x3
                $boxRow = intval($row / 3) * 3;
                $boxCol = intval($col / 3) * 3;
                
                for ($r = $boxRow; $r < $boxRow + 3; $r++) {
                    for ($c = $boxCol; $c < $boxCol + 3; $c++) {
                        if (($r !== $row || $c !== $col) && $this->grid[$r][$c] === $value) {
                            $this->conflicts[] = ['row' => $row, 'col' => $col];
                            $this->conflicts[] = ['row' => $r, 'col' => $c];
                        }
                    }
                }
            }
        }
        
        // Aggiorna contatore errori
        $this->errorCount = count(array_unique(array_map(fn($c) => $c['row'] . ',' . $c['col'], $this->conflicts))) / 2;
    }

    private function calculateCompletionPercentage(): int
    {
        $filled = 0;
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] !== null) {
                    $filled++;
                }
            }
        }
        return intval(($filled / 81) * 100);
    }

    private function checkCompletion(): void
    {
        if ($this->calculateCompletionPercentage() === 100 && empty($this->conflicts)) {
            $this->completeChallenge();
        }
    }

    private function completeChallenge(): void
    {
        if ($this->isCompleted) return;
        
        $this->isCompleted = true;
        $this->isReadOnly = true;
        $this->timerRunning = false;
        
        // Aggiorna il tentativo
        $this->attempt->update([
            'duration_ms' => $this->elapsedTime * 1000,
            'errors_count' => $this->errorCount,
            'completed_at' => now(),
            'final_state' => $this->grid,
        ]);
        
        // Salva tutte le mosse
        foreach ($this->moveHistory as $index => $move) {
            AttemptMove::create([
                'attempt_id' => $this->attempt->id,
                'move_index' => $index,
                'payload_json' => $move,
            ]);
        }
        
        $this->dispatch('challenge-completed', challengeId: $this->challenge->id);
    }

    private function saveAttemptState(): void
    {
        if (!$this->attempt) return;
        
        $this->attempt->update([
            'current_state' => $this->grid,
            'errors_count' => $this->errorCount,
        ]);
    }

    #[On('tick-timer')]
    public function tickTimer(): void
    {
        if ($this->timerRunning && !$this->isCompleted) {
            $this->elapsedTime++;
        }
    }

    public function getFormattedTime(): string
    {
        $minutes = intval($this->elapsedTime / 60);
        $seconds = $this->elapsedTime % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function pauseChallenge()
    {
        $this->timerRunning = false;
        $this->saveAttemptState();
        
        return redirect()->route('challenges.index');
    }
}