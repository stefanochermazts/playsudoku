<x-site-layout>

    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.articles.index') }}" 
                   class="inline-flex items-center text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Torna agli articoli
                </a>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mt-4">
                🤖 Generatore Articoli AI
            </h1>
        </div>

        {{-- Configuration Required --}}
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            <div class="text-6xl mb-6">⚙️</div>
            
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-4">
                Configurazione OpenAI Richiesta
            </h2>
            
            <p class="text-neutral-600 dark:text-neutral-300 mb-8 max-w-2xl mx-auto">
                Per utilizzare il generatore di articoli AI è necessario configurare la chiave API di OpenAI. 
                Questa funzionalità utilizza GPT-4 per creare contenuti SEO-ottimizzati di alta qualità.
            </p>

            {{-- Configuration Steps --}}
            <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6 mb-8 text-left max-w-3xl mx-auto">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    📋 Passaggi per la configurazione:
                </h3>
                
                <ol class="space-y-4">
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 bg-primary-100 dark:bg-primary-800 rounded-full text-primary-600 dark:text-primary-300 font-bold text-sm mr-3 mt-0.5">1</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Ottieni una chiave API OpenAI</div>
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                Vai su <a href="https://platform.openai.com/api-keys" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">platform.openai.com/api-keys</a> 
                                e crea una nuova chiave API
                            </div>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 bg-primary-100 dark:bg-primary-800 rounded-full text-primary-600 dark:text-primary-300 font-bold text-sm mr-3 mt-0.5">2</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Configura il file .env</div>
                            <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                Aggiungi la seguente riga al tuo file <code class="bg-neutral-200 dark:bg-neutral-700 px-1 rounded">.env</code>:
                            </div>
                            <div class="bg-neutral-800 text-green-400 p-3 rounded-lg mt-2 font-mono text-sm">
                                OPENAI_API_KEY=your_api_key_here
                            </div>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <span class="flex items-center justify-center w-6 h-6 bg-primary-100 dark:bg-primary-800 rounded-full text-primary-600 dark:text-primary-300 font-bold text-sm mr-3 mt-0.5">3</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Riavvia l'applicazione</div>
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                Dopo aver aggiunto la chiave API, riavvia il server Laravel per applicare le modifiche
                            </div>
                        </div>
                    </li>
                </ol>
            </div>

            {{-- Features Overview --}}
            <div class="bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    ✨ Cosa potrai fare con il Generatore AI:
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Articoli di 1000+ parole</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Ottimizzazione SEO automatica</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Outline strutturato</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Keywords integration</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Meta tags ottimizzati</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-green-500 mr-2">✅</span>
                        <span class="text-neutral-700 dark:text-neutral-300">Contenuto per target audience</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('admin.articles.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors">
                    ✏️ Crea Articolo Manualmente
                </a>
                
                <button onclick="window.location.reload()" 
                        class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    🔄 Ricarica Pagina
                </button>
            </div>

            {{-- Help Link --}}
            <div class="mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-600">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Hai bisogno di aiuto? 
                    <a href="https://platform.openai.com/docs/quickstart" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                        Consulta la documentazione OpenAI
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-site-layout>
