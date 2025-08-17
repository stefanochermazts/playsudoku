<div x-data="{}" 
     tabindex="0"
     wire:keydown.window="handleKeyInput($event.key)"
     class="sudoku-game w-full max-w-4xl mx-auto p-4 bg-white dark:bg-gray-900 rounded-xl shadow-lg"
     role="main"
     aria-label="{{ __('app.aria.sudoku_game') }}">

    {{-- Skip link per accessibilità --}}
    <a href="#sudoku-main-grid" 
       class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary-600 text-white px-4 py-2 rounded-md z-50">
        Vai alla griglia Sudoku
    </a>

    {{-- Header con timer e controlli --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
        {{-- Timer e statistiche --}}
        <section class="flex items-center space-x-6" 
                 role="region" 
                 aria-label="{{ __('app.aria.game_stats') }}">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white" data-role="timer" aria-label="{{ __('app.dashboard.game_time') }}">{{ $this->getFormattedTime() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.dashboard.time') }}</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $completionPercentage }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.board.completed') }}</div>
            </div>
            
            @if (!$readOnly)
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $errorsCount }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.board.errors') }}</div>
            </div>
            
            @if($hintsEnabled)
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $hintsUsed }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.board.hints') }}</div>
                </div>
            @endif
        @endif
        </section>

		{{-- Controlli --}}
		<section class="flex items-center gap-1" 
		         role="region" 
		         aria-label="{{ __('app.aria.game_controls') }}">
			@if (!$readOnly)
				@if($hintsEnabled)
					<button wire:click="getHint" 
					        class="px-2 py-1 rounded-md border text-xs font-medium bg-yellow-500 text-white hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500"
					        aria-label="{{ $isCompetitiveMode ? __('app.aria.request_hint_competitive') : __('app.aria.request_hint') }}"
					        @if($isCompleted) disabled @endif>
						💡 {{ __('app.board.hint_button') }}
						@if($isCompetitiveMode) <span class="text-xs">(+20s)</span> @endif
					</button>
				@endif
				@if($candidatesAllowed)
					<button wire:click="$toggle('showCandidates')" 
					        class="px-2 py-1 rounded-md border text-xs font-medium bg-white text-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-50 dark:hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
					        aria-label="{{ $showCandidates ? __('app.board.hide_candidates') : __('app.board.show_candidates') }}">
					        {!! $showCandidates ? '👁️ ' . __('app.board.hide_candidates') : '👁️ ' . __('app.board.show_candidates') !!}
					</button>
				@endif
				<button wire:click="undo" 
				        class="p-1.5 rounded-md border bg-gray-100 dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
				        aria-label="{{ __('app.aria.undo_move') }}">↶</button>
				<button wire:click="redo" 
				        class="p-1.5 rounded-md border bg-gray-100 dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
				        aria-label="{{ __('app.aria.redo_move') }}">↷</button>
			@endif
		</section>
    </div>

    {{-- Messaggio hint --}}
    @if($hintsEnabled && !empty($lastHintMessage))
        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-start space-x-2">
                <div class="text-yellow-600 dark:text-yellow-400 text-lg">💡</div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ $lastHintMessage }}
                    </div>
                    @if(!empty($lastHintTechnique))
                        <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                            {{ __('app.hints.technique_label') }} {{ $this->getTechniqueName($lastHintTechnique) }}
                        </div>
                    @endif
                    @if($highlightedHintValue !== null)
                        <div class="text-xs text-yellow-700 dark:text-yellow-300 mt-2 font-medium">
                            {{ __('app.hints.click_to_confirm') }}
                        </div>
                    @endif
                </div>
                @if($highlightedHintValue !== null)
                    <button wire:click="clearHintHighlight" 
                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-200 text-lg">
                        ✕
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Istruzioni tastiera (visibili solo per screen reader) --}}
    <div class="sr-only mb-4" aria-live="polite">
        <h2>Istruzioni navigazione da tastiera:</h2>
        <ul>
            <li>Usa le frecce direzionali per muoverti tra le celle</li>
            <li>Premi i numeri 1-9 per inserire un valore</li>
            <li>Premi Backspace o Delete per cancellare</li>
            <li>Premi Tab per passare ai controlli</li>
            @if(!$readOnly)
                <li>Premi C per attivare/disattivare i candidati</li>
                <li>Premi U per annullare, R per ripetere</li>
            @endif
        </ul>
    </div>

    {{-- Griglia principale --}}
    <div class="relative overflow-x-auto touch-pan-x touch-pan-y -mx-4 sm:mx-0">
        <div id="sudoku-main-grid"
             class="sudoku-grid mx-auto rounded-lg overflow-visible {{ $isCompetitiveMode ? 'competitive-mode' : '' }} {{ $showErrorEffect ? 'error-effect' : '' }}"
             style="position: relative;"
             role="grid"
             aria-label="{{ __('app.aria.sudoku_grid') }}"
             aria-rowcount="9"
             aria-colcount="9"
             style="display: grid; grid-template-columns: repeat(9, 1fr); grid-template-rows: repeat(9, 1fr); width: 100%; max-width: min(92vw, 640px); aspect-ratio: 1; gap: 0; border: 4px solid #1f2937; background-color: #1f2937; position: relative;"
             @if($isCompetitiveMode) 
                 oncontextmenu="return false;" 
                 onselectstart="return false;" 
                 ondragstart="return false;"
             @endif>
        @for ($row = 0; $row < 9; $row++)
            @for ($col = 0; $col < 9; $col++)
                @php
                    $value = $grid[$row][$col];
                    $isGiven = $initialGrid[$row][$col] !== null;
                    $isSelected = $selectedRow === $row && $selectedCol === $col;
                    $hasConflict = in_array(['row' => $row, 'col' => $col], $conflicts);
                    $cellCandidates = $candidates[$row][$col] ?? [];

                    // Evidenziazione riga/colonna/box della cella selezionata
                    $sameRow = $selectedRow === $row;
                    $sameCol = $selectedCol === $col;
                    $sameBox = $selectedRow !== null && $selectedCol !== null && (int) floor($selectedRow/3) === (int) floor($row/3) && (int) floor($selectedCol/3) === (int) floor($col/3);

                    // Classi base
                    $classes = ['sudoku-cell', 'flex', 'items-center', 'justify-center', 'text-center', 'cursor-pointer', 'bg-white', 'dark:bg-gray-800'];
                    
                    // Inizializza stili bordi prima di tutto (rimosso min-height per mobile)
                    $borderStyle = "width: 100%; height: 100%; aspect-ratio: 1; position: relative; border: 1px solid #d1d5db;";
                    
                    // Bordi spessi per separatori 3x3
                    if ($row % 3 === 0 && $row > 0) {
                        $borderStyle .= " border-top: 4px solid #1f2937;";
                        $classes[] = 'thick-top';
                    }
                    if ($col % 3 === 0 && $col > 0) {
                        $borderStyle .= " border-left: 4px solid #1f2937;";
                        $classes[] = 'thick-left';
                    }
                    
                    // Evidenziazioni
                    if (!$isSelected && ($sameRow || $sameCol || $sameBox)) {
                        $classes[] = 'bg-blue-50/70';
                        $classes[] = 'dark:bg-blue-900/25';
                    }
                    if ($isSelected) {
                        // Mostra solo il bordo interno: nascondi completamente il bordo esterno
                        $borderStyle .= " border-color: transparent !important; border-left-color: transparent !important; border-top-color: transparent !important; border-right-color: transparent !important; border-bottom-color: transparent !important; box-shadow: inset 0 0 0 3px #3b82f6;";
                    }
                    if ($hasConflict && $highlightConflicts) {
                        $classes[] = 'bg-red-100';
                        $classes[] = 'dark:bg-red-900';
                    }
                    if ($isGiven) {
                        $classes[] = 'bg-gray-100';
                        $classes[] = 'dark:bg-gray-700';
                    }
                @endphp
                
                <div wire:key="sudoku-cell-{{ $row }}-{{ $col }}" wire:click="selectCell({{ $row }}, {{ $col }})"
                     class="relative focus:outline-none {{ implode(' ', $classes) }} hover:bg-gray-50 dark:hover:bg-gray-700 {{ $hasConflict ? 'conflict-indicator' : '' }} {{ $isGiven ? 'given-indicator' : '' }}"
                     style="{{ $borderStyle }}"
                     data-row="{{ $row }}"
                     data-col="{{ $col }}"
                     role="gridcell" 
                     aria-rowindex="{{ $row + 1 }}"
                     aria-colindex="{{ $col + 1 }}"
                     aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                     aria-label="{{ 
                         $value ? __('app.aria.cell_with_value', ['row' => $row + 1, 'col' => $col + 1, 'value' => $value]) :
                         ($isGiven ? __('app.aria.cell_given', ['row' => $row + 1, 'col' => $col + 1]) :
                         __('app.aria.cell_empty', ['row' => $row + 1, 'col' => $col + 1]))
                     }}{{ $hasConflict ? ', ' . __('app.aria.cell_error') : '' }}"
                     tabindex="{{ $isSelected ? '0' : '-1' }}">
                
                    {{-- Overlay click full-cell per assicurare il click ovunque --}}
                    <button type="button" wire:click="selectCell({{ $row }}, {{ $col }})" aria-hidden="true" tabindex="-1"
                            class="absolute inset-0 w-full h-full focus:outline-none bg-transparent"></button>

                    @if($value)
                        <span wire:click="selectCell({{ $row }}, {{ $col }})" class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-none
                                    @if($hasConflict && $highlightConflicts) 
                                        text-red-700 dark:text-red-400
                                    @elseif($isGiven) 
                                        text-gray-800 {{ $isSelected ? 'dark:!text-white' : 'dark:!text-gray-200' }}
                                    @else 
                                        text-blue-700 {{ $isSelected ? 'dark:!text-white' : 'dark:!text-blue-400' }}
                                    @endif">
                            {{ $value }}
                        </span>
                    @elseif(!$isGiven && $showCandidates && $candidatesAllowed)
                        <div wire:click="selectCell({{ $row }}, {{ $col }})" class="grid grid-cols-3 gap-[2px] sm:gap-1 text-[12px] sm:text-sm text-gray-700 {{ $isSelected ? 'dark:!text-white' : 'dark:text-gray-300' }} p-2 sm:p-3 w-full h-full">
                            @for($i = 1; $i <= 9; $i++)
                                @php
                                    $isHintCandidate = $this->isHintHighlighted($row, $col, $i);
                                    $candidateClasses = "flex items-center justify-center rounded focus:outline-none focus:ring-1 focus:ring-blue-500 min-h-0 ";
                                    if ($isHintCandidate) {
                                        $candidateClasses .= "bg-yellow-400 dark:bg-yellow-500 text-white font-bold animate-pulse shadow-lg ring-2 ring-yellow-600 hover:bg-yellow-500 dark:hover:bg-yellow-600";
                                    } else {
                                        $candidateClasses .= "hover:bg-blue-50 dark:hover:bg-blue-900";
                                    }
                                @endphp
                                <button type="button"
                                        wire:click.stop="promoteCandidate({{ $row }}, {{ $col }}, {{ $i }})"
                                        class="{{ $candidateClasses }}"
                                        aria-label="{{ __('app.aria.candidate', ['number' => $i]) }}"
                                        @disabled(!in_array($i, $cellCandidates))>
                                    @if(in_array($i, $cellCandidates))
                                        {{ $i }}
                                    @else
                                        &nbsp;
                                    @endif
                                </button>
                            @endfor
                        </div>
                    @elseif(!$isGiven && !$value)
                        @if($isSelected)
                            <span class="text-gray-400 text-xl">•</span>
                        @endif
                    @endif
                </div>
            @endfor
        @endfor
        </div>
        
        {{-- Loading overlay --}}
        @if($isLoading)
            <div class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm rounded-lg flex items-center justify-center z-50"
                 role="status" 
                 aria-live="polite" 
                 aria-label="{{ __('app.aria.loading_puzzle') }}">
                <div class="flex flex-col items-center space-y-4">
                    {{-- Spinner animato --}}
                    <div class="relative">
                        <div class="w-16 h-16 border-4 border-primary-200 dark:border-primary-800 rounded-full animate-spin"></div>
                        <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-t-primary-600 dark:border-t-primary-400 rounded-full animate-spin"></div>
                    </div>
                    
                    {{-- Messaggio --}}
                    <div class="text-center">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">🎯 Generazione puzzle...</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Creazione griglia unica in corso</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Pannello numeri --}}
    @if (!$readOnly)
    <section class="mt-6" role="region" aria-label="{{ __('app.aria.number_input_panel') }}">
        <div class="flex flex-wrap justify-center gap-2">
            @for($num = 1; $num <= 9; $num++)
                <button wire:click="inputNumber({{ $num }})"
                        class="w-12 h-12 text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                               hover:bg-blue-50 dark:hover:bg-blue-900
                               focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="{{ __('app.aria.insert_number', ['number' => $num]) }}">
                    {{ $num }}
                </button>
            @endfor
            
            <button wire:click="setCellValue({{ $selectedRow ?? 0 }}, {{ $selectedCol ?? 0 }}, null)"
                    class="w-12 h-12 text-lg font-bold rounded-lg border border-gray-300 dark:border-gray-600 
                           bg-white dark:bg-gray-800 text-red-600 dark:text-red-400
                           hover:bg-red-50 dark:hover:bg-red-900
                           focus:outline-none focus:ring-2 focus:ring-red-500"
                    aria-label="{{ __('app.aria.clear_cell') }}">
                    ✕
            </button>
        </div>
    </section>
    @endif

    {{-- Annunci accessibilità --}}
    @if($announceChanges && $lastAction)
        <div aria-live="polite" class="sr-only">{{ $lastAction }}</div>
    @endif
    
    {{-- Status region per informazioni di gioco --}}
    <div aria-live="polite" aria-label="{{ __('app.aria.game_status') }}" class="sr-only">
        @if($isCompleted)
            {{ __('app.board.sudoku_completed') }}
        @else
            {{ __('app.board.sudoku_in_progress', ['percentage' => $completionPercentage]) }}
            @if($errorsCount > 0)
                {{ __('app.board.errors_detected', ['count' => $errorsCount]) }}
            @endif
            @if($hintsUsed > 0)
                {{ __('app.board.hints_used', ['count' => $hintsUsed]) }}
            @endif
        @endif
    </div>
