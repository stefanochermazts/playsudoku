<?php
declare(strict_types=1);

namespace App\Domain\Sudoku;

use App\Domain\Sudoku\Contracts\GeneratorInterface;
use App\Domain\Sudoku\Contracts\ValidatorInterface;
use Illuminate\Support\Facades\Log;
use App\Domain\Sudoku\Exceptions\InvalidGridException;
use App\Domain\Sudoku\ValueObjects\Cell;


/**
 * Generatore deterministico di puzzle Sudoku.
 * 
 * Algoritmo:
 * 1. Genera una griglia completa valida usando backtracking con shuffle deterministico
 * 2. Rimuove celle mantenendo la soluzione unica
 * 3. Applica tecniche di symmetric removal per estetica
 */
final class Generator implements GeneratorInterface
{
    private int $currentSeed;

    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
        $this->setSeed(time());
    }

    /**
     * Imposta il seed per la generazione deterministica
     */
    public function setSeed(int $seed): void
    {
        $this->currentSeed = $seed;
        mt_srand($seed);
    }

    /**
     * Genera un puzzle completo (griglia risolta)
     */
    public function generateCompleteGrid(int $seed): Grid
    {
        $this->setSeed($seed);
        
        // Primo tentativo: algoritmo ottimizzato
        $grid = Grid::empty();
        $grid = $this->fillDiagonalBoxes($grid);
        $completeGrid = $this->fillGridRecursive($grid);
        
        if ($completeGrid !== null) {
            return $completeGrid;
        }
        
        // Fallback 1: Tentativo con seed modificato
        $this->setSeed($seed + 1000);
        $grid = Grid::empty();
        $grid = $this->fillDiagonalBoxes($grid);
        $completeGrid = $this->fillGridRecursive($grid);
        
        if ($completeGrid !== null) {
            return $completeGrid;
        }
        
        // Fallback 2: Pattern deterministico basato su seed
        return $this->generateFromDeterministicPattern($seed);
    }
    
    /**
     * Genera una griglia usando pattern deterministici basati su seed
     */
    private function generateFromDeterministicPattern(int $seed): Grid
    {
        // Pattern base valido (griglia sudoku completa)
        $basePattern = [
            [1,2,3,4,5,6,7,8,9],
            [4,5,6,7,8,9,1,2,3], 
            [7,8,9,1,2,3,4,5,6],
            [2,3,4,5,6,7,8,9,1],
            [5,6,7,8,9,1,2,3,4],
            [8,9,1,2,3,4,5,6,7],
            [3,4,5,6,7,8,9,1,2],
            [6,7,8,9,1,2,3,4,5],
            [9,1,2,3,4,5,6,7,8]
        ];
        
        // Applica trasformazioni deterministiche basate su seed
        $pattern = $this->applyDeterministicTransformations($basePattern, $seed);
        
        return Grid::fromArray($pattern);
    }
    
    /**
     * Applica trasformazioni deterministiche per creare varietà
     */
    private function applyDeterministicTransformations(array $pattern, int $seed): array
    {
        $this->setSeed($seed);
        
        // Swap righe all'interno degli stessi gruppi di 3
        for ($group = 0; $group < 3; $group++) {
            if ($this->getRandomInt(0, 1)) {
                $row1 = $group * 3 + 0;
                $row2 = $group * 3 + 1;
                [$pattern[$row1], $pattern[$row2]] = [$pattern[$row2], $pattern[$row1]];
            }
            if ($this->getRandomInt(0, 1)) {
                $row1 = $group * 3 + 1;
                $row2 = $group * 3 + 2;
                [$pattern[$row1], $pattern[$row2]] = [$pattern[$row2], $pattern[$row1]];
            }
        }
        
        // Swap colonne all'interno degli stessi gruppi di 3
        for ($group = 0; $group < 3; $group++) {
            if ($this->getRandomInt(0, 1)) {
                for ($row = 0; $row < 9; $row++) {
                    $col1 = $group * 3 + 0;
                    $col2 = $group * 3 + 1;
                    [$pattern[$row][$col1], $pattern[$row][$col2]] = [$pattern[$row][$col2], $pattern[$row][$col1]];
                }
            }
            if ($this->getRandomInt(0, 1)) {
                for ($row = 0; $row < 9; $row++) {
                    $col1 = $group * 3 + 1;
                    $col2 = $group * 3 + 2;
                    [$pattern[$row][$col1], $pattern[$row][$col2]] = [$pattern[$row][$col2], $pattern[$row][$col1]];
                }
            }
        }
        
        // Permutazione dei numeri
        $mapping = [1,2,3,4,5,6,7,8,9];
        $this->shuffleArray($mapping);
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $pattern[$row][$col] = $mapping[$pattern[$row][$col] - 1];
            }
        }
        
        return $pattern;
    }

    /**
     * Genera un puzzle con il numero specificato di celle given
     */
    public function generatePuzzle(int $seed, int $givens = 25): Grid
    {
        if ($givens < 17 || $givens > 80) {
            throw new InvalidGridException("Givens must be between 17 and 80, got {$givens}");
        }

        $this->setSeed($seed);
        
        // Genera griglia completa
        $completeGrid = $this->generateCompleteGrid($seed);
        
        // Rimuovi celle mantenendo la soluzione unica
        $puzzle = $this->removeCells($completeGrid, 81 - $givens);
        
        // Marca le celle rimanenti come "given"
        $puzzle = $this->markRemainingCellsAsGiven($puzzle);
        
        if (!$this->validator->hasUniqueSolution($puzzle)) {
            throw new InvalidGridException("Generated puzzle does not have unique solution");
        }

        return $puzzle;
    }

    /**
     * Genera un puzzle con difficoltà specifica
     */
    public function generatePuzzleWithDifficulty(int $seed, string $difficulty): Grid
    {
        $givensRange = $this->getGivensRangeForDifficulty($difficulty);
        $targetGivens = $this->getRandomInt($givensRange['min'], $givensRange['max']);
        
        return $this->generatePuzzle($seed, $targetGivens);
    }

    /**
     * Riempie i box diagonali (0, 4, 8) indipendentemente
     */
    private function fillDiagonalBoxes(Grid $grid): Grid
    {
        $diagonalBoxes = [0, 4, 8]; // Top-left, center, bottom-right
        
        foreach ($diagonalBoxes as $box) {
            $grid = $this->fillBox($grid, $box);
        }
        
        return $grid;
    }

    /**
     * Riempie un singolo box con valori casuali validi
     */
    private function fillBox(Grid $grid, int $box): Grid
    {
        $startRow = intval($box / 3) * 3;
        $startCol = ($box % 3) * 3;
        
        $values = $this->getShuffledValues();
        $valueIndex = 0;
        
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 3; $c++) {
                $row = $startRow + $r;
                $col = $startCol + $c;
                
                $grid = $grid->setCell($row, $col, $values[$valueIndex]);
                $valueIndex++;
            }
        }
        
        return $grid;
    }

    /**
     * Riempie la griglia usando backtracking con shuffle deterministico
     */
    private function fillGridRecursive(Grid $grid, int $depth = 0, float $startTime = null, array &$debugInfo = []): ?Grid
    {
        // Inizializza timestamp e debug se prima chiamata
        if ($startTime === null) {
            $startTime = microtime(true);
            $debugInfo = [
                'calls' => 0,
                'max_depth' => 0,
                'timeouts' => 0,
                'backtracks' => 0,
                'last_positions' => [],
                'start_time' => $startTime
            ];
        }
        
        $debugInfo['calls']++;
        $debugInfo['max_depth'] = max($debugInfo['max_depth'], $depth);
        
        // Debug ridotto - solo ogni 10000 chiamate o ogni 3 secondi
        if ($debugInfo['calls'] % 10000 === 0 || ($debugInfo['calls'] % 1000 === 0 && microtime(true) - $startTime > 3.0)) {
            \Log::info("Generator Progress", [
                'calls' => $debugInfo['calls'],
                'depth' => $depth,
                'elapsed' => round(microtime(true) - $startTime, 2) . 's',
                'filled_cells' => 81 - $grid->countEmptyCells()
            ]);
        }
        
        // Controllo timeout (max 5 secondi per generazione)
        if (microtime(true) - $startTime > 5.0) {
            $debugInfo['timeouts']++;
            \Log::warning("Generator timeout reached", [
                'calls' => $debugInfo['calls'],
                'depth' => $depth,
                'elapsed' => round(microtime(true) - $startTime, 2) . 's'
            ]);
            return null;
        }
        
        // Limite profondità ricorsiva (evita stack overflow)
        if ($depth > 81) {
            \Log::error("Generator depth limit exceeded", ['depth' => $depth]);
            return null;
        }
        
        // Trova la cella vuota
        $bestCell = $this->findMostConstrainedCell($grid);
        if ($bestCell === null) {
            // Griglia completa - successo!
            Log::info("Generator SUCCESS", [
                'total_calls' => $debugInfo['calls'],
                'max_depth' => $debugInfo['max_depth'],
                'total_time' => round(microtime(true) - $startTime, 2) . 's',
                'backtracks' => $debugInfo['backtracks']
            ]);
            return $grid;
        }
        
        [$row, $col] = $bestCell;
        
        // Traccia posizioni per rilevare loop (più permissivo)
        $position = "{$row},{$col}";
        $debugInfo['last_positions'][$position] = ($debugInfo['last_positions'][$position] ?? 0) + 1;
        
        // Solo log di warning senza interrompere la generazione
        if ($debugInfo['last_positions'][$position] > 50 && $debugInfo['last_positions'][$position] % 25 === 0) {
            Log::warning("Position visited many times (normal for backtracking)", [
                'position' => $position,
                'visit_count' => $debugInfo['last_positions'][$position],
                'depth' => $depth,
                'calls' => $debugInfo['calls']
            ]);
        }
        
        // Genera lista di valori possibili
        $values = $this->getShuffledValues();
        $validValues = [];
        
        foreach ($values as $value) {
            if ($this->validator->canPlaceValue($grid, $row, $col, $value)) {
                $validValues[] = $value;
            }
        }
        
        // Debug se nessun valore valido (ridotto)
        if (empty($validValues)) {
            $debugInfo['backtracks']++;
            return null;
        }
        
        foreach ($validValues as $value) {
            $newGrid = $grid->setCell($row, $col, $value);
            
            // Continua ricorsivamente con limiti
            $result = $this->fillGridRecursive($newGrid, $depth + 1, $startTime, $debugInfo);
            if ($result !== null) {
                return $result;
            }
        }
        
        // Nessun valore valido trovato, backtrack
        $debugInfo['backtracks']++;
        return null;
    }
    
    /**
     * Trova la cella vuota con il minor numero di valori possibili (MCV heuristic)
     * Versione semplificata per evitare overhead di memoria
     */
    private function findMostConstrainedCell(Grid $grid): ?array
    {
        // Trova semplicemente la prima cella vuota per ora (strategia semplice ma funzionale)
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->isEmpty()) {
                    return [$row, $col];
                }
            }
        }
        
        return null; // Griglia completa
    }

    /**
     * Rimuove celle dalla griglia mantenendo la soluzione unica
     */
    private function removeCells(Grid $grid, int $cellsToRemove): Grid
    {
        $currentGrid = $grid;
        $positions = $this->getAllPositions();
        
        // Shuffle delle posizioni per rimozione deterministica
        $this->shuffleArray($positions);
        
        $removed = 0;
        
        foreach ($positions as [$row, $col]) {
            if ($removed >= $cellsToRemove) {
                break;
            }
            
            $cell = $currentGrid->getCell($row, $col);
            if ($cell->hasValue() && !$cell->isGiven) {
                // Prova a rimuovere la cella
                $testGrid = $currentGrid->clearCell($row, $col);
                
                // Verifica che la soluzione rimanga unica
                if ($this->validator->hasUniqueSolution($testGrid)) {
                    $currentGrid = $testGrid;
                    $removed++;
                } elseif ($this->useSymmetricRemoval($currentGrid, $row, $col)) {
                    // Prova rimozione simmetrica
                    $symmetricPositions = $this->getSymmetricPositions($row, $col);
                    $allCanRemove = true;
                    
                    // Verifica che tutte le posizioni simmetriche possano essere rimosse
                    $tempGrid = $currentGrid;
                    foreach ($symmetricPositions as [$sRow, $sCol]) {
                        $testGrid = $tempGrid->clearCell($sRow, $sCol);
                        if (!$this->validator->hasUniqueSolution($testGrid)) {
                            $allCanRemove = false;
                            break;
                        }
                        $tempGrid = $testGrid;
                    }
                    
                    if ($allCanRemove && $removed + count($symmetricPositions) <= $cellsToRemove) {
                        $currentGrid = $tempGrid;
                        $removed += count($symmetricPositions);
                    }
                }
            }
        }
        
        return $currentGrid;
    }

    /**
     * Ottiene valori 1-9 in ordine casuale deterministico
     * 
     * @return array<int>
     */
    private function getShuffledValues(): array
    {
        $values = range(1, 9);
        $this->shuffleArray($values);
        return $values;
    }

    /**
     * Ottiene tutte le posizioni della griglia
     * 
     * @return array<array{int, int}>
     */
    private function getAllPositions(): array
    {
        $positions = [];
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $positions[] = [$row, $col];
            }
        }
        return $positions;
    }

    /**
     * Shuffle deterministico di un array
     */
    private function shuffleArray(array &$array): void
    {
        for ($i = count($array) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
        }
    }

    /**
     * Determina se usare la rimozione simmetrica
     */
    private function useSymmetricRemoval(Grid $grid, int $row, int $col): bool
    {
        // Usa rimozione simmetrica nel 30% dei casi
        return mt_rand(1, 100) <= 30;
    }

    /**
     * Ottiene posizioni simmetriche per una cella
     * 
     * @return array<array{int, int}>
     */
    private function getSymmetricPositions(int $row, int $col): array
    {
        $positions = [];
        
        // Simmetria centrale (180 gradi)
        $centerRow = 8 - $row;
        $centerCol = 8 - $col;
        if ($centerRow !== $row || $centerCol !== $col) {
            $positions[] = [$centerRow, $centerCol];
        }
        
        // Simmetria orizzontale (opzionale)
        if (mt_rand(1, 100) <= 20) {
            $hRow = 8 - $row;
            if ($hRow !== $row && !in_array([$hRow, $col], $positions)) {
                $positions[] = [$hRow, $col];
            }
        }
        
        // Simmetria verticale (opzionale)
        if (mt_rand(1, 100) <= 20) {
            $vCol = 8 - $col;
            if ($vCol !== $col && !in_array([$row, $vCol], $positions)) {
                $positions[] = [$row, $vCol];
            }
        }
        
        return $positions;
    }

    /**
     * Ottiene il range di givens per una difficoltà
     * 
     * @return array{min: int, max: int}
     */
    private function getGivensRangeForDifficulty(string $difficulty): array
    {
        return match (strtolower($difficulty)) {
            'easy', 'facile' => ['min' => 36, 'max' => 46],
            'medium', 'medio' => ['min' => 28, 'max' => 35],
            'hard', 'difficile' => ['min' => 22, 'max' => 27],
            'expert', 'esperto' => ['min' => 17, 'max' => 21],
            default => ['min' => 25, 'max' => 35], // Default: medium
        };
    }

    /**
     * Genera un numero casuale nel range specificato
     */
    private function getRandomInt(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    /**
     * Genera una griglia con pattern specifico (per testing)
     */
    public function generateWithPattern(int $seed, string $pattern): Grid
    {
        $this->setSeed($seed);
        
        return match ($pattern) {
            'minimal' => $this->generateMinimalPuzzle($seed),
            'symmetric' => $this->generateSymmetricPuzzle($seed),
            'diagonal' => $this->generateDiagonalPuzzle($seed),
            default => $this->generatePuzzle($seed),
        };
    }

    /**
     * Genera un puzzle con il minor numero possibile di givens
     */
    private function generateMinimalPuzzle(int $seed): Grid
    {
        $completeGrid = $this->generateCompleteGrid($seed);
        
        // Rimuovi celle fino al limite minimo (tipicamente 17)
        for ($givens = 25; $givens >= 17; $givens--) {
            try {
                $puzzle = $this->removeCells($completeGrid, 81 - $givens);
                if ($this->validator->hasUniqueSolution($puzzle)) {
                    return $puzzle;
                }
            } catch (\Exception $e) {
                // Continua con più givens
                continue;
            }
        }
        
        // Fallback
        return $this->generatePuzzle($seed, 17);
    }

    /**
     * Genera un puzzle con simmetria perfetta
     */
    private function generateSymmetricPuzzle(int $seed): Grid
    {
        $completeGrid = $this->generateCompleteGrid($seed);
        $currentGrid = $completeGrid;
        
        $positions = $this->getAllPositions();
        $this->shuffleArray($positions);
        
        $processed = [];
        
        foreach ($positions as [$row, $col]) {
            $key = "{$row},{$col}";
            if (isset($processed[$key])) {
                continue;
            }
            
            // Trova posizione simmetrica
            $symRow = 8 - $row;
            $symCol = 8 - $col;
            $symKey = "{$symRow},{$symCol}";
            
            if ($row === $symRow && $col === $symCol) {
                // Cella centrale, rimuovi singolarmente
                $testGrid = $currentGrid->clearCell($row, $col);
                if ($this->validator->hasUniqueSolution($testGrid)) {
                    $currentGrid = $testGrid;
                }
            } else {
                // Rimuovi entrambe le celle simmetriche
                $testGrid = $currentGrid->clearCell($row, $col)->clearCell($symRow, $symCol);
                if ($this->validator->hasUniqueSolution($testGrid)) {
                    $currentGrid = $testGrid;
                }
            }
            
            $processed[$key] = true;
            $processed[$symKey] = true;
        }
        
        return $currentGrid;
    }

    /**
     * Genera un puzzle con pattern diagonale
     */
    private function generateDiagonalPuzzle(int $seed): Grid
    {
        $this->setSeed($seed);
        
        $grid = Grid::empty();
        
        // Riempie prima le diagonali (box 0, 4, 8)
        $grid = $this->fillDiagonalBoxes($grid);
        
        // Completa il resto della griglia
        $completeGrid = $this->fillGridRecursive($grid);
        
        if ($completeGrid === null) {
            throw new InvalidGridException("Failed to generate diagonal puzzle");
        }
        
        // Rimuovi celle mantenendo il pattern
        return $this->removeCells($completeGrid, 56); // 25 givens
    }

    /**
     * Marca tutte le celle con valore come "given" per il puzzle finale
     */
    private function markRemainingCellsAsGiven(Grid $puzzle): Grid
    {
        // Approccio più semplice: crea una nuova griglia con solo le celle given
        $givenValues = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $puzzle->getCell($row, $col);
                if ($cell->hasValue()) {
                    $givenValues[$row][$col] = $cell->value;
                } else {
                    $givenValues[$row][$col] = null;
                }
            }
        }
        
        // Crea una nuova griglia e imposta i valori come given
        $newGrid = Grid::fromArray($givenValues);
        
        return $newGrid;
    }

}
