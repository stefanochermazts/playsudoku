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
                            class="difficulty-btn px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 
                                   focus:outline-none focus:ring-2 focus:ring-red-500 font-medium text-sm
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-red-500
                                   flex items-center space-x-2">
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

        {{-- Informazioni tecniche --}}
        <div class="mt-8 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.training.tech_title') }}
            </h3>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <strong class="text-gray-900 dark:text-white">{{ __('app.training.tech_frontend') }}</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• Livewire 3 (componente reattivo)</li>
                        <li>• Alpine.js (interazioni)</li>
                        <li>• Tailwind CSS (styling)</li>
                        <li>• CSS Grid (layout griglia)</li>
                    </ul>
                </div>
                <div>
                    <strong class="text-gray-900 dark:text-white">{{ __('app.training.tech_backend') }}</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• PHP 8.2+ (strict types)</li>
                        <li>• Domain objects (Grid, Move, MoveLog)</li>
                        <li>• Generator/Validator integrati</li>
                        <li>• PSR-12 code style</li>
                    </ul>
                </div>
                <div>
                    <strong class="text-gray-900 dark:text-white">{{ __('app.training.tech_accessibility') }}</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• ARIA labels e roles</li>
                        <li>• Navigazione da tastiera</li>
                        <li>• Screen reader support</li>
                        <li>• Annunci live</li>
                    </ul>
                </div>
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
    
    // Mostra loading sui pulsanti
    showButtonLoading(difficulty);
    
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
    
    // Timeout di sicurezza per nascondere loading dopo 5 secondi
    clearTimeout(window.loadingTimeout);
    window.loadingTimeout = setTimeout(() => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('⏰ Loading timeout - pulsanti riabilitati');
    }, 5000);
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
    // Ascolta i cambiamenti di stato del componente
    Livewire.hook('morph.updated', (el, component) => {
        if (component && component.name === 'sudoku-board') {
            // Controlla se isLoading è false nel componente
            if (component.get && !component.get('isLoading')) {
                hideButtonLoading();
                if (window.APP_DEBUG) console.log('✅ Loading completato - pulsanti riabilitati');
            }
        }
    });
    
    // Ascolta eventi custom per sincronizzazione immediata
    window.addEventListener('sudoku-loading-complete', () => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('🎯 Evento custom loading-complete ricevuto');
    });
    
    // Ascolta evento Livewire di puzzle caricato
    Livewire.on('puzzle-loaded', () => {
        hideButtonLoading();
        if (window.APP_DEBUG) console.log('🎯 Puzzle caricato - pulsanti riabilitati immediatamente');
    });
});

// Backward compatibility
function loadSamplePuzzle() {
    loadPuzzle('medium');
}
</script>
</x-site-layout>