</div>

@push('styles')
<style>
/* Focus styles migliorati per accessibilità WCAG 2.2 AA */
.sudoku-game {
    --focus-ring-color: #2563eb;
    --focus-ring-width: 3px;
    --focus-ring-offset: 2px;
}

/* Focus ring personalizzato per celle griglia */
.sudoku-grid [role="gridcell"]:focus {
    outline: var(--focus-ring-width) solid var(--focus-ring-color) !important;
    outline-offset: var(--focus-ring-offset);
    box-shadow: 0 0 0 calc(var(--focus-ring-width) + var(--focus-ring-offset)) rgba(37, 99, 235, 0.2);
    z-index: 10;
    position: relative;
}

/* Focus migliorato per pulsanti */
.sudoku-game button:focus {
    outline: var(--focus-ring-width) solid var(--focus-ring-color) !important;
    outline-offset: var(--focus-ring-offset);
    box-shadow: 0 0 0 calc(var(--focus-ring-width) + var(--focus-ring-offset)) rgba(37, 99, 235, 0.2);
}

/* Focus per skip link */
.skip-link:focus {
    outline: var(--focus-ring-width) solid #ffffff !important;
    outline-offset: var(--focus-ring-offset);
    box-shadow: 0 0 0 calc(var(--focus-ring-width) + var(--focus-ring-offset)) rgba(255, 255, 255, 0.3);
}

