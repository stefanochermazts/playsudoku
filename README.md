<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo"></p>

## PlaySudoku — Piattaforma Sudoku Competitiva (TALL)

PlaySudoku è un’app web basata su stack TALL (Tailwind CSS, Alpine.js, Laravel 12, Livewire) per giocare Sudoku 9×9 e partecipare a sfide asincrone sulla stessa griglia con classifiche a tempo, replay e solver logico integrato.

### Funzionalità principali
- Sudoku 9×9 classico con candidati (pencil marks), evidenziazione riga/colonna/box, undo/redo e timer
- Sfide asincrone (daily/weekly/custom) con seed condiviso e ranking a miglior tempo (tie‑break: errori, timestamp, hint)
- Classifiche per sfida, board del giorno/settimana, profili con best times e progressione
- Solver logico con tecniche progressive e hint passo‑passo; import schema esistente e verifica unicità
- Replay personale delle mosse
- Anti‑cheat: validazione server‑side del risultato e analisi tempi anomali
- Accessibilità WCAG 2.2 AA, tema chiaro/scuro

### Architettura logica
- Frontend: Blade + Livewire; Alpine.js per micro‑interazioni; Tailwind per UI
- Backend: Laravel 12; servizi applicativi (`ChallengeService`, `ResultService`, `LeaderboardService`)
- Dominio gioco: `App\Domain\Sudoku` (grid, mosse, candidati, generator, validator, solver)
- Storage: PostgreSQL (primario), Redis (cache/queue), S3 per asset
- Realtime opzionale: broadcasting per aggiornare leaderboard (polling fallback)

Per i dettagli vedi: `docs/analisi-funzionale.md`.

---

## Avvio rapido (dev)
Prerequisiti: PHP 8.3, Composer 2, Node 20, PostgreSQL/SQLite, Redis (opzionale in dev).

```bash
cp .env.example .env
php artisan key:generate
composer install
npm install
npm run dev
php artisan migrate
php artisan serve
```

Login/registrazione sono fornite via Breeze (Livewire). Gli asset sono gestiti da Vite.

---

## Test & Qualità
- Linter: Laravel Pint (`vendor/bin/pint --test`)
- Test: `php artisan test`
- CI: GitHub Actions (lint + test su SQLite)

---

## Documentazione
- Backlog: `docs/todo.md`
- Deploy produzione: `docs/deploy.md`
- Analisi funzionale: `docs/analisi-funzionale.md`

---

## Licenza
MIT
