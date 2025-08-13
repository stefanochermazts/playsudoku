<div x-data="{}" 
     tabindex="0"
     wire:keydown.window="handleKeyInput($event.key)"
     class="sudoku-game w-full max-w-4xl mx-auto p-4 bg-white dark:bg-gray-900 rounded-xl shadow-lg">

    {{-- Header con timer e controlli --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
        {{-- Timer e statistiche --}}
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white" data-role="timer" aria-label="Tempo di gioco">{{ $this->getFormattedTime() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Tempo</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $completionPercentage }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Completato</div>
            </div>
            
            @if (!$readOnly)
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $errorsCount }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Errori</div>
            </div>
            @endif
        </div>

		{{-- Controlli --}}
		<div class="flex items-center gap-2">
			@if (!$readOnly)
				@if($candidatesAllowed)
					<button wire:click="$toggle('showCandidates')" class="px-3 py-2 rounded-lg border text-sm font-medium bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">{!! $showCandidates ? '👁️ Nascondi candidati' : '👁️ Mostra candidati' !!}</button>
				@endif
				<button wire:click="undo" class="p-2 rounded-lg border bg-gray-100 dark:bg-gray-700">↶</button>
				<button wire:click="redo" class="p-2 rounded-lg border bg-gray-100 dark:bg-gray-700">↷</button>
			@endif
		</div>
    </div>

    {{-- Griglia principale --}}
    <div class="sudoku-grid mx-auto rounded-lg overflow-hidden" style="display: grid; grid-template-columns: repeat(9, 1fr); grid-template-rows: repeat(9, 1fr); width: 100%; max-width: 640px; aspect-ratio: 1; gap: 0; border: 4px solid #1f2937; background-color: #1f2937;">
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
                    
                    // Inizializza stili bordi prima di tutto
                    $borderStyle = "width: 100%; height: 100%; min-height: 70px; aspect-ratio: 1; position: relative; border: 1px solid #d1d5db;";
                    
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
                        $borderStyle .= " background-color: #dbeafe !important; box-shadow: inset 0 0 0 3px #3b82f6;";
                        $classes[] = 'bg-blue-100';
                        $classes[] = 'dark:bg-blue-900'; 
                        $classes[] = 'ring-2';
                        $classes[] = 'ring-blue-500';
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
                
                <div wire:click="selectCell({{ $row }}, {{ $col }})"
                     class="{{ implode(' ', $classes) }} hover:bg-gray-50 dark:hover:bg-gray-700"
                     style="{{ $borderStyle }}"
                     role="gridcell" aria-selected="{{ $isSelected ? 'true' : 'false' }}">
                
                    @if($value)
                        <span class="text-4xl md:text-5xl font-bold leading-none
                                    @if($isGiven) text-gray-900 dark:text-white @else text-blue-600 dark:text-blue-400 @endif
                                    @if($hasConflict && $highlightConflicts) text-red-600 dark:text-red-400 @endif">
                            {{ $value }}
                        </span>
                    @elseif(!$isGiven && $showCandidates && $candidatesAllowed)
                        <div class="grid grid-cols-3 gap-[2px] sm:gap-1 text-[12px] sm:text-sm text-gray-600 dark:text-gray-300 p-2 sm:p-3 w-full h-full">
                            @for($i = 1; $i <= 9; $i++)
                                <button type="button"
                                        wire:click.stop="promoteCandidate({{ $row }}, {{ $col }}, {{ $i }})"
                                        class="flex items-center justify-center rounded hover:bg-blue-50 dark:hover:bg-blue-900 focus:outline-none focus:ring-1 focus:ring-blue-500 min-h-0"
                                        aria-label="Candidato {{ $i }}"
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

    {{-- Pannello numeri --}}
    @if (!$readOnly)
    <div class="mt-6 flex flex-wrap justify-center gap-2">
        @for($num = 1; $num <= 9; $num++)
            <button wire:click="inputNumber({{ $num }})"
                    class="w-12 h-12 text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                           hover:bg-blue-50 dark:hover:bg-blue-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ $num }}
            </button>
        @endfor
        
        <button wire:click="setCellValue({{ $selectedRow ?? 0 }}, {{ $selectedCol ?? 0 }}, null)"
                class="w-12 h-12 text-lg font-bold rounded-lg border border-gray-300 dark:border-gray-600 
                       bg-white dark:bg-gray-800 text-red-600 dark:text-red-400
                       hover:bg-red-50 dark:hover:bg-red-900
                       focus:outline-none focus:ring-2 focus:ring-red-500">
                ✕
        </button>
    </div>
    @endif

    {{-- Annunci accessibilità --}}
    @if($announceChanges && $lastAction)
        <div aria-live="polite" class="sr-only">{{ $lastAction }}</div>
    @endif
</div>

@push('scripts')
<script>
    let sudokuTimerInterval;
    
    // Usa livewire:init invece di DOMContentLoaded per garantire che @this sia disponibile
    document.addEventListener('livewire:init', function() {
        if (window.APP_DEBUG) console.log('🚀 SudokuBoard: Livewire inizializzato');
        
        // Clear any existing interval
        if (sudokuTimerInterval) {
            clearInterval(sudokuTimerInterval);
        }
        
        // Start server tick interval (1s) - UI aggiornata solo da Livewire
        sudokuTimerInterval = setInterval(() => {
            // Find this specific SudokuBoard component and call tickTimer
            const component = @this;
            if (component && typeof component.call === 'function') {
                try {
                    component.call('tickTimer');
                    if (running) {
                        baseSeconds += 1;
                        lastBaseMs = Date.now();
                        if (timerEl) {
                            timerEl.textContent = formatTenths(baseSeconds);
                        }
                    }
                } catch (error) {
                    if (window.APP_DEBUG) console.log('SudokuBoard timer tick error:', error);
                }
            }
        }, 1000);

        // Event listeners per stato timer
        window.addEventListener('start-timer', () => { running = true; lastBaseMs = Date.now(); startUiTimer(); });
        window.addEventListener('stop-timer', () => { running = false; });
        window.addEventListener('puzzle-completed', () => { running = false; stopUiTimer(); });
        
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
</style>
@endpush