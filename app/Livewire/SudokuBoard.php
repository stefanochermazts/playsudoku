<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\MoveLog;
use App\Domain\Sudoku\ValueObjects\Move;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente Livewire per la board Sudoku 9×9
 */
class SudokuBoard extends Component
{
    use SudokuBoardHelpers;
    // Stato della griglia
    public array $grid = [];
    public array $initialGrid = [];
    public array $candidates = [];
    
    // Stato dell'interfaccia
    public ?int $selectedRow = null;
    public ?int $selectedCol = null;
    public string $inputMode = 'value'; // 'value' o 'candidates'
    public bool $highlightConflicts = true;
    public bool $showCandidates = true;
    public int $completionPercentage = 0;
    
    // Timer e statistiche
    public int $timeElapsed = 0;
    public bool $timerRunning = false;
    public int $errorsCount = 0;
    public int $hintsUsed = 0;
    
    // Undo/Redo e mosse
    public array $moves = [];
    public int $currentMoveIndex = -1;
    public int $maxUndoSteps = 100;
    
    // Stato del gioco
    public bool $isCompleted = false;
    public bool $isValid = true;
    public bool $readOnly = false;
    
    // Accessibilità
    public string $lastAction = '';
    public bool $announceChanges = true;

    public bool $startTimer = false;

    public function mount(
        ?array $initialGrid = null,
        bool $readOnly = false,
        bool $startTimer = false
    ): void {
        $this->readOnly = $readOnly;
        $this->startTimer = $startTimer;
        $this->initializeGrid($initialGrid);
        if (!$this->readOnly && $this->selectedRow === null) {
            $this->selectedRow = 0;
            $this->selectedCol = 0;
        }
        if ($this->startTimer) {
            $this->timerRunning = true;
        }
    }

    public function render()
    {
        $this->computeCompletion();
        return view('livewire.sudoku-board', [
            'conflicts' => $this->highlightConflicts ? $this->findConflicts() : [],
            'completionPercentage' => $this->completionPercentage ?? 0,
        ]);
    }

    /**
     * Inizializza la griglia
     */
    private function initializeGrid(?array $initialGrid = null): void
    {
        if ($initialGrid) {
            $this->initialGrid = $initialGrid;
            $this->grid = $initialGrid;
        } else {
            // Genera un puzzle di esempio per il demo invece di griglia vuota
            try {
                $generator = app(\App\Domain\Sudoku\Contracts\GeneratorInterface::class);
                $seed = 123456; // Seed fisso per il demo
                $puzzle = $generator->generatePuzzleWithDifficulty($seed, 'easy');
                $this->initialGrid = $puzzle->toArray();
                $this->grid = $puzzle->toArray();
            } catch (\Exception $e) {
                // Fallback a griglia vuota in caso di errore
                $this->initialGrid = array_fill(0, 9, array_fill(0, 9, null));
                $this->grid = array_fill(0, 9, array_fill(0, 9, null));
            }
        }

        // Inizializza i candidati
        $this->initializeCandidates();
        
        // Seleziona automaticamente la prima cella se non readonly
        if (!$this->readOnly && $this->selectedRow === null) {
            $this->selectedRow = 0;
            $this->selectedCol = 0;
        }
    }

