<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-full flex items-center justify-center text-white font-bold text-2xl mr-6">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $user->name }}</h1>
                            <p class="text-neutral-600 dark:text-neutral-300 mt-1">{{ $user->email }}</p>
                            <div class="flex items-center mt-2 space-x-4">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300">
                                        👑 Amministratore
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                        👤 Utente
                                    </span>
                                @endif
                                
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                        ✓ Email Verificata
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300">
                                        ⏳ Email Non Verificata
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-warning-600 to-warning-700 text-white font-medium rounded-lg hover:from-warning-700 hover:to-warning-800 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Modifica
                        </a>
                        <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-medium rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Torna alla Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-success-50 dark:bg-success-900/50 border border-success-200 dark:border-success-800 text-success-800 dark:text-success-200 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Informazioni Principali -->
                <div class="lg:col-span-2">
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-6">Informazioni Account</h2>
                        
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Nome Completo</dt>
                                <dd class="mt-1 text-sm text-neutral-900 dark:text-white">{{ $user->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Indirizzo Email</dt>
                                <dd class="mt-1 text-sm text-neutral-900 dark:text-white">{{ $user->email }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Ruolo</dt>
                                <dd class="mt-1">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300">
                                            👑 Amministratore
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                            👤 Utente
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Stato Email</dt>
                                <dd class="mt-1">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                            ✓ Verificata il {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300">
                                            ⏳ Non Verificata
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Data Registrazione</dt>
                                <dd class="mt-1 text-sm text-neutral-900 dark:text-white">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                        ({{ $user->created_at->diffForHumans() }})
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Ultimo Aggiornamento</dt>
                                <dd class="mt-1 text-sm text-neutral-900 dark:text-white">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                        ({{ $user->updated_at->diffForHumans() }})
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Azioni Rapide -->
                <div class="space-y-6">
                    
                    <!-- Azioni Account -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Azioni</h3>
                        
                        <div class="space-y-3">
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-warning-600 to-warning-700 text-white font-medium rounded-lg hover:from-warning-700 hover:to-warning-800 transition-all">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifica Utente
                            </a>
                            
                            @if($user->id !== auth()->id() && !($user->role === 'admin' && \App\Models\User::where('role', 'admin')->count() <= 1))
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                      onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?\n\nQuesta azione non può essere annullata.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-danger-600 to-danger-700 text-white font-medium rounded-lg hover:from-danger-700 hover:to-danger-800 transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Elimina Utente
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Informazioni Sistema -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Informazioni Sistema</h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">ID Utente</dt>
                                <dd class="mt-1 text-sm text-neutral-900 dark:text-white font-mono">#{{ $user->id }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Account Attivo</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300">
                                        ✓ Attivo
                                    </span>
                                </dd>
                            </div>
                            
                            @if($user->id === auth()->id())
                                <div class="pt-3 border-t border-neutral-200 dark:border-neutral-700">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                                        👤 Il tuo account
                                    </span>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
