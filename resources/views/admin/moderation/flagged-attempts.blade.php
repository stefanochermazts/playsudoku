<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                            🚨 Tentativi Sospetti
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">
                            Gestione tentativi flaggati dal sistema anti-cheat
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.moderation.dashboard') }}" 
                           class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            ← Moderazione
                        </a>
                        <a href="{{ route('admin.moderation.export.suspicious') }}" 
                           class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                            📊 Esporta CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Filtri -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
                <form method="GET" action="{{ route('admin.moderation.flagged') }}" class="grid md:grid-cols-5 gap-4">
                    <!-- Stato -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Stato</label>
                        <select name="status" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                            <option value="">Tutti</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>In Attesa</option>
                            <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Revisionati</option>
                        </select>
                    </div>

                    <!-- Tipo Anomalia -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Anomalia</label>
                        <select name="anomaly_type" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                            <option value="">Tutte</option>
                            <option value="too_fast" {{ request('anomaly_type') === 'too_fast' ? 'selected' : '' }}>Troppo veloce</option>
                            <option value="too_slow" {{ request('anomaly_type') === 'too_slow' ? 'selected' : '' }}>Troppo lento</option>
                        </select>
                    </div>

                    <!-- Sfida -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sfida</label>
                        <select name="challenge_id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                            <option value="">Tutte le sfide</option>
                            @foreach($challenges as $challenge)
                                <option value="{{ $challenge->id }}" {{ request('challenge_id') == $challenge->id ? 'selected' : '' }}>
                                    {{ ucfirst($challenge->type) }} #{{ $challenge->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Data da -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Da</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                    </div>

                    <!-- Data a -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">A</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                    </div>

                    <!-- Pulsanti -->
                    <div class="md:col-span-5 flex space-x-3">
                        <button type="submit" 
                                class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                            🔍 Filtra
                        </button>
                        <a href="{{ route('admin.moderation.flagged') }}" 
                           class="bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 transition-colors">
                            🗑️ Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Lista Tentativi -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl border border-neutral-200/50 dark:border-neutral-700/50 overflow-hidden">
                @if($attempts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-neutral-50 dark:bg-neutral-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Utente & Sfida
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Tempo & Errori
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Anomalia
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Stato
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Data
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                        Azioni
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach($attempts as $attempt)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/30">
                                    <!-- Utente & Sfida -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                {{ substr($attempt->user->name, 0, 2) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                                    {{ $attempt->user->name }}
                                                </div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ $attempt->user->email }}
                                                </div>
                                                <div class="text-xs text-neutral-400 dark:text-neutral-500">
                                                    Sfida {{ ucfirst($attempt->challenge->type) }} #{{ $attempt->challenge->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tempo & Errori -->
                                    <td class="px-6 py-4">
                                        @if($attempt->duration_ms)
                                            <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                                {{ $attempt->getFormattedDuration() }}
                                            </div>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-500 text-sm">N/A</span>
                                        @endif
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ $attempt->errors_count }} errori, {{ $attempt->hints_used }} hint
                                        </div>
                                    </td>

                                    <!-- Anomalia -->
                                    <td class="px-6 py-4">
                                        @if(str_contains($attempt->validation_notes, 'too_fast'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                🏃‍♂️ Troppo veloce
                                            </span>
                                        @elseif(str_contains($attempt->validation_notes, 'too_slow'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                🐌 Troppo lento
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                ⚠️ Sospetto
                                            </span>
                                        @endif
                                        @if($attempt->validation_notes)
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                {{ Str::limit($attempt->validation_notes, 40) }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Stato -->
                                    <td class="px-6 py-4">
                                        @if($attempt->reviewed_at)
                                            @if($attempt->valid)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    ✅ Approvato
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                    ❌ Rifiutato
                                                </span>
                                            @endif
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                {{ $attempt->reviewed_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                ⏳ In attesa
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Data -->
                                    <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ $attempt->created_at->format('d/m/Y') }}<br>
                                        <span class="text-xs">{{ $attempt->created_at->format('H:i') }}</span>
                                    </td>

                                    <!-- Azioni -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('admin.moderation.attempts.show', $attempt) }}" 
                                               class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-3 py-1 rounded-lg text-xs hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors">
                                                👁️ Dettagli
                                            </a>
                                            
                                            @if(!$attempt->reviewed_at)
                                                <form method="POST" action="{{ route('admin.moderation.attempts.approve', $attempt) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Approvare questo tentativo?')"
                                                            class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-3 py-1 rounded-lg text-xs hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                                                        ✅ Approva
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($attempts->hasPages())
                        <div class="bg-neutral-50 dark:bg-neutral-700/30 px-6 py-3">
                            {{ $attempts->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <!-- Stato vuoto -->
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🎉</div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                            Nessun tentativo sospetto
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300">
                            @if(request()->hasAny(['status', 'anomaly_type', 'challenge_id', 'date_from', 'date_to']))
                                Nessun risultato con i filtri applicati.
                            @else
                                Ottimo! Il sistema anti-cheat non ha rilevato anomalie.
                            @endif
                        </p>
                        @if(request()->hasAny(['status', 'anomaly_type', 'challenge_id', 'date_from', 'date_to']))
                            <a href="{{ route('admin.moderation.flagged') }}" 
                               class="inline-block mt-4 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                                Rimuovi filtri
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-site-layout>
