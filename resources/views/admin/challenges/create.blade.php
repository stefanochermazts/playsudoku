<x-site-layout>

    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Crea Nuova Sfida</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-300">Configura una nuova sfida Sudoku per gli utenti</p>
                
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
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">Nuova</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Form -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                <form method="POST" action="{{ route('admin.challenges.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <!-- Titolo -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Titolo *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
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
                                      class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">{{ old('description') }}</textarea>
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
                                    <option value="daily" {{ old('type') === 'daily' ? 'selected' : '' }}>Giornaliera</option>
                                    <option value="weekly" {{ old('type') === 'weekly' ? 'selected' : '' }}>Settimanale</option>
                                    <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>Personalizzata</option>
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
                                    <option value="">Seleziona puzzle disponibile</option>
                                    @forelse($puzzles as $puzzle)
                                        <option value="{{ $puzzle->id }}" {{ old('puzzle_id') == $puzzle->id ? 'selected' : '' }}>
                                            Puzzle #{{ $puzzle->id }} - {{ ucfirst($puzzle->difficulty) }} (Seed: {{ $puzzle->seed }})
                                        </option>
                                    @empty
                                        <option disabled>Nessun puzzle disponibile</option>
                                    @endforelse
                                </select>
                                @error('puzzle_id')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Solo puzzle non ancora assegnati ad altre sfide
                                    @if($puzzles->count() === 0)
                                        - <a href="{{ route('admin.puzzles.generate') }}" class="text-primary-600 dark:text-primary-400 hover:underline">Genera nuovi puzzle</a>
                                    @endif
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
                                       value="{{ old('starts_at', now()->addHour()->format('Y-m-d\TH:i')) }}" 
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
                                       value="{{ old('ends_at', now()->addDays(7)->format('Y-m-d\TH:i')) }}" 
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
                                    <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Pubblica</option>
                                    <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Privata</option>
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
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bozza</option>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Attiva</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completata</option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Annullata</option>
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
                                    <option value="1" {{ old('settings.hints_allowed', 1) ? 'selected' : '' }}>Abilitati</option>
                                    <option value="0" {{ !old('settings.hints_allowed', 1) ? 'selected' : '' }}>Disabilitati</option>
                                </select>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Se disabilitati, la board non mostrerà candidati né il relativo pulsante.</p>
                            </div>
                            
                            <div>
                                <label for="time_limit" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Limite Tempo (minuti)
                                </label>
                                <input type="number" 
                                       id="time_limit" 
                                       name="settings[time_limit]" 
                                       value="{{ old('settings.time_limit') }}" 
                                       min="1" 
                                       max="180"
                                       placeholder="Es. 30"
                                       class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Lascia vuoto per nessun limite. Il gioco si fermerà automaticamente alla scadenza.</p>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                            <a href="{{ route('admin.challenges') }}" 
                               class="px-6 py-3 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors">
                                Annulla
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 transition-all shadow-lg shadow-primary-500/25">
                                Crea Sfida
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-site-layout>
