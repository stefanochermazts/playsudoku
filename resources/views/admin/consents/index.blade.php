<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">📊 Gestione Consensi GDPR</h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">Panoramica e gestione dei consensi utente per la compliance GDPR</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-medium rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all">
                            ← Dashboard Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                                <span class="text-blue-600 dark:text-blue-400 text-sm font-bold">📋</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Consensi Totali</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($statistics['total']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center">
                                <span class="text-green-600 dark:text-green-400 text-sm font-bold">✅</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Attivi</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($statistics['active']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-md flex items-center justify-center">
                                <span class="text-yellow-600 dark:text-yellow-400 text-sm font-bold">🚫</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ritirati</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($statistics['withdrawn']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center">
                                <span class="text-purple-600 dark:text-purple-400 text-sm font-bold">📈</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Compliance Rate</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $statistics['compliance_rate'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Bar --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6 p-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    
                    {{-- Primary Actions --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        <form method="POST" action="{{ route('admin.consents.cleanup') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Sei sicuro di voler pulire i consensi scaduti?')"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm font-medium">
                                🧹 Pulizia Consensi Scaduti
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.consents.statistics') }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                            📊 Statistiche API
                        </a>
                    </div>

                    {{-- Export Actions --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative" x-data="{ showExport: false }">
                            <button @click="showExport = !showExport" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm font-medium">
                                📥 Export Dati
                            </button>
                            
                            <div x-show="showExport" 
                                 @click.away="showExport = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 p-4 z-10">
                                
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Export Consensi</h4>
                                
                                <form method="POST" action="{{ route('admin.consents.export') }}" class="space-y-3">
                                    @csrf
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Formato</label>
                                            <select name="format" class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="csv">CSV</option>
                                                <option value="json">JSON</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                                            <select name="type" class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">Tutti</option>
                                                <option value="essential">Cookie Essenziali</option>
                                                <option value="analytics">Cookie Analytics</option>
                                                <option value="marketing">Cookie Marketing</option>
                                                <option value="contact_form">Form di Contatto</option>
                                                <option value="registration">Registrazione</option>
                                                <option value="privacy_settings">Impostazioni Privacy</option>
                                                <option value="newsletter">Newsletter</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Da</label>
                                            <input type="date" name="date_from" 
                                                   class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">A</label>
                                            <input type="date" name="date_to" 
                                                   class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" 
                                            class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-xs font-medium">
                                        📥 Esporta
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- GDPR User Export --}}
                        <div class="relative" x-data="{ showUserExport: false }">
                            <button @click="showUserExport = !showUserExport" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm font-medium">
                                👤 Export GDPR Utente
                            </button>
                            
                            <div x-show="showUserExport" 
                                 @click.away="showUserExport = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 p-4 z-10">
                                
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Export Dati Utente GDPR</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Esporta tutti i dati di un utente per richieste GDPR</p>
                                
                                <form method="POST" action="{{ route('admin.consents.export-user') }}" class="space-y-3">
                                    @csrf
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Utente</label>
                                        <select name="user_id" required class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            <option value="">Seleziona utente...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Formato</label>
                                        <select name="format" class="w-full text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            <option value="json">JSON</option>
                                            <option value="csv">CSV</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" 
                                            class="w-full px-3 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 text-xs font-medium">
                                        👤 Esporta Dati Utente
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtri</h3>
                
                <form method="GET" action="{{ route('admin.consents.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo Consenso</label>
                        <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Tutti i tipi</option>
                            <option value="essential" {{ request('type') === 'essential' ? 'selected' : '' }}>Cookie Essenziali</option>
                            <option value="analytics" {{ request('type') === 'analytics' ? 'selected' : '' }}>Cookie Analytics</option>
                            <option value="marketing" {{ request('type') === 'marketing' ? 'selected' : '' }}>Cookie Marketing</option>
                            <option value="contact_form" {{ request('type') === 'contact_form' ? 'selected' : '' }}>Form di Contatto</option>
                            <option value="registration" {{ request('type') === 'registration' ? 'selected' : '' }}>Registrazione</option>
                            <option value="privacy_settings" {{ request('type') === 'privacy_settings' ? 'selected' : '' }}>Impostazioni Privacy</option>
                            <option value="newsletter" {{ request('type') === 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stato</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Tutti gli stati</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Scaduti</option>
                            <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Ritirati</option>
                            <option value="granted" {{ request('status') === 'granted' ? 'selected' : '' }}>Concessi</option>
                            <option value="denied" {{ request('status') === 'denied' ? 'selected' : '' }}>Negati</option>
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utente</label>
                        <select name="user_id" id="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Tutti gli utenti</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            🔍 Filtra
                        </button>
                    </div>
                </form>
            </div>

            {{-- Consents Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Consensi Utente ({{ $consents->total() }} totali)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valore</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scadenza</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($consents as $consent)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($consent->user)
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $consent->user->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $consent->user->email }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400 italic">
                                                    Ospite ({{ Str::limit($consent->session_id, 12) }})
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $consent->consent_type === 'essential' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                            {{ $consent->consent_type === 'analytics' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $consent->consent_type === 'marketing' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                            {{ $consent->consent_type === 'contact_form' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                            {{ $consent->consent_type === 'registration' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}
                                            {{ $consent->consent_type === 'privacy_settings' ? 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200' : '' }}
                                            {{ $consent->consent_type === 'newsletter' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}">
                                            {{ $consent->display_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($consent->consent_value)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                ✅ Concesso
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                ❌ Negato
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $consent->isActive() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $consent->isExpired() ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                            {{ $consent->isWithdrawn() ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                            {{ $consent->status_display }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $consent->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($consent->expires_at)
                                            {{ $consent->expires_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">Mai</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.consents.show', $consent) }}" 
                                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                👁️ Dettagli
                                            </a>
                                            
                                            @if(!$consent->isWithdrawn() && $consent->consent_value)
                                                <form method="POST" action="{{ route('admin.consents.withdraw', $consent) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Sei sicuro di voler ritirare questo consenso?')"
                                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                        🚫 Ritira
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="text-center">
                                            <p class="text-lg mb-2">📋 Nessun consenso trovato</p>
                                            <p class="text-sm">Nessun consenso corrisponde ai filtri selezionati.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($consents->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $consents->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
