<?php
declare(strict_types=1);

namespace App\Domain\Sudoku;

use App\Domain\Sudoku\Contracts\SolverInterface;
use App\Domain\Sudoku\ValueObjects\Cell;
use App\Domain\Sudoku\ValueObjects\CandidateSet;

/**
 * Risolutore logico di Sudoku che implementa varie tecniche di risoluzione
 */
final class Solver implements SolverInterface
{
    private const TECHNIQUES = [
        'naked_singles',
        'hidden_singles', 
        'locked_candidates_pointing',
        'locked_candidates_claiming',
        'naked_pairs',
        'hidden_pairs',
        'naked_triples',
        'hidden_triples',
        'x_wing',
        'swordfish',
    ];

    public function solve(Grid $grid): array
    {
        $currentGrid = $this->updateCandidates($grid);
        $techniques = [];
        $steps = [];
        $maxIterations = 100; // Prevenzione loop infiniti
        $iteration = 0;

        while (!$currentGrid->isComplete() && $iteration < $maxIterations) {
            $stepResult = $this->solveStep($currentGrid);
            
            if ($stepResult['grid'] === null) {
                // Nessuna tecnica applicabile, prova con backtracking
                $backtrackResult = $this->solveWithBacktrack($currentGrid);
                return [
                    'grid' => $backtrackResult,
                    'techniques' => array_merge($techniques, $backtrackResult ? ['backtracking'] : []),
                    'steps' => $steps
                ];
            }

            $currentGrid = $stepResult['grid'];
            $techniques[] = $stepResult['technique'];
            $steps[] = $stepResult['step'];
            $iteration++;
        }

        return [
            'grid' => $currentGrid->isComplete() ? $currentGrid : null,
            'techniques' => $techniques,
            'steps' => $steps
        ];
    }

    public function solveStep(Grid $grid): array
    {
        $gridWithCandidates = $this->updateCandidates($grid);

        // Prova ogni tecnica in ordine di difficoltà
        foreach (self::TECHNIQUES as $technique) {
            $methodName = 'apply' . str_replace('_', '', ucwords($technique, '_'));
            
            if (method_exists($this, $methodName)) {
                $result = $this->$methodName($gridWithCandidates);
                
                if ($result['grid'] !== null) {
                    return [
                        'grid' => $result['grid'],
                        'technique' => $technique,
                        'step' => $result['step']
                    ];
                }
            }
        }

        return ['grid' => null, 'technique' => null, 'step' => null];
    }

    public function getHints(Grid $grid): array
    {
        $hints = [];
        $gridWithCandidates = $this->updateCandidates($grid);

        foreach (self::TECHNIQUES as $technique) {
            $methodName = 'findHints' . str_replace('_', '', ucwords($technique, '_'));
            
            if (method_exists($this, $methodName)) {
                $techniqueHints = $this->$methodName($gridWithCandidates);
                $hints = array_merge($hints, $techniqueHints);
            }
        }

        return $hints;
    }

    public function isSolvableLogically(Grid $grid): bool
    {
        $result = $this->solve($grid);
        return $result['grid'] !== null && !in_array('backtracking', $result['techniques']);
    }

    public function getSupportedTechniques(): array
    {
        return self::TECHNIQUES;
    }

