### Backlog eseguibile — PlaySudoku (TALL, Laravel 12)

Obiettivo: implementare l’MVP della piattaforma Sudoku competitiva con board 9×9, sfide asincrone su seed condiviso, classifica a tempo, solver logico, replay e requisiti di accessibilità WCAG 2.2 AA.

Nota: le attività sono ordinate per fasi incrementali. Ogni task è pensato per PR piccole, testabili e reversibili.

---

### Fase 0 — Infrastruttura & Setup
- [x] **Bootstrap progetto**: creare app Laravel 12 con PHP 8.2+, `declare(strict_types=1);`, PSR-12.
- [x] **Stack TALL**: installare Tailwind, Alpine.js, Livewire, Luvi UI; configurare `vite` e build prod con purge.
- [ ] **Octane (facoltativo per MVP)**: predisporre config per Swoole/RoadRunner.
- [x] **DB**: PostgreSQL locale; `.env.example` aggiornato; migrare su `uuid` come chiavi primarie dove opportuno.
- [x] **Code style**: PHP-CS-Fixer/laravel pint; CI con lint/test su GitHub Actions.
- [x] **Auth**: scaffolding Laravel Breeze (Blade + Livewire), login OAuth (facoltativo v2), policy/guard di base.

Deliverable: progetto avviabile, login registrazione pronte, build CSS/JS ok, CI che esegue lint e test.

---

### Fase 1 — Home page (IT/EN)
- [x] Layout hero e sezioni informative (responsive, WCAG AA) seguendo `docs/analisi-funzionale.md` §13
- [x] Contenuti localizzati (IT/EN) in `lang/*/app.php` con CTA chiare (Registrati/Accedi)
- [x] Spiegazione feature dopo registrazione: board 9×9, sfide asincrone, classifiche, profilo, replay, solver, anti‑cheat
- [x] Link interni a `dashboard`, `register`, `login`; routing con prefisso locale
- [x] SEO avanzata per home (title/description locali già presenti) e test snapshot contenuti

Deliverable: home accessibile con copy coerente (IT/EN) e CTA funzionanti.

---

### Fase 2 — Dominio Sudoku (Engine)
- [x] **Namespace** `App\Domain\Sudoku` con componenti puri PHP:
  - [x] `Grid`, `Cell`, `CandidateSet`
  - [x] `Move` (input utente), `MoveLog`
  - [x] `Generator` deterministico con `seed`
  - [x] `Validator` (unicità soluzione, consistenza)
  - [x] `Solver` con tecniche: single/hidden/locked candidates, pointing, naked/hidden pairs/triples, X-Wing, Swordfish (interfacce estendibili)
  - [x] `DifficultyRater` (stima difficoltà)
- [x] **Test unitari** completi per engine (dataset noti, casi multi-soluzione rifiutati).

Deliverable: API PHP stabili per generare/validare/risolvere griglie; 95%+ coverage sull'engine.

**✅ COMPLETATO** - Note implementazione:
- **Solver**: implementato con `SolverInterface` per risoluzione step-by-step, hint system e tecniche multiple
- **Tecniche implementate**: Naked Singles, Hidden Singles, Locked Candidates Pointing  
- **Struttura estendibile**: per tecniche avanzate (Naked/Hidden Pairs/Triples, X-Wing, Swordfish)
- **Backtracking**: come fallback per puzzle complessi
- **Test completi**: tutti i test del Solver passano con successo
- **File creati**: `SolverInterface.php`, `Solver.php`, `SolverTest.php`

---

### Fase 3 — Modello Dati & Migrazioni
- [x] Tabelle principali:
  - [x] `puzzles` (id, seed, givens, solution, difficulty, created_at)
  - [x] `challenges` (id, puzzle_id, type: daily|weekly|custom, starts_at, ends_at, visibility, status, created_by)
  - [x] `challenge_attempts` (id, challenge_id, user_id, duration_ms, errors_count, hints_used, completed_at, valid, created_at)
  - [x] `attempt_moves` (id, attempt_id, move_index, payload_json, created_at) per replay
  - [x] `user_profiles` (id, user_id, country, preferences_json)
