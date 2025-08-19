<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.dashboard') }}" class="text-neutral-600 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Gestione Redis</h1>
                            <p class="text-neutral-600 dark:text-neutral-300 mt-2">Statistiche e amministrazione cache Redis</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-4 py-2 rounded-lg">
                            <span class="text-sm font-medium">⚡ Redis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Redis Status -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 mb-8">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">📊 Stato Redis</h2>
                
                @if($redisStats['status'] === 'connected')
                    <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6">
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Status</p>
                                    <p class="text-lg font-bold text-green-900 dark:text-green-100">Connesso</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Versione</p>
                                    <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $redisStats['redis_version'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-xl border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-purple-800 dark:text-purple-200">Chiavi Totali</p>
                                    <p class="text-lg font-bold text-purple-900 dark:text-purple-100">{{ number_format($redisStats['total_keys']) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl border border-orange-200 dark:border-orange-800">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-orange-800 dark:text-orange-200">Memoria</p>
                                    <p class="text-lg font-bold text-orange-900 dark:text-orange-100">{{ $redisStats['used_memory_human'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid lg:grid-cols-2 gap-6">
                        <div class="bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-xl">
                            <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">Dettagli Connessione</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-neutral-600 dark:text-neutral-400">Client Connessi:</span>
                                    <span class="font-mono text-neutral-900 dark:text-neutral-100">{{ $redisStats['connected_clients'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-600 dark:text-neutral-400">Uptime:</span>
                                    <span class="font-mono text-neutral-900 dark:text-neutral-100">{{ $redisStats['uptime_in_days'] }} giorni</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-xl">
                            <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">Configurazione Cache</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-neutral-600 dark:text-neutral-400">Driver:</span>
                                    <span class="font-mono text-neutral-900 dark:text-neutral-100">{{ config('cache.default') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-600 dark:text-neutral-400">Namespace:</span>
                                    <span class="font-mono text-neutral-900 dark:text-neutral-100">playsudoku:</span>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif($redisStats['status'] === 'not_redis')
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-xl border border-yellow-200 dark:border-yellow-800">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">Redis Non Attivo</h3>
                                <p class="text-yellow-700 dark:text-yellow-300">Driver cache corrente: <strong>{{ $redisStats['driver'] }}</strong></p>
                                <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">{{ $redisStats['message'] }}</p>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-xl border border-red-200 dark:border-red-800">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Errore Connessione Redis</h3>
                                <p class="text-red-700 dark:text-red-300">{{ $redisStats['message'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if($redisStats['status'] === 'connected' && !empty($redisUsage))
            <!-- Cache Usage Analysis -->
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 mb-8">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">🔍 Analisi Utilizzo Cache</h2>
                
                <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($redisUsage as $type => $data)
                        <div class="bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-800 dark:to-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white capitalize">{{ $type }}</h3>
                                <div class="flex space-x-2">
                                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $data['key_count'] }} totali
                                    </span>
                                    @if(isset($data['active_keys']) && $data['active_keys'] > 0)
                                        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-medium">
                                            {{ $data['active_keys'] }} attive
                                        </span>
                                    @endif
                                    @if(isset($data['expired_keys']) && $data['expired_keys'] > 0)
                                        <span class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-3 py-1 rounded-full text-sm font-medium">
                                            {{ $data['expired_keys'] }} scadute
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ $data['description'] }}</p>
                            
                            @if($data['avg_ttl_human'])
                                <div class="mb-4">
                                    <div class="flex items-center text-sm">
                                        <span class="text-neutral-600 dark:text-neutral-400">TTL medio:</span>
                                        <span class="ml-2 font-mono bg-neutral-200 dark:bg-neutral-700 px-2 py-1 rounded text-neutral-900 dark:text-neutral-100">{{ $data['avg_ttl_human'] }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($data['sample_keys']))
                                <div>
                                    <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Chiavi di esempio:</h4>
                                    <div class="space-y-1">
                                        @foreach(array_slice($data['sample_keys'], 0, 3) as $key)
                                            <div class="font-mono text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200 px-2 py-1 rounded truncate">
                                                {{ $key }}
                                            </div>
                                        @endforeach
                                        @if(count($data['sample_keys']) > 3)
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                                ... e altre {{ count($data['sample_keys']) - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            @if($data['key_count'] > 0)
                            <!-- Info per categoria -->
                            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-600">
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                    @if($type === 'laravel_cache')
                                        <p class="mb-2">📝 Queste sono cache create dal CacheService (hashate da Laravel per sicurezza)</p>
                                    @elseif($type === 'framework')
                                        <p class="mb-2">⚙️ Cache interne di Laravel (schedule, configurazioni, ecc.)</p>
                                    @elseif($type === 'queues')
                                        <p class="mb-2">📤 Code di lavoro per task asincroni</p>
                                    @endif
                                    
                                    @if($data['expired_keys'] > 0)
                                        <p class="text-orange-600 dark:text-orange-400">
                                            ⚠️ {{ $data['expired_keys'] }} chiavi scadute (verranno rimosse automaticamente)
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Danger Zone -->
            <div class="bg-red-50/70 dark:bg-red-900/20 backdrop-blur-sm rounded-2xl p-8 border border-red-200/50 dark:border-red-800/50">
                <h2 class="text-2xl font-bold text-red-900 dark:text-red-100 mb-6">⚠️ Zona Pericolosa</h2>
                
                <div class="bg-white/80 dark:bg-neutral-800/80 p-6 rounded-xl border border-red-200 dark:border-red-700">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">Reset Completo Redis</h3>
                    <p class="text-red-700 dark:text-red-300 mb-6">
                        Questa operazione rimuoverà <strong>tutte le chiavi cache Laravel</strong> del sito (prefisso: playsudoku_prod_laravel_cache_*).
                        L'operazione è irreversibile e comporterà il reload di tutte le cache dal database.
                        <br><br>
                        <strong>Include:</strong> Cache applicazione, statistiche, leaderboard, sessioni framework.
                    </p>
                    
                    <div class="flex items-center space-x-4">
                        <button id="btn-reset-redis" onclick="confirmRedisReset()" 
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg 
                                       focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 
                                       dark:focus:ring-offset-neutral-900 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Reset Completo Redis
                        </button>
                        
                        <div class="text-sm text-red-600 dark:text-red-400">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                Operazione irreversibile
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function confirmRedisReset() {
            if (confirm('⚠️ ATTENZIONE: Sei sicuro di voler resettare TUTTO il Redis cache?\n\nQuesta operazione:\n- Rimuoverà tutte le cache del sito\n- Causerà un temporaneo rallentamento\n- È IRREVERSIBILE\n\nProcedere?')) {
                resetRedis();
            }
        }

        function resetRedis() {
            const btn = document.getElementById('btn-reset-redis');
            const originalText = btn.innerHTML;
            
            // Mostra loading
            btn.innerHTML = '<svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Resettando...';
            btn.disabled = true;
            
            fetch('{{ route("admin.redis.reset") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
                            .then(data => {
                    if (data.success) {
                        let message = `✅ ${data.message}`;
                        if (data.keys_found !== undefined && data.deleted_keys !== undefined) {
                            message += `\n\n📊 Dettagli:\n• Chiavi trovate: ${data.keys_found}\n• Chiavi cancellate: ${data.deleted_keys}`;
                        }
                        alert(message);
                        location.reload(); // Ricarica la pagina per aggiornare le statistiche
                    } else {
                        alert(`❌ Errore: ${data.message}`);
                    }
                })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Errore di rete durante il reset');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // Auto-refresh ogni 60 secondi per aggiornare le statistiche
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</x-site-layout>
