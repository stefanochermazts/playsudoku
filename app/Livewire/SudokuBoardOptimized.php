<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\MoveLog;
use App\Domain\Sudoku\ValueObjects\Move;
use App\Domain\Sudoku\Contracts\SolverInterface;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Versione ottimizzata del componente SudokuBoard per migliori performance
 */
class SudokuBoardOptimized extends Component
{
    use SudokuBoardHelpers;
    
    // Stato della griglia
    public array $grid = [];
    public array $initialGrid = [];
    public array $candidates = [];
    
    // Stato dell'interfaccia
    public ?int $selectedRow = null;
    public ?int $selectedCol = null;
    public string $inputMode = 'value';
    public bool $highlightConflicts = true;
    public bool $showCandidates = true;
    public bool $candidatesAllowed = true;
    
    // Timer e statistiche
    public int $timeElapsed = 0;
    public bool $timerRunning = false;
    public int $errorsCount = 0;
    public int $hintsUsed = 0;
    public ?float $startedAt = null;
    public ?float $finishedAt = null;
    public ?int $finalElapsedMs = null;
    
    // Undo/Redo e mosse
    public array $moves = [];
    public int $currentMoveIndex = -1;
    public int $maxUndoSteps = 50; // Ridotto da 100 per memoria
    
    // Stato del gioco
    public bool $isCompleted = false;
    public bool $isValid = true;
    public bool $readOnly = false;
    
    // Accessibilità
    public string $lastAction = '';
    public bool $announceChanges = true;
    public bool $startTimer = false;
    
    // Feedback visivo errori
    public bool $showErrorEffect = false;
    
    // Sistema hint
    public bool $hintsEnabled = true;
    public string $lastHintMessage = '';
    public ?int $highlightedHintRow = null;
    public ?int $highlightedHintCol = null;
    public ?int $highlightedHintValue = null;
    public string $lastHintTechnique = '';
    public bool $isCompetitiveMode = false;

    // **OTTIMIZZAZIONI - Cache e stati computati**
    private ?int $cachedCompletionPercentage = null;
    private ?array $cachedConflicts = null;
    private string $lastGridStateHash = '';
    private bool $gridChanged = true;
    private array $conflictCache = []; // Cache per conflitti per posizione
    
    // Throttling per aggiornamenti rapidi
    private float $lastGridUpdate = 0;
    private const GRID_UPDATE_THROTTLE = 0.1; // 100ms minimum tra aggiornamenti

    /**
     * OTTIMIZZAZIONE: Render con cache intelligente
     */
    public function render()
    {
        $currentGridHash = $this->getGridStateHash();
        
        // Solo se la griglia è cambiata, ricalcola
        if ($currentGridHash !== $this->lastGridStateHash) {
            $this->gridChanged = true;
            $this->lastGridStateHash = $currentGridHash;
            $this->invalidateCache();
        } else {
            $this->gridChanged = false;
        }

        return view('livewire.sudoku-board', [
            'conflicts' => $this->getCachedConflicts(),
            'completionPercentage' => $this->getCachedCompletion(),
        ]);
    }

    /**
     * OTTIMIZZAZIONE: Hash dello stato della griglia per detection cambiamenti
     */
    private function getGridStateHash(): string
    {
        return md5(serialize($this->grid));
    }

    /**
     * OTTIMIZZAZIONE: Invalidazione cache quando griglia cambia
     */
    private function invalidateCache(): void
    {
        $this->cachedCompletionPercentage = null;
        $this->cachedConflicts = null;
        $this->conflictCache = [];
    }

    /**
     * OTTIMIZZAZIONE: Completion percentage con cache
     */
    private function getCachedCompletion(): int
    {
        if ($this->cachedCompletionPercentage === null || $this->gridChanged) {
            $filled = 0;
            for ($r = 0; $r < 9; $r++) {
                for ($c = 0; $c < 9; $c++) {
                    if ($this->grid[$r][$c] !== null) $filled++;
                }
            }
            $this->cachedCompletionPercentage = (int) floor($filled / 81 * 100);
        }
        
        return $this->cachedCompletionPercentage;
    }

    /**
     * OTTIMIZZAZIONE: Conflitti con cache e calcolo differenziale
     */
    private function getCachedConflicts(): array
    {
        if (!$this->highlightConflicts) {
            return [];
        }

        if ($this->cachedConflicts === null || $this->gridChanged) {
            $this->cachedConflicts = $this->findConflictsOptimized();
        }
        
        return $this->cachedConflicts;
    }

