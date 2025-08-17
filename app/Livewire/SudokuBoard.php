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
    public bool $candidatesAllowed = true;
    public int $completionPercentage = 0;
    
    // Timer e statistiche
    public int $timeElapsed = 0;
    public bool $timerRunning = false;
    public int $errorsCount = 0;
    public int $hintsUsed = 0;
    public ?float $startedAt = null; // timestamp in secondi con microtime(true)
    public ?float $finishedAt = null; // timestamp fine
    public ?int $finalElapsedMs = null; // durata finale in millisecondi
    
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
    
    // Loading state
    public bool $isLoading = false;
    
    // Feedback visivo errori
    public bool $showErrorEffect = false;

    public bool $startTimer = false;
    
    // Sistema hint
    public bool $hintsEnabled = true;
    public string $lastHintMessage = '';
    public string $lastHintTechnique = '';
    public bool $isCompetitiveMode = false;
    public ?int $highlightedHintRow = null;
    public ?int $highlightedHintCol = null;
    public ?int $highlightedHintValue = null;

    public function mount(
        ?array $initialGrid = null,
        bool $readOnly = false,
        bool $startTimer = false,
        ?bool $showCandidates = null,
        ?bool $candidatesAllowed = null,
        ?int $initialSeconds = null,
        ?array $currentGrid = null,
        ?int $initialErrors = null,
        bool $isCompetitiveMode = false,
        bool $hintsEnabled = true
    ): void {
        $this->readOnly = $readOnly;
        $this->startTimer = $startTimer;
        $this->isCompetitiveMode = $isCompetitiveMode;
        $this->hintsEnabled = $hintsEnabled;
        if ($candidatesAllowed !== null) {
            $this->candidatesAllowed = $candidatesAllowed;
        }
        if ($showCandidates !== null) {
            $this->showCandidates = $showCandidates;
        } elseif ($candidatesAllowed === false) {
            $this->showCandidates = false;
        }
        $this->initializeGrid($initialGrid);
        if (is_array($currentGrid) && !empty($currentGrid)) {
            $this->grid = $currentGrid;
        }
        if (!$this->readOnly && $this->selectedRow === null) {
            $this->selectedRow = 0;
            $this->selectedCol = 0;
        }
        if ($initialSeconds !== null && $initialSeconds >= 0) {
            $this->timeElapsed = (int) $initialSeconds;
        }
        if ($initialErrors !== null && $initialErrors >= 0) {
            $this->errorsCount = (int) $initialErrors;
        }
        if ($this->startTimer) {
            $this->timerRunning = true;
            $this->startedAt = microtime(true);
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
                $validator = app(\App\Domain\Sudoku\Contracts\ValidatorInterface::class);
                $attempts = 0;
                $maxAttempts = 15;
                $puzzle = null;
                // Prova prima con il seed fisso per coerenza demo
                $seed = 123456;
                do {
                    $attempts++;
                    $puzzle = $generator->generatePuzzleWithDifficulty($seed, 'easy');
                    $noImpossibleCells = empty($validator->getValidationErrors($puzzle)['impossible_cells'] ?? []);
                    $isValid = $validator->isValid($puzzle) && $validator->isSolvable($puzzle) && $validator->hasUniqueSolution($puzzle) && $noImpossibleCells;
                    if (!$isValid) {
                        // Cambia seed e riprova
                        $seed = random_int(1000, 999999);
                    }
                } while(!$isValid && $attempts < $maxAttempts);

                $this->initialGrid = $puzzle->toArray();
                $this->grid = $puzzle->toArray();
                $this->isValid = $isValid;
            } catch (\Exception $e) {
                // Fallback a griglia vuota in caso di errore
                $this->initialGrid = array_fill(0, 9, array_fill(0, 9, null));
                $this->grid = array_fill(0, 9, array_fill(0, 9, null));
                $this->isValid = false;
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
            $cellValue = $this->grid[$row][$col];
            $isGiven = $this->initialGrid[$row][$col] !== null;
            $hasConflict = $cellValue && $this->hasConflict($row, $col, $cellValue);
            $blockNum = floor($row / 3) * 3 + floor($col / 3) + 1;
            
            $description = "Selezionata cella riga " . ($row + 1) . " colonna " . ($col + 1) . ", blocco " . $blockNum;
            
            if ($cellValue) {
                $description .= $isGiven ? ", numero fisso " . $cellValue : ", valore inserito " . $cellValue;
            } else {
                $description .= ", cella vuota";
            }
            
            if ($hasConflict) {
                $description .= ", attenzione: numero in conflitto";
            }
            
            $this->lastAction = $description;
        }
        
        $this->dispatch('cell-selected', row: $row, col: $col);
    }

    /**
     * Imposta un valore in una cella (con validazione dominio)
     */
    public function setCellValue(int $row, int $col, ?int $value): void
    {
        if ($this->readOnly || $this->isGivenCell($row, $col)) {
            return;
        }
        
        // Pulisci l'evidenziazione hint quando l'utente inserisce un valore
        $this->clearHintHighlight();

        // Avvia timer al primo input se non già avviato
        if (!$this->timerRunning && !$this->readOnly && $value !== null) {
            $this->startTimer();
        }

        // Validazione dominio prima di applicare la mossa
        if ($value !== null) {
            /** @var \App\Domain\Sudoku\Contracts\ValidatorInterface $validator */
            $validator = app(\App\Domain\Sudoku\Contracts\ValidatorInterface::class);

            // Costruisci una griglia di dominio corretta:
            // - i valori di initialGrid sono "given"
            // - i valori inseriti dall'utente NON sono given
            $domainGrid = \App\Domain\Sudoku\Grid::fromArray($this->initialGrid);
            for ($r = 0; $r < 9; $r++) {
                for ($c = 0; $c < 9; $c++) {
                    $current = $this->grid[$r][$c] ?? null;
                    $isGiven = $this->initialGrid[$r][$c] !== null;
                    if ($current !== null && !$isGiven) {
                        $domainGrid = $domainGrid->setCell($r, $c, (int) $current);
                    }
                }
            }

            if (!$validator->canPlaceValue($domainGrid, $row, $col, (int) $value)) {
                $this->errorsCount++;
                $this->isValid = false;
                $this->triggerErrorEffect();
                if ($this->announceChanges) {
                    $this->lastAction = "Mossa non valida: {$value} in riga " . ($row + 1) . " colonna " . ($col + 1);
                }
                return; // Non applicare la mossa
            }

            // Anche verifica che lo stato resti risolvibile
            $testGrid = $domainGrid->setCell($row, $col, (int) $value);
            if (!$validator->isSolvable($testGrid)) {
                $this->errorsCount++;
                $this->isValid = false;
                $this->triggerErrorEffect();
                if ($this->announceChanges) {
                    $this->lastAction = "Mossa porta a stato non risolvibile: {$value} in riga " . ($row + 1) . " colonna " . ($col + 1);
                }
                return; // Non applicare la mossa
            }
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
     * Gestisce un batch di input da tastiera (invocato dalla tastiera on‑screen)
     *
     * @param array<int, string> $keys
     */
    public function handleKeyInputBatch(array $keys = []): void
    {
        if (empty($keys)) {
            return;
        }

        foreach ($keys as $key) {
            if (!is_string($key)) {
                continue;
            }
            $this->handleKeyInput($key);
        }
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
            $this->lastAction = __('app.hints.candidate_removed', [
                'number' => $number,
                'row' => $row + 1,
                'col' => $col + 1
            ]);
        } else {
            $candidates[] = $number;
            sort($candidates);
            $this->lastAction = __('app.hints.candidate_added', [
                'number' => $number,
                'row' => $row + 1,
                'col' => $col + 1
            ]);
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
        $this->isLoading = true;
        
        try {
            // Usa il service container per dependency injection
            $generator = app(\App\Domain\Sudoku\Contracts\GeneratorInterface::class);
            $validator = app(\App\Domain\Sudoku\Contracts\ValidatorInterface::class);

            // Genera garantendo validità e unicità soluzione
            $attempts = 0;
            $maxAttempts = 15;
            $puzzle = null;
            $isValid = false;
            $seed = 0;
            do {
                $attempts++;
                $seed = random_int(1000, 999999);
                $puzzle = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
                $noImpossibleCells = empty($validator->getValidationErrors($puzzle)['impossible_cells'] ?? []);
                $isValid = $validator->isValid($puzzle) && $validator->isSolvable($puzzle) && $validator->hasUniqueSolution($puzzle) && $noImpossibleCells;
            } while(!$isValid && $attempts < $maxAttempts);

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
            $this->isValid = $isValid;
            
            // NON avviare timer automaticamente - si avvia al primo input
            $this->timerRunning = false;
            
            $this->lastAction = "Caricato nuovo puzzle {$difficulty} (seed: {$seed})";
        } catch (\Exception $e) {
            $this->lastAction = "Errore caricamento puzzle: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
            $this->dispatch('puzzle-loaded'); // Evento per sincronizzazione UI
        }
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
        $this->moves = []; $this->currentMoveIndex = -1; $this->isCompleted = false;
        $this->timerRunning = false;
        $this->startedAt = null; $this->finishedAt = null; $this->finalElapsedMs = null;
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

    /**
     * Ottiene un suggerimento dal solver per la prossima mossa
     */
    public function getHint(): void
    {
        if (!$this->hintsEnabled || $this->readOnly || $this->isCompleted) {
            return;
        }

        try {
            /** @var SolverInterface $solver */
            $solver = app(SolverInterface::class);
            $grid = $this->createGridObject();
            
            // Ottieni il prossimo step logico
            $stepResult = $solver->solveStep($grid);
            
            if ($stepResult['grid'] !== null && $stepResult['technique'] !== null && $stepResult['step'] !== null) {
                $this->hintsUsed++;
                $this->lastHintTechnique = $stepResult['technique'];
                
                // Applica penalizzazione nelle sfide competitive 
                if ($this->isCompetitiveMode) {
                    $this->timeElapsed += 20; // +20 secondi
                    $this->lastHintMessage = __('app.hints.competitive_penalty', [
                        'technique' => $this->getTechniqueName($stepResult['technique'])
                    ]);
                } else {
                    // Modalità demo: spiegazione dettagliata
                    $this->lastHintMessage = $this->getDetailedHintExplanation(
                        $stepResult['technique'], 
                        $stepResult['step'], 
                        $grid
                    );
                }
                
                // Evidenzia il candidato suggerito invece di inserirlo automaticamente
                $step = $stepResult['step'];
                if (isset($step['row'], $step['col'], $step['value'])) {
                    // Seleziona la cella e evidenzia il candidato
                    $this->selectedRow = $step['row'];
                    $this->selectedCol = $step['col'];
                    $this->highlightedHintRow = $step['row'];
                    $this->highlightedHintCol = $step['col'];
                    $this->highlightedHintValue = $step['value'];
                    
                    // Assicurati che la cella sia in modalità candidati per visualizzare l'evidenziazione
                    if ($this->candidatesAllowed && $this->grid[$step['row']][$step['col']] === null) {
                        $this->showCandidates = true;
                    }
                }
                
                if ($this->announceChanges) {
                    $this->lastAction = $this->lastHintMessage;
                }
                
                // Emetti evento per eventuali listener esterni
                $this->dispatch('hint-used', [
                    'technique' => $stepResult['technique'],
                    'hintsUsed' => $this->hintsUsed,
                    'timeElapsed' => $this->timeElapsed
                ]);
                
            } else {
                $this->lastHintMessage = "Nessun suggerimento logico disponibile al momento";
                if ($this->announceChanges) {
                    $this->lastAction = $this->lastHintMessage;
                }
            }
            
        } catch (\Exception $e) {
            $this->lastHintMessage = __('app.hints.processing_error', ['error' => $e->getMessage()]);
            if ($this->announceChanges) {
                $this->lastAction = $this->lastHintMessage;
            }
            
            // Log per debugging
            \Log::error('Hint system error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'grid_state' => $this->grid,
            ]);
        }
    }

    /**
     * Crea un oggetto Grid dal state corrente del componente
     */
    private function createGridObject(): Grid
    {
        return Grid::fromArray($this->grid);
    }

    /**
     * Traduce il nome della tecnica nella lingua corrente
     */
    private function getTechniqueName(string $technique): string
    {
        $translationKey = 'app.hints.techniques.' . $technique;
        $translated = __($translationKey);
        
        // Se la traduzione non esiste, usa un fallback
        if ($translated === $translationKey) {
            return ucfirst(str_replace('_', ' ', $technique));
        }
        
        return $translated;
    }

    /**
     * Genera spiegazioni dettagliate per la modalità demo
     */
    private function getDetailedHintExplanation(string $technique, array $step, Grid $grid): string
    {
        $row = $step['row'] ?? 0;
        $col = $step['col'] ?? 0;
        $value = $step['value'] ?? 0;
        $cellName = $this->getCellName($row, $col);
        
        $translationKey = 'app.hints.explanations.' . $technique;
        $translated = __($translationKey, [
            'cell' => $cellName,
            'value' => $value
        ]);
        
        // Se la traduzione specifica non esiste, usa il template default
        if ($translated === $translationKey) {
            return __('app.hints.explanations.default', [
                'technique' => $this->getTechniqueName($technique),
                'cell' => $cellName,
                'value' => $value
            ]);
        }
        
        return $translated;
    }

    /**
     * Converte coordinate in nome cella leggibile (es: R1C5 -> "R1C5")
     */
    private function getCellName(int $row, int $col): string
    {
        return sprintf("R%dC%d", $row + 1, $col + 1);
    }

    /**
     * Pulisce l'evidenziazione del hint
     */
    public function clearHintHighlight(): void
    {
        $this->highlightedHintRow = null;
        $this->highlightedHintCol = null;
        $this->highlightedHintValue = null;
        $this->lastHintMessage = '';
        $this->lastHintTechnique = '';
    }

    /**
     * Verifica se un candidato è quello evidenziato dal hint
     */
    public function isHintHighlighted(int $row, int $col, int $value): bool
    {
        return $this->highlightedHintRow === $row && 
               $this->highlightedHintCol === $col && 
               $this->highlightedHintValue === $value;
    }

    /**
     * Attiva l'effetto visivo per errori (sfondo rosso temporaneo)
     */
    public function triggerErrorEffect(): void
    {
        $this->showErrorEffect = true;
        $this->dispatch('error-effect-triggered');
    }
}
