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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @switch($puzzle->difficulty)
                                @case('easy')
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @break
                                @case('medium')
                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @break
                                @case('hard')
                                    bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                    @break
                                @case('expert')
                                    bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @break
                                @case('evil')
                                    bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                    @break
                                @default
                                    bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endswitch
                        ">
                            🎯 {{ ucfirst($puzzle->difficulty) }}
                        </span>
                    @endif
                    
                    @if($puzzle->solving_time_ms)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            ⚡ {{ $puzzle->solving_time_ms }}ms
                        </span>
                    @endif
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        👁️ {{ number_format($puzzle->view_count) }} 
                        @switch(app()->getLocale())
                            @case('it')
                                visualizzazioni
                                @break
                            @case('de')
                                Aufrufe
                                @break
                            @case('es')
                                visualizaciones
                                @break
                            @default
                                views
                        @endswitch
                    </span>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Left Column: Puzzle Grids --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Original Puzzle --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        @switch(app()->getLocale())
                            @case('it')
                                📝 Puzzle Originale
                                @break
                            @case('de')
                                📝 Original-Puzzle
                                @break
                            @case('es')
                                📝 Puzzle Original
                                @break
                            @default
                                📝 Original Puzzle
                        @endswitch
                    </h2>
                    
                    <div class="flex justify-center mb-4">
                        <div class="grid grid-cols-9 gap-1 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            @for ($row = 0; $row < 9; $row++)
                                @for ($col = 0; $col < 9; $col++)
                                    @php
                                        $value = $puzzle->grid_data[$row][$col] ?? 0;
                                    @endphp
                                    <div class="w-8 h-8 border border-gray-300 dark:border-gray-600 rounded flex items-center justify-center text-sm font-medium
                                        @if (($row + 1) % 3 == 0 && $row != 8) border-b-2 border-b-gray-500 @endif
                                        @if (($col + 1) % 3 == 0 && $col != 8) border-r-2 border-r-gray-500 @endif
                                        {{ $value ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-100 font-bold' : 'bg-white dark:bg-gray-600' }}
                                    ">
                                        {{ $value ?: '' }}
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                </div>
                
                {{-- Solution --}}
                @if($puzzle->solution_data && $puzzle->is_solvable)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            @switch(app()->getLocale())
                                @case('it')
                                    ✅ Soluzione Completa
                                    @break
                                @case('de')
                                    ✅ Vollständige Lösung
                                    @break
                                @case('es')
                                    ✅ Solución Completa
                                    @break
                                @default
                                    ✅ Complete Solution
                            @endswitch
                        </h2>
                        
                        <div class="flex justify-center">
                            <div class="grid grid-cols-9 gap-1 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                @for ($row = 0; $row < 9; $row++)
                                    @for ($col = 0; $col < 9; $col++)
                                        @php
                                            $originalValue = $puzzle->grid_data[$row][$col] ?? 0;
                                            $solutionValue = $puzzle->solution_data[$row][$col] ?? 0;
                                            $isOriginal = $originalValue > 0;
                                        @endphp
                                        <div class="w-8 h-8 border border-gray-300 dark:border-gray-600 rounded flex items-center justify-center text-sm font-medium
                                            @if (($row + 1) % 3 == 0 && $row != 8) border-b-2 border-b-gray-500 @endif
                                            @if (($col + 1) % 3 == 0 && $col != 8) border-r-2 border-r-gray-500 @endif
                                            {{ $isOriginal ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-100 font-bold' : 'bg-green-50 dark:bg-green-900/30 text-green-900 dark:text-green-100' }}
                                        ">
                                            {{ $solutionValue }}
                                        </div>
                                    @endfor
                                @endfor
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                            <span class="inline-block w-3 h-3 bg-blue-50 dark:bg-blue-900/30 rounded mr-2"></span>
                            @switch(app()->getLocale())
                                @case('it')
                                    Numeri originali
                                    @break
                                @case('de')
                                    Ursprüngliche Zahlen
                                    @break
                                @case('es')
                                    Números originales
                                    @break
                                @default
                                    Original numbers
                            @endswitch
                            <span class="inline-block w-3 h-3 bg-green-50 dark:bg-green-900/30 rounded mr-2 ml-4"></span>
                            @switch(app()->getLocale())
                                @case('it')
                                    Numeri risolti
                                    @break
                                @case('de')
                                    Gelöste Zahlen
                                    @break
                                @case('es')
                                    Números resueltos
                                    @break
                                @default
                                    Solved numbers
                            @endswitch
                        </div>
                    </div>
                @endif
            </div>

                         {{-- Right Column: Analysis & Actions --}}
             <div class="space-y-6">
                {{-- Puzzle Analysis --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                        @switch(app()->getLocale())
                            @case('it')
                                📊 Analisi Puzzle
                                @break
                            @case('de')
                                📊 Puzzle-Analyse
                                @break
                            @case('es')
                                📊 Análisis del Puzzle
                                @break
                            @default
                                📊 Puzzle Analysis
                        @endswitch
                    </h3>
                    
                    <div class="space-y-4">
                        @if($puzzle->is_solvable)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">
                                    @switch(app()->getLocale())
                                        @case('it')
                                            Stato:
                                            @break
                                        @case('de')
                                            Status:
                                            @break
                                        @case('es')
                                            Estado:
                                            @break
                                        @default
                                            Status:
                                    @endswitch
                                </span>
                                <span class="text-green-600 dark:text-green-400 font-medium">
                                    ✅ 
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
                                </span>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">
                                    @switch(app()->getLocale())
                                        @case('it')
                                            Stato:
                                            @break
                                        @case('de')
                                            Status:
                                            @break
                                        @case('es')
                                            Estado:
                                            @break
                                        @default
                                            Status:
                                    @endswitch
                                </span>
                                <span class="text-red-600 dark:text-red-400 font-medium">
                                    ❌ 
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
                                            Not solvable
                                    @endswitch
                                </span>
                            </div>
                        @endif
                        
                        {{-- Solving Steps from Database (isolated partial) --}}
                        @if($puzzle->solver_steps && is_array($puzzle->solver_steps) && count($puzzle->solver_steps) > 0)
                            @include('public-solver.partials.solution-steps', [
                                'steps' => $puzzle->solver_steps,
                                'techniques' => is_array($puzzle->techniques_used) ? $puzzle->techniques_used : []
                            ])
                        @endif
                        
                        {{-- Fallback: solo tecniche usate --}}
                        @if((!$puzzle->solver_steps || !is_array($puzzle->solver_steps) || count($puzzle->solver_steps) === 0) && $puzzle->techniques_used && is_array($puzzle->techniques_used) && count($puzzle->techniques_used) > 0)
                            <div>
                                <span class="text-gray-600 dark:text-gray-400 text-sm">
                                    🏷️ Tecniche: {{ implode(', ', array_map(fn($t) => str_replace('_', ' ', ucfirst($t)), $puzzle->techniques_used)) }}
                                </span>
                                <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-xs text-yellow-700 dark:text-yellow-300">
                                    ℹ️ Passi dettagliati non disponibili - puzzle risolto rapidamente
                                </div>
                            </div>
                        @endif

                        
                        @if($puzzle->status === 'pending')
                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                <span class="text-yellow-800 dark:text-yellow-200 text-sm">
                                    @switch(app()->getLocale())
                                        @case('it')
                                            ⏳ Puzzle in elaborazione...
                                            @break
                                        @case('de')
                                            ⏳ Puzzle wird verarbeitet...
                                            @break
                                        @case('es')
                                            ⏳ Procesando puzzle...
                                            @break
                                        @default
                                            ⏳ Processing puzzle...
                                    @endswitch
                                </span>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                @switch(app()->getLocale())
                                    @case('it')
                                        Creato il:
                                        @break
                                    @case('de')
                                        Erstellt am:
                                        @break
                                    @case('es')
                                        Creado el:
                                        @break
                                    @default
                                        Created on:
                                @endswitch
                            </span>
                            <span class="text-gray-900 dark:text-white">{{ $puzzle->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        @if($puzzle->processed_at)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">
                                    @switch(app()->getLocale())
                                        @case('it')
                                            Processato il:
                                            @break
                                        @case('de')
                                            Verarbeitet am:
                                            @break
                                        @case('es')
                                            Procesado el:
                                            @break
                                        @default
                                            Processed on:
                                    @endswitch
                                </span>
                                <span class="text-gray-900 dark:text-white">{{ $puzzle->processed_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                                 </div>
                 

 
                 {{-- Sharing Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                        @switch(app()->getLocale())
                            @case('it')
                                🔗 Condividi
                                @break
                            @case('de')
                                🔗 Teilen
                                @break
                            @case('es')
                                🔗 Compartir
                                @break
                            @default
                                🔗 Share
                        @endswitch
                    </h3>
                    
                    <div class="space-y-3">
                        <button onclick="copyToClipboard('{{ $puzzle->canonical_url ?: request()->url() }}', this)" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            📋 
                            @switch(app()->getLocale())
                                @case('it')
                                    Copia Link
                                    @break
                                @case('de')
                                    Link Kopieren
                                    @break
                                @case('es')
                                    Copiar Enlace
                                    @break
                                @default
                                    Copy Link
                            @endswitch
                        </button>
                        
                        <button onclick="shareToTwitter()" 
                                class="w-full px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 text-sm font-medium">
                            🐦 Twitter
                        </button>
                        
                        <button onclick="shareToFacebook()" 
                                class="w-full px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 text-sm font-medium">
                            📘 Facebook
                        </button>
                        
                        <button onclick="shareToWhatsApp()" 
                                class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium">
                            💬 WhatsApp
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center text-xs text-gray-600 dark:text-gray-400">
                        {{ number_format($puzzle->share_count) }} 
                        @switch(app()->getLocale())
                            @case('it')
                                condivisioni
                                @break
                            @case('de')
                                mal geteilt
                                @break
                            @case('es')
                                veces compartido
                                @break
                            @default
                                shares
                        @endswitch
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                        @switch(app()->getLocale())
                            @case('it')
                                🎯 Azioni
                                @break
                            @case('de')
                                🎯 Aktionen
                                @break
                            @case('es')
                                🎯 Acciones
                                @break
                            @default
                                🎯 Actions
                        @endswitch
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('localized.public-solver.index', app()->getLocale()) }}" 
                           class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium text-center">
                            🤖 
                            @switch(app()->getLocale())
                                @case('it')
                                    Risolvi Altro Puzzle
                                    @break
                                @case('de')
                                    Anderes Puzzle Lösen
                                    @break
                                @case('es')
                                    Resolver Otro Puzzle
                                    @break
                                @default
                                    Solve Another Puzzle
                            @endswitch
                        </a>
                        
                        <a href="{{ route('localized.sudoku.training', app()->getLocale()) }}" 
                           class="block w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium text-center">
                            🎮 
                            @switch(app()->getLocale())
                                @case('it')
                                    Modalità Allenamento
                                    @break
                                @case('de')
                                    Trainingsmodus
                                    @break
                                @case('es')
                                    Modo Entrenamiento
                                    @break
                                @default
                                    Training Mode
                            @endswitch
                        </a>
                        
                        @if(auth()->check())
                            <a href="{{ route('localized.challenges.index', app()->getLocale()) }}" 
                               class="block w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium text-center">
                                🏆 
                                @switch(app()->getLocale())
                                    @case('it')
                                        Sfide Competitive
                                        @break
                                    @case('de')
                                        Wettbewerbsherausforderungen
                                        @break
                                    @case('es')
                                        Desafíos Competitivos
                                        @break
                                    @default
                                        Competitive Challenges
                                @endswitch
                            </a>
                        @else
                            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" 
                               class="block w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium text-center">
                                🏆 
                                @switch(app()->getLocale())
                                    @case('it')
                                        Registrati per Sfide
                                        @break
                                    @case('de')
                                        Für Herausforderungen Registrieren
                                        @break
                                    @case('es')
                                        Regístrate para Desafíos
                                        @break
                                    @default
                                        Register for Challenges
                                @endswitch
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Schema.org JSON-LD temporaneamente disabilitato per problemi sintassi Blade --}}

@php
    $copiedText = match(app()->getLocale()) {
        'it' => 'Copiato!',
        'de' => 'Kopiert!',
        'es' => '¡Copiado!',
        default => 'Copied!'
    };
    
    $copyErrorText = match(app()->getLocale()) {
        'it' => 'Errore nella copia del link',
        'de' => 'Fehler beim Kopieren des Links',
        'es' => 'Error al copiar el enlace',
        default => 'Failed to copy link'
    };
@endphp

@push('scripts')
<script>
// Sharing functions for social media
const puzzleUrl = @json($puzzle->canonical_url ?: request()->url());
const puzzleTitle = @json($puzzle->seo_title ?: "Sudoku Puzzle Solution");
const puzzleDescription = @json($puzzle->seo_description ?: "Check out this step-by-step Sudoku solution!");
const copiedText = @json($copiedText);
const copyErrorText = @json($copyErrorText);

function copyToClipboard(url, buttonElement) {
    // Get button element - either passed directly or find the calling button
    const button = buttonElement || document.querySelector('button[onclick*="copyToClipboard"]');
    
    if (!button) {
        console.error('Button element not found');
        alert(copyErrorText);
        return;
    }
    
    // Check if clipboard API is available
    if (!navigator.clipboard) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showCopySuccess(button);
        } catch (err) {
            console.error('Fallback copy failed: ', err);
            alert(copyErrorText);
        }
        document.body.removeChild(textArea);
        return;
    }
    
    navigator.clipboard.writeText(url).then(function() {
        showCopySuccess(button);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert(copyErrorText);
    });
}

function showCopySuccess(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '✅ ' + copiedText;
    button.classList.add('bg-green-600', 'hover:bg-green-700');
    button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
    }, 2000);
}

function shareToTwitter() {
    try {
        const text = encodeURIComponent(puzzleTitle + ' - ' + puzzleDescription);
        const url = encodeURIComponent(puzzleUrl);
        const twitterUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}&hashtags=sudoku,puzzle,solver`;
        window.open(twitterUrl, '_blank', 'width=600,height=400');
    } catch (err) {
        console.error('Twitter share error:', err);
    }
}

function shareToFacebook() {
    try {
        const url = encodeURIComponent(puzzleUrl);
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        window.open(facebookUrl, '_blank', 'width=600,height=400');
    } catch (err) {
        console.error('Facebook share error:', err);
    }
}

function shareToWhatsApp() {
    try {
        const text = encodeURIComponent(puzzleTitle + ' - ' + puzzleDescription + '\n' + puzzleUrl);
        const whatsappUrl = `https://wa.me/?text=${text}`;
        window.open(whatsappUrl, '_blank');
    } catch (err) {
        console.error('WhatsApp share error:', err);
    }
}

// Suppress browser extension errors
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('message channel closed')) {
        e.preventDefault();
        return true;
    }
});

// Suppress unhandled promise rejections from browser extensions
window.addEventListener('unhandledrejection', function(e) {
    if (e.reason && e.reason.message && e.reason.message.includes('message channel closed')) {
        e.preventDefault();
        return true;
    }
});
</script>
@endpush

</x-site-layout>

