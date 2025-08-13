<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                🧩 Demo Board Sudoku
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                Prova la nostra board Sudoku interattiva con tutte le funzionalità implementate
            </p>
            
            <div class="flex flex-wrap justify-center gap-4 mb-6">
                <a href="{{ route('sudoku.play') }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 
                          focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    🎮 Gioca Ora
                </a>
                <button onclick="loadEmptyBoard()" 
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 
                               focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                    📝 Board Vuota
                </button>
            </div>
            
            {{-- Selezione livelli di difficoltà --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 text-center">
                    🎯 Carica Puzzle per Difficoltà
                </h3>
                <div class="flex flex-wrap justify-center gap-2">
                    <button onclick="loadPuzzle('easy')" 
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 
                                   focus:outline-none focus:ring-2 focus:ring-green-500 font-medium text-sm">
                        🟢 Easy
                    </button>
                    <button onclick="loadPuzzle('medium')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-sm">
                        🔵 Medium
                    </button>
                    <button onclick="loadPuzzle('hard')" 
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 
                                   focus:outline-none focus:ring-2 focus:ring-yellow-500 font-medium text-sm">
                        🟡 Hard
                    </button>
                    <button onclick="loadPuzzle('expert')" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 
                                   focus:outline-none focus:ring-2 focus:ring-red-500 font-medium text-sm">
                        🔴 Expert
                    </button>
                    <button onclick="loadPuzzle('crazy')" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 
                                   focus:outline-none focus:ring-2 focus:ring-red-500 font-medium text-sm">
                        🔴 Crazy
                    </button>
                </div>
            </div>
        </div>

        {{-- Funzionalità implementate --}}
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    ✨ Funzionalità Implementate
                </h3>
                <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Rendering griglia 9×9 con evidenziazione</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Input da tastiera e mouse</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Modalità valori definitivi / candidati</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Undo/Redo illimitato</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Timer del gioco</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Validazione conflitti</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Supporto accessibilità (screen reader)</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Design responsivo</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    ⌨️ Controlli da Tastiera
                </h3>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">1-9</kbd> Inserisci numero</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Backspace/Delete</kbd> Cancella</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Frecce</kbd> Muovi selezione</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">C</kbd> Cambia modalità (Valori/Candidati)</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">U</kbd> Undo</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">R</kbd> Redo</li>
                    <li><kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Tab</kbd> Navigazione accessibilità</li>
                </ul>
            </div>
        </div>

        {{-- Board Demo --}}
        <div id="board-container">
            <div id="sudoku-board-wrapper">
                @livewire('sudoku-board', ['readOnly' => false, 'startTimer' => false], key('sudoku-demo-board'))
            </div>
        </div>

        {{-- Informazioni tecniche --}}
        <div class="mt-8 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                🔧 Dettagli Tecnici
            </h3>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <strong class="text-gray-900 dark:text-white">Frontend:</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• Livewire 3 (componente reattivo)</li>
                        <li>• Alpine.js (interazioni)</li>
                        <li>• Tailwind CSS (styling)</li>
                        <li>• CSS Grid (layout griglia)</li>
                    </ul>
                </div>
                <div>
                    <strong class="text-gray-900 dark:text-white">Backend:</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• PHP 8.2+ (strict types)</li>
                        <li>• Domain objects (Grid, Move, MoveLog)</li>
                        <li>• Generator/Validator integrati</li>
                        <li>• PSR-12 code style</li>
                    </ul>
                </div>
                <div>
                    <strong class="text-gray-900 dark:text-white">Accessibilità:</strong>
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
    console.log('🎯 Tentativo di caricare puzzle con difficoltà:', difficulty);
    
    // Metodo 1: Usa la funzione globale esposta dal componente
    if (typeof window.sudokuBoardLoadPuzzle === 'function') {
        console.log('📞 Chiamata funzione globale...');
        const success = window.sudokuBoardLoadPuzzle(difficulty);
        if (success) {
            console.log('✅ Puzzle caricato tramite funzione globale!');
            return;
        }
    }

    // Metodo 1-bis: Dispatch evento Livewire globale (v3)
    try {
        console.log('📡 Dispatch Livewire event load-sample-puzzle');
        Livewire.dispatch('load-sample-puzzle', { difficulty });
        // Diamo un feedback visivo/log
        console.log('✅ Evento inviato a Livewire');
        return;
    } catch (e) {
        console.log('❌ Dispatch fallito, passo al fallback manuale...', e);
    }
    
    // Metodo 2: Fallback - cerca tramite Livewire con retry
    const sudokuElements = document.querySelectorAll('[wire\\:id]');
    console.log('🔍 Trovati', sudokuElements.length, 'elementi Livewire');
    
    let attempts = 0;
    const maxAttempts = 3;
    
    function tryLoadPuzzle() {
        attempts++;
        console.log(`🔄 Tentativo ${attempts}/${maxAttempts}`);
        
        for (let element of sudokuElements) {
            const wireId = element.getAttribute('wire:id');
            console.log('🔍 Controllando elemento con wire:id:', wireId);
            
            const component = Livewire.find(wireId);
            
            if (component) {
                console.log('✅ Componente trovato:', component);
                console.log('🔍 Metodi disponibili:', Object.getOwnPropertyNames(component));
                
                if (typeof component.call === 'function') {
                    try {
                        console.log('📞 Chiamata loadSamplePuzzle...');
                        component.call('loadSamplePuzzle', difficulty);
                        console.log('✅ Puzzle caricato tramite fallback! Difficoltà:', difficulty);
                        return true;
                    } catch (error) {
                        console.log('❌ Errore durante la chiamata:', error.message, error);
                    }
                } else {
                    console.log('❌ Metodo call non disponibile');
                }
            } else {
                console.log('❌ Componente non trovato per wireId:', wireId);
            }
        }
        
        // Retry se non è riuscito e non ha raggiunto il max attempts
        if (attempts < maxAttempts) {
            console.log('⏰ Retry tra 1 secondo...');
            setTimeout(tryLoadPuzzle, 1000);
        } else {
            console.error('❌ Tutti i tentativi falliti - componente non trovato');
            console.log('Debug info:');
            console.log('- Funzione globale disponibile?', typeof window.sudokuBoardLoadPuzzle);
            console.log('- Elementi Livewire:', sudokuElements.length);
            console.log('- Livewire oggetto disponibile?', typeof Livewire);
        }
        return false;
    }
    
    // Inizia i tentativi
    tryLoadPuzzle();
}

// Backward compatibility
function loadSamplePuzzle() {
    loadPuzzle('medium');
}
</script>
</x-site-layout>
