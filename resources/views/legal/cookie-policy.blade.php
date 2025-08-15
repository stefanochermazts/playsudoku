<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.cookies.title') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('app.privacy.last_updated', ['date' => '22 Gennaio 2025']) }}
            </p>
        </div>

        {{-- Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 space-y-8">
            
            {{-- Introduzione --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Cosa sono i Cookie
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    I cookie sono piccoli file di testo che vengono memorizzati sul tuo dispositivo quando visiti un sito web. 
                    Utilizziamo i cookie per migliorare la tua esperienza su PlaySudoku, ricordare le tue preferenze e 
                    analizzare l'utilizzo del sito.
                </p>
            </section>

            {{-- Tipi di Cookie --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.cookies.cookie_types') }}
                </h2>
                
                <div class="space-y-6">
                    {{-- Cookie Essenziali --}}
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                🔒 {{ __('app.cookies.essential_cookies') }}
                            </h3>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 text-sm font-medium rounded-full">
                                Sempre Attivi
                            </span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            {{ __('app.cookies.essential_description') }}
                        </p>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cookie utilizzati:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                <li><code>playsudoku_session</code> - Gestione sessione utente (durata: sessione)</li>
                                <li><code>XSRF-TOKEN</code> - Protezione CSRF (durata: sessione)</li>
                                <li><code>locale</code> - Preferenza lingua (durata: 1 anno)</li>
                                <li><code>theme</code> - Preferenza tema chiaro/scuro (durata: 1 anno)</li>
                                <li><code>consent</code> - Consenso cookie (durata: 1 anno)</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Cookie Analytics --}}
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                📊 {{ __('app.cookies.analytics_cookies') }}
                            </h3>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 text-sm font-medium rounded-full">
                                Opzionali
                            </span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            {{ __('app.cookies.analytics_description') }}
                        </p>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cookie utilizzati:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                <li><code>_ga</code> - Google Analytics ID utente (durata: 2 anni)</li>
                                <li><code>_ga_G446PVFY6BW</code> - Google Analytics ID sessione (durata: 2 anni)</li>
                                <li><code>_gid</code> - Google Analytics ID sessione (durata: 1 giorno)</li>
                                <li><code>_gat</code> - Google Analytics throttling (durata: 1 minuto)</li>
                            </ul>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                <strong>Provider:</strong> Google LLC - 
                                <a href="https://policies.google.com/privacy" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Privacy Policy Google
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- Cookie Marketing --}}
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                🎯 {{ __('app.cookies.marketing_cookies') }}
                            </h3>
                            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-100 text-sm font-medium rounded-full">
                                Non Utilizzati
                            </span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300">
                            Attualmente non utilizziamo cookie di marketing o pubblicitari. 
                            Ti informeremo e chiederemo il consenso se dovessimo introdurli in futuro.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Gestione Cookie --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Gestione delle Preferenze Cookie
                </h2>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                    <p class="text-blue-800 dark:text-blue-200 mb-4">
                        {{ __('app.cookies.manage_consent') }}
                    </p>
                    <div class="space-y-3">
                        <button onclick="showCookiePreferences()" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            🍪 {{ __('app.cookies.manage_preferences') }}
                        </button>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Puoi anche gestire i cookie direttamente dalle impostazioni del tuo browser.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Browser Settings --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Impostazioni del Browser
                </h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Puoi controllare e gestire i cookie attraverso le impostazioni del tuo browser:
                </p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Chrome</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Impostazioni → Privacy e sicurezza → Cookie
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Firefox</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Opzioni → Privacy e sicurezza → Cookie
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Safari</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Preferenze → Privacy → Cookie
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Edge</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Impostazioni → Cookie e autorizzazioni sito
                        </p>
                    </div>
                </div>
            </section>

            {{-- Contatti --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Domande sui Cookie
                </h2>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Se hai domande sulla nostra politica sui cookie:
                    </p>
                    <div class="space-y-2">
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>Email:</strong> 
                            <a href="mailto:privacy@playsudoku.com" class="text-blue-600 dark:text-blue-400 hover:underline">
                                privacy@playsudoku.com
                            </a>
                        </p>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>Form di contatto:</strong> 
                            <a href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}" 
                               class="text-blue-600 dark:text-blue-400 hover:underline">
                                Contattaci qui
                            </a>
                        </p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<script>
function showCookiePreferences() {
    if (window.showCookieBanner) {
        window.showCookieBanner();
    } else {
        alert('Funzionalità di gestione cookie in arrivo!');
    }
}
</script>

</x-site-layout>
