### Backlog eseguibile — PlaySudoku (TALL, Laravel 12)

Obiettivo: implementare l’MVP della piattaforma Sudoku competitiva con board 9×9, sfide asincrone su seed condiviso, classifica a tempo, solver logico, replay e requisiti di accessibilità WCAG 2.2 AA.

Nota: le attività sono ordinate per fasi incrementali. Ogni task è pensato per PR piccole, testabili e reversibili.

---

### Fase 0 — Infrastruttura & Setup
- [ ] **Bootstrap progetto**: creare app Laravel 12 con PHP 8.2+, `declare(strict_types=1);`, PSR-12.
- [ ] **Stack TALL**: installare Tailwind, Alpine.js, Livewire, Luvi UI; configurare `vite` e build prod con purge.
- [ ] **Octane (facoltativo per MVP)**: predisporre config per Swoole/RoadRunner.
- [ ] **DB**: PostgreSQL locale; `.env.example` aggiornato; migrare su `uuid` come chiavi primarie dove opportuno.
- [ ] **Code style**: PHP-CS-Fixer/laravel pint; CI con lint/test su GitHub Actions.
- [ ] **Auth**: scaffolding Laravel Breeze (Blade + Livewire), login OAuth (facoltativo v2), policy/guard di base.

Deliverable: progetto avviabile, login registrazione pronte, build CSS/JS ok, CI che esegue lint e test vuoti.

---

### Fase 1 — Dominio Sudoku (Engine)
- [ ] **Namespace** `App\Domain\Sudoku` con componenti puri PHP:
  - [ ] `Grid`, `Cell`, `CandidateSet`
  - [ ] `Move` (input utente), `MoveLog`
  - [ ] `Generator` deterministico con `seed`
  - [ ] `Validator` (unicità soluzione, consistenza)
  - [ ] `Solver` con tecniche: single/hidden/locked candidates, pointing, naked/hidden pairs/triples, X-Wing, Swordfish (interfacce estendibili)
  - [ ] `DifficultyRater` (stima difficoltà)
- [ ] **Test unitari** completi per engine (dataset noti, casi multi-soluzione rifiutati).

Deliverable: API PHP stabili per generare/validare/risolvere griglie; 95%+ coverage sull’engine.

---

### Fase 2 — Modello Dati & Migrazioni
- [ ] Tabelle principali:
  - [ ] `puzzles` (id, seed, givens, solution, difficulty, created_at)
  - [ ] `challenges` (id, puzzle_id, type: daily|weekly|custom, starts_at, ends_at, visibility, status, created_by)
  - [ ] `challenge_attempts` (id, challenge_id, user_id, duration_ms, errors_count, hints_used, completed_at, valid, created_at)
  - [ ] `attempt_moves` (id, attempt_id, move_index, payload_json, created_at) per replay
  - [ ] `user_profiles` (id, user_id, country, preferences_json)
- [ ] Indici: per `challenge_attempts` su `(challenge_id, valid, duration_ms)`, e su `completed_at` per query leaderboard.
- [ ] Vincoli: fk-on delete cascade; `valid` calcolato server-side.

Deliverable: migrazioni, factory e seeders di base.

---

### Fase 3 — Servizi di Dominio (Application Layer)
- [ ] `App\Services\ChallengeService`:
  - [ ] creare/schedulare sfide (daily/weekly/custom) con puzzle by seed/difficulty
  - [ ] policy: solo admin può creare custom pubbliche
- [ ] `App\Services\ResultService`:
  - [ ] validare completamento (board finale ≡ soluzione, coerenza mosse)
  - [ ] calcolare tie-break: meno errori → timestamp completamento più antico → meno hint
- [ ] `App\Services\LeaderboardService`:
  - [ ] query ottimizzate e caching per classifiche per sfida e per periodo
- [ ] Job queue (Redis): validazione attempt, aggiornamento leaderboard, generazione puzzle batch.

Deliverable: servizi testati con feature tests su DB in memoria.

---

### Fase 4 — UI Board (Blade + Livewire + Alpine + Tailwind)
- [ ] Componente `livewire/board`:
  - [ ] rendering griglia 9×9, evidenziazione riga/colonna/box
  - [ ] input da tastiera/mouse/touch, modalità numeri definitivi vs candidati
  - [ ] undo/redo illimitato; log mosse per replay
  - [ ] timer
  - [ ] validazione locale opzionale (feedback on/off), contatore errori
  - [ ] supporto screen reader (annunci celle/candidati/conflitti)
