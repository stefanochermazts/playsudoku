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
        
        // Genera lista di valori possibili ordinata per efficacia
        $validValues = $this->getOptimalValueOrder($grid, $row, $col);
        
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
     * Implementazione ottimizzata per migliorare le performance di generazione
     */
    private function findMostConstrainedCell(Grid $grid): ?array
    {
        $bestCell = null;
        $minCandidates = 10; // Più di 9 per inizializzare
        $bestCandidateCount = 0;
        
        // Prima passata: cerca celle con 2 candidati (molto efficiente)
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->isEmpty()) {
                    $candidateCount = $this->countValidValues($grid, $row, $col);
                    
                    // Se troviamo una cella con 2 candidati, prendiamola subito
                    if ($candidateCount === 2) {
                    return [$row, $col];
                    }
                    
                    // Tieni traccia della migliore cella trovata finora
                    if ($candidateCount < $minCandidates) {
                        $minCandidates = $candidateCount;
                        $bestCell = [$row, $col];
                        $bestCandidateCount = $candidateCount;
                    }
                }
            }
        }
        
        // Se non abbiamo celle vuote, la griglia è completa
        if ($bestCell === null) {
            return null;
        }
        
        // Se la miglior cella ha 1 candidato, ritornala (naked single)
        if ($bestCandidateCount === 1) {
            return $bestCell;
        }
        
        // Se la miglior cella ha 3+ candidati, cerca con euristica aggiuntiva
        if ($bestCandidateCount >= 3) {
            $improvedCell = $this->findCellWithBestHeuristics($grid, $minCandidates);
            if ($improvedCell !== null) {
                return $improvedCell;
            }
        }
        
        return $bestCell;
    }

    /**
     * Applica euristiche aggiuntive per scegliere la migliore cella tra quelle con pochi candidati
     */
    private function findCellWithBestHeuristics(Grid $grid, int $maxCandidates): ?array
    {
        $candidates = [];
        
        // Raccoglie tutte le celle con il numero minimo di candidati
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $grid->getCell($row, $col);
                if ($cell->isEmpty()) {
                    $candidateCount = $this->countValidValues($grid, $row, $col);
                    if ($candidateCount === $maxCandidates) {
                        $candidates[] = [
                            'row' => $row,
                            'col' => $col,
                            'score' => $this->calculateCellHeuristicScore($grid, $row, $col)
                        ];
                    }
                }
            }
        }
        
        if (empty($candidates)) {
            return null;
        }
        
        // Ordina per punteggio euristico (più alto = migliore)
        usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return [$candidates[0]['row'], $candidates[0]['col']];
    }

    /**
     * Calcola un punteggio euristico per una cella basato su:
     * - Numero di celle vuote nella riga/colonna/box
     * - Presenza di celle con pochi candidati nelle vicinanze
     */
    private function calculateCellHeuristicScore(Grid $grid, int $row, int $col): int
    {
        $score = 0;
        
        // Bonus per righe/colonne/box con molte celle vuote (più constraining)
        $emptyInRow = $this->countEmptyCellsInRow($grid, $row);
        $emptyInCol = $this->countEmptyCellsInColumn($grid, $col);
        $emptyInBox = $this->countEmptyCellsInBox($grid, intval($row / 3), intval($col / 3));
        
        $score += ($emptyInRow + $emptyInCol + $emptyInBox) * 2;
        
        // Bonus per vicinanza a celle con pochi candidati
        $nearbyConstrainedCells = $this->countNearbyConstrainedCells($grid, $row, $col);
        $score += $nearbyConstrainedCells * 5;
        
        return $score;
    }

    /**
     * Conta rapidamente i valori validi per una cella senza generare l'intero set di candidati
     */
    private function countValidValues(Grid $grid, int $row, int $col): int
    {
        $usedValues = [];
        
        // Raccogli valori usati nella riga
        for ($c = 0; $c < 9; $c++) {
            $value = $grid->getCell($row, $c)->value;
            if ($value !== null) {
                $usedValues[$value] = true;
            }
        }
        
        // Raccogli valori usati nella colonna
        for ($r = 0; $r < 9; $r++) {
            $value = $grid->getCell($r, $col)->value;
            if ($value !== null) {
                $usedValues[$value] = true;
            }
        }
        
        // Raccogli valori usati nel box
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;
        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                $value = $grid->getCell($r, $c)->value;
                if ($value !== null) {
                    $usedValues[$value] = true;
                }
            }
        }
        
        // Conta valori disponibili
        return 9 - count($usedValues);
    }

    private function countEmptyCellsInRow(Grid $grid, int $row): int
    {
        $count = 0;
        for ($col = 0; $col < 9; $col++) {
            if ($grid->getCell($row, $col)->isEmpty()) {
                $count++;
            }
        }
        return $count;
    }

    private function countEmptyCellsInColumn(Grid $grid, int $col): int
    {
        $count = 0;
        for ($row = 0; $row < 9; $row++) {
            if ($grid->getCell($row, $col)->isEmpty()) {
                $count++;
            }
        }
        return $count;
    }

    private function countEmptyCellsInBox(Grid $grid, int $boxRow, int $boxCol): int
    {
        $count = 0;
        $startRow = $boxRow * 3;
        $startCol = $boxCol * 3;
        
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($grid->getCell($r, $c)->isEmpty()) {
                    $count++;
                }
            }
        }
        return $count;
    }

    private function countNearbyConstrainedCells(Grid $grid, int $targetRow, int $targetCol): int
    {
        $constrainedCount = 0;
        
        // Controlla celle nella stessa riga
        for ($col = 0; $col < 9; $col++) {
            if ($col !== $targetCol && $grid->getCell($targetRow, $col)->isEmpty()) {
                $candidateCount = $this->countValidValues($grid, $targetRow, $col);
                if ($candidateCount <= 3) {
                    $constrainedCount++;
                }
            }
        }
        
        // Controlla celle nella stessa colonna
        for ($row = 0; $row < 9; $row++) {
            if ($row !== $targetRow && $grid->getCell($row, $targetCol)->isEmpty()) {
                $candidateCount = $this->countValidValues($grid, $row, $targetCol);
                if ($candidateCount <= 3) {
                    $constrainedCount++;
                }
            }
        }
        
        // Controlla celle nello stesso box
        $boxStartRow = intval($targetRow / 3) * 3;
        $boxStartCol = intval($targetCol / 3) * 3;
        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $targetRow || $c !== $targetCol) && $grid->getCell($r, $c)->isEmpty()) {
                    $candidateCount = $this->countValidValues($grid, $r, $c);
                    if ($candidateCount <= 3) {
                        $constrainedCount++;
                    }
                }
            }
        }
        
        return $constrainedCount;
    }

    /**
     * Ottiene l'ordine ottimale dei valori da provare per una cella
     * Basato su LCV (Least Constraining Value) heuristic
     */
    private function getOptimalValueOrder(Grid $grid, int $row, int $col): array
    {
        $values = $this->getShuffledValues();
        $validValues = [];
        
        // Prima filtra solo i valori validi
        foreach ($values as $value) {
            if ($this->validator->canPlaceValue($grid, $row, $col, $value)) {
                $validValues[] = [
                    'value' => $value,
                    'constraint_score' => $this->calculateValueConstraintScore($grid, $row, $col, $value)
                ];
            }
        }
        
        // Ordina per constraint score (meno constraining first = LCV)
        usort($validValues, fn($a, $b) => $a['constraint_score'] <=> $b['constraint_score']);
        
        // Estrai solo i valori ordinati
        return array_map(fn($item) => $item['value'], $validValues);
    }

    /**
     * Calcola quanto un valore è "constraining" per le celle vicine
     * Punteggio più basso = meno constraining = meglio provare per primo
     */
    private function calculateValueConstraintScore(Grid $grid, int $row, int $col, int $value): int
    {
        $constraintScore = 0;
        
        // Conta quante celle vuote nella riga perderebbero questo valore come candidato
        for ($c = 0; $c < 9; $c++) {
            if ($c !== $col && $grid->getCell($row, $c)->isEmpty()) {
                if ($this->validator->canPlaceValue($grid, $row, $c, $value)) {
                    $remainingCandidates = $this->countValidValues($grid, $row, $c) - 1;
                    // Penalizza di più se riduce molto le opzioni
                    $constraintScore += $remainingCandidates <= 2 ? 10 : 1;
                }
            }
        }
        
        // Conta quante celle vuote nella colonna perderebbero questo valore come candidato
        for ($r = 0; $r < 9; $r++) {
            if ($r !== $row && $grid->getCell($r, $col)->isEmpty()) {
                if ($this->validator->canPlaceValue($grid, $r, $col, $value)) {
                    $remainingCandidates = $this->countValidValues($grid, $r, $col) - 1;
                    $constraintScore += $remainingCandidates <= 2 ? 10 : 1;
                }
            }
        }
        
        // Conta quante celle vuote nel box perderebbero questo valore come candidato
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;
        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $grid->getCell($r, $c)->isEmpty()) {
                    if ($this->validator->canPlaceValue($grid, $r, $c, $value)) {
                        $remainingCandidates = $this->countValidValues($grid, $r, $c) - 1;
                        $constraintScore += $remainingCandidates <= 2 ? 10 : 1;
                    }
                }
            }
        }
        
        return $constraintScore;
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
            if ($cell->hasValue()) {
                // Prova a rimuovere la cella (forza la rimozione anche se è given)
                $testGrid = $this->forceRemoveCell($currentGrid, $row, $col);
                
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
                        $testGrid = $this->forceRemoveCell($tempGrid, $sRow, $sCol);
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
            'normal', 'medium', 'medio' => ['min' => 28, 'max' => 35],
            'hard', 'difficile' => ['min' => 22, 'max' => 27],
            'expert', 'esperto' => ['min' => 17, 'max' => 21],
            'crazy' => ['min' => 12, 'max' => 16],
            default => ['min' => 25, 'max' => 35], // Default: normal
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
     * Forza la rimozione di una cella anche se è given
     */
    private function forceRemoveCell(Grid $grid, int $row, int $col): Grid
    {
        // Ottieni l'array della griglia
        $gridArray = $grid->toArray();
        
        // Imposta la cella come null
        $gridArray[$row][$col] = null;
        
        // Crea una nuova griglia
        return Grid::fromArray($gridArray);
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
