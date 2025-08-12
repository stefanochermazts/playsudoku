<div x-data="{}" 
     tabindex="0"
     wire:keydown.window="handleKeyInput($event.key)"
     class="sudoku-game w-full max-w-4xl mx-auto p-4 bg-white dark:bg-gray-900 rounded-xl shadow-lg">

    {{-- Header con timer e controlli --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
        {{-- Timer e statistiche --}}
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getFormattedTime() }}</div>
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
            <button wire:click="toggleInputMode" 
                    class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors
                           @if($inputMode === 'value') bg-blue-600 text-white border-blue-600 @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 @endif
                           hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($inputMode === 'value') Valori @else Candidati @endif
            </button>
            <button wire:click="$toggle('showCandidates')" class="px-3 py-2 rounded-lg border text-sm font-medium bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">{!! $showCandidates ? '👁️ Nascondi candidati' : '👁️ Mostra candidati' !!}</button>
            <button wire:click="undo" class="p-2 rounded-lg border bg-gray-100 dark:bg-gray-700">↶</button>
            <button wire:click="redo" class="p-2 rounded-lg border bg-gray-100 dark:bg-gray-700">↷</button>
            @endif
        </div>
    </div>

    {{-- Griglia principale --}}
    <div class="sudoku-grid mx-auto rounded-lg overflow-hidden">
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
                    
                    // Evidenziazioni
                    if (!$isSelected && ($sameRow || $sameCol || $sameBox)) {
                        $classes[] = 'bg-blue-50/70';
                        $classes[] = 'dark:bg-blue-900/25';
                    }
                    if ($isSelected) {
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
                    
                    // Bordi spessi per separatori 3x3
                    if ($row % 3 === 0 && $row > 0) $classes[] = 'thick-top';
                    if ($col % 3 === 0 && $col > 0) $classes[] = 'thick-left';
                @endphp
                
                <div wire:click="selectCell({{ $row }}, {{ $col }})"
                     class="{{ implode(' ', $classes) }} hover:bg-gray-50 dark:hover:bg-gray-700"
                     role="gridcell" aria-selected="{{ $isSelected ? 'true' : 'false' }}">
                
                    @if($value)
                        <span class="text-4xl md:text-5xl font-bold leading-none
                                    @if($isGiven) text-gray-900 dark:text-white @else text-blue-600 dark:text-blue-400 @endif
                                    @if($hasConflict && $highlightConflicts) text-red-600 dark:text-red-400 @endif">
                            {{ $value }}
                        </span>
                    @elseif(!$isGiven && $showCandidates)
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
document.addEventListener('livewire:init', () => {
    // Timer tick ogni secondo
    setInterval(() => {
        // Trova tutti i componenti SudokuBoard e fa tick del timer
        Livewire.all().forEach(component => {
            if (component.name === 'sudoku-board') {
                component.call('tickTimer');
            }
        });
    }, 1000);
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