- [ ] Design responsivo con Tailwind + Luvi UI; tema chiaro/scuro.

Deliverable: pagina single‑player con board accessibile e stabile.

---

### Fase 5 — Sfide asincrone
- [ ] Pagina lista sfide (daily/weekly/custom) con stato personificato (mai iniziata / in corso / completata)
- [ ] Dettaglio sfida: avvio/pausa/ripresa; syncing seed; blocco hint se impostato
- [ ] Conclusione: invio risultato → validazione → redirect a classifica sfida
- [ ] Anti‑abuso base: limite tentativi per sfida (rate-limit + regole business)

Deliverable: flusso end‑to‑end per completare una sfida e comparire in classifica.

---

### Fase 6 — Classifiche
- [ ] Vista classifica per sfida con filtri (globale, difficoltà)
- [ ] Board del giorno/settimana con archivio e trend semplici
- [ ] Profili utente: best times per difficoltà, percentuali completati
- [ ] Esportazione CSV
- [ ] Aggiornamento near‑real‑time: broadcasting Reverb (opzionale) oltre polling

Deliverable: leaderboard performanti con caching e UX reattiva.

---

### Fase 7 — Solver & Hinting
- [ ] Endpoint/azione Livewire per "risolvi passo" che espone la prossima tecnica applicata
- [ ] Report finale tecniche utilizzate su un puzzle importato
- [ ] Pagina "Risolvi schema esistente" con import manuale/JSON e verifica unicità
- [ ] Policy: hint disabilitabili nelle sfide competitive

Deliverable: solver integrato alla UI e usabile come hint didattico.

---

### Fase 8 — Anti‑cheat & Validazione server‑side
- [ ] Validazione mossa lato server su tentativi competitivi (sampling o completa a fine partita)
- [ ] Analisi tempi anomali (z-score semplice su distribuzione sfida)
- [ ] Flag moderazione per risultati sospetti, annullamento risultati, riapertura sfide
- [ ] Opzione blocco copy/paste in board competitiva (best effort)

Deliverable: regole minime di fair‑play con strumenti admin.

---

### Fase 9 — Accessibilità (WCAG 2.2 AA)
- [ ] Navigazione completa da tastiera (Tab/Shift+Tab, frecce, 1–9, Backspace)
- [ ] Ruoli/ARIA per griglia, region landmark, annunci dinamici via `aria-live`
- [ ] Focus management visibile (outline sufficient contrast); skip link
- [ ] Contrasto colori tema chiaro/scuro; preferenze salvate
- [ ] Test con screen reader (NVDA/JAWS/VoiceOver) su flussi principali

Deliverable: audit AA superato per le schermate MVP.

---

### Fase 10 — Performance & Scalabilità
- [ ] Query ottimizzate con eager loading e indici
- [ ] Cache: leaderboard e dettagli sfida; invalidazione su eventi
- [ ] Octane (se abilitato): compatibilità sessioni, warmup engine
- [ ] Redis per queue e cache; TTL ragionati

Deliverable: tempi di risposta sub‑200ms su operazioni principali in ambiente staging.

---

### Fase 11 — Scheduling & Automazioni
- [ ] Scheduler: generazione sfida `daily` h00:00 e `weekly` lunedì h00:00 con seed/difficoltà predefiniti
- [ ] Cleanup: rimozione tentativi incompleti oltre soglia, compattazione log mosse
- [ ] Notifiche opzionali (email) per risultati e aperture sfide

Deliverable: routine giornaliere e settimanali affidabili.

---

### Fase 12 — Sicurezza & Compliance
- [ ] CSRF su tutte le form; rate limiting per endpoint sensibili Livewire
- [ ] Policy/authorization per admin (crea/sospendi sfide, modera risultati)
- [ ] Log eventi sicurezza e audit trail su azioni admin
- [ ] Backup DB e strategia restore (documentata)

Deliverable: baseline sicurezza conforme alle best practice Laravel.

---

### Fase 13 — Test & Qualità
- [ ] Unit test: engine Sudoku (copertura alta)
- [ ] Feature test: flusso sfida → classifica (criteri di tie‑break)
- [ ] Test accessibilità automatizzati (axe) e manuali
- [ ] Dusk (facoltativo): smoke test board e completamento sfida
- [ ] Report coverage in CI; gate su PR

Deliverable: pipeline verde e copertura >80% su domini critici.

---

### Fase 14 — Deploy & Observability
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


