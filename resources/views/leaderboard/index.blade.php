<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">
                    🏆 Classifiche
                </h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-300">
                    Scopri i migliori giocatori e le loro performance
                </p>
            </div>
            
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-neutral-900 dark:text-white mb-2">Classifiche in Arrivo</h3>
                    <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                        Le classifiche globali saranno disponibili nella Fase 7.<br>
                        Continua a giocare per scalare le posizioni!
                    </p>
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Torna al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
