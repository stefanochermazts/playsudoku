<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.terms.title') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('app.privacy.last_updated', ['date' => '22 Gennaio 2025']) }}
            </p>
        </div>

        {{-- Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 space-y-8">
            
            {{-- Accettazione --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.acceptance') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Utilizzando PlaySudoku, accetti questi termini di servizio. Se non accetti questi termini, 
                    non utilizzare il nostro servizio. Ci riserviamo il diritto di modificare questi termini 
                    in qualsiasi momento, con notifica agli utenti registrati.
                </p>
            </section>

            {{-- Descrizione del Servizio --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.service_description') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    PlaySudoku è una piattaforma online che offre:
                </p>
                <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2">
                    <li>Puzzle Sudoku interattivi di varie difficoltà</li>
                    <li>Sfide quotidiane e settimanali competitive</li>
                    <li>Classifiche e statistiche personali</li>
                    <li>Strumenti di analisi e risoluzione puzzle</li>
                    <li>Modalità allenamento con suggerimenti</li>
                </ul>
            </section>

            {{-- Obblighi dell'Utente --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.user_obligations') }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Registrazione Account</h3>
                        <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                            <li>Fornire informazioni accurate e aggiornate</li>
                            <li>Mantenere riservate le credenziali di accesso</li>
                            <li>Essere responsabile di tutte le attività del proprio account</li>
                            <li>Notificare immediatamente accessi non autorizzati</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Utilizzo del Servizio</h3>
                        <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                            <li>Utilizzare il servizio solo per scopi legali e personali</li>
                            <li>Rispettare gli altri utenti e mantenere un comportamento sportivo</li>
                            <li>Non condividere soluzioni o trucchi per le sfide competitive</li>
                            <li>Segnalare comportamenti inappropriati o violazioni</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Usi Vietati --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.prohibited_uses') }}
                </h2>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-6">
                    <p class="text-red-800 dark:text-red-200 mb-4 font-semibold">
                        È espressamente vietato:
                    </p>
                    <ul class="list-disc list-inside text-red-700 dark:text-red-300 space-y-2">
                        <li>Utilizzare bot, script automatici o software di cheating</li>
                        <li>Tentare di hackerare, compromettere o sovraccaricare il sistema</li>
                        <li>Creare account multipli per ottenere vantaggi ingiusti</li>
                        <li>Condividere credenziali di accesso con altri utenti</li>
                        <li>Copiare, distribuire o modificare il contenuto del sito</li>
                        <li>Utilizzare il servizio per attività commerciali non autorizzate</li>
                        <li>Pubblicare contenuti offensivi, illegali o inappropriati</li>
                        <li>Interferire con l'esperienza di gioco di altri utenti</li>
                    </ul>
                </div>
            </section>

            {{-- Fair Play --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Regole di Fair Play
                </h2>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                    <p class="text-blue-800 dark:text-blue-200 mb-4">
                        Per mantenere l'integrità delle competizioni:
                    </p>
                    <ul class="list-disc list-inside text-blue-700 dark:text-blue-300 space-y-2">
                        <li>Tutte le soluzioni vengono validate automaticamente dal sistema</li>
                        <li>I tempi di completamento sospetti vengono analizzati e verificati</li>
                        <li>Le pause eccessive durante le sfide possono invalidare il risultato</li>
                        <li>Ci riserviamo il diritto di rimuovere risultati anomali dalle classifiche</li>
                        <li>Gli utenti che violano il fair play possono essere sospesi o bannati</li>
                    </ul>
                </div>
            </section>

            {{-- Proprietà Intellettuale --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.intellectual_property') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    Tutti i contenuti di PlaySudoku, inclusi design, algoritmi, puzzle e software, 
                    sono protetti da diritti d'autore e proprietà intellettuale.
                </p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">✅ Permesso</h4>
                        <ul class="text-green-800 dark:text-green-200 text-sm space-y-1">
                            <li>Utilizzare il servizio per uso personale</li>
                            <li>Condividere screenshot delle tue statistiche</li>
                            <li>Scaricare i tuoi dati personali</li>
                        </ul>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <h4 class="font-semibold text-red-900 dark:text-red-100 mb-2">❌ Vietato</h4>
                        <ul class="text-red-800 dark:text-red-200 text-sm space-y-1">
                            <li>Copiare o riprodurre il codice sorgente</li>
                            <li>Creare servizi derivati o competitivi</li>
                            <li>Utilizzare contenuti per scopi commerciali</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Limitazione di Responsabilità --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.limitation_liability') }}
                </h2>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6">
                    <p class="text-yellow-800 dark:text-yellow-200 leading-relaxed">
                        PlaySudoku è fornito "così com'è" senza garanzie di alcun tipo. 
                        Non siamo responsabili per interruzioni del servizio, perdita di dati, 
                        o danni indiretti derivanti dall'uso della piattaforma. 
                        La nostra responsabilità è limitata al massimo consentito dalla legge.
                    </p>
                </div>
            </section>

            {{-- Risoluzione --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.termination') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    Puoi chiudere il tuo account in qualsiasi momento dalle impostazioni del profilo. 
                    Ci riserviamo il diritto di sospendere o terminare account che violano questi termini.
                </p>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cosa succede alla chiusura:</h4>
                    <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                        <li>I tuoi dati personali vengono eliminati secondo la privacy policy</li>
                        <li>Le statistiche nelle classifiche pubbliche rimangono anonimizzate</li>
                        <li>Non puoi più accedere al servizio con quell'account</li>
                        <li>Tutti i diritti e obblighi cessano immediatamente</li>
                    </ul>
                </div>
            </section>

            {{-- Legge Applicabile --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.terms.governing_law') }}
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Questi termini sono governati dalla legge italiana. 
                    Eventuali controversie saranno risolte presso i tribunali competenti in Italia. 
                    Per le controversie con consumatori, si applicano le normative di protezione del consumatore.
                </p>
            </section>

            {{-- Contatti --}}
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Contatti Legali
                </h2>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Per domande su questi termini di servizio:
                    </p>
                    <div class="space-y-2">
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>Email:</strong> 
                            <a href="mailto:legal@playsudoku.com" class="text-blue-600 dark:text-blue-400 hover:underline">
                                legal@playsudoku.com
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
</x-site-layout>