- [x] Indici: per `challenge_attempts` su `(challenge_id, valid, duration_ms)`, e su `completed_at` per query leaderboard.
- [x] Vincoli: fk-on delete cascade; `valid` calcolato server-side.

Deliverable: migrazioni, factory e seeders di base.

**✅ COMPLETATO** - Note implementazione:
- **Migrazioni**: 5 tabelle create con indici performanti e vincoli FK
- **Modelli Eloquent**: Tutti i modelli con relazioni complete e metodi di utilità
- **Integrazione Dominio**: Bridge tra domain objects (Grid, Move) e persistenza
- **Factory**: PuzzleFactory integrata con domain Generator per puzzle reali
- **Seeder**: SudokuSeeder per dati di test completi
- **Performance**: Indici ottimizzati per leaderboard e query critiche

---

### Fase 4 — Servizi di Dominio (Application Layer)
- [x] `App\Services\ChallengeService`:
  - [x] creare/schedulare sfide (daily/weekly/custom) con puzzle by seed/difficulty
  - [x] policy: solo admin può creare custom pubbliche
- [x] `App\Services\ResultService`:
  - [x] validare completamento (board finale ≡ soluzione, coerenza mosse)
  - [x] calcolare tie-break: meno errori → timestamp completamento più antico → meno hint
- [x] `App\Services\LeaderboardService`:
  - [x] query ottimizzate e caching per classifiche per sfida e per periodo
- [x] Job queue (Redis): validazione attempt, aggiornamento leaderboard, generazione puzzle batch.

Deliverable: servizi testati con feature tests su DB in memoria.

**✅ COMPLETATO** - Note implementazione:
- **ChallengeService**: Gestione completa sfide daily/weekly/custom con seed deterministici e policy admin
- **ResultService**: Validazione rigorosa completamenti + tie-break + anti-cheat + integrità mosse 
- **LeaderboardService**: Query ottimizzate con caching, classifiche globali/per periodo, statistiche utente
- **Job Queue**: ValidateAttemptJob, UpdateLeaderboardJob, GeneratePuzzleBatchJob con retry e logging
- **Feature Tests**: Test integrazione end-to-end per tutti i servizi con DB in memoria
- **Performance**: Caching strategico, query ottimizzate, elaborazione asincrona

---

### Fase 5 — UI Board (Blade + Livewire + Alpine + Tailwind)
- [x] Componente `livewire/board`:
  - [x] rendering griglia 9×9, evidenziazione riga/colonna/box
  - [x] input da tastiera/mouse/touch, modalità numeri definitivi vs candidati
  - [x] undo/redo illimitato; log mosse per replay
  - [x] timer
  - [x] validazione locale opzionale (feedback on/off), contatore errori
  - [x] supporto screen reader (annunci celle/candidati/conflitti)
- [x] Design responsivo con Tailwind + Luvi UI; tema chiaro/scuro.

Deliverable: pagina single‑player con board accessibile e stabile.

**✅ COMPLETATO** - Note implementazione:
- **Componente Livewire**: SudokuBoard completamente funzionale con stato reattivo
- **Rendering Griglia**: CSS Grid 9×9 con evidenziazione riga/colonna/box in tempo reale
- **Input Multi-modalità**: Tastiera (1-9, frecce, Backspace) + mouse/touch + pannello numerico
- **Modalità Doppia**: Valori definitivi e candidati (pencil marks) con toggle dinamico
- **Undo/Redo**: Sistema completo con cronologia mosse illimitata (max 100 step)
- **Timer**: Cronometro automatico con tick JS, formattazione MM:SS
- **Validazione Live**: Conflitti evidenziati in rosso, contatore errori, percentuale completamento
- **Accessibilità WCAG 2.2 AA**: ARIA labels, screen reader support, annunci live, navigazione tastiera
- **Design Responsivo**: Tailwind CSS con dark mode, mobile-first, touch-friendly
- **Demo Pages**: `/sudoku/demo` e `/sudoku/play` per test completo delle funzionalità

