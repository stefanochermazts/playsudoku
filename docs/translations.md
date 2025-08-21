# 📝 Sistema di Traduzioni PlaySudoku

Documentazione completa per la gestione delle traduzioni automatiche degli articoli tramite OpenAI.

## 🚀 Avvio Rapido

### Lanciare la Coda per le Traduzioni

```bash
# Comando principale per avviare il worker delle traduzioni
php artisan queue:work --queue=translations --sleep=3 --tries=3 --timeout=900

# Per ambiente di sviluppo con debug
php artisan queue:work --queue=translations --sleep=1 --tries=3 --timeout=900 --verbose

# Per produzione con tutte le code
php artisan queue:work --sleep=3 --tries=3 --timeout=900
```

### Comandi Alternativi

```bash
# Processare solo un job alla volta (utile per test)
php artisan queue:work --queue=translations --once

# Con timeout esteso per articoli lunghi
php artisan queue:work --queue=translations --timeout=1200 --memory=512

# Restart automatico dei worker dopo ogni job (sicurezza memoria)
php artisan queue:work --queue=translations --max-jobs=1 --sleep=2
```

---

## 📊 Panoramica Sistema

### Lingue Supportate
- **Sorgente**: Italiano (IT) 
- **Target**: 
  - 🇺🇸 Inglese (EN)
  - 🇩🇪 Tedesco (DE) 
  - 🇪🇸 Spagnolo (ES)

### Flusso di Traduzione
1. **Creazione**: Articolo scritto in italiano
2. **Trigger**: Attivazione automatica al publish o manuale
3. **Queue**: Job inviato alla coda `translations`
4. **OpenAI**: Traduzione tramite GPT-4
5. **Storage**: Salvataggio delle traduzioni nel database

---

## 🔧 Configurazione

### Prerequisiti

1. **OpenAI API Key configurata**:
   ```bash
   # In .env
   OPENAI_API_KEY=sk-your-key-here
   ```

2. **Redis per le code**:
   ```bash
   # In .env
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

3. **Verifica configurazione**:
   ```bash
   php artisan tinker
   >>> app(\App\Services\OpenAITranslationService::class)->isConfigured()
   ```

---

## 📋 Comandi Gestione Code

### Controllo Stato

```bash
# Lista job in coda
php artisan queue:monitor

# Job falliti
php artisan queue:failed

# Statistiche dettagliate
php artisan queue:monitor translations --verbose

# Cancellare tutti i job in coda
php artisan queue:clear

# Riavviare tutti i worker
php artisan queue:restart
```

### Gestione Job Falliti

```bash
# Lista job falliti con dettagli
php artisan queue:failed

# Riprovare un job specifico
php artisan queue:retry {id}

# Riprovare tutti i job falliti
php artisan queue:retry all

# Rimuovere job falliti
php artisan queue:forget {id}
php artisan queue:flush  # tutti
```

---

## 🎯 Trigger Traduzioni

### 1. Automatico (al publish)

Quando crei un articolo con "Auto-traduzione" attiva:
```php
// Nel form di creazione articolo
'auto_translate' => true,
'status' => 'published'
```

### 2. Manuale tramite Admin

1. Vai su **Admin → Articoli**
2. Clicca su un articolo esistente 
3. Premi il bottone **"🌐 Traduci"**

### 3. Programmatico

```php
use App\Jobs\TranslateArticleJob;

// Traduzione singola
TranslateArticleJob::dispatch($article);

// Traduzione di tutti gli articoli pubblicati senza traduzioni
Article::published()
    ->whereDoesntHave('translations', function($q) {
        $q->whereIn('locale', ['en', 'de', 'es']);
    })
    ->each(function($article) {
        TranslateArticleJob::dispatch($article);
    });
```

---

## ⚙️ Configurazione Avanzata

### Parametri del Job

Il `TranslateArticleJob` ha le seguenti configurazioni:

```php
public int $tries = 3;              // Tentativi massimi
public int $timeout = 600;          // Timeout: 10 minuti
public int $backoff = 60;           // Attesa tra retry: 1 minuto
```

### Backoff Strategy
```php
public function backoff(): array
{
    return [60, 180, 300]; // 1min, 3min, 5min
}
```

### Timeout Retry
```php
public function retryUntil(): DateTime
{
    return now()->addHours(2); // Stop retry dopo 2 ore
}
```

---

## 🐛 Debugging e Monitoring

### Log delle Traduzioni

```bash
# Monitorare log in tempo reale
tail -f storage/logs/laravel.log | grep -i "translation"