/* Dark mode focus adjustments */
.dark .sudoku-game {
    --focus-ring-color: #60a5fa;
}

.dark .sudoku-grid [role="gridcell"]:focus {
    box-shadow: 0 0 0 calc(var(--focus-ring-width) + var(--focus-ring-offset)) rgba(96, 165, 250, 0.3);
}

.dark .sudoku-game button:focus {
    box-shadow: 0 0 0 calc(var(--focus-ring-width) + var(--focus-ring-offset)) rgba(96, 165, 250, 0.3);
}

/* Miglioramenti contrasto WCAG AA (ratio 4.5:1 minimum) */
.sudoku-game {
    /* Colori con contrasto migliorato */
    --text-primary-light: #111827; /* gray-900 - contrasto 13.54:1 */
    --text-secondary-light: #4b5563; /* gray-600 - contrasto 7.32:1 */
    --text-error-light: #dc2626; /* red-600 - contrasto 5.74:1 */
    --text-success-light: #059669; /* emerald-600 - contrasto 4.56:1 */
    --text-warning-light: #d97706; /* amber-600 - contrasto 5.49:1 */
    
    --text-primary-dark: #f9fafb; /* gray-50 - contrasto 18.7:1 */
    --text-secondary-dark: #d1d5db; /* gray-300 - contrasto 9.85:1 */
    --text-error-dark: #fca5a5; /* red-300 - contrasto 5.2:1 */
    --text-success-dark: #6ee7b7; /* emerald-300 - contrasto 8.9:1 */
    --text-warning-dark: #fcd34d; /* amber-300 - contrasto 10.8:1 */
}

