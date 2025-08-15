# 📅 Guida Scheduling PlaySudoku

Questa guida documenta il sistema di scheduling automatico per PlaySudoku, che gestisce la generazione delle sfide giornaliere/settimanali, il cleanup del database e le notifiche email.

## 🎯 Overview

Il sistema di scheduling di PlaySudoku si basa su **Laravel Scheduler** e comprende:

- 📅 **Generazione sfide giornaliere** (ogni giorno alle 00:00)
- 📆 **Generazione sfide settimanali** (ogni lunedì alle 00:00)
- 🧹 **Cleanup database** (ogni notte alle 02:00)
- ⚡ **Ottimizzazione performance** (ogni domenica alle 03:00)
- 🔍 **Analisi anomalie** (ogni ora)

## 🚀 Setup Produzione

### 1. Configurazione Cron

Per attivare lo scheduler in produzione, aggiungi questa riga al crontab del server:

```bash
* * * * * cd /path/to/playsudoku && php artisan schedule:run >> /dev/null 2>&1
```

#### Esempio per server Ubuntu/Debian:

```bash
# Apri il crontab
sudo crontab -e

# Aggiungi la riga (sostituisci il path)
* * * * * cd /var/www/playsudoku && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Configurazione Environment

Aggiungi queste variabili al tuo file `.env`:

```env
# Notifiche email per nuove sfide (opzionale)
SUDOKU_NOTIFY_NEW_CHALLENGES=false

# Orari di generazione sfide
SUDOKU_DAILY_TIME=00:00
SUDOKU_WEEKLY_TIME=00:00

# Soglie cleanup (giorni)
SUDOKU_CLEANUP_DAYS=7
SUDOKU_CLEANUP_MOVES_DAYS=30
SUDOKU_CLEANUP_FLAGGED_DAYS=90

# Impostazioni notifiche
SUDOKU_NOTIFICATION_BATCH_SIZE=100
SUDOKU_NOTIFICATION_DELAY_MS=100
```

### 3. Setup Coda per Notifiche

Se abiliti le notifiche email, assicurati che la coda Laravel sia attiva:

```bash
# Avvia worker coda (in produzione usa supervisor)
php artisan queue:work --tries=3 --timeout=60

