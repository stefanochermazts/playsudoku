<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                            🛡️ Dashboard Moderazione Anti-Cheat
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">
                            Monitoraggio anomalie e gestione tentativi sospetti
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-4 py-2 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            ← Admin Dashboard
                        </a>
                        <div class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-4 py-2 rounded-lg">
                            <span class="text-sm font-medium">🛡️ Moderatore</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Quick Stats -->
            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Tentativi Flaggati -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Tot. Flaggati</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['total_flagged'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- In Attesa Revisione -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">In Attesa</p>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['pending_review'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Revisionati Oggi -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Oggi</p>
                            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['reviewed_today'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tentativi Invalidi -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Invalidi</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['invalid_attempts'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Validazione Mosse Fallita -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Mov. Invalid</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['move_validation_failed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Row -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Tentativi Flaggati -->
                <a href="{{ route('admin.moderation.flagged') }}" 
                   class="group bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:border-red-300 dark:hover:border-red-600 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                🚨 Tentativi Sospetti
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300 text-sm">
                                Gestisci i tentativi flaggati dal sistema anti-cheat
                            </p>
                        </div>
                        <div class="text-red-500 group-hover:text-red-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                    @if($stats['pending_review'] > 0)
                        <div class="mt-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-3 py-2 rounded-lg text-sm">
                            {{ $stats['pending_review'] }} in attesa di revisione
                        </div>
                    @endif
                </a>

                <!-- Export Report -->
                <a href="{{ route('admin.moderation.export.suspicious') }}" 
                   class="group bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:border-primary-300 dark:hover:border-primary-600 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                📊 Esporta Report
                            </h3>
                            <p class="text-neutral-600 dark:text-neutral-300 text-sm">
                                Scarica report CSV dei tentativi sospetti
                            </p>
                        </div>
                        <div class="text-primary-500 group-hover:text-primary-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Sistema Stats -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                        ⚙️ Sistema Anti-Cheat
                    </h3>
                    <p class="text-neutral-600 dark:text-neutral-300 text-sm mb-4">
                        Stato dei sistemi di validazione automatica
                    </p>
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">Attivo</span>
                    </div>
                </div>
            </div>

            <!-- Ultimi Tentativi Sospetti -->
            @if($recentSuspicious->count() > 0)
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        🚨 Ultimi Tentativi Sospetti
                    </h3>
                    <a href="{{ route('admin.moderation.flagged') }}" 
                       class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">
                        Vedi tutti →
                    </a>
                </div>

                <div class="space-y-4">
                    @foreach($recentSuspicious as $attempt)
                    <div class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                <span class="text-red-600 dark:text-red-400 font-semibold text-sm">
                                    {{ substr($attempt->user->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <div class="font-medium text-neutral-900 dark:text-white">
                                    {{ $attempt->user->name }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                                    Sfida {{ ucfirst($attempt->challenge->type) }} - 
                                    {{ $attempt->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($attempt->duration_ms)
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $attempt->getFormattedDuration() }}
                                </span>
                            @endif
                            <a href="{{ route('admin.moderation.attempts.show', $attempt) }}" 
                               class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-3 py-1 rounded-lg text-sm hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors">
                                Esamina
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Statistiche Periodo -->
            @if($periodicStats->count() > 0)
            <div class="mt-8 bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                    📈 Statistiche Ultimi 7 Giorni
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm font-medium text-neutral-600 dark:text-neutral-300">
                                <th class="pb-4">Data</th>
                                <th class="pb-4">Tot. Tentativi</th>
                                <th class="pb-4">Flaggati</th>
                                <th class="pb-4">Invalidi</th>
                                <th class="pb-4">% Anomale</th>
                            </tr>
                        </thead>
                        <tbody class="space-y-2">
                            @foreach($periodicStats as $stat)
                            <tr class="text-sm">
                                <td class="py-3 font-medium text-neutral-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 text-neutral-600 dark:text-neutral-300">
                                    {{ $stat->total_attempts }}
                                </td>
                                <td class="py-3">
                                    <span class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-1 rounded text-xs">
                                        {{ $stat->flagged_count }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-2 py-1 rounded text-xs">
                                        {{ $stat->invalid_count }}
                                    </span>
                                </td>
                                <td class="py-3 text-neutral-600 dark:text-neutral-300">
                                    @if($stat->total_attempts > 0)
                                        {{ number_format(($stat->flagged_count / $stat->total_attempts) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-site-layout>
