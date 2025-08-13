<x-site-layout seo-title="Gestione Puzzle - Admin" seo-description="Gestione dei puzzle Sudoku">
    
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 via-primary-50/30 to-accent-50/20 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Gestione Puzzle</h1>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-300">Genera e gestisci i puzzle Sudoku disponibili per le sfide</p>
                    </div>
                    <a href="{{ route('admin.puzzles.generate') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all shadow-lg shadow-primary-500/25">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Genera Puzzle
                    </a>
                </div>
                
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
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">Puzzle</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Filters -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 mb-8">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Difficoltà</label>
                        <select name="difficulty" class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            <option value="">Tutte le difficoltà</option>
                            <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Facile</option>
                            <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medio</option>
                            <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Difficile</option>
                            <option value="expert" {{ request('difficulty') === 'expert' ? 'selected' : '' }}>Esperto</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Stato</label>
                        <select name="assigned" class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            <option value="">Tutti i puzzle</option>
                            <option value="no" {{ request('assigned') === 'no' ? 'selected' : '' }}>🟢 Disponibili</option>
                            <option value="yes" {{ request('assigned') === 'yes' ? 'selected' : '' }}>🔴 Assegnati</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                            Filtra
                        </button>
                        <a href="{{ route('admin.puzzles') }}" 
                           class="px-6 py-2 bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-success-50 dark:bg-success-900/50 border border-success-200 dark:border-success-800 text-success-800 dark:text-success-200 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

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

            <!-- Puzzles Table -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl border border-neutral-200/50 dark:border-neutral-700/50 overflow-hidden">
                @if($puzzles->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">ID</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Seed</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Difficoltà</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Stato</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Sfide Assegnate</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Creato</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-neutral-900 dark:text-white">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach($puzzles as $puzzle)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-700/50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">
                                            #{{ $puzzle->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $puzzle->seed }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $difficultyColors = [
                                                    'easy' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                                    'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                                    'hard' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                                                    'expert' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
                                                ];
                                                $difficultyLabels = [
                                                    'easy' => 'Facile',
                                                    'medium' => 'Medio',
                                                    'hard' => 'Difficile',
                                                    'expert' => 'Esperto'
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $difficultyColors[$puzzle->difficulty] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                                {{ $difficultyLabels[$puzzle->difficulty] ?? $puzzle->difficulty }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($puzzle->challenges->count() > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/50 dark:text-danger-300">
                                                    🔴 Assegnato
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                                    🟢 Disponibile
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            @if($puzzle->challenges->count() > 0)
                                                @foreach($puzzle->challenges as $challenge)
                                                    <div class="mb-1">
                                                        <a href="{{ route('admin.challenges.show', $challenge) }}" 
                                                           class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                                            {{ Str::limit($challenge->title, 30) }}
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-neutral-400 italic">Nessuna</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $puzzle->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($puzzle->challenges->count() === 0)
                                                <form method="POST" action="{{ route('admin.puzzles.destroy', $puzzle) }}" class="inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo puzzle?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-danger-600 dark:text-danger-400 hover:text-danger-900 dark:hover:text-danger-300" 
                                                            title="Elimina">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-neutral-400" title="Impossibile eliminare: puzzle assegnato a sfide">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($puzzles->hasPages())
                        <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                            {{ $puzzles->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-neutral-400 dark:text-neutral-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 0V7a2 2 0 012-2h12a2 2 0 012 2v2M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Nessun puzzle trovato</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 mb-6">
                            @if(request()->hasAny(['difficulty', 'assigned']))
                                Nessun puzzle corrisponde ai filtri selezionati.
                            @else
                                Non ci sono ancora puzzle nel sistema.
                            @endif
                        </p>
                        <a href="{{ route('admin.puzzles.generate') }}" 
                           class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Genera Primi Puzzle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-site-layout>