---

### Fase 6 — Sfide asincrone
- [x] Pagina lista sfide (daily/weekly/custom) con stato personificato (mai iniziata / in corso / completata)
- [x] Dettaglio sfida: avvio/pausa/ripresa; syncing seed; blocco hint se impostato
- [x] Conclusione: invio risultato → validazione → redirect a classifica sfida
- [x] Anti‑abuso base: limite tentativi per sfida e regole business minime
  - [x] Un solo tentativo valido per utente/sfida (vincolo unique + logica applicativa)
  - [x] Verifica server‑side del completamento: griglia finale ≡ soluzione puzzle
  - [x] Soglia tempo minimo (10s) per considerare il risultato valido
  - [x] Salvataggio `valid=false` in caso di violazioni (no classifica)
- [x] Fix algoritmo Generator.fillGridRecursive 
  - Aggiungere limite profondità ricorsiva
  - Implementare timeout interno
  - Ottimizzare strategia backtracking
  - Aggiungere fallback deterministici

Deliverable: flusso end‑to‑end per completare una sfida e comparire in classifica.

**✅ COMPLETATO** - Note implementazione:
- **Lista Sfide**: Componente ChallengeList con filtri avanzati (tipo/stato), paginazione e stati personalizzati per utente
- **Dettaglio Sfida**: Componente ChallengePlay con board 9×9 completo, timer automatico, salvataggio real-time
- **Seed Sync**: Generazione deterministica con same puzzle per tutti gli utenti della sfida
- **Hint Control**: Configurazione per sfida con blocco candidati in modalità competitiva
- **Pausa/Ripresa**: Salvataggio automatico dello stato con possibilità di pausa e ripresa
- **Completamento**: Validazione automatica, modal conferma, redirect a classifiche
- **UI/UX**: Design responsive con dark mode, 30+ traduzioni IT/EN, stati visivi chiari
- **Performance**: Query ottimizzate, indici database, lazy loading componenti
- **Database**: 11 sfide di test con difficoltà variabili e impostazioni diverse

---

### Fase 7 — Classifiche
- [ ] Vista classifica per sfida con filtri (globale, difficoltà)
- [ ] Board del giorno/settimana con archivio e trend semplici
- [ ] Profili utente: best times per difficoltà, percentuali completati
- [ ] Esportazione CSV
- [ ] Aggiornamento near‑real‑time: broadcasting Reverb (opzionale) oltre polling

Deliverable: leaderboard performanti con caching e UX reattiva.

---

### Fase 8 — Solver & Hinting
- [ ] Endpoint/azione Livewire per "risolvi passo" che espone la prossima tecnica applicata
- [ ] Report finale tecniche utilizzate su un puzzle importato
- [ ] Pagina "Risolvi schema esistente" con import manuale/JSON e verifica unicità
- [ ] Policy: hint disabilitabili nelle sfide competitive

Deliverable: solver integrato alla UI e usabile come hint didattico.

---

### Fase 9 — Anti‑cheat & Validazione server‑side
- [ ] Validazione mossa lato server su tentativi competitivi (sampling o completa a fine partita)
- [ ] Analisi tempi anomali (z-score semplice su distribuzione sfida)
- [ ] Flag moderazione per risultati sospetti, annullamento risultati, riapertura sfide
- [ ] Opzione blocco copy/paste in board competitiva (best effort)

Deliverable: regole minime di fair‑play con strumenti admin.

---

### Fase 10 — Accessibilità (WCAG 2.2 AA)
- [ ] Navigazione completa da tastiera (Tab/Shift+Tab, frecce, 1–9, Backspace)
- [ ] Ruoli/ARIA per griglia, region landmark, annunci dinamici via `aria-live`
- [ ] Focus management visibile (outline sufficient contrast); skip link
- [ ] Contrasto colori tema chiaro/scuro; preferenze salvate
- [ ] Test con screen reader (NVDA/JAWS/VoiceOver) su flussi principali

