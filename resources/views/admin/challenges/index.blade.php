<x-site-layout seo-title="Gestione Sfide - Admin" seo-description="Gestione delle sfide Sudoku">
    
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 via-primary-50/30 to-accent-50/20 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Gestione Sfide</h1>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-300">Crea e gestisci le sfide Sudoku per gli utenti</p>
                    </div>
                    <a href="{{ route('admin.challenges.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all shadow-lg shadow-primary-500/25">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuova Sfida
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
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">Sfide</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Filters -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 mb-8">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-64">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Ricerca per titolo</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cerca sfide..."
                               class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Tipo</label>
                        <select name="type" class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            <option value="">Tutti i tipi</option>
                            <option value="daily" {{ request('type') === 'daily' ? 'selected' : '' }}>Giornaliera</option>
                            <option value="weekly" {{ request('type') === 'weekly' ? 'selected' : '' }}>Settimanale</option>
                            <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Personalizzata</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Stato</label>
                        <select name="status" class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-800 dark:text-white">
                            <option value="">Tutti gli stati</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bozza</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attiva</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completata</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annullata</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                            Filtra
                        </button>
                        <a href="{{ route('admin.challenges') }}" 
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

            <!-- Challenges Table -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl border border-neutral-200/50 dark:border-neutral-700/50 overflow-hidden">
                @if($challenges->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Titolo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Tipo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Stato</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Creatore</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 dark:text-white">Tentativi</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-neutral-900 dark:text-white">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach($challenges as $challenge)
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                                    {{ $challenge->title }}
                                                </div>
                                                @if($challenge->description)
                                                    <div class="text-sm text-neutral-500 dark:text-neutral-400 truncate max-w-xs">
                                                        {{ Str::limit($challenge->description, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $typeColors = [
                                                    'daily' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                                    'weekly' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                                    'custom' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                                                ];
                                                $typeLabels = [
                                                    'daily' => 'Giornaliera',
                                                    'weekly' => 'Settimanale',
                                                    'custom' => 'Personalizzata'
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$challenge->type] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                                {{ $typeLabels[$challenge->type] ?? $challenge->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusColors = [
                                                    'draft' => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
                                                    'active' => 'bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300',
                                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                                    'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/50 dark:text-danger-300'
                                                ];
                                                $statusLabels = [
                                                    'draft' => 'Bozza',
                                                    'active' => 'Attiva',
                                                    'completed' => 'Completata',
                                                    'cancelled' => 'Annullata'
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$challenge->status] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                                {{ $statusLabels[$challenge->status] ?? $challenge->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            <div>Inizia: {{ $challenge->starts_at->format('d/m/Y H:i') }}</div>
                                            <div>Finisce: {{ $challenge->ends_at->format('d/m/Y H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $challenge->creator->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $challenge->attempts_count ?? $challenge->attempts()->count() }} tentativi
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('admin.challenges.show', $challenge) }}" 
                                                   class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300" 
                                                   title="Visualizza">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('admin.challenges.edit', $challenge) }}" 
                                                   class="text-warning-600 dark:text-warning-400 hover:text-warning-900 dark:hover:text-warning-300" 
                                                   title="Modifica">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <form method="POST" action="{{ route('admin.challenges.destroy', $challenge) }}" class="inline" onsubmit="return confirm('Sei sicuro di voler eliminare questa sfida?')">
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
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($challenges->hasPages())
                        <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                            {{ $challenges->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-neutral-400 dark:text-neutral-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Nessuna sfida trovata</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 mb-6">
                            @if(request()->hasAny(['search', 'type', 'status']))
                                Nessuna sfida corrisponde ai filtri selezionati.
                            @else
                                Non ci sono ancora sfide nel sistema.
                            @endif
                        </p>
                        <a href="{{ route('admin.challenges.create') }}" 
                           class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Crea Prima Sfida
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-site-layout>
