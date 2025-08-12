<?php
declare(strict_types=1);

namespace App\Livewire;

/**
 * Trait con metodi helper per SudokuBoard
 */
trait SudokuBoardHelpers
{
    /**
     * Verifica se una cella è given (predefinita)
     */
    private function isGivenCell(int $row, int $col): bool
    {
        return $this->initialGrid[$row][$col] !== null;
    }

    /**
     * Registra una mossa per undo/redo
     */
    private function recordMove(int $row, int $col, mixed $oldValue, mixed $newValue, string $type): void
    {
        // Rimuovi le mosse successive se stiamo nel mezzo della storia
        if ($this->currentMoveIndex < count($this->moves) - 1) {
            $this->moves = array_slice($this->moves, 0, $this->currentMoveIndex + 1);
        }

        // Aggiungi la nuova mossa
        $this->moves[] = [
            'type' => $type,
            'row' => $row,
            'col' => $col,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
            'oldCandidates' => $this->candidates[$row][$col] ?? [],
            'timestamp' => now()->timestamp,
        ];

        $this->currentMoveIndex++;

        // Limita la storia undo
        if (count($this->moves) > $this->maxUndoSteps) {
            $this->moves = array_slice($this->moves, -$this->maxUndoSteps);
            $this->currentMoveIndex = count($this->moves) - 1;
        }
    }

    /**
     * Verifica completamento del gioco
     */
    private function checkCompletion(): void
    {
        $isComplete = true;
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] === null) {
                    $isComplete = false;
                    break 2;
                }
            }
        }

        if ($isComplete && count($this->findConflicts()) === 0) {
            $this->isCompleted = true;
            $this->stopTimer();
            
            if ($this->announceChanges) {
                $this->lastAction = "Congratulazioni! Puzzle completato in " . $this->getFormattedTime();
            }
            
            $this->dispatch('puzzle-completed', time: $this->timeElapsed);
        }
    }

    /**
     * Trova conflitti nella griglia
     */
    private function findConflicts(): array
    {
        $conflicts = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $value = $this->grid[$row][$col];
                if ($value === null) continue;
                
                if ($this->hasConflict($row, $col, $value)) {
                    $conflicts[] = ['row' => $row, 'col' => $col];
                }
            }
        }
        
        return $conflicts;
    }

    /**
     * Verifica se una cella ha conflitti
     */
    private function hasConflict(int $row, int $col, int $value): bool
    {
        // Verifica riga
        for ($c = 0; $c < 9; $c++) {
            if ($c !== $col && $this->grid[$row][$c] === $value) {
                return true;
            }
        }

        // Verifica colonna
        for ($r = 0; $r < 9; $r++) {
            if ($r !== $row && $this->grid[$r][$col] === $value) {
                return true;
            }
        }

        // Verifica box 3x3
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;

        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $this->grid[$r][$c] === $value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Calcola percentuale completamento
     */
    private function getCompletionPercentage(): int
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

    /**
     * Avvia il timer
     */
    public function startTimer(): void
    {
        $this->timerRunning = true;
        $this->dispatch('start-timer');
    }

    /**
     * Ferma il timer
     */
    public function stopTimer(): void
    {
        $this->timerRunning = false;
        $this->dispatch('stop-timer');
    }

    /**
     * Formatta il tempo
     */
    public function getFormattedTime(): string
    {
        $minutes = intval($this->timeElapsed / 60);
        $seconds = $this->timeElapsed % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Ottiene il MoveLog per il replay
     */
    public function getMoveLog(): \App\Domain\Sudoku\MoveLog
    {
        $moveLog = new \App\Domain\Sudoku\MoveLog();
        
        foreach ($this->moves as $moveData) {
            if ($moveData['type'] === 'set_value') {
                $move = $moveData['newValue'] 
                    ? \App\Domain\Sudoku\ValueObjects\Move::setValue($moveData['row'], $moveData['col'], $moveData['newValue'], $moveData['timestamp'])
                    : \App\Domain\Sudoku\ValueObjects\Move::clearValue($moveData['row'], $moveData['col'], $moveData['timestamp']);
                
                $moveLog->addMove($move);
            }
        }
        
        return $moveLog;
    }

    /**
     * Ottiene la griglia come Grid del dominio
     */
    public function getDomainGrid(): \App\Domain\Sudoku\Grid
    {
        return \App\Domain\Sudoku\Grid::fromArray($this->grid);
    }
}