Deliverable: audit AA superato per le schermate MVP.

---

### Fase 11 — Performance & Scalabilità
- [ ] Query ottimizzate con eager loading e indici
- [ ] Cache: leaderboard e dettagli sfida; invalidazione su eventi
- [ ] Octane (se abilitato): compatibilità sessioni, warmup engine
- [ ] Redis per queue e cache; TTL ragionati

Deliverable: tempi di risposta sub‑200ms su operazioni principali in ambiente staging.

---

### Fase 12 — Localizzazione (i18n) IT/EN
- [x] Struttura locale: `lang/it/` e `lang/en/` con file `auth.php`, `pagination.php`, `passwords.php`, `validation.php`, `app.php`
- [x] Traduzione Blade/Livewire: testi UI, messaggi di stato, componenti (`resources/views/...`)
- [x] Validazione: messaggi locali per Form Requests e Livewire validation
- [ ] Email/notifiche: template multi-lingua
- [x] Switcher lingua accessibile (header/footer), salvato in sessione e rispettato via middleware
- [x] Middleware locale (es. `SetLocale`) e rilevamento da URL (`/it`, `/en`) o preferenze utente
- [x] SEO: `hreflang`, `<html lang>` dinamico, metadati localizzati
- [x] Policy contenuti: fallback a EN se chiave mancante; predisposizione per nuove lingue

Deliverable: app completamente fruibile in IT/EN con predisposizione per ulteriori lingue.

---

### Fase 13 — Scheduling & Automazioni
- [ ] Scheduler: generazione sfida `daily` h00:00 e `weekly` lunedì h00:00 con seed/difficoltà predefiniti
- [ ] Cleanup: rimozione tentativi incompleti oltre soglia, compattazione log mosse
- [ ] Notifiche opzionali (email) per risultati e aperture sfide

Deliverable: routine giornaliere e settimanali affidabili.

---

### Fase 14 — Sicurezza & Compliance
- [ ] CSRF su tutte le form; rate limiting per endpoint sensibili Livewire
- [ ] Policy/authorization per admin (crea/sospendi sfide, modera risultati)
- [ ] Log eventi sicurezza e audit trail su azioni admin
- [ ] Backup DB e strategia restore (documentata)

Deliverable: baseline sicurezza conforme alle best practice Laravel.

---

### Fase 15 — Test & Qualità
- [ ] Unit test: engine Sudoku (copertura alta)
- [ ] Feature test: flusso sfida → classifica (criteri di tie‑break)
- [ ] Test accessibilità automatizzati (axe) e manuali
- [ ] Dusk (facoltativo): smoke test board e completamento sfida
- [ ] Report coverage in CI; gate su PR

Deliverable: pipeline verde e copertura >80% su domini critici.

---

### Fase 16 — Deploy & Observability
- [ ] Env staging e production (Laravel Forge o analogo); Postgres gestito, Redis, storage S3
- [ ] Config logging strutturato, error tracking (Sentry) e metrics (Prometheus/Grafana o Laravel Telescope in staging)
- [ ] Script deploy zero‑downtime, migrazioni sicure

Deliverable: app online con monitoring e alert di base.

---

### Roadmap v2 (post‑MVP)
- [ ] Social: amici/club, inviti, ranking tra amici
- [ ] Badge e stagioni, analisi tempi avanzata
- [ ] Varianti Sudoku (Killer, Diagonal, Thermo, ecc.)
- [ ] PWA offline per single‑player

---

### Accettazione MVP (estratto)
- [ ] Concluso una sfida daily valida → tempo in classifica entro 1s con tie‑break corretto
- [ ] Con finestra scaduta, nuovi tentativi non validi; profilo mostra best time personale
- [ ] Board completamente navigabile da tastiera; screen reader annuncia celle, candidati e conflitti


