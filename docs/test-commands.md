# 🚀 Quick Test Commands - Sudoku Solver AI Public

Comandi rapidi per testare il sistema **Sudoku Solver AI Public** da terminale.

---

## 📋 Setup Veloce

```bash
# 1. Queue worker per job asincroni
php artisan queue:work --queue=solver

# 2. Verifica routes esistano
php artisan route:list | grep public-solver

# 3. Crea utente admin per test (se serve)
php artisan tinker --execute="\\App\\Models\\User::create(['name' => 'Test Admin', 'email' => 'test@admin.com', 'role' => 'admin', 'password' => bcrypt('test123'), 'email_verified_at' => now()])"
```

---

## 🧪 Test API con cURL

### Test Risoluzione Immediata:
```bash
curl -X POST http://localhost/api/public-solver/solve \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token()')" \
  -d '{
    "grid": [
      [8,0,0,0,0,0,0,0,0],
      [0,0,3,6,0,0,0,0,0],
      [0,7,0,0,9,0,2,0,0],
      [0,5,0,0,0,7,0,0,0],
      [0,0,0,0,4,5,7,0,0],
      [0,0,0,1,0,0,0,3,0],
      [0,0,1,0,0,0,0,6,8],
      [0,0,8,5,0,0,0,1,0],
      [0,9,0,0,0,0,4,0,0]
    ],
    "step_by_step": true
  }'
```

### Test Submit per Link Permanente:
```bash
curl -X POST http://localhost/api/public-solver/submit \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token()')" \
  -d '{
    "grid": [
      [8,0,0,0,0,0,0,0,0],
      [0,0,3,6,0,0,0,0,0],
      [0,7,0,0,9,0,2,0,0],
      [0,5,0,0,0,7,0,0,0],
      [0,0,0,0,4,5,7,0,0],
      [0,0,0,1,0,0,0,3,0],
      [0,0,1,0,0,0,0,6,8],
      [0,0,8,5,0,0,0,1,0],
      [0,9,0,0,0,0,0,4,0]
    ]
  }'
```

### Test Statistiche:
```bash
curl http://localhost/api/public-solver/stats | jq
```

---

## 📊 Database Quick Check

```bash
# Vedi puzzle processati
php artisan tinker --execute="echo \\App\\Models\\PublicPuzzle::count() . ' puzzle nel database'"

# Vedi puzzle per status
php artisan tinker --execute="\\App\\Models\\PublicPuzzle::selectRaw('status, count(*) as count')->groupBy('status')->get()->each(fn(\$r) => print \$r->status . ': ' . \$r->count . \"\n\");"

# Vedi puzzle più visualizzati
php artisan tinker --execute="\\App\\Models\\PublicPuzzle::orderByDesc('view_count')->limit(5)->get(['hash', 'view_count', 'difficulty'])->each(fn(\$p) => print \$p->hash . ' - ' . \$p->view_count . ' views - ' . \$p->difficulty . \"\n\");"
```

---

## 🔧 Cache & Performance

```bash
# Vedi cache stats
php artisan tinker --execute="echo 'Cache stats: ' . Cache::get('public_solver_stats') ? 'cached' : 'empty'"

# Invalida cache
php artisan cache:forget public_solver_stats
php artisan cache:forget public_solver_popular

# Vedi job queue
php artisan queue:monitor
php artisan queue:failed
```

---

## 🌐 URLs di Test

- **Landing Page IT**: http://localhost/it/solve
- **Landing Page EN**: http://localhost/en/solve  
- **Landing Page DE**: http://localhost/de/solve
- **Landing Page ES**: http://localhost/es/solve

---

## 🎯 Test Rate Limiting

```bash
# Test multiple submit (dovrebbe bloccare dopo 10)
for i in {1..12}; do
  echo "Request $i:"
  curl -X POST http://localhost/api/public-solver/submit \
    -H "Content-Type: application/json" \
    -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token()')" \
    -d '{"grid":[[8,0,0,0,0,0,0,0,0],[0,0,3,6,0,0,0,0,0],[0,7,0,0,9,0,2,0,0],[0,5,0,0,0,7,0,0,0],[0,0,0,0,4,5,7,0,0],[0,0,0,1,0,0,0,3,0],[0,0,1,0,0,0,0,6,8],[0,0,8,5,0,0,0,1,0],[0,9,0,0,0,0,4,0,0]]}' \
    -w "Status: %{http_code}\n\n"
  sleep 1
done
```

---

## 🐛 Debug Mode

```bash
# Abilita debug dettagliato
echo "APP_DEBUG=true" >> .env
echo "LOG_LEVEL=debug" >> .env

# Monitora log in tempo reale
tail -f storage/logs/laravel.log | grep -E "(ProcessPublicPuzzleJob|public-solver)"

# Test specifico job
php artisan tinker --execute="
\$puzzle = \\App\\Models\\PublicPuzzle::factory()->create([
    'grid_data' => [[8,0,0,0,0,0,0,0,0],[0,0,3,6,0,0,0,0,0],[0,7,0,0,9,0,2,0,0],[0,5,0,0,0,7,0,0,0],[0,0,0,0,4,5,7,0,0],[0,0,0,1,0,0,0,3,0],[0,0,1,0,0,0,0,6,8],[0,0,8,5,0,0,0,1,0],[0,9,0,0,0,0,4,0,0]]
]);
\\App\\Jobs\\ProcessPublicPuzzleJob::dispatch(\$puzzle);
echo 'Job dispatched for puzzle: ' . \$puzzle->id;
"
```

---

## ✅ Success Indicators

**✅ Tutto funziona se:**
- Route list mostra 6 route `public-solver`
- Menu mostra link "🤖 Solver AI" / "🤖 KI-Löser" etc.
- Landing page carica senza errori
- API solve ritorna JSON con soluzione
- Submit genera URL permanente
- Job queue processa puzzle
- Database `public_puzzles` si popola
- Cache si invalida automaticamente

**❌ Problemi comuni:**
- Route non trovate → `php artisan route:clear`  
- View non trovate → Controlla file in `resources/views/public-solver/`
- JS errors → Verifica CSRF token e console browser
- Job falliti → `php artisan queue:failed` per dettagli
- Cache stale → `php artisan cache:clear`

