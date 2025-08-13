# Analisi Funzionale — Piattaforma Sudoku Competitiva (v1)

> Obiettivo: progettare e sviluppare da zero un’app web di Sudoku con **sfide asincrone sulla stessa tavola**, **classifiche a tempo**, **candidati (pencil marks)**, **replay**, **solver** e funzioni social/moderazione, basata su stack **TALL** (Tailwind CSS, Alpine.js, Laravel 12, Livewire), sfruttando l’esperienza già maturata.

---

## 1) Scope & Obiettivi

* **Scope iniziale (MVP)**: Sudoku 9×9 classico, livelli da easy a crazy(5 livelli), single‑player cronometrato, sfide asincrone con più giocatori sulla stessa griglia (seed condiviso), classifica a tempo, salvataggio progressi, replay base, **solver integrato**.

  * **Solver (single‑player)**: risolutore logico con spiegazioni passo‑passo (tecniche: single/hidden/locked candidates, pointing, naked/hidden pairs/triples, X‑Wing, Swordfish, ecc.), utilizzabile come hint avanzato o auto‑risoluzione didattica.
  * **Risolvi schema esistente**: pagina per inserire/incollare una griglia (o import JSON/CSV), avviare il solver, report tecniche e verifica unicità soluzione.
* **Modalità competitiva semplificata**: ogni utente può completare la sfida in qualsiasi momento entro la finestra disponibile; il ranking è basato sul **tempo di completamento**.
* **Estensioni v2**: amici/club, badge, stagioni, analisi tempi avanzata, varianti di Sudoku, PWA offline per single‑player.
* **KPI**: partecipazione alle sfide, tempi medi di completamento, retention settimanale, percentuale puzzle completati.

---

## 2) Regole ufficiali del Sudoku

1. La griglia è composta da 9 righe × 9 colonne, suddivisa in 9 riquadri (box) 3×3.
2. Ogni riga deve contenere tutti i numeri da 1 a 9 senza ripetizioni.
3. Ogni colonna deve contenere tutti i numeri da 1 a 9 senza ripetizioni.
4. Ogni riquadro 3×3 deve contenere tutti i numeri da 1 a 9 senza ripetizioni.
5. Gli indizi (numeri iniziali) forniti nel puzzle non possono essere modificati.
6. Il puzzle ha sempre un’unica soluzione valida.
7. Le mosse devono rispettare contemporaneamente le regole di riga, colonna e riquadro.
8. Non è consentito inserire simboli, lettere o valori diversi da 1–9.
9. In modalità competitiva, errori o violazioni delle regole possono comportare penalità secondo regolamento.
10. In modalità candidati, i segni provvisori non influenzano la validità finale ma devono essere coerenti con le regole.

---

## 3) Personas

1. **Casual**: gioca da ospite, cerca aiuti e progressione.
2. **Competitivo**: partecipa a sfide asincrone e punta a scalare la classifica.
3. **Organizer/Admin**: crea e pianifica sfide, modera community e contenuti.
4. **Spectator**: guarda replay e risultati.

---

## 4) Use case principali

* **UC01**: Avvio rapido single‑player (ospite/loggato), scelta difficoltà, timer, candidati, undo/redo.
* **UC02**: Partecipazione a sfida condivisa (daily/weekly/custom) con seed fisso; completamento in qualsiasi momento.
* **UC03**: Creazione sfida (admin) con difficoltà, durata e visibilità.
* **UC04**: Visualizzazione classifica con filtri (globale, periodo, amici).
* **UC05**: Replay personale e confronto con best time.

---

## 5) Funzionalità di gioco (Board)

* Input via mouse/touch/tastiera, evidenziazione riga/colonna/box.
* Modalità inserimento: definitivo / candidati.
* Validazione: feedback on/off, contatore errori.
* Undo/Redo illimitato; log mosse per replay.
* Timer individuale.
* Hints configurabili (off nelle sfide competitive).

---

## 6) Generazione, Difficoltà e Verifica puzzle

* Generazione deterministica con seed → soluzione unica.
* Parametri difficoltà: numero indizi, distribuzione, tecniche richieste.
* Validator: risolutore logico + backtracking; scarto puzzle con multi‑soluzione.

---

## 7) Sfide asincrone & board condivise

