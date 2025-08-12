<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Dashboard Amministrativa</h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">Panoramica generale del sistema PlaySudoku</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-primary-500 to-secondary-500 text-white px-4 py-2 rounded-lg">
                            <span class="text-sm font-medium">👑 Admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Quick Stats -->
            <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Totale Utenti</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['total_users'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Utenti Normali</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['regular_users'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Amministratori</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['admin_users'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Oggi</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['users_today'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Questa Settimana</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['users_this_week'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-danger-500 to-danger-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Questo Mese</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $stats['users_this_month'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid lg:grid-cols-2 gap-8">
                
                <!-- Actions Panel -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">Azioni Rapide</h2>
                    <div class="space-y-4">
                        <a href="{{ route('admin.users') }}" class="block w-full p-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105 text-center">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Gestisci Utenti
                            </div>
                        </a>
                        
                        <button class="block w-full p-4 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-semibold rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all backdrop-blur-sm text-center" disabled>
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Gestisci Sfide (Prossimamente)
                            </div>
                        </button>

                        <button class="block w-full p-4 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-semibold rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all backdrop-blur-sm text-center" disabled>
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Report & Analytics (Prossimamente)
                            </div>
                        </button>
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">Stato Sistema</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-success-500 rounded-full mr-3"></div>
                                <span class="text-success-700 dark:text-success-300 font-medium">Database</span>
                            </div>
                            <span class="text-success-600 dark:text-success-400 text-sm">Operativo</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-success-500 rounded-full mr-3"></div>
                                <span class="text-success-700 dark:text-success-300 font-medium">Laravel Framework</span>
                            </div>
                            <span class="text-success-600 dark:text-success-400 text-sm">v{{ app()->version() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-success-500 rounded-full mr-3"></div>
                                <span class="text-success-700 dark:text-success-300 font-medium">PHP</span>
                            </div>
                            <span class="text-success-600 dark:text-success-400 text-sm">v{{ phpversion() }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-warning-500 rounded-full mr-3"></div>
                                <span class="text-warning-700 dark:text-warning-300 font-medium">Game Engine</span>
                            </div>
                            <span class="text-warning-600 dark:text-warning-400 text-sm">In Sviluppo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
