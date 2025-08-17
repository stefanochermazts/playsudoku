<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Header --}}
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            🔍 Dettaglio Consenso #{{ $consent->id }}
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Informazioni dettagliate sul consenso e audit trail
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.consents.index') }}" 
                           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            ← Torna alla Lista
                        </a>
                        
                        @if(!$consent->isWithdrawn() && $consent->consent_value)
                            <form method="POST" action="{{ route('admin.consents.withdraw', $consent) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Sei sicuro di voler ritirare questo consenso?')"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    🚫 Ritira Consenso
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                
                {{-- Consent Details --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">📋 Informazioni Consenso</h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            
                            {{-- Basic Info --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID</label>
                                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $consent->id }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo Consenso</label>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $consent->consent_type === 'essential' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $consent->consent_type === 'analytics' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $consent->consent_type === 'marketing' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}">
                                        {{ $consent->display_name }}
                                    </span>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valore</label>
                                    @if($consent->consent_value)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ✅ Concesso
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            ❌ Negato
                                        </span>
                                    @endif
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stato</label>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $consent->isActive() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $consent->isExpired() ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                        {{ $consent->isWithdrawn() ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                        {{ $consent->status_display }}
                                    </span>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Versione</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->consent_version ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            {{-- Dates --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Creazione</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                
                                @if($consent->granted_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Concessione</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->granted_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                @endif
                                
                                @if($consent->withdrawn_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Ritiro</label>
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $consent->withdrawn_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                @endif
                                
                                @if($consent->expires_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Scadenza</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->expires_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                @endif
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ultimo Aggiornamento</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->updated_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Technical Details --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">🔧 Dettagli Tecnici</h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Indirizzo IP</label>
                                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $consent->ip_address ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Session ID</label>
                                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ Str::limit($consent->session_id ?? 'N/A', 20) }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Agent</label>
                                    <p class="text-sm text-gray-900 dark:text-white break-all">{{ Str::limit($consent->user_agent ?? 'N/A', 60) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($consent->metadata && is_array($consent->metadata))
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metadata</label>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <pre class="text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ json_encode($consent->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Audit Trail --}}
                    @if(isset($auditLogs) && $auditLogs->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">📜 Audit Trail</h2>
                        
                        <div class="space-y-4">
                            @foreach($auditLogs as $log)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->action }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                                
                                @if($log->metadata)
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    @foreach($log->metadata as $key => $value)
                                        <span class="inline-block mr-4"><strong>{{ $key }}:</strong> {{ $value }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div>
                    {{-- User Info --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">👤 Informazioni Utente</h3>
                        
                        @if($consent->user)
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->user->name }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $consent->user->email }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Utente</label>
                                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $consent->user->id }}</p>
                                </div>
                                
                                <div class="pt-3">
                                    <a href="{{ route('admin.users.show', $consent->user) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">
                                        👤 Visualizza Profilo Utente
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                                Utente ospite (sessione: {{ Str::limit($consent->session_id, 12) }})
                            </p>
                        @endif
                    </div>

                    {{-- Related Consents --}}
                    @if(isset($relatedConsents) && $relatedConsents->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">🔗 Consensi Correlati</h3>
                        
                        <div class="space-y-3">
                            @foreach($relatedConsents->take(5) as $related)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $related->display_name }}</span>
                                    <span class="text-xs {{ $related->consent_value ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $related->consent_value ? '✅' : '❌' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $related->created_at->format('d/m/Y H:i') }}</p>
                                <a href="{{ route('admin.consents.show', $related) }}" 
                                   class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                    Visualizza →
                                </a>
                            </div>
                            @endforeach
                            
                            @if($relatedConsents->count() > 5)
                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                ... e altri {{ $relatedConsents->count() - 5 }} consensi
                            </p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
