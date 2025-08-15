<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.privacy.title') }}
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
                    {{ __('app.privacy.introduction') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Questa informativa descrive come raccogliamo, utilizziamo e proteggiamo i tuoi dati personali 
                    quando utilizzi PlaySudoku. Siamo impegnati a rispettare la tua privacy e a garantire la sicurezza 
                    dei tuoi dati in conformità al Regolamento Generale sulla Protezione dei Dati (GDPR).
                </p>
            </section>

            {{-- Titolare del Trattamento --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.data_controller') }}
                </h2>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-gray-700 dark:text-gray-300">
                        <strong>PlaySudoku</strong><br>
                        Email: <a href="mailto:privacy@playsudoku.com" class="text-blue-600 dark:text-blue-400 hover:underline">privacy@playsudoku.com</a>
                    </p>
                </div>
            </section>

            {{-- Dati Raccolti --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.data_collected') }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dati di Registrazione</h3>
                        <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                            <li>Nome utente e indirizzo email</li>
                            <li>Password (crittografata)</li>
                            <li>Preferenze di notificazione</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dati di Gioco</h3>
                        <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                            <li>Statistiche di gioco e progressi</li>
                            <li>Tempi di completamento delle sfide</li>
                            <li>Posizioni in classifica</li>
                            <li>Cronologia delle partite</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dati Tecnici</h3>
                        <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                            <li>Indirizzo IP</li>
                            <li>Tipo di browser e dispositivo</li>
                            <li>Cookie e preferenze del sito</li>
                            <li>Dati di utilizzo tramite Google Analytics</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Come Utilizziamo i Dati --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.data_usage') }}
                </h2>
                <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2">
                    <li>Fornire e mantenere il servizio PlaySudoku</li>
                    <li>Gestire il tuo account e autenticazione</li>
                    <li>Generare classifiche e statistiche di gioco</li>
                    <li>Inviare notifiche per nuove sfide (se consentito)</li>
                    <li>Migliorare l'esperienza utente e le funzionalità</li>
                    <li>Analizzare l'utilizzo del sito per ottimizzazioni</li>
                    <li>Rispondere alle tue richieste di supporto</li>
                </ul>
            </section>

            {{-- Condivisione dei Dati --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.data_sharing') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    Non vendiamo né condividiamo i tuoi dati personali con terze parti, eccetto nei seguenti casi:
                </p>
                <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2">
                    <li><strong>Google Analytics:</strong> Dati aggregati e anonimizzati per analisi del traffico</li>
                    <li><strong>Fornitori di servizi:</strong> Per hosting, email e manutenzione del sito</li>
                    <li><strong>Obblighi legali:</strong> Se richiesto dalla legge o autorità competenti</li>
                    <li><strong>Classifiche pubbliche:</strong> Il tuo nome utente può apparire nelle classifiche pubbliche</li>
                </ul>
            </section>

            {{-- Conservazione dei Dati --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.data_retention') }}
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Conserviamo i tuoi dati personali per il tempo necessario a fornire il servizio:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2">
                        <li><strong>Dati dell'account:</strong> Fino alla cancellazione dell'account</li>
                        <li><strong>Statistiche di gioco:</strong> 5 anni per l'analisi dei trend</li>
                        <li><strong>Log di sistema:</strong> 2 anni per sicurezza e debugging</li>
                        <li><strong>Cookie analytics:</strong> 26 mesi (Google Analytics)</li>
                    </ul>
                </div>
            </section>

            {{-- I Tuoi Diritti --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.your_rights') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    Secondo il GDPR, hai i seguenti diritti sui tuoi dati personali:
                </p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Diritto di Accesso</h4>
                        <p class="text-blue-800 dark:text-blue-200 text-sm">
                            Puoi richiedere una copia dei tuoi dati personali
                        </p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Diritto di Rettifica</h4>
                        <p class="text-green-800 dark:text-green-200 text-sm">
                            Puoi correggere dati inesatti o incompleti
                        </p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-red-900 dark:text-red-100 mb-2">Diritto di Cancellazione</h4>
                        <p class="text-red-800 dark:text-red-200 text-sm">
                            Puoi richiedere la cancellazione dei tuoi dati
                        </p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-2">Diritto di Portabilità</h4>
                        <p class="text-purple-800 dark:text-purple-200 text-sm">
                            Puoi ottenere i tuoi dati in formato leggibile
                        </p>
                    </div>
                </div>
            </section>

            {{-- Contatti --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.privacy.contact_privacy') }}
                </h2>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Per qualsiasi domanda riguardo questa informativa sulla privacy o per esercitare i tuoi diritti:
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

            {{-- Footer --}}
            <div class="text-center pt-8 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Questa informativa può essere aggiornata periodicamente. 
                    Ti informeremo di eventuali modifiche significative.
                </p>
            </div>

        </div>
    </div>
</div>
</x-site-layout>