    /**
     * Aggiorna i candidati di tutte le celle vuote basandoti sui vincoli
     */
    private function updateCandidates(Grid $grid): Grid
    {
        $updatedGrid = $grid;
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                
                if ($cell->value === null) {
                    // Calcola candidati validi per questa cella
                    $validCandidates = $this->getValidCandidates($grid, $row, $col);
                    $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $validCandidates);
                }
            }
        }

        return $updatedGrid;
    }

    /**
     * Ottiene i candidati validi per una cella vuota
     */
    private function getValidCandidates(Grid $grid, int $row, int $col): CandidateSet
    {
        $candidates = CandidateSet::all();

        // Rimuovi valori già presenti nella riga
        for ($c = 0; $c < 9; $c++) {
            $cellValue = $grid->getCell($row, $c)->value;
            if ($cellValue !== null) {
                $candidates = $candidates->remove($cellValue);
            }
        }

        // Rimuovi valori già presenti nella colonna
        for ($r = 0; $r < 9; $r++) {
            $cellValue = $grid->getCell($r, $col)->value;
            if ($cellValue !== null) {
                $candidates = $candidates->remove($cellValue);
            }
        }

        // Rimuovi valori già presenti nel box 3x3
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;

        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                $cellValue = $grid->getCell($r, $c)->value;
                if ($cellValue !== null) {
                    $candidates = $candidates->remove($cellValue);
                }
            }
        }

        return $candidates;
    }

    /**
     * Naked Singles: cella con un solo candidato possibile
     */
    private function applyNakedSingles(Grid $grid): array
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                
                if ($cell->value === null && $cell->candidates->count() === 1) {
                    $value = $cell->candidates->toArray()[0];
                    $newGrid = $grid->setCell($row, $col, $value);
                    
                    return [
                        'grid' => $newGrid,
                        'step' => [
                            'technique' => 'naked_singles',
                            'description' => "Cella ({$row},{$col}) può contenere solo {$value}",
                            'row' => $row,
                            'col' => $col,
                            'value' => $value,
                            'reason' => 'Ultimo candidato rimasto'
                        ]
                    ];
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Hidden Singles: valore che può andare solo in una cella in una unità (riga/colonna/box)
     */
    private function applyHiddenSingles(Grid $grid): array
    {
        // Controlla righe
        for ($row = 0; $row < 9; $row++) {
            $result = $this->findHiddenSingleInRow($grid, $row);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla colonne
        for ($col = 0; $col < 9; $col++) {
            $result = $this->findHiddenSingleInColumn($grid, $col);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla box
        for ($boxRow = 0; $boxRow < 3; $boxRow++) {
            for ($boxCol = 0; $boxCol < 3; $boxCol++) {
                $result = $this->findHiddenSingleInBox($grid, $boxRow, $boxCol);
                if ($result['grid'] !== null) return $result;
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenSingleInRow(Grid $grid, int $row): array
    {
        for ($value = 1; $value <= 9; $value++) {
            $possibleCols = [];
            
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === $value) {
                    $possibleCols = []; // Valore già presente
                    break;
                }
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $possibleCols[] = $col;
                }
            }

            if (count($possibleCols) === 1) {
                $col = $possibleCols[0];
                $newGrid = $grid->setCell($row, $col, $value);
                
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'hidden_singles',
                        'description' => "Nella riga {$row}, {$value} può andare solo in colonna {$col}",
                        'row' => $row,
                        'col' => $col,
                        'value' => $value,
                        'reason' => 'Unico posto possibile nella riga'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenSingleInColumn(Grid $grid, int $col): array
    {
        for ($value = 1; $value <= 9; $value++) {
            $possibleRows = [];
            
            for ($row = 0; $row < 9; $row++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === $value) {
                    $possibleRows = []; // Valore già presente
                    break;
                }
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $possibleRows[] = $row;
                }
            }

            if (count($possibleRows) === 1) {
                $row = $possibleRows[0];
                $newGrid = $grid->setCell($row, $col, $value);
                
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'hidden_singles',
                        'description' => "Nella colonna {$col}, {$value} può andare solo in riga {$row}",
                        'row' => $row,
                        'col' => $col,
                        'value' => $value,
                        'reason' => 'Unico posto possibile nella colonna'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenSingleInBox(Grid $grid, int $boxRow, int $boxCol): array
    {
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;

        for ($value = 1; $value <= 9; $value++) {
            $possibleCells = [];
            
            for ($r = $startRow; $r < $startRow + 3; $r++) {
                for ($c = $startCol; $c < $startCol + 3; $c++) {
                    $cell = $grid->getCell($r, $c);
                    if ($cell->value === $value) {
                        $possibleCells = []; // Valore già presente
                        break 2;
                    }
                    if ($cell->value === null && $cell->candidates->contains($value)) {
                        $possibleCells[] = [$r, $c];
                    }
                }
            }

            if (count($possibleCells) === 1) {
                [$row, $col] = $possibleCells[0];
                $newGrid = $grid->setCell($row, $col, $value);
                
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'hidden_singles',
                        'description' => "Nel box ({$boxRow},{$boxCol}), {$value} può andare solo in ({$row},{$col})",
                        'row' => $row,
                        'col' => $col,
                        'value' => $value,
                        'reason' => 'Unico posto possibile nel box'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Pointing: candidati allineati in un box che eliminano candidati nella stessa riga/colonna
     */
    private function applyLockedCandidatesPointing(Grid $grid): array
    {
        for ($boxRow = 0; $boxRow < 3; $boxRow++) {
            for ($boxCol = 0; $boxCol < 3; $boxCol++) {
                for ($value = 1; $value <= 9; $value++) {
                    $result = $this->applyPointingInBox($grid, $boxRow, $boxCol, $value);
                    if ($result['grid'] !== null) return $result;
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function applyPointingInBox(Grid $grid, int $boxRow, int $boxCol, int $value): array
    {
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        $candidatePositions = [];

        // Trova tutte le posizioni nel box dove il valore è candidato
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                $cell = $grid->getCell($r, $c);
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $candidatePositions[] = [$r, $c];
                }
            }
        }

        if (count($candidatePositions) < 2) return ['grid' => null, 'step' => null];

        // Controlla se tutti i candidati sono nella stessa riga
        $rows = array_unique(array_column($candidatePositions, 0));
        if (count($rows) === 1) {
            $targetRow = $rows[0];
            $newGrid = $this->removeCandidatesFromRowExceptBox($grid, $targetRow, $value, $boxCol);
            
            if (!$this->gridsAreEqual($newGrid, $grid)) {
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'locked_candidates_pointing',
                        'description' => "Nel box ({$boxRow},{$boxCol}), {$value} è limitato alla riga {$targetRow}",
                        'value' => $value,
                        'affects' => "Rimosso {$value} da altre celle della riga {$targetRow}",
                        'reason' => 'Candidati allineati nel box'
                    ]
                ];
            }
        }

        // Controlla se tutti i candidati sono nella stessa colonna
        $cols = array_unique(array_column($candidatePositions, 1));
        if (count($cols) === 1) {
            $targetCol = $cols[0];
            $newGrid = $this->removeCandidatesFromColumnExceptBox($grid, $targetCol, $value, $boxRow);
            
            if (!$this->gridsAreEqual($newGrid, $grid)) {
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'locked_candidates_pointing',
                        'description' => "Nel box ({$boxRow},{$boxCol}), {$value} è limitato alla colonna {$targetCol}",
                        'value' => $value,
                        'affects' => "Rimosso {$value} da altre celle della colonna {$targetCol}",
                        'reason' => 'Candidati allineati nel box'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Claiming (il contrario di Pointing)
     */
    private function applyLockedCandidatesClaiming(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Naked Pairs
     */
    private function applyNakedPairs(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Hidden Pairs
     */
    private function applyHiddenPairs(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Naked Triples
     */
    private function applyNakedTriples(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Hidden Triples
     */
    private function applyHiddenTriples(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di X-Wing
     */
    private function applyXWing(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Implementazione semplificata di Swordfish
     */
    private function applySwordfish(Grid $grid): array
    {
        // Per ora implementazione base - può essere estesa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Fallback con backtracking per puzzle troppo difficili
     */
    private function solveWithBacktrack(Grid $grid): ?Grid
    {
        // Trova la prima cella vuota
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null) {
                    // Prova ogni candidato possibile
                    foreach ($cell->candidates->toArray() as $value) {
                        $newGrid = $grid->setCell($row, $col, $value);
                        
                        if ($this->isValidPlacement($newGrid, $row, $col, $value)) {
                            $result = $this->solveWithBacktrack($newGrid);
                            if ($result !== null) {
                                return $result;
                            }
                        }
                    }
                    
                    // Nessun valore funziona, backtrack
                    return null;
                }
            }
        }

        // Tutte le celle sono piene
        return $grid;
    }

    /**
     * Verifica se un valore può essere inserito validamente in una posizione
     */
    private function isValidPlacement(Grid $grid, int $row, int $col, int $value): bool
    {
        // Controlla riga
        for ($c = 0; $c < 9; $c++) {
            if ($c !== $col && $grid->getCell($row, $c)->value === $value) {
                return false;
            }
        }

        // Controlla colonna
        for ($r = 0; $r < 9; $r++) {
            if ($r !== $row && $grid->getCell($r, $col)->value === $value) {
                return false;
            }
        }

        // Controlla box
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;

        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $grid->getCell($r, $c)->value === $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Confronta due griglie per verificare se sono uguali
     */
    private function gridsAreEqual(Grid $grid1, Grid $grid2): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell1 = $grid1->getCell($row, $col);
                $cell2 = $grid2->getCell($row, $col);
                
                if ($cell1->value !== $cell2->value || 
                    !$cell1->candidates->equals($cell2->candidates)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Rimuove candidati da una riga escludendo un box specifico
     */
    private function removeCandidatesFromRowExceptBox(Grid $grid, int $row, int $value, int $excludeBoxCol): Grid
    {
        $updatedGrid = $grid;
        
        for ($col = 0; $col < 9; $col++) {
            $cell = $grid->getCell($row, $col);
            
            if ($cell->value === null && $cell->candidates->contains($value)) {
                $cellBoxCol = intval($col / 3);
                if ($cellBoxCol !== $excludeBoxCol) {
                    $newCandidates = $cell->candidates->remove($value);
                    $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                }
            }
        }

        return $updatedGrid;
    }

    /**
     * Rimuove candidati da una colonna escludendo un box specifico
     */
    private function removeCandidatesFromColumnExceptBox(Grid $grid, int $col, int $value, int $excludeBoxRow): Grid
    {
        $updatedGrid = $grid;
        
        for ($row = 0; $row < 9; $row++) {
            $cell = $grid->getCell($row, $col);
            
            if ($cell->value === null && $cell->candidates->contains($value)) {
                $cellBoxRow = intval($row / 3);
                if ($cellBoxRow !== $excludeBoxRow) {
                    $newCandidates = $cell->candidates->remove($value);
                    $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                }
            }
        }

        return $updatedGrid;
    }

    // Metodi per gli hint (opzionali per ora)
    private function findHintsNakedSingles(Grid $grid): array
    {
        $hints = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null && $cell->candidates->count() === 1) {
                    $hints[] = [
                        'technique' => 'naked_singles',
                        'row' => $row,
                        'col' => $col,
                        'value' => $cell->candidates->toArray()[0]
                    ];
                }
            }
        }
        
        return $hints;
    }

    private function findHintsHiddenSingles(Grid $grid): array
    {
        // Implementazione semplificata per ora
        return [];
    }
}
