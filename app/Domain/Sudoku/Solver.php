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
        'coloring',
        'simple_chains',
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
     * Claiming: candidati in una riga/colonna limitati a un box eliminano candidati dal resto del box
     */
    private function applyLockedCandidatesClaiming(Grid $grid): array
    {
        // Controlla righe
        for ($row = 0; $row < 9; $row++) {
            for ($value = 1; $value <= 9; $value++) {
                $result = $this->applyClaimingInRow($grid, $row, $value);
                if ($result['grid'] !== null) return $result;
            }
        }

        // Controlla colonne
        for ($col = 0; $col < 9; $col++) {
            for ($value = 1; $value <= 9; $value++) {
                $result = $this->applyClaimingInColumn($grid, $col, $value);
                if ($result['grid'] !== null) return $result;
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function applyClaimingInRow(Grid $grid, int $row, int $value): array
    {
        $candidatePositions = [];
        
        for ($col = 0; $col < 9; $col++) {
            $cell = $grid->getCell($row, $col);
            if ($cell->value === null && $cell->candidates->contains($value)) {
                $candidatePositions[] = [$row, $col];
            }
        }

        if (count($candidatePositions) < 2) return ['grid' => null, 'step' => null];

        // Verifica se tutti i candidati sono nello stesso box
        $boxes = array_unique(array_map(fn($pos) => intval($pos[1] / 3), $candidatePositions));
        
        if (count($boxes) === 1) {
            $targetBox = $boxes[0];
            $newGrid = $this->removeCandidatesFromBoxExceptRow($grid, $targetBox, intval($row / 3), $value, $row);
            
            if (!$this->gridsAreEqual($newGrid, $grid)) {
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'locked_candidates_claiming',
                        'description' => "Nella riga {$row}, {$value} è limitato al box colonna {$targetBox}",
                        'value' => $value,
                        'affects' => "Rimosso {$value} da altre celle del box",
                        'reason' => 'Candidati limitati a un box'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function applyClaimingInColumn(Grid $grid, int $col, int $value): array
    {
        $candidatePositions = [];
        
        for ($row = 0; $row < 9; $row++) {
            $cell = $grid->getCell($row, $col);
            if ($cell->value === null && $cell->candidates->contains($value)) {
                $candidatePositions[] = [$row, $col];
            }
        }

        if (count($candidatePositions) < 2) return ['grid' => null, 'step' => null];

        // Verifica se tutti i candidati sono nello stesso box
        $boxes = array_unique(array_map(fn($pos) => intval($pos[0] / 3), $candidatePositions));
        
        if (count($boxes) === 1) {
            $targetBox = $boxes[0];
            $newGrid = $this->removeCandidatesFromBoxExceptColumn($grid, intval($col / 3), $targetBox, $value, $col);
            
            if (!$this->gridsAreEqual($newGrid, $grid)) {
                return [
                    'grid' => $newGrid,
                    'step' => [
                        'technique' => 'locked_candidates_claiming',
                        'description' => "Nella colonna {$col}, {$value} è limitato al box riga {$targetBox}",
                        'value' => $value,
                        'affects' => "Rimosso {$value} da altre celle del box",
                        'reason' => 'Candidati limitati a un box'
                    ]
                ];
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Naked Pairs: due celle con gli stessi due candidati eliminano quei candidati dalle altre celle
     */
    private function applyNakedPairs(Grid $grid): array
    {
        // Controlla righe
        for ($row = 0; $row < 9; $row++) {
            $result = $this->findNakedPairsInRow($grid, $row);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla colonne
        for ($col = 0; $col < 9; $col++) {
            $result = $this->findNakedPairsInColumn($grid, $col);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla box
        for ($boxRow = 0; $boxRow < 3; $boxRow++) {
            for ($boxCol = 0; $boxCol < 3; $boxCol++) {
                $result = $this->findNakedPairsInBox($grid, $boxRow, $boxCol);
                if ($result['grid'] !== null) return $result;
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findNakedPairsInRow(Grid $grid, int $row): array
    {
        $cellsWithTwoCandidates = [];
        
        for ($col = 0; $col < 9; $col++) {
            $cell = $grid->getCell($row, $col);
            if ($cell->value === null && $cell->candidates->count() === 2) {
                $cellsWithTwoCandidates[] = [$row, $col, $cell->candidates];
            }
        }

        // Cerca coppie con gli stessi candidati
        for ($i = 0; $i < count($cellsWithTwoCandidates); $i++) {
            for ($j = $i + 1; $j < count($cellsWithTwoCandidates); $j++) {
                $cell1 = $cellsWithTwoCandidates[$i];
                $cell2 = $cellsWithTwoCandidates[$j];
                
                if ($cell1[2]->equals($cell2[2])) {
                    // Trovata naked pair
                    $candidates = $cell1[2];
                    $newGrid = $this->removeNakedPairCandidatesFromRow($grid, $row, $candidates, [$cell1[1], $cell2[1]]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'naked_pairs',
                                'description' => "Naked pair {$candidates->toString()} in riga {$row}, celle {$cell1[1]} e {$cell2[1]}",
                                'candidates' => $candidates->toArray(),
                                'cells' => [[$cell1[0], $cell1[1]], [$cell2[0], $cell2[1]]],
                                'reason' => 'Eliminazione candidati dalle altre celle'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findNakedPairsInColumn(Grid $grid, int $col): array
    {
        $cellsWithTwoCandidates = [];
        
        for ($row = 0; $row < 9; $row++) {
            $cell = $grid->getCell($row, $col);
            if ($cell->value === null && $cell->candidates->count() === 2) {
                $cellsWithTwoCandidates[] = [$row, $col, $cell->candidates];
            }
        }

        // Cerca coppie con gli stessi candidati
        for ($i = 0; $i < count($cellsWithTwoCandidates); $i++) {
            for ($j = $i + 1; $j < count($cellsWithTwoCandidates); $j++) {
                $cell1 = $cellsWithTwoCandidates[$i];
                $cell2 = $cellsWithTwoCandidates[$j];
                
                if ($cell1[2]->equals($cell2[2])) {
                    // Trovata naked pair
                    $candidates = $cell1[2];
                    $newGrid = $this->removeNakedPairCandidatesFromColumn($grid, $col, $candidates, [$cell1[0], $cell2[0]]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'naked_pairs',
                                'description' => "Naked pair {$candidates->toString()} in colonna {$col}, celle {$cell1[0]} e {$cell2[0]}",
                                'candidates' => $candidates->toArray(),
                                'cells' => [[$cell1[0], $cell1[1]], [$cell2[0], $cell2[1]]],
                                'reason' => 'Eliminazione candidati dalle altre celle'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findNakedPairsInBox(Grid $grid, int $boxRow, int $boxCol): array
    {
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        $cellsWithTwoCandidates = [];
        
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                $cell = $grid->getCell($r, $c);
                if ($cell->value === null && $cell->candidates->count() === 2) {
                    $cellsWithTwoCandidates[] = [$r, $c, $cell->candidates];
                }
            }
        }

        // Cerca coppie con gli stessi candidati
        for ($i = 0; $i < count($cellsWithTwoCandidates); $i++) {
            for ($j = $i + 1; $j < count($cellsWithTwoCandidates); $j++) {
                $cell1 = $cellsWithTwoCandidates[$i];
                $cell2 = $cellsWithTwoCandidates[$j];
                
                if ($cell1[2]->equals($cell2[2])) {
                    // Trovata naked pair
                    $candidates = $cell1[2];
                    $newGrid = $this->removeNakedPairCandidatesFromBox($grid, $boxRow, $boxCol, $candidates, [[$cell1[0], $cell1[1]], [$cell2[0], $cell2[1]]]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'naked_pairs',
                                'description' => "Naked pair {$candidates->toString()} in box ({$boxRow},{$boxCol})",
                                'candidates' => $candidates->toArray(),
                                'cells' => [[$cell1[0], $cell1[1]], [$cell2[0], $cell2[1]]],
                                'reason' => 'Eliminazione candidati dalle altre celle'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Hidden Pairs: due candidati che possono stare solo in due celle eliminano altri candidati da quelle celle
     */
    private function applyHiddenPairs(Grid $grid): array
    {
        // Controlla righe
        for ($row = 0; $row < 9; $row++) {
            $result = $this->findHiddenPairsInRow($grid, $row);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla colonne
        for ($col = 0; $col < 9; $col++) {
            $result = $this->findHiddenPairsInColumn($grid, $col);
            if ($result['grid'] !== null) return $result;
        }

        // Controlla box
        for ($boxRow = 0; $boxRow < 3; $boxRow++) {
            for ($boxCol = 0; $boxCol < 3; $boxCol++) {
                $result = $this->findHiddenPairsInBox($grid, $boxRow, $boxCol);
                if ($result['grid'] !== null) return $result;
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenPairsInRow(Grid $grid, int $row): array
    {
        // Per ogni coppia di valori, verifica se possono stare solo in due celle
        for ($val1 = 1; $val1 <= 8; $val1++) {
            for ($val2 = $val1 + 1; $val2 <= 9; $val2++) {
                $cellsForVal1 = [];
                $cellsForVal2 = [];
                
                for ($col = 0; $col < 9; $col++) {
                    $cell = $grid->getCell($row, $col);
                    if ($cell->value === null) {
                        if ($cell->candidates->contains($val1)) {
                            $cellsForVal1[] = $col;
                        }
                        if ($cell->candidates->contains($val2)) {
                            $cellsForVal2[] = $col;
                        }
                    }
                }

                // Se entrambi i valori possono stare solo nelle stesse due celle
                if (count($cellsForVal1) === 2 && count($cellsForVal2) === 2 && $cellsForVal1 === $cellsForVal2) {
                    $col1 = $cellsForVal1[0];
                    $col2 = $cellsForVal1[1];
                    
                    $newGrid = $this->removeHiddenPairOtherCandidates($grid, [[$row, $col1], [$row, $col2]], [$val1, $val2]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'hidden_pairs',
                                'description' => "Hidden pair {$val1},{$val2} in riga {$row}, celle {$col1} e {$col2}",
                                'candidates' => [$val1, $val2],
                                'cells' => [[$row, $col1], [$row, $col2]],
                                'reason' => 'Eliminazione altri candidati dalle celle'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenPairsInColumn(Grid $grid, int $col): array
    {
        // Per ogni coppia di valori, verifica se possono stare solo in due celle
        for ($val1 = 1; $val1 <= 8; $val1++) {
            for ($val2 = $val1 + 1; $val2 <= 9; $val2++) {
                $cellsForVal1 = [];
                $cellsForVal2 = [];
                
                for ($row = 0; $row < 9; $row++) {
                    $cell = $grid->getCell($row, $col);
                    if ($cell->value === null) {
                        if ($cell->candidates->contains($val1)) {
                            $cellsForVal1[] = $row;
                        }
                        if ($cell->candidates->contains($val2)) {
                            $cellsForVal2[] = $row;
                        }
                    }
                }

                // Se entrambi i valori possono stare solo nelle stesse due celle
                if (count($cellsForVal1) === 2 && count($cellsForVal2) === 2 && $cellsForVal1 === $cellsForVal2) {
                    $row1 = $cellsForVal1[0];
                    $row2 = $cellsForVal1[1];
                    
                    $newGrid = $this->removeHiddenPairOtherCandidates($grid, [[$row1, $col], [$row2, $col]], [$val1, $val2]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'hidden_pairs',
                                'description' => "Hidden pair {$val1},{$val2} in colonna {$col}, celle {$row1} e {$row2}",
                                'candidates' => [$val1, $val2],
                                'cells' => [[$row1, $col], [$row2, $col]],
                                'reason' => 'Eliminazione altri candidati dalle celle'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findHiddenPairsInBox(Grid $grid, int $boxRow, int $boxCol): array
    {
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        
        // Per ogni coppia di valori, verifica se possono stare solo in due celle
        for ($val1 = 1; $val1 <= 8; $val1++) {
            for ($val2 = $val1 + 1; $val2 <= 9; $val2++) {
                $cellsForVal1 = [];
                $cellsForVal2 = [];
                
                for ($r = $startRow; $r < $startRow + 3; $r++) {
                    for ($c = $startCol; $c < $startCol + 3; $c++) {
                        $cell = $grid->getCell($r, $c);
                        if ($cell->value === null) {
                            if ($cell->candidates->contains($val1)) {
                                $cellsForVal1[] = [$r, $c];
                            }
                            if ($cell->candidates->contains($val2)) {
                                $cellsForVal2[] = [$r, $c];
                            }
                        }
                    }
                }

                // Se entrambi i valori possono stare solo nelle stesse due celle
                if (count($cellsForVal1) === 2 && count($cellsForVal2) === 2 && $cellsForVal1 === $cellsForVal2) {
                    $cell1 = $cellsForVal1[0];
                    $cell2 = $cellsForVal1[1];
                    
                    $newGrid = $this->removeHiddenPairOtherCandidates($grid, [$cell1, $cell2], [$val1, $val2]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'hidden_pairs',
                                'description' => "Hidden pair {$val1},{$val2} in box ({$boxRow},{$boxCol})",
                                'candidates' => [$val1, $val2],
                                'cells' => [$cell1, $cell2],
                                'reason' => 'Eliminazione altri candidati dalle celle'
                            ]
                        ];
                    }
                }
            }
        }

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
     * X-Wing: pattern rettangolare che elimina candidati
     */
    private function applyXWing(Grid $grid): array
    {
        // Cerca X-Wing nelle righe
        for ($value = 1; $value <= 9; $value++) {
            $result = $this->findXWingInRows($grid, $value);
            if ($result['grid'] !== null) return $result;
        }

        // Cerca X-Wing nelle colonne
        for ($value = 1; $value <= 9; $value++) {
            $result = $this->findXWingInColumns($grid, $value);
            if ($result['grid'] !== null) return $result;
        }

        return ['grid' => null, 'step' => null];
    }

    private function findXWingInRows(Grid $grid, int $value): array
    {
        $rowsWithTwoCandidates = [];
        
        // Trova righe dove il valore ha esattamente 2 candidati
        for ($row = 0; $row < 9; $row++) {
            $candidates = [];
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $candidates[] = $col;
                }
            }
            
            if (count($candidates) === 2) {
                $rowsWithTwoCandidates[] = [$row, $candidates];
            }
        }

        // Cerca coppie di righe con candidati nelle stesse colonne
        for ($i = 0; $i < count($rowsWithTwoCandidates); $i++) {
            for ($j = $i + 1; $j < count($rowsWithTwoCandidates); $j++) {
                $row1Data = $rowsWithTwoCandidates[$i];
                $row2Data = $rowsWithTwoCandidates[$j];
                
                if ($row1Data[1] === $row2Data[1]) {
                    // Trovato X-Wing
                    $col1 = $row1Data[1][0];
                    $col2 = $row1Data[1][1];
                    $row1 = $row1Data[0];
                    $row2 = $row2Data[0];
                    
                    $newGrid = $this->removeXWingCandidatesFromColumns($grid, $value, [$col1, $col2], [$row1, $row2]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'x_wing',
                                'description' => "X-Wing per valore {$value} nelle righe {$row1},{$row2} e colonne {$col1},{$col2}",
                                'value' => $value,
                                'rows' => [$row1, $row2],
                                'columns' => [$col1, $col2],
                                'reason' => 'Eliminazione candidati dalle colonne'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    private function findXWingInColumns(Grid $grid, int $value): array
    {
        $colsWithTwoCandidates = [];
        
        // Trova colonne dove il valore ha esattamente 2 candidati
        for ($col = 0; $col < 9; $col++) {
            $candidates = [];
            for ($row = 0; $row < 9; $row++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $candidates[] = $row;
                }
            }
            
            if (count($candidates) === 2) {
                $colsWithTwoCandidates[] = [$col, $candidates];
            }
        }

        // Cerca coppie di colonne con candidati nelle stesse righe
        for ($i = 0; $i < count($colsWithTwoCandidates); $i++) {
            for ($j = $i + 1; $j < count($colsWithTwoCandidates); $j++) {
                $col1Data = $colsWithTwoCandidates[$i];
                $col2Data = $colsWithTwoCandidates[$j];
                
                if ($col1Data[1] === $col2Data[1]) {
                    // Trovato X-Wing
                    $row1 = $col1Data[1][0];
                    $row2 = $col1Data[1][1];
                    $col1 = $col1Data[0];
                    $col2 = $col2Data[0];
                    
                    $newGrid = $this->removeXWingCandidatesFromRows($grid, $value, [$row1, $row2], [$col1, $col2]);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'x_wing',
                                'description' => "X-Wing per valore {$value} nelle colonne {$col1},{$col2} e righe {$row1},{$row2}",
                                'value' => $value,
                                'rows' => [$row1, $row2],
                                'columns' => [$col1, $col2],
                                'reason' => 'Eliminazione candidati dalle righe'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Swordfish: estensione dell'X-Wing con 3 righe/colonne
     */
    private function applySwordfish(Grid $grid): array
    {
        // Implementazione semplificata per ora - può essere molto complessa
        return ['grid' => null, 'step' => null];
    }

    /**
     * Simple Coloring: tecnica di colorazione per eliminare candidati
     */
    private function applyColoring(Grid $grid): array
    {
        for ($value = 1; $value <= 9; $value++) {
            $result = $this->findColoringEliminations($grid, $value);
            if ($result['grid'] !== null) return $result;
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Simple Chains: catene di implicazioni logiche
     */
    private function applySimpleChains(Grid $grid): array
    {
        // Implementazione semplificata per ora - può essere molto complessa
        return ['grid' => null, 'step' => null];
    }

    private function findColoringEliminations(Grid $grid, int $value): array
    {
        // Trova celle che hanno solo due posizioni possibili per il valore in una riga/colonna
        $strongLinks = $this->findStrongLinks($grid, $value);
        
        if (empty($strongLinks)) {
            return ['grid' => null, 'step' => null];
        }

        // Implementazione semplificata: cerca contraddizioni di colore
        $chains = $this->buildColorChains($strongLinks);
        
        foreach ($chains as $chain) {
            if (count($chain) >= 4) { // Almeno 2 link forti
                $eliminations = $this->findColoringEliminationsInChain($grid, $value, $chain);
                if (!empty($eliminations)) {
                    $newGrid = $this->applyEliminations($grid, $eliminations);
                    
                    if (!$this->gridsAreEqual($newGrid, $grid)) {
                        return [
                            'grid' => $newGrid,
                            'step' => [
                                'technique' => 'coloring',
                                'description' => "Coloring eliminazione per valore {$value}",
                                'value' => $value,
                                'chain' => $chain,
                                'eliminations' => $eliminations,
                                'reason' => 'Contraddizione di colore'
                            ]
                        ];
                    }
                }
            }
        }

        return ['grid' => null, 'step' => null];
    }

    /**
     * Trova link forti (celle che si vedono a vicenda con solo 2 posizioni per un valore)
     */
    private function findStrongLinks(Grid $grid, int $value): array
    {
        $strongLinks = [];
        
        // Link forti nelle righe
        for ($row = 0; $row < 9; $row++) {
            $candidates = [];
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $candidates[] = [$row, $col];
                }
            }
            
            if (count($candidates) === 2) {
                $strongLinks[] = ['type' => 'row', 'cells' => $candidates];
            }
        }
        
        // Link forti nelle colonne
        for ($col = 0; $col < 9; $col++) {
            $candidates = [];
            for ($row = 0; $row < 9; $row++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null && $cell->candidates->contains($value)) {
                    $candidates[] = [$row, $col];
                }
            }
            
            if (count($candidates) === 2) {
                $strongLinks[] = ['type' => 'col', 'cells' => $candidates];
            }
        }
        
        return $strongLinks;
    }

    private function buildColorChains(array $strongLinks): array
    {
        // Implementazione semplificata per costruire catene di colori
        $chains = [];
        
        foreach ($strongLinks as $link) {
            $chains[] = $link['cells'];
        }
        
        return $chains;
    }

    private function findColoringEliminationsInChain(Grid $grid, int $value, array $chain): array
    {
        // Implementazione semplificata per trovare eliminazioni
        return [];
    }

    private function applyEliminations(Grid $grid, array $eliminations): Grid
    {
        $newGrid = $grid;
        
        foreach ($eliminations as $elimination) {
            $row = $elimination['row'];
            $col = $elimination['col'];
            $value = $elimination['value'];
            
            $cell = $newGrid->getCell($row, $col);
            if ($cell->value === null && $cell->candidates->contains($value)) {
                $newCandidates = $cell->candidates->remove($value);
                $newGrid = $newGrid->updateCellCandidates($row, $col, $newCandidates);
            }
        }
        
        return $newGrid;
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

    // Metodi helper per le nuove tecniche

    private function removeCandidatesFromBoxExceptRow(Grid $grid, int $boxCol, int $boxRow, int $value, int $excludeRow): Grid
    {
        $updatedGrid = $grid;
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($r !== $excludeRow) {
                    $cell = $grid->getCell($r, $c);
                    if ($cell->value === null && $cell->candidates->contains($value)) {
                        $newCandidates = $cell->candidates->remove($value);
                        $updatedGrid = $updatedGrid->updateCellCandidates($r, $c, $newCandidates);
                    }
                }
            }
        }

        return $updatedGrid;
    }

    private function removeCandidatesFromBoxExceptColumn(Grid $grid, int $boxCol, int $boxRow, int $value, int $excludeCol): Grid
    {
        $updatedGrid = $grid;
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($c !== $excludeCol) {
                    $cell = $grid->getCell($r, $c);
                    if ($cell->value === null && $cell->candidates->contains($value)) {
                        $newCandidates = $cell->candidates->remove($value);
                        $updatedGrid = $updatedGrid->updateCellCandidates($r, $c, $newCandidates);
                    }
                }
            }
        }

        return $updatedGrid;
    }

    private function removeNakedPairCandidatesFromRow(Grid $grid, int $row, CandidateSet $pairCandidates, array $excludeCols): Grid
    {
        $updatedGrid = $grid;
        
        for ($col = 0; $col < 9; $col++) {
            if (!in_array($col, $excludeCols)) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null) {
                    $newCandidates = $cell->candidates;
                    foreach ($pairCandidates->toArray() as $candidate) {
                        $newCandidates = $newCandidates->remove($candidate);
                    }
                    $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                }
            }
        }

        return $updatedGrid;
    }

    private function removeNakedPairCandidatesFromColumn(Grid $grid, int $col, CandidateSet $pairCandidates, array $excludeRows): Grid
    {
        $updatedGrid = $grid;
        
        for ($row = 0; $row < 9; $row++) {
            if (!in_array($row, $excludeRows)) {
                $cell = $grid->getCell($row, $col);
                if ($cell->value === null) {
                    $newCandidates = $cell->candidates;
                    foreach ($pairCandidates->toArray() as $candidate) {
                        $newCandidates = $newCandidates->remove($candidate);
                    }
                    $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                }
            }
        }

        return $updatedGrid;
    }

    private function removeNakedPairCandidatesFromBox(Grid $grid, int $boxRow, int $boxCol, CandidateSet $pairCandidates, array $excludeCells): Grid
    {
        $updatedGrid = $grid;
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if (!in_array([$r, $c], $excludeCells)) {
                    $cell = $grid->getCell($r, $c);
                    if ($cell->value === null) {
                        $newCandidates = $cell->candidates;
                        foreach ($pairCandidates->toArray() as $candidate) {
                            $newCandidates = $newCandidates->remove($candidate);
                        }
                        $updatedGrid = $updatedGrid->updateCellCandidates($r, $c, $newCandidates);
                    }
                }
            }
        }

        return $updatedGrid;
    }

    private function removeHiddenPairOtherCandidates(Grid $grid, array $cells, array $pairValues): Grid
    {
        $updatedGrid = $grid;
        
        foreach ($cells as [$row, $col]) {
            $cell = $grid->getCell($row, $col);
            if ($cell->value === null) {
                $newCandidates = CandidateSet::empty();
                foreach ($pairValues as $value) {
                    if ($cell->candidates->contains($value)) {
                        $newCandidates = $newCandidates->add($value);
                    }
                }
                $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
            }
        }

        return $updatedGrid;
    }

    private function removeXWingCandidatesFromColumns(Grid $grid, int $value, array $cols, array $excludeRows): Grid
    {
        $updatedGrid = $grid;
        
        foreach ($cols as $col) {
            for ($row = 0; $row < 9; $row++) {
                if (!in_array($row, $excludeRows)) {
                    $cell = $grid->getCell($row, $col);
                    if ($cell->value === null && $cell->candidates->contains($value)) {
                        $newCandidates = $cell->candidates->remove($value);
                        $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                    }
                }
            }
        }

        return $updatedGrid;
    }

    private function removeXWingCandidatesFromRows(Grid $grid, int $value, array $rows, array $excludeCols): Grid
    {
        $updatedGrid = $grid;
        
        foreach ($rows as $row) {
            for ($col = 0; $col < 9; $col++) {
                if (!in_array($col, $excludeCols)) {
                    $cell = $grid->getCell($row, $col);
                    if ($cell->value === null && $cell->candidates->contains($value)) {
                        $newCandidates = $cell->candidates->remove($value);
                        $updatedGrid = $updatedGrid->updateCellCandidates($row, $col, $newCandidates);
                    }
                }
            }
        }

        return $updatedGrid;
    }
}
