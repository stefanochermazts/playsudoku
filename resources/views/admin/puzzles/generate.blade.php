<x-site-layout seo-title="Genera Puzzle - Admin" seo-description="Genera nuovi puzzle Sudoku">
    
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 via-primary-50/30 to-accent-50/20 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Genera Nuovi Puzzle</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-300">Crea un set di puzzle Sudoku con parametri personalizzati</p>
                
                <!-- Breadcrumb -->
                <nav class="flex mt-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                                Admin
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('admin.puzzles') }}" class="ml-1 text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                                    Puzzle
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">Genera</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 px-6 py-4 rounded-lg mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold mb-2">ℹ️ Informazioni sulla Generazione</h3>
                        <ul class="text-sm space-y-1">
                            <li>• I puzzle vengono generati usando algoritmi deterministici con il nostro motore Sudoku</li>
                            <li>• Ogni puzzle ha un seed univoco e una soluzione garantita unica</li>
                            <li>• La difficoltà viene valutata automaticamente e potrebbe variare leggermente</li>
                            <li>• La generazione può richiedere tempo per difficoltà elevate</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if(session('error'))
                <div class="bg-danger-50 dark:bg-danger-900/50 border border-danger-200 dark:border-danger-800 text-danger-800 dark:text-danger-200 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Form -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                <form method="POST" action="{{ route('admin.puzzles.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <!-- Numero Puzzle -->
                        <div>
                            <label for="count" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Numero di Puzzle da Generare *
                            </label>
                            <input type="number" 
                                   id="count" 
                                   name="count" 
                                   value="{{ old('count', 5) }}" 
                                   min="1" 
                                   max="50"
                                   required
                                   class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            @error('count')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Massimo 50 puzzle per volta per evitare timeout
                            </p>
                        </div>

                        <!-- Difficoltà -->
                        <div>
                            <label for="difficulty" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Livello di Difficoltà *
                            </label>
                            <select id="difficulty" 
                                    name="difficulty" 
                                    required
                                    class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                <option value="">Seleziona difficoltà</option>
                                <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>🟢 Facile</option>
                                <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>🟡 Medio</option>
                                <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>🟠 Difficile</option>
                                <option value="expert" {{ old('difficulty') === 'expert' ? 'selected' : '' }}>🔴 Esperto</option>
                            </select>
                            @error('difficulty')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                La difficoltà effettiva potrebbe variare leggermente in base agli algoritmi di valutazione
                            </p>
                        </div>

                        <!-- Seed Base (Opzionale) -->
                        <div>
                            <label for="seed_base" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Seed Base (Opzionale)
                            </label>
                            <input type="number" 
                                   id="seed_base" 
                                   name="seed_base" 
                                   value="{{ old('seed_base') }}" 
                                   min="1"
                                   placeholder="Es. 12345 (lascia vuoto per usare timestamp)"
                                   class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            @error('seed_base')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Seed per la generazione deterministica. Se vuoto, usa il timestamp corrente
                            </p>
                        </div>

                        <!-- Anteprima Configurazione -->
                        <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">📋 Riepilogo Generazione</h3>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-neutral-600 dark:text-neutral-300">Puzzle da generare:</span>
                                    <span class="font-medium text-neutral-900 dark:text-white ml-2" id="preview-count">5</span>
                                </div>
                                <div>
                                    <span class="text-neutral-600 dark:text-neutral-300">Difficoltà target:</span>
                                    <span class="font-medium text-neutral-900 dark:text-white ml-2" id="preview-difficulty">Non selezionata</span>
                                </div>
                                <div>
                                    <span class="text-neutral-600 dark:text-neutral-300">Seed base:</span>
                                    <span class="font-medium text-neutral-900 dark:text-white ml-2" id="preview-seed">Automatico</span>
                                </div>
                                <div>
                                    <span class="text-neutral-600 dark:text-neutral-300">Tempo stimato:</span>
                                    <span class="font-medium text-neutral-900 dark:text-white ml-2" id="preview-time">~30-60 secondi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning per Expert -->
                        <div id="expert-warning" class="hidden bg-warning-50 dark:bg-warning-900/50 border border-warning-200 dark:border-warning-800 text-warning-800 dark:text-warning-200 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span>⚠️ Difficoltà Esperto: La generazione potrebbe richiedere più tempo e alcuni tentativi potrebbero fallire.</span>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                            <a href="{{ route('admin.puzzles') }}" 
                               class="px-6 py-3 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors">
                                Annulla
                            </a>
                            <button type="submit" 
                                    id="generate-btn"
                                    class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 transition-all shadow-lg shadow-primary-500/25">
                                <span id="btn-text">🎯 Genera Puzzle</span>
                                <span id="btn-loading" class="hidden">⏳ Generazione in corso...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript per preview dinamica -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countInput = document.getElementById('count');
            const difficultySelect = document.getElementById('difficulty');
            const seedInput = document.getElementById('seed_base');
            const generateBtn = document.getElementById('generate-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const expertWarning = document.getElementById('expert-warning');

            // Preview updates
            function updatePreview() {
                const count = countInput.value || 5;
                const difficulty = difficultySelect.value;
                const seed = seedInput.value;

                document.getElementById('preview-count').textContent = count;
                document.getElementById('preview-difficulty').textContent = difficulty ? 
                    difficultySelect.options[difficultySelect.selectedIndex].text : 'Non selezionata';
                document.getElementById('preview-seed').textContent = seed || 'Automatico';

                // Stima tempo
                const baseTime = parseInt(count) * (difficulty === 'expert' ? 3 : difficulty === 'hard' ? 2 : 1);
                document.getElementById('preview-time').textContent = `~${Math.max(10, baseTime)}-${Math.max(30, baseTime * 2)} secondi`;

                // Warning per expert
                if (difficulty === 'expert') {
                    expertWarning.classList.remove('hidden');
                } else {
                    expertWarning.classList.add('hidden');
                }
            }

            countInput.addEventListener('input', updatePreview);
            difficultySelect.addEventListener('change', updatePreview);
            seedInput.addEventListener('input', updatePreview);

            // Form submission
            document.querySelector('form').addEventListener('submit', function() {
                generateBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
            });

            // Initial preview
            updatePreview();
        });
    </script>

</x-site-layout>