* **Seed condiviso**: per ogni sfida è definito un `seed` deterministico; tutti i partecipanti giocano la **stessa griglia**.
* **Finestre temporali**: **daily** (24h) e **weekly** (lun→dom); possibilità di **sfide custom** con scadenza definita dall’organizer.
* **Partecipazione**: si può **iniziare/pausare/riprendere** e **concludere** entro la finestra; registriamo il **miglior tempo** per utente.
* **Inviti**: link/ID sfida condivisibile per amici/colleghi.
* **Tie‑break**: (1) **meno errori**; (2) **timestamp** di completamento più **antico**; (3) **meno hint** usati.
* **Aggiornamenti**: la classifica si aggiorna quando un giocatore termina; opzionale push via **Broadcasting** (Reverb) oltre al polling.
* **Anti‑abuso**: limiti tentativi per sfida, **validazione server‑side** del risultato, analisi **tempi anomali**, blocco opzionale del copy/paste.
* **Amministrazione**: scheduling seed/difficoltà, annullamento risultati anomali, ri‑apertura sfide.

---

## 8) Classifiche

* **Per sfida**: classifica a **miglior tempo** con filtri (globale, periodo, difficoltà, paese, amici).
* **Board del giorno/settimana**: pagine dedicate con **archivio** e **trend**.
* **Profili**: best times per difficoltà, percentuale completati, progressione personale.
* **Esportazione**: CSV/screenshot.
* **Stagioni leggere**: aggregati mensili/stagionali con badge.

---

## 9) Anti‑cheat & Fair‑Play

- Validazione risultato server‑side (alla conclusione):
  - Griglia finale deve essere identica alla soluzione del puzzle (confronto cella‑per‑cella)
  - Tempo minimo di completamento (default: 10s) per evitare submit istantanei
  - Un solo completamento valido per tentativo (idempotenza)
- Gestione pausa e metriche anti‑abuso per tentativi competitivi:
  - Tracciamo `started_at`, `last_activity_at`, `pause_started_at`, `paused_ms_total`, `pauses_count`
  - Alla pausa incrementiamo `pauses_count` e segniamo `pause_started_at`; al completamento accumuliamo le pause aperte
  - Regola base: invalidazione se `paused_ms_total` > 70% del tempo reale (`now - started_at`)
  - Regola base: invalidazione se `pauses_count` > 5
- Opzioni di sfida:
  - Candidati (Hints) abilitabili/disabilitabili per singola sfida (`settings.hints_allowed`)
- Estensioni (v2):
  - Rate‑limit su pause (finestra/numero), analisi outlier tempi (z‑score) e flag per moderazione
  - Validazione mossa live sampling in modalità competitiva

---

## 10) Requisiti Non Funzionali

* Performance ottimizzata con Laravel Octane.
* Architettura stateless con Redis.
* Accessibilità WCAG 2.2 AA (pattern W3C APG, tema chiaro/scuro).
* Compatibilità: Chrome/Edge/Firefox/Safari, mobile responsive.
* Multilingua: italiano, inglese con predisposizione per l'aggiunta di altre lingue

---

## 11) Architettura logica

* **Stack**: TALL (Tailwind CSS, Alpine.js, Laravel 12, Livewire).
* **Front‑end**: Blade + Livewire (board, sfide, leaderboard, admin scheduler), Alpine.js per micro‑interazioni, Tailwind per UI.
* **Interazione**: HTTP/Livewire; Broadcasting opzionale per aggiornare leaderboard in near‑real‑time.
* **Back‑end**: Laravel 12, Octane opzionale; code/queue Redis per validazione e calcolo classifiche.
* **Game Engine**: PHP puro in `App\\Domain\\Sudoku` (board, mosse, candidati, validator, generator, solver).
* **Servizi**: ChallengeService, ResultService, LeaderboardService.
* **Storage**: PostgreSQL (primario), Redis (cache/queue), S3 per asset.

---

## 12) Criteri di accettazione (estratto)

* Completando una sfida **daily** con soluzione valida → il **tempo** appare in classifica entro **1s** e rispetta le regole di **tie‑break**.
* Con finestra scaduta, nessun nuovo tentativo risulta **valido**; il profilo mostra il **best time** personale.
* Accessibilità: la board è **completamente navigabile da tastiera**; lo screen reader **annuncia** celle, candidati, conflitti.

13) Layout del sito
Stile generale: pulito, ordinato, colorato ma non eccessivo, con palette armoniosa ispirata a tonalità pastello o colori caldi/freschi ben bilanciati.
Impostazione: layout centrato, full width su desktop con margini generosi, responsive su mobile (colonne verticali).
Griglia di gioco: in evidenza, grande e ben leggibile, con bordi spessi per le sezioni 3×3, sfondi leggermente differenti per box alternati, e numeri ad alto contrasto.
Tipografia: font moderno e leggibile (es. Inter, Nunito, Lato), titoli chiari e testi di supporto leggibili anche su schermi piccoli.
Temi: supporto chiaro e scuro con contrasti conformi a WCAG 2.2 AA; colori degli stati (selezione, evidenziazione, errori) distinti ma coerenti.
Decorazioni: elementi grafici leggeri (pattern geometrici o icone sottili) per dare personalità, ma senza animazioni e senza distrarre dal gioco.