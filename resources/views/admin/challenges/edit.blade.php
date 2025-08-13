<x-site-layout seo-title="Modifica Sfida - Admin" seo-description="Modifica la sfida Sudoku">
    
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 via-primary-50/30 to-accent-50/20 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Modifica Sfida</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-300">Aggiorna le impostazioni della sfida "{{ $challenge->title }}"</p>
                
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
                                <a href="{{ route('admin.challenges') }}" class="ml-1 text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                                    Sfide
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('admin.challenges.show', $challenge) }}" class="ml-1 text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ Str::limit($challenge->title, 20) }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">Modifica</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Form -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                <form method="POST" action="{{ route('admin.challenges.update', $challenge) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Titolo -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Titolo *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $challenge->title) }}" 
                                   required
                                   placeholder="Es. Sfida del Lunedì, Sudoku della Settimana..."
                                   class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            @error('title')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrizione -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Descrizione
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Descrizione opzionale della sfida..."
                                      class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">{{ old('description', $challenge->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grid 2 Colonne -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Tipo Sfida -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Tipo Sfida *
                                </label>
                                <select id="type" 
                                        name="type" 
                                        required
                                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                    <option value="">Seleziona tipo</option>
                                    <option value="daily" {{ old('type', $challenge->type) === 'daily' ? 'selected' : '' }}>Giornaliera</option>
                                    <option value="weekly" {{ old('type', $challenge->type) === 'weekly' ? 'selected' : '' }}>Settimanale</option>
                                    <option value="custom" {{ old('type', $challenge->type) === 'custom' ? 'selected' : '' }}>Personalizzata</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Puzzle -->
                            <div>
                                <label for="puzzle_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Puzzle Sudoku *
                                </label>
                                <select id="puzzle_id" 
                                        name="puzzle_id" 
                                        required
                                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                    <option value="">Seleziona puzzle</option>
                                    @foreach($puzzles as $puzzle)
                                        <option value="{{ $puzzle->id }}" {{ old('puzzle_id', $challenge->puzzle_id) == $puzzle->id ? 'selected' : '' }}>
                                            Puzzle #{{ $puzzle->id }} - {{ ucfirst($puzzle->difficulty) }} (Seed: {{ $puzzle->seed }})
                                            @if($puzzle->id === $challenge->puzzle_id) - [CORRENTE] @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('puzzle_id')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Include puzzle disponibili + quello attualmente assegnato
                                </p>
                            </div>
                        </div>

                        <!-- Grid 2 Colonne - Date -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Data Inizio -->
                            <div>
                                <label for="starts_at" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Data e Ora Inizio *
                                </label>
                                <input type="datetime-local" 
                                       id="starts_at" 
                                       name="starts_at" 
                                       value="{{ old('starts_at', $challenge->starts_at->format('Y-m-d\TH:i')) }}" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                @error('starts_at')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data Fine -->
                            <div>
                                <label for="ends_at" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Data e Ora Fine *
                                </label>
                                <input type="datetime-local" 
                                       id="ends_at" 
                                       name="ends_at" 
                                       value="{{ old('ends_at', $challenge->ends_at->format('Y-m-d\TH:i')) }}" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                @error('ends_at')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Grid 2 Colonne - Opzioni -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Visibilità -->
                            <div>
                                <label for="visibility" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Visibilità *
                                </label>
                                <select id="visibility" 
                                        name="visibility" 
                                        required
                                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                    <option value="public" {{ old('visibility', $challenge->visibility) === 'public' ? 'selected' : '' }}>Pubblica</option>
                                    <option value="private" {{ old('visibility', $challenge->visibility) === 'private' ? 'selected' : '' }}>Privata</option>
                                </select>
                                @error('visibility')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Le sfide pubbliche sono visibili a tutti gli utenti
                                </p>
                            </div>

                            <!-- Stato -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Stato *
                                </label>
                                <select id="status" 
                                        name="status" 
                                        required
                                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                    <option value="draft" {{ old('status', $challenge->status) === 'draft' ? 'selected' : '' }}>Bozza</option>
                                    <option value="active" {{ old('status', $challenge->status) === 'active' ? 'selected' : '' }}>Attiva</option>
                                    <option value="completed" {{ old('status', $challenge->status) === 'completed' ? 'selected' : '' }}>Completata</option>
                                    <option value="cancelled" {{ old('status', $challenge->status) === 'cancelled' ? 'selected' : '' }}>Annullata</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Solo le sfide attive possono essere giocate dagli utenti
                                </p>
                            </div>
                        </div>

                        <!-- Impostazioni gioco -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="hints_allowed" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Candidati (Hints)
                                </label>
                                <select id="hints_allowed" name="settings[hints_allowed]" class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                    @php($allowed = data_get($challenge->settings, 'hints_allowed', true))
                                    <option value="1" {{ $allowed ? 'selected' : '' }}>Abilitati</option>
                                    <option value="0" {{ !$allowed ? 'selected' : '' }}>Disabilitati</option>
                                </select>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Se disabilitati, la board non mostrerà candidati né il relativo pulsante.</p>
                            </div>
                        </div>

                        <!-- Warning Messages -->
                        @if($challenge->attempts->count() > 0)
                            <div class="bg-warning-50 dark:bg-warning-900/50 border border-warning-200 dark:border-warning-800 text-warning-800 dark:text-warning-200 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Attenzione: questa sfida ha {{ $challenge->attempts->count() }} tentativo/i registrato/i. Le modifiche potrebbero influenzare i dati esistenti.</span>
                                </div>
                            </div>
                        @endif

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                            <a href="{{ route('admin.challenges.show', $challenge) }}" 
                               class="px-6 py-3 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors">
                                Annulla
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 transition-all shadow-lg shadow-primary-500/25">
                                Aggiorna Sfida
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-site-layout>