# Oppure usa supervisor (raccomandato)
sudo apt install supervisor
```

Esempio configurazione Supervisor (`/etc/supervisor/conf.d/playsudoku-worker.conf`):

```ini
[program:playsudoku-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/playsudoku/artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/playsudoku/storage/logs/worker.log
```

## 🔧 Comandi Disponibili

### Generazione Sfide

```bash
# Genera sfida giornaliera (manuale)
php artisan challenge:generate-daily

# Genera sfida settimanale (manuale) 
php artisan challenge:generate-weekly

# Forza generazione anche se esiste già
php artisan challenge:generate-daily --force
php artisan challenge:generate-weekly --force
```

### Cleanup Database

```bash
# Cleanup con impostazioni predefinite (7 giorni)
php artisan challenge:cleanup

# Cleanup personalizzato
php artisan challenge:cleanup --days=14

# Dry-run (mostra cosa verrebbe rimosso)
php artisan challenge:cleanup --dry-run
```

### Notifiche Utenti

```bash
# Notifica utenti per una sfida specifica
php artisan challenge:notify-users 123

# Notifica con tipo specificato  
php artisan challenge:notify-users 123 --type=weekly

# Batch personalizzato
php artisan challenge:notify-users 123 --limit=50
```

### Performance

```bash
# Ottimizzazione completa sistema
php artisan performance:optimize

# Analisi anomalie
php artisan challenge:analyze-anomalies
```

### Diagnostics

```bash
# Stato scheduler
php artisan schedule:status

# Lista comandi schedulati
php artisan schedule:list

# Test manuale scheduler (1 minuto)
php artisan schedule:test
```

## 📊 Schema Scheduling

| Comando | Frequenza | Orario | Descrizione |
|---------|-----------|--------|-------------|
| `challenge:generate-daily` | Giornaliera | 00:00 | Crea sfida giornaliera con difficoltà ciclica |
| `challenge:generate-weekly` | Lunedì | 00:00 | Crea sfida settimanale (Expert/Crazy) |
| `challenge:cleanup` | Giornaliera | 02:00 | Rimuove tentativi incompleti e compatta log |
| `performance:optimize` | Domenica | 03:00 | Ottimizza cache, database e config Laravel |
| `challenge:analyze-anomalies` | Ogni ora | :00 | Analizza tempi sospetti nelle sfide |

## 🎯 Logica Sfide Giornaliere

Le sfide giornaliere seguono uno schema di difficoltà settimanale:

- **Lunedì**: Easy (🟢)
- **Martedì-Mercoledì**: Medium (🔵)  
- **Giovedì-Venerdì**: Hard (🟡)
- **Sabato-Domenica**: Expert (🔴)

### Seed Deterministico

- **Daily**: Formato `YYYYMMDD` (es: `20250115`)
- **Weekly**: Formato `YYYYWW` (es: `202503` per settimana 3 del 2025)

Questo garantisce che le sfide siano sempre le stesse per tutti gli utenti in una data specifica.

## 🧹 Logica Cleanup

Il sistema di cleanup rimuove automaticamente:

1. **Tentativi incompleti** > 7 giorni (configurabile)
2. **Compattazione log mosse** > 30 giorni (mantiene solo prime/ultime 5 mosse)
3. **Tentativi flagged revisionati** > 90 giorni

### Cosa viene conservato:

- ✅ Tutti i tentativi completati
- ✅ Tentativi flagged non ancora revisionati
- ✅ Primi 5 e ultimi 5 mosse per debug
- ✅ Statistiche e tempi di completamento

## 📧 Sistema Notifiche

### Criteri Utenti Attivi

Le notifiche vengono inviate solo a:

- ✅ Utenti con email verificata
- ✅ Registrati da almeno 1 giorno
- ✅ Con almeno 1 tentativo negli ultimi 30 giorni

**Per sfide settimanali** (filtro aggiuntivo):
- ✅ Almeno 1 tentativo completato negli ultimi 7 giorni

### Configurazione Notifiche

```php
// config/sudoku.php
'notifications' => [
    'new_challenges' => env('SUDOKU_NOTIFY_NEW_CHALLENGES', false),
    'batch_size' => 100,
    'rate_limit_delay' => 100, // ms
],
```

### Template Email

Le email utilizzano il template personalizzato PlaySudoku con:
- 🎨 Brand colors e logo
- 🌍 Supporto multilingua (IT/EN)
- 📱 Design responsive
- 🔗 Link diretti alle sfide

## 🔍 Monitoring e Troubleshooting

### Log Files

```bash
# Log scheduler generale
tail -f storage/logs/scheduler.log

# Log Laravel generale  
tail -f storage/logs/laravel.log

# Log worker coda (se attivo)
tail -f storage/logs/worker.log
```

### Verifica Configurazione

```bash
# Controlla se scheduler è configurato
php artisan schedule:list

# Testa scheduler manualmente
php artisan schedule:run

# Verifica configurazione sudoku
php artisan config:show sudoku
```

### Problemi Comuni

#### Scheduler non funziona

1. **Verifica crontab**: `crontab -l`
2. **Controlla path**: Assicurati che il path nel cron sia corretto
3. **Permessi**: `chown -R www-data:www-data storage/`
4. **Log**: Controlla `storage/logs/scheduler.log`

#### Email non inviate

1. **Configura coda**: `php artisan queue:work`
2. **Verifica SMTP**: Controlla `config/mail.php`
3. **Log queue**: `php artisan queue:failed`

#### Sfide duplicate

1. **Usa --force**: `php artisan challenge:generate-daily --force`
2. **Controlla fuso orario**: Verifica `APP_TIMEZONE` in `.env`

## 🔧 Comandi di Manutenzione

```bash
# Riavvia scheduler worker
sudo supervisorctl restart playsudoku-worker:*

# Pulisci cache scheduler  
php artisan schedule:clear-cache

# Riprova job falliti
php artisan queue:retry all

# Statistiche sistema
php artisan challenge:stats
```

## 🎯 Best Practices

1. **Monitoring**: Imposta alert per job falliti
2. **Backup**: Backup database prima di ogni deploy
3. **Testing**: Testa scheduler su staging prima di produzione
4. **Logs**: Monitora regolarmente i log dello scheduler
5. **Performance**: Usa Redis per cache e queue in produzione

## 📈 Metriche Raccomandate

- ⏱️ Tempo esecuzione comandi scheduler
- 📧 Tasso successo notifiche email
- 🗑️ Spazio liberato dal cleanup
- 🎯 Numero sfide generate al giorno/settimana
- 👥 Utenti attivi che ricevono notifiche

---

**Per supporto**: Controlla `storage/logs/` o usa `php artisan schedule:status` per diagnostica rapida.
