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
    public int $elapsedTime = 0;
    public ?int $finalElapsedMs = null; // in millisecondi
    public bool $timerRunning = false;
    public bool $isCompleted = false;
    public bool $isReadOnly = false;
    public bool $isArchivedChallenge = false;
    public array $conflicts = [];

    public function mount($challengeId): void
    {
        $this->challenge = Challenge::with('puzzle')->findOrFail((int) $challengeId);
        
        // Determina se la sfida è in modalità allenamento (scaduta/non attiva)
        $this->isArchivedChallenge = ($this->challenge->status !== 'active' || $this->challenge->ends_at <= now());

        // Carica o crea tentativo
        $this->loadOrCreateAttempt();
        
        // Inizializza la griglia
        $this->initializeGrid();
        
        // Controlla se le hints sono bloccate
        if (isset($this->challenge->settings['hints_allowed']) && !$this->challenge->settings['hints_allowed']) {
            $this->showCandidates = false;
        }
        
        // Avvia il timer se non è read-only e non è già completato
        if (!$this->isReadOnly && !$this->isCompleted) {
            $this->timerRunning = true;
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
        
        // Ripristina snapshot se presente
        if ($this->attempt && $this->attempt->current_state) {
            $this->grid = is_array($this->attempt->current_state)
                ? $this->attempt->current_state
                : json_decode($this->attempt->current_state, true);
            // Se duration_ms è null, ripristina elapsedTime solo da snapshot salvato
            // Usando secondi interi calcolati dal DB se esiste un apposito campo
        }
    }

    private function initializeGrid(): void
    {
        // Se c'è stato salvato, la griglia è già stata impostata in loadOrCreateAttempt()
        if (empty($this->grid)) {
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
        // Aggiorna last activity
        $this->attempt?->update(['last_activity_at' => now()]);

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
        
        // Anti-abuso base: validazione risultato prima del salvataggio definitivo
        $isValid = true;
        
        // 1) Modalità allenamento: attempts su sfide scadute non sono competitivi
        if ($this->isArchivedChallenge) {
            $isValid = false;
        }
        
        // 2) Tempo minimo ragionevole (evita submit istantanei/abusi)
        $minSeconds = 10; // configurabile se necessario
        if ($this->elapsedTime < $minSeconds) {
            $isValid = false;
        }
        
        // 3) Confronto con soluzione del puzzle
        $solution = $this->challenge->puzzle->solution;
        if (is_string($solution)) {
            $solution = json_decode($solution, true);
        }
        if (!is_array($solution) || count($solution) !== 9) {
            $isValid = false;
        } else {
            for ($r = 0; $r < 9 && $isValid; $r++) {
                for ($c = 0; $c < 9; $c++) {
                    if (($this->grid[$r][$c] ?? null) !== ($solution[$r][$c] ?? null)) {
                        $isValid = false;
                        break;
                    }
                }
            }
        }
        
        // 4) Un solo completamento per tentativo (idempotenza)
        if ($this->attempt->completed_at !== null) {
            $isValid = false;
        }
        
        // 5) Soglia pause: invalidare se pause totali > 70% del tempo reale
        if ($this->attempt) {
            $realDurationMs = $this->attempt->started_at ? 
                (int) abs($this->attempt->started_at->diffInMilliseconds(now())) : 
                ($this->elapsedTime * 1000);
            $pausedMs = (int) ($this->attempt->paused_ms_total ?? 0);
            if ($realDurationMs > 0 && ($pausedMs / $realDurationMs) > 0.7) {
                $isValid = false;
            }
        }
        
        // 6) Limite numero pause (max 5)
        if (($this->attempt->pauses_count ?? 0) > 5) {
            $isValid = false;
        }
        
        // Aggiorna il tentativo con tempo preciso
        $durationMs = $this->finalElapsedMs ?? ($this->elapsedTime * 1000);
        
        $this->attempt->update([
            'duration_ms' => $durationMs,
            'errors_count' => $this->errorCount,
            'completed_at' => now(),
            'final_state' => $this->grid,
            'valid' => $isValid,
        ]);

        // Programma validazione anti-cheat asincrona per tentativi validi competitivi
        if ($isValid && in_array($this->challenge->type, ['daily', 'weekly'])) {
            \App\Jobs\ValidateAttemptMovesJob::dispatch($this->attempt, true);
            \App\Jobs\AnalyzeTimingAnomaliesJob::dispatch($this->challenge, $this->attempt);
        }
        
        // Award badges per tentativi validi
        if ($isValid) {
            app(\App\Services\BadgeService::class)->onChallengeCompleted(auth()->user(), [
                'duration_ms' => $durationMs,
                'errors_count' => $this->errorCount,
                'hints_used' => $this->attempt->hints_used ?? 0,
                'difficulty' => $this->challenge->puzzle->difficulty ?? 'normal',
                'type' => $this->challenge->type,
            ]);
        }
        
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
            'duration_ms' => $this->elapsedTime * 1000,
        ]);
    }

    #[On('tick-timer')]
    public function tickTimer(): void
    {
        if ($this->timerRunning && !$this->isCompleted) {
            $this->elapsedTime++;
            
            // Controllo time_limit
            $timeLimit = $this->challenge->settings['time_limit'] ?? null;
            if ($timeLimit && ($this->elapsedTime * 1000) >= $timeLimit) {
                $this->timeExpired();
            }
        }
    }

    /**
     * Gestisce la scadenza del tempo limite
     */
    public function timeExpired(): void
    {
        if ($this->isCompleted) return;
        
        $this->timerRunning = false;
        $this->isReadOnly = true;
        $this->isCompleted = true;
        
        // Gestisci pause in corso prima di salvare
        if ($this->attempt && $this->attempt->pause_started_at) {
            $pauseStart = $this->attempt->pause_started_at;
            $pauseEnd = now();
            
            if ($pauseStart <= $pauseEnd) {
                $pausedMs = (int) abs($pauseStart->diffInMilliseconds($pauseEnd));
                $currentPausedMs = (int) ($this->attempt->paused_ms_total ?? 0);
                $this->attempt->paused_ms_total = $currentPausedMs + $pausedMs;
            }
            $this->attempt->pause_started_at = null;
        }
        
        // Salva il tentativo come non completato ma con tempo scaduto
        $this->attempt->update([
            'duration_ms' => $this->elapsedTime * 1000,
            'errors_count' => $this->errorCount,
            'current_state' => $this->grid,
            'valid' => false, // Non valido perché tempo scaduto
        ]);
        
        $this->dispatch('time-expired');
        session()->flash('warning', 'Tempo scaduto! Il tentativo è stato salvato ma non è valido per la classifica.');
    }

    #[On('puzzle-completed')]
    public function onPuzzleCompleted(int $time, int $errors = 0, ?array $grid = null, ?int $finalMs = null): void
    {
        if ($this->isCompleted) return;
        
        // Usa il tempo finale preciso in millisecondi se disponibile
        if ($finalMs !== null) {
            $this->elapsedTime = (int) round($finalMs / 1000); // Secondi interi
            $this->finalElapsedMs = $finalMs; // Millisecondi precisi
        } else {
            $this->elapsedTime = $time;
        }
        
        $this->errorCount = $errors;
        if (is_array($grid)) {
            $this->grid = $grid;
        }
        // Se c'era una pausa in corso, accumula il tempo pausa e azzera il flag
        if ($this->attempt && $this->attempt->pause_started_at) {
            // Calcola il tempo pausa assicurandosi che sia positivo
            $pauseStart = $this->attempt->pause_started_at;
            $pauseEnd = now();
            
            // Verifica che pause_started_at non sia nel futuro
            if ($pauseStart <= $pauseEnd) {
                $pausedMs = (int) abs($pauseStart->diffInMilliseconds($pauseEnd));
                $currentPausedMs = (int) ($this->attempt->paused_ms_total ?? 0);
                $newTotalPausedMs = $currentPausedMs + $pausedMs;
                
                $this->attempt->paused_ms_total = $newTotalPausedMs;
            } else {
                // Se pause_started_at è nel futuro, ignora questo calcolo
                \Log::warning('Pause start time is in the future, skipping pause calculation', [
                    'attempt_id' => $this->attempt->id,
                    'pause_started_at' => $pauseStart->toDateTimeString(),
                    'now' => $pauseEnd->toDateTimeString()
                ]);
            }
            
            $this->attempt->pause_started_at = null;
            $this->attempt->save();
        }
        $this->completeChallenge();
    }

    public function getFormattedTime(): string
    {
        // Se abbiamo il tempo finale preciso, mostriamo i centesimi
        if ($this->isCompleted && $this->finalElapsedMs !== null) {
            $totalMs = $this->finalElapsedMs;
            $minutes = intdiv($totalMs, 60_000);
            $seconds = intdiv($totalMs % 60_000, 1000);
            $centis = intdiv($totalMs % 1000, 10);
            return sprintf('%d:%02d.%02d', $minutes, $seconds, $centis);
        }
        
        // Durante il gioco: solo mm:ss
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

    public function pauseChallengeWithState(?array $state = null): void
    {
        $this->timerRunning = false;
        // Gestione pausa: marca inizio pausa, aggiorna last activity
        if ($this->attempt) {
            $now = now();
            $this->attempt->pause_started_at = $now;
            $this->attempt->last_activity_at = $now;
            $this->attempt->save();
        }
        if (is_array($state)) {
            $grid = $state['grid'] ?? null;
            $errors = $state['errors'] ?? null;
            $seconds = $state['seconds'] ?? null;
            if (is_array($grid)) {
                $this->grid = $grid;
            }
            if (is_int($errors)) {
                $this->errorCount = $errors;
            }
            if (is_int($seconds)) {
                $this->elapsedTime = $seconds;
            }
        }
        $this->saveAttemptState();
        // Dopo salvataggio, redirect alla lista challenges
        $url = app()->has('locale') && in_array(app()->getLocale(), ['en', 'it'])
            ? route('localized.challenges.index', ['locale' => app()->getLocale()])
            : route('challenges.index');
        // Restituiamo il redirect per farlo gestire a Livewire
        $this->redirect($url, navigate: true);
    }

    /**
     * Listener per l'evento hint-used dal SudokuBoard
     */
    #[On('hint-used')]
    public function onHintUsed(array $data): void
    {
        if ($this->attempt && !$this->isCompleted) {
            // Aggiorna il conteggio hint nel database
            $this->attempt->hints_used = $data['hintsUsed'] ?? $this->attempt->hints_used + 1;
            
            // Aggiorna il tempo se c'è stata penalizzazione
            if (isset($data['timeElapsed'])) {
                $this->elapsedTime = $data['timeElapsed'];
                $this->attempt->duration_ms = $this->elapsedTime * 1000;
            }
            
            $this->attempt->save();
            
            // Salva anche lo stato corrente
            $this->saveAttemptState();
        }
    }
}