# Grep specifico per errori
tail -f storage/logs/laravel.log | grep -E "(Translation|OpenAI|TranslateArticle)"
```

### Job Tags per Ricerca

```bash
# In Laravel Tinker
php artisan tinker

# Trova job per articolo specifico
>>> \Illuminate\Support\Facades\Queue::size('translations')
>>> // Lista job con tag specifici
```

### Controllo Stato Articolo

```php
// In tinker o controller
$article = Article::find(1);

// Completezza traduzioni (%)
$completeness = $article->getTranslationCompleteness();

// Status traduzioni per lingua
$status = $article->translation_status;
// ['en' => 'completed', 'de' => 'pending', 'es' => 'failed']

// Traduzioni esistenti
$translations = $article->translations; 
```

---

## 🚨 Troubleshooting

### Problemi Comuni

#### 1. Job che falliscono costantemente
```bash
# Controlla configurazione OpenAI
php artisan tinker
>>> config('openai.api_key')
>>> app(\App\Services\OpenAITranslationService::class)->isConfigured()
```

#### 2. Timeout delle traduzioni
```bash
# Aumenta timeout per articoli lunghi
php artisan queue:work --queue=translations --timeout=1800  # 30 min
```

#### 3. Memoria insufficiente
```bash
# Aumenta limite memoria
php artisan queue:work --queue=translations --memory=1024
```

#### 4. Redis disconnessione
```bash
# Riavvia Redis
sudo systemctl restart redis-server

# Verifica connessione
redis-cli ping
```

### Log di Debug

Per debugging avanzato, aggiungi nel file `.env`:
```bash
LOG_LEVEL=debug
LOG_CHANNEL=daily
```

---

## 🏭 Produzione - Supervisor

### Configurazione Supervisor

Crea `/etc/supervisor/conf.d/playsudoku-translations.conf`:

```ini
[program:playsudoku-translations]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/playsudoku/artisan queue:work --queue=translations --sleep=3 --tries=3 --timeout=900 --memory=512
directory=/var/www/playsudoku
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/playsudoku/storage/logs/translations-worker.log
stopwaitsecs=3600
```

### Comandi Supervisor

```bash
# Ricarica configurazione
sudo supervisorctl reread
sudo supervisorctl update

# Controlla stato
sudo supervisorctl status playsudoku-translations

# Riavvia worker
sudo supervisorctl restart playsudoku-translations:*

# Stop/start
sudo supervisorctl stop playsudoku-translations:*
sudo supervisorctl start playsudoku-translations:*
```

---

## 📊 Statistiche e Monitoring

### Metriche Importanti

```bash
# Numero job in coda
php artisan queue:monitor translations

# Job processati oggi
grep "Translation completed" storage/logs/laravel-$(date +%Y-%m-%d).log | wc -l

# Job falliti oggi  
grep "Translation failed" storage/logs/laravel-$(date +%Y-%m-%d).log | wc -l

# Tempo medio traduzione
grep "Translation completed" storage/logs/laravel.log | tail -50
```

### Dashboard Admin

Accedi a **Admin → Dashboard** per vedere:
- ✅ Statistiche traduzioni in tempo reale
- 📊 Completezza per lingua
- 🔥 Job falliti recenti
- ⏱️ Tempi di elaborazione

---

## 🔗 Comandi Utili Correlati

```bash
# Ottimizzazione dopo deploy
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reset cache translations
php artisan cache:forget translation_*

# Cleanup job vecchi
php artisan queue:prune-failed --hours=48

# Statistiche Redis
redis-cli info memory
redis-cli info clients
```

---

## 📝 Note Tecniche

### Qualità Traduzioni
- **Score qualità**: Calcolato automaticamente (0-100)
- **Revisione**: Traduzioni auto-marchiate come "auto_translated"
- **Editing**: Possibile modifica manuale post-traduzione

### Limitazioni OpenAI
- **Rate limiting**: 3 richieste/minuto per account gratuito
- **Token limit**: 4000 token per richiesta
- **Costo**: ~$0.03 per 1K token (GPT-4)

### Sicurezza
- **API Key**: Mai esporre in log o frontend
- **Timeout**: Sempre configurare per evitare job infiniti
- **Retry**: Limitare retry per evitare loop infiniti

---

## 🆘 Supporto

Per problemi con le traduzioni:

1. **Controlla log**: `storage/logs/laravel.log`
2. **Verifica code**: `php artisan queue:monitor`
3. **Testa OpenAI**: Via admin o tinker
4. **Riavvia worker**: `php artisan queue:restart`

**In caso di emergenza**: Disabilita auto-traduzione nel database e processa manualmente.
