<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('app.nav.dashboard') }}</h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">{{ __('Welcome back') }}, {{ auth()->user()->name }}!</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-primary-500 to-secondary-500 text-white px-4 py-2 rounded-lg">
                            <span class="text-sm font-medium">{{ __('Level') }} 1</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Quick Stats -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Puzzle Risolti</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">0</p>
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
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Miglior Tempo</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">--:--</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Streak Corrente</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">0</p>
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
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Ranking</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">--</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Daily Challenge -->
                <div class="lg:col-span-2">
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Sfida di Oggi</h2>
                            <span class="bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-3 py-1 rounded-lg text-sm font-medium">
                                Facile
                            </span>
                        </div>
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Inizia la Sfida di Oggi</h3>
                            <p class="text-neutral-600 dark:text-neutral-300 mb-6">Un nuovo puzzle ti aspetta. Metti alla prova le tue abilità!</p>
                            <button class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Inizia Ora
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Recent Activity -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Attività Recente</h3>
                        <div class="space-y-3">
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">Nessuna attività recente</p>
                        </div>
                    </div>

                    <!-- Leaderboard -->
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Classifica Globale</h3>
                        <div class="space-y-3">
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">Caricamento...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
