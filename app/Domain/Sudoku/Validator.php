<?php
declare(strict_types=1);

namespace App\Domain\Sudoku;

use App\Domain\Sudoku\Contracts\ValidatorInterface;
use App\Domain\Sudoku\Exceptions\InvalidGridException;

/**
 * Validatore per griglie Sudoku.
 * 
 * Verifica:
 * - Consistenza delle regole Sudoku (no duplicati in riga/colonna/box)
 * - Unicità della soluzione
 * - Validità delle mosse
 */
final class Validator implements ValidatorInterface
{
    /**
     * Verifica se una griglia è valida (nessun conflitto)
     */
    public function isValid(Grid $grid): bool
    {
        return $grid->isValid();
    }

    /**
     * Verifica se una griglia ha una soluzione unica
     */
    public function hasUniqueSolution(Grid $grid): bool
    {
        $solutions = $this->findAllSolutions($grid, 2); // Trova max 2 soluzioni
        return count($solutions) === 1;
    }

    /**
     * Ottiene tutti gli errori di validazione in una griglia
     * 
     * @return array<string, mixed>
     */
    public function getValidationErrors(Grid $grid): array
    {
        $errors = [];

        // Controlla conflitti nelle righe
        for ($row = 0; $row < 9; $row++) {
            $conflicts = $this->findRowConflicts($grid, $row);
            if (!empty($conflicts)) {
                $errors["row_{$row}"] = $conflicts;
            }
        }

        // Controlla conflitti nelle colonne
        for ($col = 0; $col < 9; $col++) {
            $conflicts = $this->findColConflicts($grid, $col);
            if (!empty($conflicts)) {
                $errors["col_{$col}"] = $conflicts;
            }
        }

        // Controlla conflitti nei box
        for ($box = 0; $box < 9; $box++) {
            $conflicts = $this->findBoxConflicts($grid, $box);
            if (!empty($conflicts)) {
                $errors["box_{$box}"] = $conflicts;
            }
        }

        // Controlla celle con candidati impossibili
        $invalidCells = $this->findCellsWithoutValidCandidates($grid);
        if (!empty($invalidCells)) {
            $errors['impossible_cells'] = $invalidCells;
        }

        return $errors;
    }

