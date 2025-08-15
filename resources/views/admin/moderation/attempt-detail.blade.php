<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                            🔍 Dettaglio Tentativo #{{ $attempt->id }}
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">
                            Analisi completa del tentativo sospetto
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.moderation.flagged') }}" 
                           class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            ← Lista Flaggati
                        </a>
                        @if(!$attempt->reviewed_at)
                            <div class="flex space-x-2">
                                <!-- Approva Modal Trigger -->
                                <button onclick="document.getElementById('approve-modal').classList.remove('hidden')"
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    ✅ Approva
                                </button>
                                <!-- Rifiuta Modal Trigger -->
                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                    ❌ Rifiuta
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Status Card -->
            <div class="mb-8">
                @if($attempt->reviewed_at)
                    @if($attempt->valid)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                                        Tentativo Approvato
                                    </h3>
                                    <p class="text-green-600 dark:text-green-300">
                                        Revisionato da {{ $attempt->reviewer->name }} il {{ $attempt->reviewed_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @if($attempt->admin_notes)
                                <div class="mt-4 p-3 bg-green-100 dark:bg-green-900/40 rounded-lg">
                                    <p class="text-sm text-green-800 dark:text-green-200">
                                        <strong>Note:</strong> {{ $attempt->admin_notes }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                                        Tentativo Rifiutato
                                    </h3>
                                    <p class="text-red-600 dark:text-red-300">
                                        Invalidato da {{ $attempt->reviewer->name }} il {{ $attempt->reviewed_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @if($attempt->admin_notes)
                                <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/40 rounded-lg">
                                    <p class="text-sm text-red-800 dark:text-red-200">
                                        <strong>Motivo:</strong> {{ $attempt->admin_notes }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200">
                                    In Attesa di Revisione
                                </h3>
                                <p class="text-orange-600 dark:text-orange-300">
                                    Questo tentativo necessita di valutazione manuale
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Colonna principale -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Informazioni Generali -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            📋 Informazioni Generali
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-neutral-700 dark:text-neutral-300 mb-3">Utente</h4>
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ substr($attempt->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            {{ $attempt->user->name }}
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-300">
                                            {{ $attempt->user->email }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-medium text-neutral-700 dark:text-neutral-300 mb-3">Sfida</h4>
                                <div class="space-y-1">
                                    <div class="text-sm">
                                        <span class="font-medium">Tipo:</span> 
                                        <span class="capitalize">{{ $attempt->challenge->type }}</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="font-medium">ID:</span> #{{ $attempt->challenge->id }}
                                    </div>
                                    <div class="text-sm">
                                        <span class="font-medium">Difficoltà:</span> 
                                        {{ ucfirst($attempt->challenge->puzzle->difficulty ?? 'N/A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analisi Anomalie -->
                    @if(isset($analysis['is_anomalous']) && $analysis['is_anomalous'])
                        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                                🔍 Analisi Anomalie Temporali
                            </h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                        <div class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">
                                            Z-Score
                                        </div>
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                            {{ number_format($analysis['z_score'], 2) }}
                                        </div>
                                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                            Soglia: ±3.0
                                        </div>
                                    </div>

                                    <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                                        <div class="text-sm font-medium text-orange-800 dark:text-orange-200 mb-1">
                                            Percentile
                                        </div>
                                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                            {{ number_format($analysis['percentile'], 1) }}%
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Tipo Anomalia
                                        </div>
                                        @if($analysis['anomaly_type'] === 'too_fast')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                🏃‍♂️ Sospettosamente veloce
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                🐌 Sospettosamente lento
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Confronto Media
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-300">
                                            <div>Tentativo: {{ $attempt->getFormattedDuration() }}</div>
                                            <div>Media sfida: {{ gmdate('i:s', intval($analysis['mean_duration'] / 1000)) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Performance Dettagli -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            ⚡ Dettagli Performance
                        </h3>
                        
                        <div class="grid md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                    {{ $attempt->getFormattedDuration() }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">Tempo</div>
                            </div>
                            
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ $attempt->errors_count }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">Errori</div>
                            </div>
                            
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ $attempt->hints_used }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">Hint</div>
                            </div>
                            
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $attempt->moves->count() }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">Mosse</div>
                            </div>
                        </div>

                        @if($attempt->paused_ms_total > 0)
                            <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                                    ⏸️ <strong>Pause totali:</strong> 
                                    {{ gmdate('i:s', intval($attempt->paused_ms_total / 1000)) }}
                                    ({{ $attempt->pauses_count ?? 0 }} volte)
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Validazione Mosse -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            🎯 Validazione Mosse
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <span class="font-medium">Stato validazione:</span>
                                @if($attempt->move_validation_passed === true)
                                    <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-3 py-1 rounded-full text-sm">
                                        ✅ Superata
                                    </span>
                                @elseif($attempt->move_validation_passed === false)
                                    <span class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 px-3 py-1 rounded-full text-sm">
                                        ❌ Fallita
                                    </span>
                                @else
                                    <span class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 px-3 py-1 rounded-full text-sm">
                                        ⏳ In attesa
                                    </span>
                                @endif
                            </div>

                            @if($attempt->validation_notes)
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Note validazione:</strong><br>
                                        {{ $attempt->validation_notes }}
                                    </div>
                                </div>
                            @endif

                            @if($attempt->validated_at)
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                                    Validato il {{ $attempt->validated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Statistiche Sfida -->
                    @if(isset($challengeStats['sample_count']) && $challengeStats['sample_count'] > 0)
                        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                                📊 Statistiche Sfida
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">Campioni:</span>
                                    <span class="font-medium">{{ $challengeStats['sample_count'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">Tempo medio:</span>
                                    <span class="font-medium">{{ gmdate('i:s', intval($challengeStats['mean_duration'] / 1000)) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">Mediana:</span>
                                    <span class="font-medium">{{ gmdate('i:s', intval($challengeStats['median_duration'] / 1000)) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">Min/Max:</span>
                                    <span class="font-medium text-xs">
                                        {{ gmdate('i:s', intval($challengeStats['min_duration'] / 1000)) }} / 
                                        {{ gmdate('i:s', intval($challengeStats['max_duration'] / 1000)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Statistiche Utente -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            👤 Profilo Utente
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Tot. tentativi:</span>
                                <span class="font-medium">{{ $userStats['total_attempts'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Validi:</span>
                                <span class="font-medium text-green-600">{{ $userStats['valid_attempts'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Flaggati:</span>
                                <span class="font-medium text-red-600">{{ $userStats['flagged_attempts'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Tempo medio:</span>
                                <span class="font-medium">
                                    @if($userStats['avg_duration'])
                                        {{ gmdate('i:s', intval($userStats['avg_duration'] / 1000)) }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Altri Tentativi -->
                    @if($userAttempts->count() > 0)
                        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                                🔄 Altri Tentativi
                            </h3>
                            
                            <div class="space-y-3">
                                @foreach($userAttempts->take(5) as $otherAttempt)
                                    <div class="p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm">
                                                {{ $otherAttempt->created_at->format('d/m') }}
                                            </span>
                                            <div class="flex items-center space-x-2">
                                                @if($otherAttempt->duration_ms)
                                                    <span class="text-xs">{{ $otherAttempt->getFormattedDuration() }}</span>
                                                @endif
                                                @if($otherAttempt->flagged_for_review)
                                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                                @elseif($otherAttempt->valid)
                                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                                @else
                                                    <span class="w-2 h-2 bg-neutral-400 rounded-full"></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Azioni Quick -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            🛠️ Azioni Quick
                        </h3>
                        
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('admin.moderation.challenges.analyze', $attempt->challenge) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-4 py-2 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors text-sm">
                                    🔄 Ri-analizza Sfida
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Approvazione -->
    <div id="approve-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                ✅ Approva Tentativo
            </h3>
            <p class="text-neutral-600 dark:text-neutral-300 mb-4">
                Confermi di voler approvare questo tentativo? Sarà considerato valido.
            </p>
            
            <form method="POST" action="{{ route('admin.moderation.attempts.approve', $attempt) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Note (opzionale)
                    </label>
                    <textarea name="notes" rows="3" 
                              class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                              placeholder="Aggiungi note sulla decisione..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        Conferma Approvazione
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('approve-modal').classList.add('hidden')"
                            class="flex-1 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors">
                        Annulla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Rifiuto -->
    <div id="reject-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                ❌ Rifiuta Tentativo
            </h3>
            <p class="text-neutral-600 dark:text-neutral-300 mb-4">
                Questo tentativo sarà invalidato e rimosso dalle classifiche.
            </p>
            
            <form method="POST" action="{{ route('admin.moderation.attempts.reject', $attempt) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Motivo del rifiuto *
                    </label>
                    <textarea name="reason" rows="3" required
                              class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                              placeholder="Spiega perché questo tentativo viene rifiutato..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        Conferma Rifiuto
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('reject-modal').classList.add('hidden')"
                            class="flex-1 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors">
                        Annulla
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-site-layout>
