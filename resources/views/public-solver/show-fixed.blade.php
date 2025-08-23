<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header with Breadcrumbs --}}
        <div class="mb-8">
            <nav class="text-sm breadcrumbs mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                    <li><a href="{{ route('localized.public-solver.index', app()->getLocale()) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        @switch(app()->getLocale())
                            @case('it')
                                Solver AI
                                @break
                            @case('de')
                                KI-Löser
                                @break
                            @case('es')
                                Solucionador IA
                                @break
                            @default
                                AI Solver
                        @endswitch
                    </a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-900 dark:text-white">
                        @switch(app()->getLocale())
                            @case('it')
                                Puzzle Risolto
                                @break
                            @case('de')
                                Gelöstes Puzzle
                                @break
                            @case('es')
                                Puzzle Resuelto
                                @break
                            @default
                                Solved Puzzle
                        @endswitch
                    </li>
                </ol>
            </nav>
            
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $puzzle->seo_title ?: "Sudoku Puzzle Solution" }}
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                    {{ $puzzle->seo_description ?: "Step-by-step solution with advanced solving techniques" }}
                </p>
                
                <div class="flex flex-wrap justify-center gap-4 mb-6">
                    @if($puzzle->difficulty)
                        <div class="bg-white dark:bg-gray-800 rounded-lg px-4 py-2 shadow-sm">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">
                                @switch(app()->getLocale())
                                    @case('it')
                                        Difficoltà:
                                        @break
                                    @case('de')
                                        Schwierigkeit:
                                        @break
                                    @case('es')
                                        Dificultad:
                                        @break
                                    @default
                                        Difficulty:
                                @endswitch
                            </span>
                            @switch($puzzle->difficulty)
                                @case('easy')
                                    <span class="ml-2 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">{{ __('Easy') }}</span>
                                    @break
                                @case('medium') 
                                    <span class="ml-2 px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full text-sm font-medium">{{ __('Medium') }}</span>
                                    @break
                                @case('hard')
                                    <span class="ml-2 px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-sm font-medium">{{ __('Hard') }}</span>
                                    @break
                                @default
                                    <span class="ml-2 px-3 py-1 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium">{{ ucfirst($puzzle->difficulty) }}</span>
                            @endswitch
                        </div>
                    @endif
                    
                    @if($puzzle->solving_time_ms)
                        <div class="bg-white dark:bg-gray-800 rounded-lg px-4 py-2 shadow-sm">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">
                                @switch(app()->getLocale())
                                    @case('it')
                                        Tempo di Risoluzione:
                                        @break
                                    @case('de')
                                        Lösungszeit:
                                        @break
                                    @case('es')
                                        Tiempo de Resolución:
                                        @break
                                    @default
                                        Solving Time:
                                @endswitch
                            </span>
                            <span class="ml-2 font-medium text-blue-600 dark:text-blue-400">{{ number_format($puzzle->solving_time_ms / 1000, 3) }}s</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {{-- Original Puzzle --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    @switch(app()->getLocale())
                        @case('it')
                            Puzzle Originale
                            @break
                        @case('de')
                            Original Puzzle
                            @break
                        @case('es')
                            Puzzle Original
                            @break
                        @default
                            Original Puzzle
                    @endswitch
                </h2>
                <div class="flex justify-center">
                    <div class="sudoku-grid-display" style="display: grid; grid-template-columns: repeat(9, 1fr); grid-template-rows: repeat(9, 1fr); gap: 1px; width: 300px; height: 300px; background-color: #374151; border: 3px solid #374151;">
                        @for ($row = 0; $row < 9; $row++)
                            @for ($col = 0; $col < 9; $col++)
                                <div class="sudoku-cell-display flex items-center justify-center bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-bold text-lg
                                    @if (($row + 1) % 3 == 0 && $row != 8) border-b-2 border-b-gray-500 @endif
                                    @if (($col + 1) % 3 == 0 && $col != 8) border-r-2 border-r-gray-500 @endif
                                ">
                                    {{ $puzzle->grid_data[$row][$col] ?: '' }}
                                </div>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Solution (if available) --}}
            @if($puzzle->solution_data && $puzzle->is_solvable)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        @switch(app()->getLocale())
                            @case('it')
                                Soluzione Completa
                                @break
                            @case('de')
                                Vollständige Lösung
                                @break
                            @case('es')
                                Solución Completa
                                @break
                            @default
                                Complete Solution
                        @endswitch
                    </h2>
                    <div class="flex justify-center">
                        <div class="sudoku-grid-display" style="display: grid; grid-template-columns: repeat(9, 1fr); grid-template-rows: repeat(9, 1fr); gap: 1px; width: 300px; height: 300px; background-color: #374151; border: 3px solid #374151;">
                            @for ($row = 0; $row < 9; $row++)
                                @for ($col = 0; $col < 9; $col++)
                                    <div class="sudoku-cell-display flex items-center justify-center bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 font-bold text-lg
                                        @if (($row + 1) % 3 == 0 && $row != 8) border-b-2 border-b-gray-500 @endif
                                        @if (($col + 1) % 3 == 0 && $col != 8) border-r-2 border-r-gray-500 @endif
                                    ">
                                        {{ $puzzle->solution_data[$row][$col] }}
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Puzzle Analytics --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">
                @switch(app()->getLocale())
                    @case('it')
                        Informazioni del Puzzle
                        @break
                    @case('de')
                        Puzzle-Informationen
                        @break
                    @case('es')
                        Información del Puzzle
                        @break
                    @default
                        Puzzle Information
                @endswitch
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $puzzle->view_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @switch(app()->getLocale())
                            @case('it')
                                Visualizzazioni
                                @break
                            @case('de')
                                Aufrufe
                                @break
                            @case('es')
                                Visualizaciones
                                @break
                            @default
                                Views
                        @endswitch
                    </div>
                </div>
                
                @if($puzzle->is_solvable)
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">✓</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @switch(app()->getLocale())
                                @case('it')
                                    Risolvibile
                                    @break
                                @case('de')
                                    Lösbar
                                    @break
                                @case('es')
                                    Solucionable
                                    @break
                                @default
                                    Solvable
                            @endswitch
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400">✗</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @switch(app()->getLocale())
                                @case('it')
                                    Non risolvibile
                                    @break
                                @case('de')
                                    Nicht lösbar
                                    @break
                                @case('es')
                                    No solucionable
                                    @break
                                @default
                                    Unsolvable
                            @endswitch
                        </div>
                    </div>
                @endif
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $puzzle->created_at->diffForHumans() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @switch(app()->getLocale())
                            @case('it')
                                Creato
                                @break
                            @case('de')
                                Erstellt
                                @break
                            @case('es')
                                Creado
                                @break
                            @default
                                Created
                        @endswitch
                    </div>
                </div>
            </div>
            
            {{-- Techniques Used --}}
            @if($puzzle->techniques_used && is_array($puzzle->techniques_used) && count($puzzle->techniques_used) > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                        @switch(app()->getLocale())
                            @case('it')
                                Tecniche Utilizzate ({{ count($puzzle->techniques_used) }})
                                @break
                            @case('de')
                                Verwendete Techniken ({{ count($puzzle->techniques_used) }})
                                @break
                            @case('es')
                                Técnicas Utilizadas ({{ count($puzzle->techniques_used) }})
                                @break
                            @default
                                Techniques Used ({{ count($puzzle->techniques_used) }})
                        @endswitch
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($puzzle->techniques_used as $technique)
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                {{ str_replace('_', ' ', ucfirst($technique)) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        {{-- Action Buttons --}}
        <div class="flex justify-center space-x-4">
            <button onclick="window.location.href='{{ route('localized.public-solver.index', app()->getLocale()) }}'"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                @switch(app()->getLocale())
                    @case('it')
                        🧩 Risolvi un Altro Puzzle
                        @break
                    @case('de')
                        🧩 Weiteres Puzzle Lösen
                        @break
                    @case('es')
                        🧩 Resolver Otro Puzzle
                        @break
                    @default
                        🧩 Solve Another Puzzle
                @endswitch
            </button>
        </div>
    </div>
</div>
</x-site-layout>
