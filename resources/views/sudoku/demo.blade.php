<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.training.title') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                {{ __('app.training.subtitle') }}
            </p>
            
            <div class="flex flex-wrap justify-center gap-4 mb-6">
                <a href="{{ route('localized.sudoku.play', ['locale' => app()->getLocale()]) }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 
                          focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    {{ __('app.training.play_now') }}
                </a>
                <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
                   class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 
                          focus:outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                    {{ __('app.training.analyzer') }}
                </a>
                <button onclick="loadEmptyBoard()" 
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 
                               focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                    {{ __('app.training.empty_board') }}
                </button>
            </div>
            
            {{-- Selezione livelli di difficoltà --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 text-center">
                    {{ __('app.training.load_puzzle_difficulty') }}
                </h3>
                <div class="flex flex-wrap justify-center gap-2">
                    <button id="btn-easy" onclick="loadPuzzle('easy')" 
                            class="difficulty-btn px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 
                                   focus:outline-none focus:ring-2 focus:ring-green-500 font-medium text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-500
                                   flex items-center space-x-2">
                        <span class="btn-text">{{ __('app.training.difficulty_easy') }}</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </div>
                    </button>
                    <button id="btn-medium" onclick="loadPuzzle('medium')" 
                            class="difficulty-btn px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-500
                                   flex items-center space-x-2">
                        <span class="btn-text">{{ __('app.training.difficulty_medium') }}</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </div>
                    </button>
                    <button id="btn-hard" onclick="loadPuzzle('hard')" 
                            class="difficulty-btn px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 
                                   focus:outline-none focus:ring-2 focus:ring-yellow-500 font-medium text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-yellow-500
                                   flex items-center space-x-2">
                        <span class="btn-text">{{ __('app.training.difficulty_hard') }}</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </div>
                    </button>
                    <button id="btn-expert" onclick="loadPuzzle('expert')" 
                            class="difficulty-btn px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 
                                   focus:outline-none focus:ring-2 focus:ring-red-500 font-medium text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-red-500
                                   flex items-center space-x-2">
                        <span class="btn-text">{{ __('app.training.difficulty_expert') }}</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </div>
                    </button>
                    <button id="btn-crazy" onclick="loadPuzzle('crazy')" 
                            class="difficulty-btn px-4 py-2 bg-gradient-to-r from-red-600 to-purple-600 text-white rounded-lg 
                                   hover:from-red-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-red-500 
                                   font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed 
                                   disabled:hover:from-red-600 disabled:hover:to-purple-600 flex items-center space-x-2">
                        <span class="btn-text">{{ __('app.training.difficulty_crazy') }}</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        

        {{-- Board Demo --}}
        <div id="board-container">
            <div id="sudoku-board-wrapper">
                @livewire('sudoku-board', ['readOnly' => false, 'startTimer' => false], key('sudoku-demo-board'))
            </div>
        </div>

        {{-- Funzionalità implementate (spostate sotto la board) --}}
        <div class="grid md:grid-cols-2 gap-6 mt-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.training.features_title') }}
                </h3>
                <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_grid') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_input') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_modes') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_undo') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_timer') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_validation') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_accessibility') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_responsive') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_hints') }}</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> {{ __('app.training.feature_analyzer') }}</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.training.keyboard_title') }}
                </h3>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">1-9</kbd> {{ __('app.training.key_numbers') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Backspace/Delete</kbd> {{ __('app.training.key_delete') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Arrow Keys</kbd> {{ __('app.training.key_arrows') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">C</kbd> {{ __('app.training.key_mode') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">U</kbd> {{ __('app.training.key_undo') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">R</kbd> {{ __('app.training.key_redo') }}</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Tab</kbd> {{ __('app.training.key_tab') }}</li>
                </ul>
            </div>
        </div>

        
    </div>
</div>

<script>
function loadEmptyBoard() {
    // Ricarica con board vuota
    Livewire.dispatch('reload-board', { initialGrid: null });
}

function loadPuzzle(difficulty) {
    if (window.APP_DEBUG) console.log('🎯 Tentativo di caricare puzzle con difficoltà:', difficulty);
    
    // FERMA IMMEDIATAMENTE IL TIMER prima di fare qualsiasi altra cosa
    window.dispatchEvent(new CustomEvent('stop-timer'));
    
    // Mostra loading sui pulsanti
    showButtonLoading(difficulty);
    
    // Messaggio speciale per crazy difficulty
    if (difficulty === 'crazy') {
        console.log('⚠️ Caricamento puzzle CRAZY - Potrebbe richiedere più tempo...');
    }
    
    // Metodo 1: Usa la funzione globale esposta dal componente
    if (typeof window.sudokuBoardLoadPuzzle === 'function') {
        if (window.APP_DEBUG) console.log('📞 Chiamata funzione globale...');
        const success = window.sudokuBoardLoadPuzzle(difficulty);
        if (success) {
            if (window.APP_DEBUG) console.log('✅ Puzzle caricato tramite funzione globale!');
            return;
        }
    }

    // Metodo 1-bis: Dispatch evento Livewire globale (v3)
    try {
        if (window.APP_DEBUG) console.log('📡 Dispatch Livewire event load-sample-puzzle');
        Livewire.dispatch('load-sample-puzzle', { difficulty });
        // Diamo un feedback visivo/log
        if (window.APP_DEBUG) console.log('✅ Evento inviato a Livewire');
        return;
    } catch (e) {
        if (window.APP_DEBUG) console.log('❌ Dispatch fallito, passo al fallback manuale...', e);
        hideButtonLoading();
    }
    
    // Metodo 2: Fallback - cerca tramite Livewire con retry
    const sudokuElements = document.querySelectorAll('[wire\\:id]');
    if (window.APP_DEBUG) console.log('🔍 Trovati', sudokuElements.length, 'elementi Livewire');
    
    let attempts = 0;
    const maxAttempts = 3;
    
    function tryLoadPuzzle() {
        attempts++;
        if (window.APP_DEBUG) console.log(`🔄 Tentativo ${attempts}/${maxAttempts}`);
        
        for (let element of sudokuElements) {
            const wireId = element.getAttribute('wire:id');
            if (window.APP_DEBUG) console.log('🔍 Controllando elemento con wire:id:', wireId);
            
            const component = Livewire.find(wireId);
            
            if (component) {
                if (window.APP_DEBUG) console.log('✅ Componente trovato:', component);
                if (window.APP_DEBUG) console.log('🔍 Metodi disponibili:', Object.getOwnPropertyNames(component));
                
                                        if (typeof component.call === 'function') {
                            try {
                                if (window.APP_DEBUG) console.log('📞 Chiamata loadSamplePuzzle...');
                                component.call('loadSamplePuzzle', difficulty);
                                if (window.APP_DEBUG) console.log('✅ Puzzle caricato tramite fallback! Difficoltà:', difficulty);
                                return true;
                            } catch (error) {
                                if (window.APP_DEBUG) console.log('❌ Errore durante la chiamata:', error.message, error);
                                hideButtonLoading();
                            }
                        } else {
                            if (window.APP_DEBUG) console.log('❌ Metodo call non disponibile');
                        }
            } else {
                if (window.APP_DEBUG) console.log('❌ Componente non trovato per wireId:', wireId);
            }
        }
        
        // Retry se non è riuscito e non ha raggiunto il max attempts
        if (attempts < maxAttempts) {
            if (window.APP_DEBUG) console.log('⏰ Retry tra 1 secondo...');
            setTimeout(tryLoadPuzzle, 1000);
        } else {
            if (window.APP_DEBUG) {
                console.error('❌ Tutti i tentativi falliti - componente non trovato');
                console.log('Debug info:');
                console.log('- Funzione globale disponibile?', typeof window.sudokuBoardLoadPuzzle);
                console.log('- Elementi Livewire:', sudokuElements.length);
                console.log('- Livewire oggetto disponibile?', typeof Livewire);
            }
            hideButtonLoading();
        }
        return false;
    }
    
    // Inizia i tentativi
    tryLoadPuzzle();
}

// Gestione loading dei pulsanti di difficoltà
function showButtonLoading(difficulty) {
    // Disabilita tutti i pulsanti
    const buttons = document.querySelectorAll('.difficulty-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
        const spinner = btn.querySelector('.btn-spinner');
        const text = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('hidden');
        if (text) text.classList.remove('hidden');
    });
    
    // Mostra spinner sul pulsante specifico
    const activeBtn = document.getElementById(`btn-${difficulty}`);
    if (activeBtn) {
        const spinner = activeBtn.querySelector('.btn-spinner');
        const text = activeBtn.querySelector('.btn-text');
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.classList.add('hidden');
    }
    
    // Timeout di sicurezza solo per logging, NON riabilita i pulsanti
    clearTimeout(window.loadingTimeout);
    window.loadingTimeout = setTimeout(() => {
        if (window.APP_DEBUG) console.log('⏰ Loading timeout raggiunto - ma i pulsanti rimangono disabilitati fino agli eventi');
    }, 10000);
}

function hideButtonLoading() {
    // Riabilita tutti i pulsanti e nasconde spinner
    const buttons = document.querySelectorAll('.difficulty-btn');
    buttons.forEach(btn => {
        btn.disabled = false;
        const spinner = btn.querySelector('.btn-spinner');
        const text = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('hidden');
        if (text) text.classList.remove('hidden');
    });
    
    // Cancella timeout
    clearTimeout(window.loadingTimeout);
}

// Listener per eventi Livewire
document.addEventListener('livewire:init', () => {
    let lastLoadingState = null;
    
    // Ascolta i cambiamenti di stato del componente dopo ogni morph
    Livewire.hook('morph.updated', (el, component) => {
        if (component && (component.name === 'sudoku-board' || component.fingerprint?.name === 'sudoku-board')) {
            try {
            // Controlla se isLoading è false nel componente
                const currentLoadingState = component.get ? component.get('isLoading') : null;
                
                // Se lo stato di loading è cambiato da true a false
                if (lastLoadingState === true && currentLoadingState === false) {
                    // Ritarda leggermente per assicurarsi che il DOM sia aggiornato
                    setTimeout(() => {
                hideButtonLoading();
                        if (window.APP_DEBUG) console.log('✅ Loading completato dopo morph - pulsanti riabilitati');
                    }, 100);
                }
                
                lastLoadingState = currentLoadingState;
            } catch (e) {
                if (window.APP_DEBUG) console.log('Errore nel controllo loading state:', e);
            }
        }
    });
    
    // Ascolta evento Livewire di puzzle caricato (backup)
    Livewire.on('puzzle-loaded', () => {
        // Ritarda un po' di più per essere sicuri
        setTimeout(() => {
            hideButtonLoading();
            if (window.APP_DEBUG) console.log('🎯 Puzzle caricato - pulsanti riabilitati dopo delay');
        }, 200);
    });
    
    // Ascolta evento personalizzato emesso dalla board quando il DOM è aggiornato
    window.addEventListener('sudoku-board-updated', () => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('🎯 Board DOM aggiornata - pulsanti riabilitati');
    });
    
    // Ascolta evento di rendering completato emesso dal componente Livewire
    window.addEventListener('sudoku-rendering-complete', () => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('🎯 Rendering board completato - pulsanti riabilitati');
    });
    
    // Ascolta evento di cache cleanup - il vero indicatore che il processo è finito
    window.addEventListener('sudoku-cache-cleaned', (event) => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('🎯 Cache pulita, processo completato - pulsanti riabilitati (cache size:', event.detail.cacheSize, ')');
    });
});

// Backward compatibility
function loadSamplePuzzle() {
    loadPuzzle('medium');
}
</script>
</x-site-layout>
