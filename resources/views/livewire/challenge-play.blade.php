<div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
    <!-- Banner Modalità Allenamento per sfide scadute -->
    @if($isArchivedChallenge)
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-center">
                        <p class="font-semibold">⏰ Modalità Allenamento</p>
                        <p class="text-sm opacity-90">Sfida scaduta - Il completamento non influenzerà le classifiche competitive</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                            @if($isArchivedChallenge)
                                <span class="text-amber-600 dark:text-amber-400 font-medium">{{ __('Modalità Allenamento') }}</span>
                            @else
                                <span>{{ __('app.challenges.ends_in') }}: {{ $challenge->ends_at->diffForHumans() }}</span>
                            @endif
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
                            <span id="challenge-timer" class="font-mono text-lg font-semibold text-neutral-900 dark:text-white">{{ $this->getFormattedTime() }}</span>
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
            <!-- Sudoku Board (riutilizza la stessa board della /demo) -->
            <div class="lg:col-span-2">
                @php($initialGrid = is_array($challenge->puzzle->givens) ? $challenge->puzzle->givens : json_decode($challenge->puzzle->givens, true))
                @php($hintsAllowed = $challenge->settings['hints_allowed'] ?? true)
                @php($attemptState = $attempt?->current_state ?? null)
                @php($attemptSeconds = $attempt?->duration_ms ? intval($attempt->duration_ms/1000) : 0)
                @php($attemptErrors = $attempt?->errors_count ?? 0)
                @php($isReadOnlyBoard = (bool) ($attempt?->completed_at))
                @php($shouldStartTimer = (!$isReadOnlyBoard) && (($attemptState && is_array($attemptState) && count($attemptState) > 0) || ($attemptSeconds > 0)))
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-4 border border-neutral-200/50 dark:border-neutral-700/50">
                    @livewire('sudoku-board', [
                        'readOnly' => $isReadOnlyBoard ? true : false,
                        'startTimer' => $shouldStartTimer,
                        'initialGrid' => $initialGrid,
                        'candidatesAllowed' => $hintsAllowed,
                        'showCandidates' => $hintsAllowed,
                        'initialSeconds' => $attemptSeconds,
                        'currentGrid' => is_array($attemptState) ? $attemptState : (is_string($attemptState) ? json_decode($attemptState, true) : null),
                        'initialErrors' => $attemptErrors,
                        'isCompetitiveMode' => true,
                        'hintsEnabled' => $hintsAllowed,
                    ], key('challenge-board-'.$challenge->id))
                </div>
            </div>

            <!-- Challenge Controls -->
            <div class="space-y-6">
                <!-- Challenge Info -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">{{ __('app.dashboard.challenge_info') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">Tipo:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($challenge->type) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.difficulty_label') }}</span>
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
                            <button onclick="pauseAndSaveChallenge()"
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
                    <h3 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">{{ __('app.dashboard.challenge_completed') }}</h3>
                    <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                        Hai concluso il sudoku con questo tempo: <strong>{{ $this->getFormattedTime() }}</strong> e con <strong>{{ $errorCount }}</strong> errori.
                    </p>
                    @if($isArchivedChallenge)
                        {{-- Modalità allenamento: solo pulsante per tornare alle sfide --}}
                        <div class="text-center">
                            <p class="text-amber-600 dark:text-amber-400 mb-4 text-sm">
                                🏆 Completamento in modalità allenamento - Non influenzerà le classifiche
                            </p>
                            <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.challenges.index') : route('challenges.index') }}" 
                               class="inline-block px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-medium rounded-lg hover:from-amber-600 hover:to-orange-600 transition-colors">
                                Torna alle Sfide
                            </a>
                        </div>
                    @else
                        {{-- Modalità competitiva: pulsanti normali con leaderboard --}}
                        <div class="flex space-x-3">
                            <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.challenges.index') : route('challenges.index') }}" 
                               class="flex-1 px-4 py-3 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors text-center">
                                Torna alle Sfide
                            </a>
                            <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                               class="flex-1 px-4 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 transition-colors text-center">
                                {{ __('app.dashboard.view_leaderboard') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Stili della board rimossi per evitare conflitti: si usano quelli del componente `sudoku-board` --}}

@push('scripts')
<script>
    // Sincronizza il timer dell'header con quello della board riusata
    document.addEventListener('livewire:init', function() {
        const headerTimer = document.getElementById('challenge-timer');
        function updateHeaderTimer() {
            const boardTimer = document.querySelector('.sudoku-game [data-role="timer"]');
            if (headerTimer && boardTimer) {
                headerTimer.textContent = boardTimer.textContent;
            }
        }

        // Aggiorna periodicamente e su eventi Livewire
        const syncInterval = setInterval(updateHeaderTimer, 500);
        window.addEventListener('start-timer', updateHeaderTimer);
        window.addEventListener('stop-timer', updateHeaderTimer);
        window.addEventListener('puzzle-completed', updateHeaderTimer);

        // Cleanup
        window.addEventListener('beforeunload', function() {
            clearInterval(syncInterval);
        });
    });

    // Pausa & Salva: legge lo stato dalla board e chiama l'azione Livewire
    function pauseAndSaveChallenge() {
        try {
            const state = window.sudokuBoardGetState ? window.sudokuBoardGetState() : null;
            const component = @this;
            if (component && typeof component.call === 'function') {
                component.call('pauseChallengeWithState', state);
            }
        } catch (e) {
            if (window.APP_DEBUG) console.log('pauseAndSaveChallenge error:', e);
        }
    }
</script>
@endpush