    /**
     * Verifica se un valore può essere inserito in una specifica posizione
     */
    public function canPlaceValue(Grid $grid, int $row, int $col, int $value): bool
    {
        if ($value < 1 || $value > 9) {
            return false;
        }

        $cell = $grid->getCell($row, $col);
        
        // Non si può modificare una cella given
        if ($cell->isGiven) {
            return false;
        }

        // Se la cella ha già un valore, può essere sostituito solo se non è given
        if ($cell->hasValue() && $cell->value === $value) {
            return true; // Stesso valore, OK
        }

        // Verifica conflitti con peers
        $peers = $grid->getPeers($row, $col);
        
        // Controlla riga
        foreach ($peers['row'] as $peerCell) {
            if ($peerCell->hasValue() && $peerCell->value === $value) {
                return false;
            }
        }

        // Controlla colonna
        foreach ($peers['col'] as $peerCell) {
            if ($peerCell->hasValue() && $peerCell->value === $value) {
                return false;
            }
        }

        // Controlla box
        foreach ($peers['box'] as $peerCell) {
            if ($peerCell->hasValue() && $peerCell->value === $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se una griglia è completamente risolta
     */
    public function isSolved(Grid $grid): bool
    {
        return $grid->isSolved();
    }

    /**
     * Verifica se una griglia è risolvibile (ha almeno una soluzione)
     */
    public function isSolvable(Grid $grid): bool
    {
        $solutions = $this->findAllSolutions($grid, 1); // Trova solo la prima soluzione
        return count($solutions) > 0;
    }

    /**
     * Conta il numero di soluzioni possibili (limitato a un massimo)
     */
    public function countSolutions(Grid $grid, int $maxSolutions = 10): int
    {
        $solutions = $this->findAllSolutions($grid, $maxSolutions);
        return count($solutions);
    }

    /**
     * Trova tutte le soluzioni possibili (limitato a un numero massimo)
     * 
     * @return array<int, Grid>
     */
    public function findAllSolutions(Grid $grid, int $maxSolutions = 10): array
    {
        $solutions = [];
        $this->solveRecursive($grid, $solutions, $maxSolutions);
        return $solutions;
    }

    /**
     * Trova la prima soluzione valida
     */
    public function findFirstSolution(Grid $grid): ?Grid
    {
        $solutions = $this->findAllSolutions($grid, 1);
        return $solutions[0] ?? null;
    }

    /**
     * Trova conflitti in una riga
     * 
     * @return array<string, mixed>
     */
    private function findRowConflicts(Grid $grid, int $row): array
    {
        $conflicts = [];
        $seen = [];
        
        foreach ($grid->getRow($row) as $col => $cell) {
            if ($cell->hasValue()) {
                $value = $cell->value;
                if (isset($seen[$value])) {
                    $conflicts[] = [
                        'value' => $value,
                        'positions' => [
                            ['row' => $row, 'col' => $seen[$value]],
                            ['row' => $row, 'col' => $col]
                        ]
                    ];
                } else {
                    $seen[$value] = $col;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Trova conflitti in una colonna
     * 
     * @return array<string, mixed>
     */
    private function findColConflicts(Grid $grid, int $col): array
    {
        $conflicts = [];
        $seen = [];
        
        foreach ($grid->getCol($col) as $row => $cell) {
            if ($cell->hasValue()) {
                $value = $cell->value;
                if (isset($seen[$value])) {
                    $conflicts[] = [
                        'value' => $value,
                        'positions' => [
                            ['row' => $seen[$value], 'col' => $col],
                            ['row' => $row, 'col' => $col]
                        ]
                    ];
                } else {
                    $seen[$value] = $row;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Trova conflitti in un box
     * 
     * @return array<string, mixed>
     */
    private function findBoxConflicts(Grid $grid, int $box): array
    {
        $conflicts = [];
        $seen = [];
        
        foreach ($grid->getBox($box) as $cell) {
            if ($cell->hasValue()) {
                $value = $cell->value;
                $position = ['row' => $cell->row, 'col' => $cell->col];
                
                if (isset($seen[$value])) {
                    $conflicts[] = [
                        'value' => $value,
                        'positions' => [$seen[$value], $position]
                    ];
                } else {
                    $seen[$value] = $position;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Trova celle che non hanno candidati validi
     * 
     * @return array<string, mixed>
     */
    private function findCellsWithoutValidCandidates(Grid $grid): array
    {
        $invalidCells = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                
                if ($cell->isEmpty()) {
                    $validCandidates = $this->getValidCandidates($grid, $row, $col);
                    if ($validCandidates->isEmpty()) {
                        $invalidCells[] = [
                            'row' => $row,
                            'col' => $col,
                            'reason' => 'No valid candidates available'
                        ];
                    }
                }
            }
        }

        return $invalidCells;
    }

    /**
     * Ottiene i candidati validi per una cella
     */
    private function getValidCandidates(Grid $grid, int $row, int $col): \App\Domain\Sudoku\ValueObjects\CandidateSet
    {
        $candidates = \App\Domain\Sudoku\ValueObjects\CandidateSet::all();
        $peers = $grid->getPeers($row, $col);
        
        // Rimuovi valori già presenti nei peers
        foreach (['row', 'col', 'box'] as $group) {
            foreach ($peers[$group] as $peerCell) {
                if ($peerCell->hasValue()) {
                    $candidates = $candidates->remove($peerCell->value);
                }
            }
        }

        return $candidates;
    }

    /**
     * Risolve ricorsivamente la griglia usando backtracking
     * 
     * @param array<int, Grid> $solutions
     */
    private function solveRecursive(Grid $grid, array &$solutions, int $maxSolutions, int $depth = 0, float $startTime = null): void
    {
        // Inizializza startTime se prima chiamata
        if ($startTime === null) {
            $startTime = microtime(true);
        }
        
        // Timeout per evitare loop infiniti (max 3 secondi)
        if (microtime(true) - $startTime > 3.0) {
            return;
        }
        
        // Limite profondità per evitare stack overflow
        if ($depth > 81) {
            return;
        }
        
        if (count($solutions) >= $maxSolutions) {
            return; // Limite raggiunto
        }

        if ($grid->isComplete()) {
            if ($grid->isValid()) {
                $solutions[] = $grid;
            }
            return;
        }

        // Trova la cella vuota con meno candidati (euristica MRV - Most Restrictive Value)
        $bestCell = null;
        $minCandidates = 10;

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->isEmpty()) {
                    $candidates = $this->getValidCandidates($grid, $row, $col);
                    $candidateCount = $candidates->count();
                    
                    if ($candidateCount === 0) {
                        return; // Nessun candidato valido, questa branch è impossibile
                    }
                    
                    if ($candidateCount < $minCandidates) {
                        $minCandidates = $candidateCount;
                        $bestCell = [$row, $col, $candidates];
                    }
                }
            }
        }

        if ($bestCell === null) {
            return; // Nessuna cella vuota trovata (non dovrebbe accadere)
        }

        [$row, $col, $candidates] = $bestCell;

        // Prova ogni candidato
        foreach ($candidates->toArray() as $value) {
            if ($this->canPlaceValue($grid, $row, $col, $value)) {
                $newGrid = $grid->setCell($row, $col, $value);
                $this->solveRecursive($newGrid, $solutions, $maxSolutions, $depth + 1, $startTime);
            }
        }
    }
}
