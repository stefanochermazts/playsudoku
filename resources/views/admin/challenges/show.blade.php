<x-site-layout seo-title="Dettagli Sfida - Admin" seo-description="Visualizza i dettagli della sfida Sudoku">
    
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 via-primary-50/30 to-accent-50/20 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $challenge->title }}</h1>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-300">Dettagli e statistiche della sfida</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.challenges.edit', $challenge) }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-warning-600 to-warning-700 text-white font-semibold rounded-xl hover:from-warning-700 hover:to-warning-800 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all shadow-lg shadow-warning-500/25">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Modifica
                        </a>
                    </div>
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
                                <span class="ml-1 text-neutral-500 dark:text-neutral-400">{{ Str::limit($challenge->title, 30) }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Dettagli Sfida -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Informazioni Generali -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">Informazioni Generali</h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tipo</dt>
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
                                <dd class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$challenge->type] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                    {{ $typeLabels[$challenge->type] ?? $challenge->type }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Stato</dt>
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
                                <dd class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$challenge->status] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                    {{ $statusLabels[$challenge->status] ?? $challenge->status }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Visibilità</dt>
                                <dd class="text-sm text-neutral-900 dark:text-white">
                                    {{ $challenge->visibility === 'public' ? 'Pubblica' : 'Privata' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Creata da</dt>
                                <dd class="text-sm text-neutral-900 dark:text-white">{{ $challenge->creator->name }}</dd>
                            </div>

                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Periodo</dt>
                                <dd class="text-sm text-neutral-900 dark:text-white">
                                    Dal {{ $challenge->starts_at->format('d/m/Y H:i') }} 
                                    al {{ $challenge->ends_at->format('d/m/Y H:i') }}
                                    <span class="text-neutral-500 dark:text-neutral-400">
                                        ({{ $challenge->starts_at->diffForHumans($challenge->ends_at, true) }})
                                    </span>
                                </dd>
                            </div>

                            @if($challenge->description)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Descrizione</dt>
                                    <dd class="text-sm text-neutral-900 dark:text-white">{{ $challenge->description }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tentativi degli Utenti -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Tentativi Utenti</h2>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $challenge->attempts->count() }} totali
                            </span>
                        </div>

                        @if($challenge->attempts->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-white">Utente</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-white">Stato</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-white">Tempo</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-white">Data</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                        @foreach($challenge->attempts->sortBy('duration_ms') as $attempt)
                                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-700/50 transition-colors">
                                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                                                    {{ $attempt->user->name }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($attempt->completed_at)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                                            ✓ Completato
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300">
                                                            ⏳ In corso
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">
                                                    @if($attempt->duration_ms)
                                                        {{ gmdate('H:i:s', $attempt->duration_ms / 1000) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ $attempt->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-neutral-400 dark:text-neutral-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-neutral-500 dark:text-neutral-400">Nessun tentativo ancora registrato</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    
                    <!-- Statistiche -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Statistiche</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Tentativi totali</span>
                                <span class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $challenge->attempts->count() }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Completati</span>
                                <span class="text-lg font-semibold text-success-600 dark:text-success-400">{{ $challenge->attempts->whereNotNull('completed_at')->count() }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">In corso</span>
                                <span class="text-lg font-semibold text-warning-600 dark:text-warning-400">{{ $challenge->attempts->whereNull('completed_at')->count() }}</span>
                            </div>

                            @if($challenge->attempts->whereNotNull('completed_at')->count() > 0)
                                @php $bestTime = $challenge->attempts->whereNotNull('completed_at')->min('duration_ms'); @endphp
                                <div class="flex items-center justify-between pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">Miglior tempo</span>
                                    <span class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                        {{ gmdate('H:i:s', $bestTime / 1000) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informazioni Puzzle -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Puzzle Associato</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300">ID Puzzle</dt>
                                <dd class="text-sm text-neutral-900 dark:text-white">#{{ $challenge->puzzle->id }}</dd>
                            </div>
                            
                            @if($challenge->puzzle->difficulty_level)
                                <div>
                                    <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Difficoltà</dt>
                                    <dd class="text-sm text-neutral-900 dark:text-white">{{ $challenge->puzzle->difficulty_level }}</dd>
                                </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Creato il</dt>
                                <dd class="text-sm text-neutral-900 dark:text-white">{{ $challenge->puzzle->created_at->format('d/m/Y') }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Azioni -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Azioni</h3>
                        
                        <div class="space-y-3">
                            <a href="{{ route('admin.challenges.edit', $challenge) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-warning-600 text-white font-medium rounded-lg hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-warning-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifica Sfida
                            </a>
                            
                            <form method="POST" action="{{ route('admin.challenges.destroy', $challenge) }}" onsubmit="return confirm('Sei sicuro di voler eliminare questa sfida? Tutti i tentativi associati verranno eliminati.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Elimina Sfida
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-site-layout>