/* Applica i colori con contrasto migliorato */
.sudoku-game .text-error {
    color: var(--text-error-light);
}

.dark .sudoku-game .text-error {
    color: var(--text-error-dark);
}

.sudoku-game .text-success {
    color: var(--text-success-light);
}

.dark .sudoku-game .text-success {
    color: var(--text-success-dark);
}

.sudoku-game .text-warning {
    color: var(--text-warning-light);
}

.dark .sudoku-game .text-warning {
    color: var(--text-warning-dark);
}

/* Indicatori di stato visibili anche senza colore */
.conflict-indicator::before {
    content: "⚠️";
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 12px;
}

.given-indicator::before {
    content: "📌";
    position: absolute;
    top: 2px;
    left: 2px;
    font-size: 10px;
    opacity: 0.7;
}

/* Animazione focus smooth */
.sudoku-grid [role="gridcell"],
.sudoku-game button {
    transition: outline 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Stili ad alto contrasto per modalità accessibilità */
@media (prefers-contrast: high) {
    .sudoku-game {
        --focus-ring-width: 4px;
        --focus-ring-color: #000000;
    }
    
    .dark .sudoku-game {
        --focus-ring-color: #ffffff;
    }
}

/* Rispetta preferenze movimento ridotto */
@media (prefers-reduced-motion: reduce) {
    .sudoku-grid [role="gridcell"],
    .sudoku-game button {
        transition: none;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/sudoku-board-optimized.js') }}" defer></script>
<script>
    let sudokuTimerInterval;
    let uiTimerInterval;
    
    // Timer con centesimi di secondo
    let baseMs = @js($timeElapsed) * 1000; // Millisecondi
    let lastUpdateMs = Date.now();
    let running = @js($timerRunning);
    
    // Funzione per formattare il tempo con centesimi
    function formatTimeWithCentis(totalMs) {
        const minutes = Math.floor(totalMs / 60000);
        const seconds = Math.floor((totalMs % 60000) / 1000);
        const centis = Math.floor((totalMs % 1000) / 10);
        
        // Durante il gioco: solo mm:ss
        if (running) {
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        // A gioco finito: mm:ss.cc
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}.${centis.toString().padStart(2, '0')}`;
    }
    
    // Usa livewire:init invece di DOMContentLoaded per garantire che @this sia disponibile
    document.addEventListener('livewire:init', function() {
        if (window.APP_DEBUG) console.log('🚀 SudokuBoard: Livewire inizializzato');
        
        const timerEl = document.querySelector('[data-role="timer"]');
        
        // Clear any existing intervals
        if (sudokuTimerInterval) clearInterval(sudokuTimerInterval);
        if (uiTimerInterval) clearInterval(uiTimerInterval);
        
        // Timer backend (1s) - sincronizza con Livewire
        sudokuTimerInterval = setInterval(() => {
            const component = @this;
            if (component && typeof component.call === 'function' && running) {
                try {
                    component.call('tickTimer');
                } catch (error) {
                    if (window.APP_DEBUG) console.log('SudokuBoard timer tick error:', error);
                }
            }
        }, 1000);
        
        // Timer UI (100ms) - mostra centesimi
        function startUiTimer() {
            uiTimerInterval = setInterval(() => {
                if (running && timerEl) {
                    const now = Date.now();
                    baseMs += (now - lastUpdateMs);
                    lastUpdateMs = now;
                    timerEl.textContent = formatTimeWithCentis(baseMs);
                }
            }, 100);
        }
        
        function stopUiTimer() {
            if (uiTimerInterval) {
                clearInterval(uiTimerInterval);
                uiTimerInterval = null;
            }
        }

        // Event listeners per stato timer
        window.addEventListener('start-timer', () => { 
            running = true; 
            lastUpdateMs = Date.now(); 
            startUiTimer(); 
        });
        window.addEventListener('stop-timer', () => { 
            running = false; 
            stopUiTimer();
            // Mostra tempo finale con centesimi
            if (timerEl) {
                timerEl.textContent = formatTimeWithCentis(baseMs);
            }
        });
        window.addEventListener('puzzle-completed', () => { 
            running = false; 
            stopUiTimer();
            // Mostra tempo finale con centesimi
            if (timerEl) {
                timerEl.textContent = formatTimeWithCentis(baseMs);
            }
        });
        
        // Gestione focus accessibilità
        window.addEventListener('livewire:update', function() {
            // Sincronizza focus DOM con selezione Livewire
            const selectedCell = document.querySelector('[role="gridcell"][aria-selected="true"]');
            if (selectedCell && document.activeElement !== selectedCell) {
                selectedCell.focus({ preventScroll: true });
            }
        });
        
        // Focus management per celle
        const gridContainer = document.querySelector('[role="grid"]');
        if (gridContainer) {
            gridContainer.addEventListener('keydown', function(e) {
                // Gestisci navigazione da tastiera se il focus è sulla griglia
                if (e.target.getAttribute('role') === 'gridcell') {
                    switch(e.key) {
                        case 'ArrowUp':
                        case 'ArrowDown':
                        case 'ArrowLeft':
                        case 'ArrowRight':
                            e.preventDefault(); // Evita scroll pagina
                            break;
                    }
                }
            });
        }
        
        // Esponi una funzione globale per caricare puzzle
        window.sudokuBoardLoadPuzzle = function(difficulty) {
            if (window.APP_DEBUG) console.log('🎯 Chiamata a window.sudokuBoardLoadPuzzle con difficoltà:', difficulty);
            const component = @this;
            if (component && typeof component.call === 'function') {
                try {
                    component.call('loadSamplePuzzle', difficulty);
                    if (window.APP_DEBUG) console.log('✅ Puzzle caricato dal componente! Difficoltà:', difficulty);
                    return true;
                } catch (error) {
                    if (window.APP_DEBUG) console.log('❌ Errore caricamento puzzle:', error);
                    return false;
                }
            } else {
                if (window.APP_DEBUG) console.log('❌ Componente non disponibile:', component);
                return false;
            }
        };
        
        // Esponi una funzione globale per leggere lo stato corrente (grid, errori, tempo)
        window.sudokuBoardGetState = function() {
            const component = @this;
            try {
                const grid = component.get ? component.get('grid') : null;
                const errors = component.get ? (component.get('errorsCount') ?? 0) : 0;
                const seconds = component.get ? (component.get('timeElapsed') ?? 0) : 0;
                return { grid, errors, seconds };
            } catch (e) {
                if (window.APP_DEBUG) console.log('sudokuBoardGetState error:', e);
                return null;
            }
        };
        
        if (window.APP_DEBUG) console.log('✅ Funzione window.sudokuBoardLoadPuzzle creata');
        
        @if($isCompetitiveMode)
        // Anti-cheat protection per modalità competitiva
        const antiCheatProtection = {
            init() {
                this.preventCopyPaste();
                this.preventRightClick();
                this.preventSelection();
                this.preventDragDrop();
                this.preventDevTools();
                this.addVisualWarnings();
            },
            
            preventCopyPaste() {
                const board = document.querySelector('.sudoku-grid');
                if (!board) return;
                
                // Blocca copy/paste/cut su tutta la board
                ['copy', 'paste', 'cut'].forEach(event => {
                    board.addEventListener(event, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.showWarning('⚠️ Copy/paste disabilitato in modalità competitiva!');
                        return false;
                    }, true);
                });
                
                // Blocca keyboard shortcuts
                board.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && 
                        (e.key === 'c' || e.key === 'v' || e.key === 'x' || e.key === 'a' || e.key === 's')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.key !== 's') { // Non mostrare warning per Ctrl+S
                            this.showWarning('🚫 Scorciatoie disabilitate in modalità competitiva!');
                        }
                        return false;
                    }
                }, true);
            },
            
            preventRightClick() {
                const board = document.querySelector('.sudoku-grid');
                if (board) {
                    board.addEventListener('contextmenu', (e) => {
                        e.preventDefault();
                        this.showWarning('🖱️ Click destro disabilitato in modalità competitiva!');
                        return false;
                    }, true);
                }
            },
            
            preventSelection() {
                const board = document.querySelector('.sudoku-grid');
                if (board) {
                    board.style.userSelect = 'none';
                    board.style.webkitUserSelect = 'none';
                    board.style.mozUserSelect = 'none';
                    board.style.msUserSelect = 'none';
                    
                    board.addEventListener('selectstart', (e) => {
                        e.preventDefault();
                        return false;
                    }, true);
                }
            },
            
            preventDragDrop() {
                const board = document.querySelector('.sudoku-grid');
                if (board) {
                    ['dragstart', 'drag', 'dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
                        board.addEventListener(event, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);
                    });
                }
            },
            
            preventDevTools() {
                // Blocca F12, Ctrl+Shift+I, Ctrl+U (best effort)
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'F12' || 
                        (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C' || e.key === 'J')) ||
                        (e.ctrlKey && e.key === 'U')) {
                        e.preventDefault();
                        this.showWarning('🔧 Developer tools disabilitati in modalità competitiva!');
                        return false;
                    }
                }, true);
            },
            
            addVisualWarnings() {
                // Aggiungi indicatore visivo modalità competitiva
                const board = document.querySelector('.sudoku-grid');
                if (board && !board.querySelector('.competitive-indicator')) {
                    const indicator = document.createElement('div');
                    indicator.className = 'competitive-indicator';
                    indicator.innerHTML = '🛡️ MODALITÀ COMPETITIVA';
                    indicator.style.cssText = `
                        position: absolute; 
                        top: -30px; 
                        right: 0; 
                        background: linear-gradient(90deg, #ef4444, #f59e0b); 
                        color: white; 
                        padding: 4px 12px; 
                        border-radius: 12px; 
                        font-size: 10px; 
                        font-weight: bold; 
                        z-index: 10;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    `;
                    board.style.position = 'relative';
                    board.appendChild(indicator);
                }
            },
            
            showWarning(message) {
                // Crea toast warning temporaneo
                const toast = document.createElement('div');
                toast.className = 'anti-cheat-warning';
                toast.textContent = message;
                toast.style.cssText = `
                    position: fixed; 
                    top: 20px; 
                    right: 20px; 
                    background: #ef4444; 
                    color: white; 
                    padding: 12px 20px; 
                    border-radius: 8px; 
                    font-weight: 600; 
                    font-size: 14px;
                    z-index: 9999; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    animation: slideInRight 0.3s ease-out;
                `;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.animation = 'slideOutRight 0.3s ease-in';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        };
        
        // Inizializza protezioni
        antiCheatProtection.init();
        
        // Controlla periodicamente se le protezioni sono ancora attive
        setInterval(() => {
            const board = document.querySelector('.sudoku-grid');
            if (board && !board.classList.contains('protected')) {
                board.classList.add('protected');
                antiCheatProtection.init();
            }
        }, 5000);
        @endif
        
    });
    
    // Fallback con DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        // Se la funzione non esiste ancora, creala
        if (typeof window.sudokuBoardLoadPuzzle === 'undefined') {
            if (window.APP_DEBUG) console.log('🔄 Fallback: creazione funzione sudokuBoardLoadPuzzle');
            
            setTimeout(() => {
                window.sudokuBoardLoadPuzzle = function(difficulty) {
                    if (window.APP_DEBUG) console.log('🎯 Fallback: Chiamata con difficoltà:', difficulty);
                    const component = @this;
                    if (component && typeof component.call === 'function') {
                        try {
                            component.call('loadSamplePuzzle', difficulty);
                            if (window.APP_DEBUG) console.log('✅ Fallback: Puzzle caricato! Difficoltà:', difficulty);
                            return true;
                        } catch (error) {
                            if (window.APP_DEBUG) console.log('❌ Fallback: Errore caricamento puzzle:', error);
                            return false;
                        }
                    } else {
                        if (window.APP_DEBUG) console.log('❌ Fallback: Componente non disponibile');
                        return false;
                    }
                };
                if (window.APP_DEBUG) console.log('✅ Fallback: Funzione window.sudokuBoardLoadPuzzle creata');
            }, 2000);
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (sudokuTimerInterval) {
            clearInterval(sudokuTimerInterval);
        }
        if (sudokuUiTimerInterval) {
            clearInterval(sudokuUiTimerInterval);
        }
        // Cleanup global function
        delete window.sudokuBoardLoadPuzzle;
    });
</script>
@endpush

@push('styles')
<style>
.sudoku-grid {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    grid-template-rows: repeat(9, 1fr);
    width: 100%;
    max-width: 540px;
    aspect-ratio: 1;
    gap: 0;
    border: 4px solid #1f2937;
    background-color: #1f2937;
}

.dark .sudoku-grid {
    border-color: #e5e7eb;
    background-color: #e5e7eb;
}

.sudoku-cell {
    width: 100%;
    height: 100%;
    min-height: 60px;
    aspect-ratio: 1;
    position: relative;
    border: 1px solid #d1d5db;
}

.dark .sudoku-cell {
    border-color: #6b7280;
}

/* Bordi spessi per separatori 3x3 */
.sudoku-cell.thick-top {
    border-top: 4px solid #1f2937 !important;
}

.sudoku-cell.thick-left {
    border-left: 4px solid #1f2937 !important;
}

.dark .sudoku-cell.thick-top {
    border-top-color: #e5e7eb !important;
}

.dark .sudoku-cell.thick-left {
    border-left-color: #e5e7eb !important;
}

@media (min-width: 640px) {
    .sudoku-cell { 
        min-height: 60px; 
    }
    .sudoku-grid {
        max-width: 600px;
    }
}

/* Protezioni modalità competitiva */
.competitive-mode {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
    -webkit-touch-callout: none !important;
    -webkit-tap-highlight-color: transparent !important;
}

.competitive-mode * {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
    -webkit-touch-callout: none !important;
    pointer-events: auto !important;
}

/* Impedisce selezione del testo anche via JavaScript */
.competitive-mode::selection {
    background: transparent !important;
}

.competitive-mode *::selection {
    background: transparent !important;
}

.competitive-mode::-moz-selection {
    background: transparent !important;
}

.competitive-mode *::-moz-selection {
    background: transparent !important;
}

/* Animazioni per i toast anti-cheat */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Indicatore modalità competitiva */
.competitive-indicator {
    pointer-events: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    user-select: none !important;
}

/* Disabilita highlight sui dispositivi touch */
.competitive-mode {
    -webkit-tap-highlight-color: rgba(0,0,0,0) !important;
    tap-highlight-color: rgba(0,0,0,0) !important;
}

/* Impedisce zoom su dispositivi mobili durante il gioco competitivo */
.competitive-mode input,
.competitive-mode select,
.competitive-mode textarea {
    font-size: 16px !important;
}

/* Effetto errore - sfondo rosso temporaneo */
.sudoku-grid.error-effect {
    position: relative;
}

.sudoku-grid.error-effect::before {
    content: '';
    position: fixed; /* fissa in viewport per copertura totale su mobile */
    inset: 0; /* top/right/bottom/left: 0 */
    background-color: rgba(239, 68, 68, 0.35);
    pointer-events: none; /* Non blocca l'input */
    border-radius: 0; /* copri tutto indipendentemente dal raggio della board */
    z-index: 9999; /* sopra UI board */
    animation: errorPulse 1.4s ease-out forwards;
}

@keyframes errorPulse {
    0% { opacity: 1; }
    70% { opacity: 1; }
    100% { opacity: 0; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('error-effect-triggered', () => {
        // Overlay calcolato sulle dimensioni reali della board
        const grid = document.getElementById('sudoku-main-grid');
        if (grid) {
            const rect = grid.getBoundingClientRect();
            const ov = document.createElement('div');
            ov.setAttribute('aria-hidden', 'true');
            ov.style.position = 'fixed';
            ov.style.left = `${rect.left}px`;
            ov.style.top = `${rect.top}px`;
            ov.style.width = `${rect.width}px`;
            ov.style.height = `${rect.height}px`;
            ov.style.pointerEvents = 'none';
            ov.style.borderRadius = getComputedStyle(grid).borderRadius;
            ov.style.backgroundColor = 'rgba(239, 68, 68, 0.35)';
            ov.style.zIndex = '9999';
            ov.style.animation = 'errorPulse 1.4s ease-out forwards';
            ov.className = 'sudoku-error-overlay';
            document.body.appendChild(ov);
            setTimeout(() => ov.remove(), 1500);
        }

        // Rimuovi il flag lato Livewire dopo 2s
        setTimeout(() => {
            @this.showErrorEffect = false;
        }, 2000);
    });
});
</script>
@endpush