    /**
     * OTTIMIZZAZIONE: Algoritmo conflitti migliorato O(n) invece di O(n²)
     */
    private function findConflictsOptimized(): array
    {
        $conflicts = [];
        $rowSets = array_fill(0, 9, []);
        $colSets = array_fill(0, 9, []);
        $boxSets = array_fill(0, 9, []);
        
        // Prima passata: costruisce set di valori per riga/colonna/box
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $value = $this->grid[$row][$col];
                if ($value === null) continue;
                
                $boxIndex = intval($row / 3) * 3 + intval($col / 3);
                
                // Registra posizioni per ogni valore
                $rowSets[$row][$value][] = ['row' => $row, 'col' => $col];
                $colSets[$col][$value][] = ['row' => $row, 'col' => $col];
                $boxSets[$boxIndex][$value][] = ['row' => $row, 'col' => $col];
            }
        }
        
        // Seconda passata: identifica conflitti
        $conflictPositions = [];
        
        foreach ($rowSets as $row => $values) {
            foreach ($values as $value => $positions) {
                if (count($positions) > 1) {
                    foreach ($positions as $pos) {
                        $conflictPositions[$pos['row']][$pos['col']] = true;
                    }
                }
            }
        }
        
        foreach ($colSets as $col => $values) {
            foreach ($values as $value => $positions) {
                if (count($positions) > 1) {
                    foreach ($positions as $pos) {
                        $conflictPositions[$pos['row']][$pos['col']] = true;
                    }
                }
            }
        }
        
        foreach ($boxSets as $box => $values) {
            foreach ($values as $value => $positions) {
                if (count($positions) > 1) {
                    foreach ($positions as $pos) {
                        $conflictPositions[$pos['row']][$pos['col']] = true;
                    }
                }
            }
        }
        
        // Converte in formato array
        foreach ($conflictPositions as $row => $cols) {
            foreach ($cols as $col => $hasConflict) {
                if ($hasConflict) {
                    $conflicts[] = ['row' => $row, 'col' => $col];
                }
            }
        }
        
        return $conflicts;
    }

    /**
     * OTTIMIZZAZIONE: Set cell con throttling e batch updates
     */
    public function setCellOptimized(int $row, int $col, ?int $value): void
    {
        $now = microtime(true);
        
        // Throttling per evitare troppi aggiornamenti rapidi
        if ($now - $this->lastGridUpdate < self::GRID_UPDATE_THROTTLE) {
            return;
        }
        
        $this->lastGridUpdate = $now;
        
        // Aggiorna solo se valore cambiato
        if ($this->grid[$row][$col] === $value) {
            return;
        }
        
        $oldValue = $this->grid[$row][$col];
        $this->grid[$row][$col] = $value;
        
        // Invalida cache solo per le aree interessate (riga/colonna/box)
        $this->invalidateAffectedCache($row, $col, $oldValue, $value);
        
        // Aggiorna candidati solo se necessario
        if ($this->candidatesAllowed) {
            $this->updateCandidatesForPosition($row, $col, $oldValue, $value);
        }
        
        // Registra la mossa per undo/redo
        $this->addMove($row, $col, $oldValue, $value);
        
        // Controlla completamento solo se necessario
        if ($value !== null && $this->getCachedCompletion() === 100) {
            $this->checkCompletion();
        }
    }

    /**
     * OTTIMIZZAZIONE: Invalidazione cache selettiva
     */
    private function invalidateAffectedCache(int $row, int $col, ?int $oldValue, ?int $newValue): void
    {
        // Invalida completion solo se filling/unfilling
        if (($oldValue === null) !== ($newValue === null)) {
            $this->cachedCompletionPercentage = null;
        }
        
        // Invalida conflitti solo se valore inserito/rimosso
        if ($oldValue !== $newValue) {
            $this->cachedConflicts = null;
        }
    }

    /**
     * OTTIMIZZAZIONE: Aggiornamento candidati incrementale
     */
    private function updateCandidatesForPosition(int $row, int $col, ?int $oldValue, ?int $newValue): void
    {
        if ($newValue !== null) {
            // Rimuovi il nuovo valore dai candidati della riga/colonna/box
            $this->removeCandidateFromUnit($row, $col, $newValue);
        }
        
        if ($oldValue !== null) {
            // Riaggiungi il vecchio valore ai candidati se possibile
            $this->addCandidateToUnit($row, $col, $oldValue);
        }
    }

    /**
     * OTTIMIZZAZIONE: Rimozione candidato da unità (riga/colonna/box)
     */
    private function removeCandidateFromUnit(int $row, int $col, int $value): void
    {
        // Riga
        for ($c = 0; $c < 9; $c++) {
            if ($c !== $col && $this->grid[$row][$c] === null) {
                $this->candidates[$row][$c] = array_values(array_filter(
                    $this->candidates[$row][$c] ?? [],
                    fn($candidate) => $candidate !== $value
                ));
            }
        }
        
        // Colonna
        for ($r = 0; $r < 9; $r++) {
            if ($r !== $row && $this->grid[$r][$col] === null) {
                $this->candidates[$r][$col] = array_values(array_filter(
                    $this->candidates[$r][$col] ?? [],
                    fn($candidate) => $candidate !== $value
                ));
            }
        }
        
        // Box 3x3
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;
        
        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $this->grid[$r][$c] === null) {
                    $this->candidates[$r][$c] = array_values(array_filter(
                        $this->candidates[$r][$c] ?? [],
                        fn($candidate) => $candidate !== $value
                    ));
                }
            }
        }
    }

    /**
     * OTTIMIZZAZIONE: Aggiunta candidato a unità se valido
     */
    private function addCandidateToUnit(int $row, int $col, int $value): void
    {
        // Controlla se il valore può essere candidato nelle celle vuote
        
        // Riga
        for ($c = 0; $c < 9; $c++) {
            if ($c !== $col && $this->grid[$row][$c] === null && $this->isValidCandidate($row, $c, $value)) {
                $candidates = $this->candidates[$row][$c] ?? [];
                if (!in_array($value, $candidates)) {
                    $candidates[] = $value;
                    sort($candidates);
                    $this->candidates[$row][$c] = $candidates;
                }
            }
        }
        
        // Colonna
        for ($r = 0; $r < 9; $r++) {
            if ($r !== $row && $this->grid[$r][$col] === null && $this->isValidCandidate($r, $col, $value)) {
                $candidates = $this->candidates[$r][$col] ?? [];
                if (!in_array($value, $candidates)) {
                    $candidates[] = $value;
                    sort($candidates);
                    $this->candidates[$r][$col] = $candidates;
                }
            }
        }
        
        // Box 3x3
        $boxStartRow = intval($row / 3) * 3;
        $boxStartCol = intval($col / 3) * 3;
        
        for ($r = $boxStartRow; $r < $boxStartRow + 3; $r++) {
            for ($c = $boxStartCol; $c < $boxStartCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $this->grid[$r][$c] === null && $this->isValidCandidate($r, $c, $value)) {
                    $candidates = $this->candidates[$r][$c] ?? [];
                    if (!in_array($value, $candidates)) {
                        $candidates[] = $value;
                        sort($candidates);
                        $this->candidates[$r][$c] = $candidates;
                    }
                }
            }
        }
    }

    /**
     * OTTIMIZZAZIONE: Verifica se un valore può essere candidato
     */
    private function isValidCandidate(int $row, int $col, int $value): bool
    {
        // Usa hasConflict esistente ma solo per la cella specifica
        $originalValue = $this->grid[$row][$col];
        $this->grid[$row][$col] = $value;
        $hasConflict = $this->hasConflict($row, $col, $value);
        $this->grid[$row][$col] = $originalValue;
        
        return !$hasConflict;
    }

    /**
     * OTTIMIZZAZIONE: Aggiunta mossa con limite sliding window
     */
    private function addMove(int $row, int $col, ?int $oldValue, ?int $newValue): void
    {
        $move = [
            'row' => $row,
            'col' => $col,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'timestamp' => microtime(true)
        ];
        
        // Rimuovi mosse dopo currentMoveIndex se facendo una nuova mossa
        if ($this->currentMoveIndex < count($this->moves) - 1) {
            $this->moves = array_slice($this->moves, 0, $this->currentMoveIndex + 1);
        }
        
        $this->moves[] = $move;
        $this->currentMoveIndex++;
        
        // Mantieni solo le ultime maxUndoSteps mosse (sliding window)
        if (count($this->moves) > $this->maxUndoSteps) {
            $this->moves = array_slice($this->moves, -$this->maxUndoSteps);
            $this->currentMoveIndex = count($this->moves) - 1;
        }
    }

    // Mantieni compatibilità con metodi esistenti
    public function setCell(int $row, int $col, ?int $value): void
    {
        $this->setCellOptimized($row, $col, $value);
    }

    /**
     * OTTIMIZZAZIONE: Gestione completamento con debouncing
     */
    private function checkCompletion(): void
    {
        $completion = $this->getCachedCompletion();
        
        if ($completion === 100 && !$this->isCompleted) {
            // Verifica che non ci siano conflitti
            $conflicts = $this->getCachedConflicts();
            
            if (empty($conflicts)) {
                $this->isCompleted = true;
                $this->timerRunning = false;
                $this->finishedAt = microtime(true);
                
                if ($this->startedAt) {
                    $this->finalElapsedMs = (int)(($this->finishedAt - $this->startedAt) * 1000);
                }
                
                $this->lastAction = 'Puzzle completato!';
                $this->dispatch('puzzle-completed');
            }
        }
    }

    /**
     * Attiva l'effetto visivo per errori (sfondo rosso temporaneo)
     */
    public function triggerErrorEffect(): void
    {
        $this->showErrorEffect = true;
        $this->dispatch('error-effect-triggered');
    }

    // TODO: Implementare altri metodi necessari mantenendo le ottimizzazioni...
    // Per ora mantengo la compatibilità delegando alla classe originale
    public function __call($method, $parameters)
    {
        // Fallback temporaneo alla classe parent per metodi non ancora ottimizzati
        return parent::$method(...$parameters);
    }
}
