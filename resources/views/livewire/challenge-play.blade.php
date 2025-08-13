<div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
    <!-- Header Challenge Info -->
    <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.challenges.index') : route('challenges.index') }}" 
                       class="p-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
<div>
                        <h1 class="text-xl font-bold text-neutral-900 dark:text-white">
                            {{ $challenge->title ?? __('app.challenges.' . $challenge->type . '_challenge') }}
                        </h1>
                        <div class="flex items-center space-x-4 text-sm text-neutral-600 dark:text-neutral-300">
                            <span>{{ ucfirst($challenge->puzzle->difficulty) }}</span>
                            <span>•</span>
                            <span>{{ __('app.challenges.ends_in') }}: {{ $challenge->ends_at->diffForHumans() }}</span>
                            @if(!$hintsAllowed)
                                <span>•</span>
                                <span class="text-orange-600 dark:text-orange-400">{{ __('app.challenges.not_allowed') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Timer -->
                    <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg px-4 py-2">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-mono text-lg font-semibold text-neutral-900 dark:text-white">{{ $this->getFormattedTime() }}</span>
                        </div>
                    </div>
                    
                    <!-- Completion -->
                    <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg px-4 py-2">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-semibold text-neutral-900 dark:text-white">{{ $completionPercentage }}%</span>
                        </div>
                    </div>
                    
                    <!-- Errors -->
                    @if($errorCount > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold text-red-600 dark:text-red-400">{{ $errorCount }} errori</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Game Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Sudoku Board -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 sudoku-game"
                     wire:keydown.window="handleKeyInput($event.key)"
                     tabindex="0">
                    
                    <!-- Sudoku Grid -->
                    <div class="sudoku-grid" style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 1px; background-color: #374151; padding: 4px; border-radius: 12px; max-width: 500px; aspect-ratio: 1; margin: 0 auto;">
                        @for($row = 0; $row < 9; $row++)
                            @for($col = 0; $col < 9; $col++)
                                @php
                                    $isSelected = ($selectedRow === $row && $selectedCol === $col);
                                    $originalGrid = is_array($challenge->puzzle->givens) ? $challenge->puzzle->givens : json_decode($challenge->puzzle->givens, true);
                                    $isGiven = isset($originalGrid[$row][$col]) && $originalGrid[$row][$col] !== null;
                                    $value = $grid[$row][$col] ?? null;
                                    $isHighlighted = ($selectedRow === $row || $selectedCol === $col || 
                                                    (intval($selectedRow / 3) === intval($row / 3) && intval($selectedCol / 3) === intval($col / 3)));
                                    $hasConflict = collect($conflicts)->contains(fn($conflict) => $conflict['row'] === $row && $conflict['col'] === $col);
                                    $candidates = $candidates[$row][$col] ?? [];
                                @endphp
                                
                                <div class="sudoku-cell {{ $isSelected ? 'selected' : '' }} {{ $isHighlighted ? 'highlighted' : '' }} {{ $hasConflict ? 'conflict' : '' }} {{ $isGiven ? 'given' : 'user-input' }}"
                                     style="background-color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; font-weight: 600; min-height: 50px; position: relative; border-radius: 2px; transition: all 0.2s ease;"
                                     wire:click="selectCell({{ $row }}, {{ $col }})">
                                    
                                    @if($value !== null)
                                        <span class="cell-number">{{ $value }}</span>
                                    @elseif($showCandidates && !empty($candidates) && !$isSelected)
                                        <div class="candidates-grid">
                                            @for($num = 1; $num <= 9; $num++)
                                                <span class="candidate {{ in_array($num, $candidates) ? 'visible' : 'hidden' }}">{{ $num }}</span>
                                            @endfor
                                        </div>
                                    @elseif($isSelected && $value === null)
                                        <span class="cell-placeholder">•</span>
                                    @endif
                                </div>
                            @endfor
                        @endfor
</div>

                    <!-- Number Input Panel -->
                    <div class="mt-6 grid grid-cols-5 gap-3">
                        @for($num = 1; $num <= 9; $num++)
                            <button wire:click="setCellValue({{ $num }})"
                                    class="aspect-square bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-xl font-bold text-neutral-900 dark:text-white hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                    @if($isReadOnly) disabled @endif>
                                {{ $num }}
                            </button>
                        @endfor
                        
                        <!-- Clear Button -->
                        <button wire:click="setCellValue(null)"
                                class="aspect-square bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 font-bold hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                                @if($isReadOnly) disabled @endif>
                            ✗
                        </button>
                    </div>
                </div>
            </div>

            <!-- Challenge Controls -->
            <div class="space-y-6">
                <!-- Challenge Info -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Info Sfida</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">Tipo:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($challenge->type) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">Difficoltà:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($challenge->puzzle->difficulty) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">Scade:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ $challenge->ends_at->diffForHumans() }}</span>
                        </div>
                        @if(isset($timeLimit))
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">Limite tempo:</span>
                                <span class="font-medium text-orange-600 dark:text-orange-400">{{ intval($timeLimit / 60000) }} min</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                @if(!$isCompleted)
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Azioni</h3>
                        <div class="space-y-3">
                            <button wire:click="pauseChallenge"
                                    class="w-full px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg text-orange-700 dark:text-orange-300 font-medium hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                                Pausa & Salva
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Progress -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Progresso</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">Completamento:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ $completionPercentage }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-primary-600 to-secondary-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $completionPercentage }}%"></div>
                        </div>
                        @if($errorCount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">Errori:</span>
                                <span class="font-medium text-red-600 dark:text-red-400">{{ $errorCount }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isCompleted)
        <!-- Completion Modal -->
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-2xl p-8 max-w-md mx-4 border border-neutral-200 dark:border-neutral-700">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">Sfida Completata!</h3>
                    <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                        Tempo finale: <strong>{{ $this->getFormattedTime() }}</strong><br>
                        Errori: <strong>{{ $errorCount }}</strong>
                    </p>
                    <div class="flex space-x-3">
                        <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.challenges.index') : route('challenges.index') }}" 
                           class="flex-1 px-4 py-3 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors text-center">
                            Torna alle Sfide
                        </a>
                        <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.leaderboard.index') : route('leaderboard.index') }}" 
                           class="flex-1 px-4 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 transition-colors text-center">
                            Vedi Classifica
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.sudoku-grid {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 1px;
    background-color: #374151;
    padding: 4px;
    border-radius: 12px;
    max-width: 500px;
    aspect-ratio: 1;
    margin: 0 auto;
}

.sudoku-cell {
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.5rem;
    font-weight: 600;
    min-height: 50px;
    position: relative;
    border-radius: 2px;
    transition: all 0.2s ease;
}

.dark .sudoku-cell {
    background-color: #1f2937;
    color: white;
}

.sudoku-cell.given {
    background-color: #f3f4f6;
    color: #111827;
    font-weight: 700;
}

.dark .sudoku-cell.given {
    background-color: #374151;
    color: #f9fafb;
}

.sudoku-cell.selected {
    background-color: #dbeafe !important;
    box-shadow: inset 0 0 0 2px #3b82f6;
}

.dark .sudoku-cell.selected {
    background-color: #1e40af !important;
}

.sudoku-cell.highlighted {
    background-color: #f0f9ff;
}

.dark .sudoku-cell.highlighted {
    background-color: #0c4a6e;
}

.sudoku-cell.conflict {
    background-color: #fee2e2 !important;
    color: #dc2626 !important;
}

.dark .sudoku-cell.conflict {
    background-color: #7f1d1d !important;
    color: #fca5a5 !important;
}

/* Thick borders for 3x3 sections */
.sudoku-cell:nth-child(3n) {
    border-right: 3px solid #374151;
}

.sudoku-cell:nth-child(n+19):nth-child(-n+27),
.sudoku-cell:nth-child(n+46):nth-child(-n+54) {
    border-bottom: 3px solid #374151;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    width: 100%;
    height: 100%;
    font-size: 0.6rem;
    font-weight: 400;
}

.candidate {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.candidate.visible {
    color: #374151;
}

.dark .candidate.visible {
    color: #9ca3af;
}

.candidate.hidden {
    visibility: hidden;
}

.cell-number {
    font-size: 1.5rem;
    font-weight: 600;
}

.cell-placeholder {
    font-size: 2rem;
    color: #9ca3af;
}

.dark .cell-placeholder {
    color: #6b7280;
}
</style>
@endpush

@push('scripts')
<script>
    let timerInterval;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Focus the game area for keyboard input
        const gameArea = document.querySelector('.sudoku-game');
        if (gameArea) {
            gameArea.focus();
        }
        
        // Start timer interval
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        timerInterval = setInterval(() => {
            const component = @this;
            if (component && typeof component.call === 'function') {
                try {
                    component.call('tickTimer');
                } catch (error) {
                    console.log('Timer tick error:', error);
                }
            }
        }, 1000);
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
    });
</script>
@endpush