    /**
     * Inizializza i candidati per tutte le celle vuote
     */
    private function initializeCandidates(): void
    {
        $this->candidates = [];
        
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $this->candidates[$row][$col] = [];
            }
        }

        // Popola i candidati iniziali in base alla griglia corrente
        $this->recomputeAllCandidates();
    }

    /**
     * Seleziona una cella
     */
    public function selectCell(int $row, int $col): void
    {
        if ($this->readOnly) return;
        
        $this->selectedRow = $row;
        $this->selectedCol = $col;
        
        if ($this->announceChanges) {
            $cellValue = $this->grid[$row][$col] ?? 'vuota';
            $this->lastAction = "Selezionata cella riga " . ($row + 1) . " colonna " . ($col + 1) . ", valore: " . $cellValue;
        }
        
        $this->dispatch('cell-selected', row: $row, col: $col);
    }

    /**
     * Imposta un valore in una cella
     */
        public function setCellValue(int $row, int $col, ?int $value): void
    {
        if ($this->readOnly || $this->isGivenCell($row, $col)) {
            return;
        }

        // Avvia timer al primo input se non già avviato
        if (!$this->timerRunning && !$this->readOnly && $value !== null) {
            $this->timerRunning = true;
        }

        $oldValue = $this->grid[$row][$col];
        
        // Registra la mossa per undo
        $this->recordMove($row, $col, $oldValue, $value, 'set_value');
        
        // Imposta il valore
        $this->grid[$row][$col] = $value;
        
        // Pulisci i candidati per questa cella se hai impostato un valore
        if ($value !== null) {
            $this->candidates[$row][$col] = [];
        }

        // Ricomputa i candidati dopo il cambiamento
        $this->recomputeAllCandidates();

        // Verifica se il gioco è completato
        $this->checkCompletion();
        
        // Aggiorna annunci accessibilità
        if ($this->announceChanges) {
            $announcement = $value 
                ? "Inserito {$value} in riga " . ($row + 1) . " colonna " . ($col + 1)
                : "Cancellato valore da riga " . ($row + 1) . " colonna " . ($col + 1);
            $this->lastAction = $announcement;
        }

        $this->dispatch('cell-changed', row: $row, col: $col, value: $value);
    }

    /**
     * Gestisce input da tastiera
     */
    public function handleKeyInput(?string $key = null): void
    {
        // Evita errori se chiamato senza parametro
        if ($key === null) {
            return;
        }

        if ($this->selectedRow === null || $this->selectedCol === null) {
            $this->selectedRow = 0; $this->selectedCol = 0;
        }
        $row = $this->selectedRow;
        $col = $this->selectedCol;
        match ($key) {
            '1','2','3','4','5','6','7','8','9' => $this->handleNumberInput((int) $key, $row, $col),
            'Backspace','Delete','0' => $this->setCellValue($row, $col, null),
            'ArrowUp' => $this->moveSelection(0, -1),
            'ArrowDown' => $this->moveSelection(0, 1),
            'ArrowLeft' => $this->moveSelection(-1, 0),
            'ArrowRight' => $this->moveSelection(1, 0),
            'c','C' => $this->toggleInputMode(),
            'u','U' => $this->undo(),
            'r','R' => $this->redo(),
            default => null
        };
    }

    /**
     * Gestisce input numerico
     */
    private function handleNumberInput(int $number, int $row, int $col): void
    {
        if ($this->inputMode === 'value') {
            $this->setCellValue($row, $col, $number);
        } else {
            $this->toggleCandidate($row, $col, $number);
        }
    }

    /**
     * Muove la selezione
     */
    private function moveSelection(int $deltaCol, int $deltaRow): void
    {
        if ($this->selectedRow === null || $this->selectedCol === null) {
            $this->selectCell(0, 0);
            return;
        }

        $newRow = max(0, min(8, $this->selectedRow + $deltaRow));
        $newCol = max(0, min(8, $this->selectedCol + $deltaCol));
        
        $this->selectCell($newRow, $newCol);
    }

    /**
     * Alterna modalità input
     */
    public function toggleInputMode(): void
    {
        $this->inputMode = $this->inputMode === 'value' ? 'candidates' : 'value';
        
        if ($this->announceChanges) {
            $mode = $this->inputMode === 'value' ? 'valori definitivi' : 'candidati';
            $this->lastAction = "Modalità cambiata a: " . $mode;
        }
    }

    /**
     * Alterna un candidato in una cella
     */
    public function toggleCandidate(int $row, int $col, int $number): void
    {
        if ($this->readOnly || $this->isGivenCell($row, $col) || $this->grid[$row][$col] !== null) {
            return;
        }

        if (!isset($this->candidates[$row][$col])) {
            $this->candidates[$row][$col] = [];
        }

        $candidates = &$this->candidates[$row][$col];
        
        if (in_array($number, $candidates)) {
            $candidates = array_values(array_diff($candidates, [$number]));
            $action = "rimosso";
        } else {
            $candidates[] = $number;
            sort($candidates);
            $action = "aggiunto";
        }

        if ($this->announceChanges) {
            $this->lastAction = "Candidato {$number} {$action} in riga " . ($row + 1) . " colonna " . ($col + 1);
        }
    }

    /**
     * Undo ultima mossa
     */
    public function undo(): void
    {
        if ($this->currentMoveIndex < 0) {
            return;
        }

        $move = $this->moves[$this->currentMoveIndex];
        
        // Ripristina lo stato precedente
        $this->grid[$move['row']][$move['col']] = $move['oldValue'];
        $this->candidates[$move['row']][$move['col']] = $move['oldCandidates'] ?? [];
        
        $this->currentMoveIndex--;
        
        // Ricomputa i candidati
        $this->recomputeAllCandidates();

        if ($this->announceChanges) {
            $this->lastAction = "Annullata ultima mossa";
        }
        
        $this->dispatch('move-undone');
    }

    /**
     * Redo mossa annullata
     */
    public function redo(): void
    {
        if ($this->currentMoveIndex >= count($this->moves) - 1) {
            return;
        }

        $this->currentMoveIndex++;
        $move = $this->moves[$this->currentMoveIndex];
        
        // Riapplica la mossa
        $this->grid[$move['row']][$move['col']] = $move['newValue'];
        if (isset($move['newCandidates'])) {
            $this->candidates[$move['row']][$move['col']] = $move['newCandidates'];
        }
        
        // Ricomputa i candidati
        $this->recomputeAllCandidates();

        if ($this->announceChanges) {
            $this->lastAction = "Ripetuta mossa annullata";
        }
        
        $this->dispatch('move-redone');
    }

    /**
     * Tick del timer
     */
    #[On('timer-tick')]
    public function tickTimer(): void
    {
        if ($this->timerRunning) {
            $this->timeElapsed++;
        }
    }

    /**
     * Carica un puzzle di esempio
     */
    public function loadSamplePuzzle(string $difficulty = 'medium'): void
    {
        // Usa il service container per dependency injection
        $generator = app(\App\Domain\Sudoku\Contracts\GeneratorInterface::class);
        
        $seed = random_int(1000, 999999);
        $puzzle = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
        
        $this->initialGrid = $puzzle->toArray();
        $this->grid = $puzzle->toArray();
        $this->initializeCandidates();
        
        // Reset stato del gioco
        $this->selectedRow = 0;
        $this->selectedCol = 0;
        $this->timeElapsed = 0;
        $this->errorsCount = 0;
        $this->hintsUsed = 0;
        $this->moves = [];
        $this->currentMoveIndex = -1;
        $this->isCompleted = false;
        $this->isValid = true;
        
        // NON avviare timer automaticamente - si avvia al primo input
        $this->timerRunning = false;
        
        $this->lastAction = "Caricato nuovo puzzle {$difficulty} (seed: {$seed})";
    }

    #[On('load-sample-puzzle')]
    public function loadSamplePuzzleEvent($payload = null): void
    {
        $difficulty = 'medium';
        if (is_array($payload) && isset($payload['difficulty'])) {
            $difficulty = (string) $payload['difficulty'];
        } elseif (is_string($payload) && $payload !== '') {
            $difficulty = $payload;
        }
        $this->loadSamplePuzzle($difficulty);
    }

    private function computeCompletion(): void
    {
        $filled = 0;
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                if ($this->grid[$r][$c] !== null) $filled++;
            }
        }
        $this->completionPercentage = (int) floor($filled / 81 * 100);
    }

    #[On('reload-board')]
    public function reloadBoard(?array $initialGrid = null): void
    {
        $this->initializeGrid($initialGrid);
        $this->selectedRow = 0; $this->selectedCol = 0;
        $this->timeElapsed = 0; $this->errorsCount = 0; $this->hintsUsed = 0;
        $this->moves = []; $this->currentMoveIndex = -1; $this->isCompleted = false; $this->isValid = true;
        $this->timerRunning = true;
        $this->recomputeAllCandidates();
        $this->lastAction = 'Nuova board caricata';
    }

    /**
     * Ricomputa tutti i candidati in base allo stato corrente della griglia
     */
    private function recomputeAllCandidates(): void
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] === null && !$this->isGivenCell($row, $col)) {
                    $this->candidates[$row][$col] = $this->computeCandidatesForCell($row, $col);
                } else {
                    $this->candidates[$row][$col] = [];
                }
            }
        }
    }

    /**
     * Calcola i candidati validi per una cella
     *
     * @return int[]
     */
    private function computeCandidatesForCell(int $row, int $col): array
    {
        $used = array_unique(array_merge(
            $this->getNumbersInRow($row),
            $this->getNumbersInCol($col),
            $this->getNumbersInBox($row, $col)
        ));
        $all = range(1, 9);
        $candidates = array_values(array_diff($all, $used));
        sort($candidates);
        return $candidates;
    }

    /** @return int[] */
    private function getNumbersInRow(int $row): array
    {
        $nums = [];
        for ($c = 0; $c < 9; $c++) {
            if ($this->grid[$row][$c] !== null) {
                $nums[] = (int) $this->grid[$row][$c];
            }
        }
        return $nums;
    }

    /** @return int[] */
    private function getNumbersInCol(int $col): array
    {
        $nums = [];
        for ($r = 0; $r < 9; $r++) {
            if ($this->grid[$r][$col] !== null) {
                $nums[] = (int) $this->grid[$r][$col];
            }
        }
        return $nums;
    }

    /** @return int[] */
    private function getNumbersInBox(int $row, int $col): array
    {
        $nums = [];
        $startRow = (int) (floor($row / 3) * 3);
        $startCol = (int) (floor($col / 3) * 3);
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($this->grid[$r][$c] !== null) {
                    $nums[] = (int) $this->grid[$r][$c];
                }
            }
        }
        return $nums;
    }

    public function inputNumber(int $number): void
    {
        if ($this->selectedRow === null || $this->selectedCol === null) {
            $this->selectedRow = 0; $this->selectedCol = 0;
        }
        $this->handleNumberInput($number, $this->selectedRow, $this->selectedCol);
    }

    public function promoteCandidate(int $row, int $col, int $number): void
    {
        if ($this->readOnly || $this->isGivenCell($row, $col)) return;
        // Promuove a valore definitivo se il candidato esiste (opzionale: verifichiamo)
        $cellCandidates = $this->candidates[$row][$col] ?? [];
        if (!in_array($number, $cellCandidates, true)) {
            // Se non presente, lo aggiungiamo comunque come valore definitivo
        }
        $this->setCellValue($row, $col, $number);
    }
}
