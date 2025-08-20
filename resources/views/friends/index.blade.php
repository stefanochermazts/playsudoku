<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.friends.title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.friends.subtitle') }}
                    </p>
                </div>
                
                {{-- Statistiche --}}
                <div class="flex md:hidden items-center space-x-6">
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total_friends'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.friends.total_friends') }}</div>
                    </div>
                    @if($stats['pending_requests'] > 0)
                    <div class="text-center">
                        <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_requests'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.friends.pending_requests') }}</div>
                    </div>
                    @endif
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total_friends'] }}</div>
                        <div class="text-sm text-neutral-500">{{ __('app.friends.total_friends') }}</div>
                    </div>
                    @if($stats['pending_requests'] > 0)
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_requests'] }}</div>
                        <div class="text-sm text-neutral-500">{{ __('app.friends.pending_requests') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 xl:grid-cols-6 gap-6 lg:gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-2 xl:col-span-2 space-y-6">
                {{-- Suggerimenti --}}
                @if($suggestions->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6" x-data="{ q: '' }">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.friends.suggestions') }}
                    </h2>
                    <input type="text" x-model="q" placeholder="{{ __('app.friends.search_placeholder') }}" class="w-full mb-3 pl-3 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white" />
                    
                    <div class="space-y-3">
                        @foreach($suggestions as $suggestion)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" x-show="'{{ strtolower($suggestion->name) }}'.includes(q.toLowerCase())">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-neutral-900 dark:text-white truncate">{{ $suggestion->name }}</div>
                                <div class="text-sm text-neutral-500">
                                    {{ __('app.friends.mutual_friends') }}: {{ $suggestion->mutual_friends_count ?? 0 }}
                                </div>
                            </div>
                            <button onclick="sendFriendRequest({{ $suggestion->id }})"
                                    class="px-3 py-1 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm whitespace-nowrap">
                                {{ __('app.friends.add') }}
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Contenuto principale --}}
            <div class="lg:col-span-3 xl:col-span-4 space-y-6">
                {{-- Richieste in attesa --}}
                @if($pendingRequests->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
                    <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('app.friends.pending_requests') }} ({{ $pendingRequests->count() }})
                        </h2>
                    </div>
                    
                    <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($pendingRequests as $request)
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex items-start space-x-4 min-w-0 flex-1">
                                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ substr($request->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-medium text-neutral-900 dark:text-white truncate">{{ $request->user->name }}</h3>
                                        <p class="text-sm text-neutral-500 truncate">{{ $request->user->email }}</p>
                                        @if($request->message)
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1 break-words">"{{ $request->message }}"</p>
                                        @endif
                                        <p class="text-xs text-neutral-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 sm:gap-0">
                                    <button onclick="acceptFriendRequest({{ $request->id }})"
                                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm whitespace-nowrap">
                                        {{ __('app.friends.accept') }}
                                    </button>
                                    <button onclick="declineFriendRequest({{ $request->id }})"
                                            class="px-4 py-2 bg-neutral-600 text-white rounded-md hover:bg-neutral-700 text-sm whitespace-nowrap">
                                        {{ __('app.friends.decline') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Cerca utenti --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.friends.search_users') }}
                    </h2>
                    
                    <div x-data="friendSearch()">
                        <div class="relative">
                            <input type="text" 
                                   x-model="query"
                                   @input.debounce.300ms="filterLists()"
                                   placeholder="{{ __('app.friends.search_placeholder') }}"
                                   class="w-full pl-4 pr-12 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        {{-- Risultati ricerca remoti --}}
                        <template x-if="results.length > 0">
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                        🔍 Risultati di ricerca
                                    </h3>
                                    <span class="text-xs text-neutral-500" x-text="`${results.length} utenti trovati`"></span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="user in results" :key="user.id">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-medium text-neutral-900 dark:text-white truncate" x-text="user.name"></div>
                                                <div class="text-sm text-neutral-500 truncate" x-text="user.email"></div>
                                                <div class="text-xs text-green-600 dark:text-green-400">
                                                    ✅ Accetta richieste di amicizia
                                                </div>
                                            </div>
                                            <button @click="sendFriendRequest(user.id)"
                                                    class="px-3 py-1 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm whitespace-nowrap">
                                                {{ __('app.friends.send_request') }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Loading --}}
                        <div x-show="loading" class="mt-4 text-center">
                            <div class="inline-flex items-center px-4 py-2 text-sm text-neutral-600 dark:text-neutral-300">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                🔍 Ricerca in corso...
                            </div>
                        </div>
                        
                        {{-- Nessun risultato --}}
                        <template x-if="!loading && query.trim().length >= 2 && results.length === 0">
                            <div class="mt-4 text-center py-6">
                                <div class="text-neutral-400 mb-2">🔍</div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                                    Nessun utente trovato per "<span x-text="query" class="font-medium"></span>"
                                </div>
                                <div class="text-xs text-neutral-500 mt-1">
                                    Solo utenti che accettano richieste di amicizia vengono mostrati
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Lista amici --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
                    <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('app.friends.my_friends') }} ({{ $friends->count() }})
                        </h2>
                    </div>
                    
                    @if($friends->count() > 0)
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 p-4">
                        @foreach($friends as $friend)
                        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4 sm:p-6 border border-neutral-200 dark:border-neutral-600" data-friend-item data-name="{{ strtolower($friend->name) }}" data-email="{{ strtolower($friend->email) }}">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex items-start space-x-4 min-w-0 flex-1">
                                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ substr($friend->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-medium text-neutral-900 dark:text-white truncate">{{ $friend->name }}</h3>
                                        <p class="text-sm text-neutral-500 truncate">{{ $friend->email }}</p>
                                        <p class="text-xs text-neutral-400">
                                            {{ __('app.friends.friends_since') }} 
                                            @if($friend->pivot && $friend->pivot->accepted_at)
                                                {{ $friend->pivot->accepted_at->format('d/m/Y') }}
                                            @elseif($friend->pivot && $friend->pivot->created_at)
                                                {{ $friend->pivot->created_at->format('d/m/Y') }}
                                            @else
                                                {{ __('app.friends.unknown_date') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 sm:gap-0">
                                    <button onclick="viewProfile({{ $friend->id }})"
                                            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm whitespace-nowrap">
                                        {{ __('app.friends.view_profile') }}
                                    </button>
                                    <button onclick="removeFriend({{ $friend->id }})"
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm whitespace-nowrap">
                                        {{ __('app.friends.remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">{{ __('app.friends.no_friends_yet') }}</h3>
                        <p class="text-neutral-500 mb-4">{{ __('app.friends.no_friends_description') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Funzione per la ricerca degli amici
        function friendSearch() {
            return {
                query: '',
                results: [],
                loading: false,
                filterTerm: '',
                searchTimeout: null,
                
                filterLists() {
                    // Se la query è vuota, pulisci i risultati e mostra suggerimenti locali
                    const trimmedQuery = this.query.trim();
                    if (trimmedQuery.length === 0) {
                        this.results = [];
                        this.filterTerm = '';
                        this.showLocalSuggestions();
                        return;
                    }
                    
                    // Se la query è troppo breve, filtra solo suggerimenti locali
                    if (trimmedQuery.length < 2) {
                        this.filterTerm = trimmedQuery.toLowerCase();
                        this.results = [];
                        this.showLocalSuggestions();
                        return;
                    }
                    
                    // Debounce per la ricerca remota
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.searchRemoteUsers(trimmedQuery);
                    }, 300);
                },
                
                showLocalSuggestions() {
                    // Filtra dinamicamente i suggerimenti locali
                    document.querySelectorAll('[data-friend-item]').forEach(el => {
                        const name = (el.getAttribute('data-name') || '').toLowerCase();
                        const email = (el.getAttribute('data-email') || '').toLowerCase();
                        el.style.display = (!this.filterTerm || name.includes(this.filterTerm) || email.includes(this.filterTerm)) ? '' : 'none';
                    });
                },
                
                async searchRemoteUsers(query) {
                    this.loading = true;
                    this.filterTerm = query.toLowerCase();
                    
                    // Nascondi i suggerimenti locali quando cerchi
                    document.querySelectorAll('[data-friend-item]').forEach(el => {
                        el.style.display = 'none';
                    });
                    
                    try {
                        const response = await fetch(`/api/friends/search?query=${encodeURIComponent(query)}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.results = data.users || [];
                        } else {
                            console.error('Errore ricerca:', data.message);
                            this.results = [];
                            showNotification(data.message || 'Errore durante la ricerca', 'error');
                        }
                    } catch (error) {
                        console.error('Errore rete:', error);
                        this.results = [];
                        showNotification('Errore di connessione durante la ricerca', 'error');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async sendFriendRequest(userId) {
                    await sendFriendRequest(userId);
                    // Rimuovi l'utente dai risultati dopo aver inviato la richiesta
                    this.results = this.results.filter(u => u.id !== userId);
                }
            }
        }
        
        // Funzioni per le operazioni sugli amici
        async function sendFriendRequest(userId, message = null) {
            try {
                const response = await fetch('/api/friends/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId, message: message })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.friends.request_error") }}', 'error');
            }
        }
        
        async function acceptFriendRequest(friendshipId) {
            try {
                const response = await fetch(`/api/friends/accept/${friendshipId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload(); // Ricarica la pagina per aggiornare la lista
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.friends.accept_error") }}', 'error');
            }
        }
        
        async function declineFriendRequest(friendshipId) {
            try {
                const response = await fetch(`/api/friends/decline/${friendshipId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.friends.decline_error") }}', 'error');
            }
        }
        
        async function removeFriend(friendId) {
            if (!confirm('{{ __("app.friends.remove_confirmation") }}')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/friends/remove/${friendId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.friends.remove_error") }}', 'error');
            }
        }
        
        function viewProfile(userId) {
            // Per ora redirect alla dashboard dell'utente (da implementare)
            window.location.href = `/users/${userId}/profile`;
        }
        
        // Sistema di notifiche
        function showNotification(message, type = 'info') {
            // Implementazione semplice con alert per ora
            // In futuro si può migliorare con toast notifications
            if (type === 'success') {
                alert('✓ ' + message);
            } else if (type === 'error') {
                alert('✗ ' + message);
            } else {
                alert(message);
            }
        }
    </script>
    @endpush
</x-site-layout>
