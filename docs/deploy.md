### Deploy in produzione — PlaySudoku (Laravel 12, TALL)

Questa guida descrive un setup di produzione su Linux (Ubuntu 22.04/24.04) con Nginx, PostgreSQL, Redis e Laravel Octane (RoadRunner). Su Windows non è consigliato il deploy in produzione.

---

### 1) Prerequisiti server
- Sistema: Ubuntu 22.04/24.04 LTS, utente non-root con sudo
- Pacchetti: git, curl, unzip, ufw, supervisor, nginx (o systemd per servizi)
- PHP 8.3 con estensioni: bcmath, ctype, curl, dom, fileinfo, intl, mbstring, openssl, pdo, pdo_pgsql, tokenizer, xml, zip, sockets
- Composer: v2.x globale
- Node: v20+ (per build asset) e npm/pnpm
- Database: PostgreSQL 14+ (consigliato 15+)
- Cache/Queue: Redis 6+
- SSL: Certbot (Let’s Encrypt)

---

### 2) Utente, cartelle e permessi
- Creare utente deploy: `adduser deploy && usermod -aG sudo deploy`
- Directory applicazione: `/var/www/playsudoku`
- Permessi Laravel: assegnare a `www-data` le cartelle di scrittura
  - `chown -R deploy:www-data /var/www/playsudoku`
  - `chmod -R ug+rwx storage bootstrap/cache`

---

### 3) Installazione dipendenze sistema (esempio)
```bash
sudo apt update && sudo apt install -y git curl unzip nginx supervisor redis-server postgresql
# PHP 8.3 (es. repo ufficiali/ppa)
sudo apt install -y php8.3 php8.3-{bcmath,ctype,curl,dom,mbstring,openssl,xml,zip,pgsql,intl}
# Composer (se mancante)
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
```

---

### 4) Clonazione e configurazione applicazione
```bash
cd /var/www
sudo mkdir -p playsudoku && sudo chown -R deploy:deploy playsudoku
cd playsudoku
# Clona il repository
git clone <REPO_URL> .
cp .env.example .env
php artisan key:generate --force
```

Configurare `.env` (valori esempio):
```env
APP_NAME=PlaySudoku
APP_ENV=production
APP_KEY=base64:... # generato
APP_DEBUG=false
APP_URL=https://playsudoku.example.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=playsudoku
DB_USERNAME=playsudoku
DB_PASSWORD=strong-password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
BROADCAST_DRIVER=log
FILESYSTEM_DISK=public
```

---

### 5) Dipendenze, build e ottimizzazioni
```bash
# Backend
composer install --no-dev --prefer-dist --optimize-autoloader

# Frontend
npm ci
npm run build

# Link storage pubblico
php artisan storage:link

# Cache app
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Migrazioni
php artisan migrate --force
```

---

### 6) Octane (RoadRunner) in produzione
Eseguire su Linux.
```bash
composer require laravel/octane spiral/roadrunner --no-interaction
php artisan octane:install --server=roadrunner
# Scarica binario RR
vendor/bin/rr get-binary -n
```

Avvio manuale (test):
```bash
php artisan octane:start --server=roadrunner --workers=auto --max-requests=1000 --host=127.0.0.1 --port=8000
```

Supervisor per Octane (`/etc/supervisor/conf.d/playsudoku-octane.conf`):
```ini
[program:playsudoku-octane]
directory=/var/www/playsudoku
environment=APP_ENV=production,APP_DEBUG=false
command=/usr/bin/php artisan octane:start --server=roadrunner --workers=auto --max-requests=1000 --host=127.0.0.1 --port=8000
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/playsudoku-octane.err.log
stdout_logfile=/var/log/supervisor/playsudoku-octane.out.log
user=deploy
stopasgroup=true
killasgroup=true
```
Attivazione:
```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status playsudoku-octane
```

Reload dopo deploy:
```bash
php artisan octane:reload
```

Nota: in alternativa usare PHP-FPM (senza Octane) puntando Nginx a `fastcgi_pass`.

---

### 7) Code/Queue worker
Supervisor per code (`/etc/supervisor/conf.d/playsudoku-queue.conf`):
```ini
[program:playsudoku-queue]
directory=/var/www/playsudoku
command=/usr/bin/php artisan queue:work --sleep=1 --tries=3 --max-time=3600
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/playsudoku-queue.err.log
stdout_logfile=/var/log/supervisor/playsudoku-queue.out.log
user=deploy
stopasgroup=true
killasgroup=true
```

---

### 8) Scheduler (cron)
```bash
* * * * * cd /var/www/playsudoku && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

#### Comandi schedulati attivi:
- **Sfida Giornaliera**: Ogni giorno alle 00:00
- **Sfida Settimanale**: Ogni lunedì alle 00:00  
- **Cleanup Database**: Ogni notte alle 02:00 (tentativi incompleti)
- **🔒 Cleanup Consensi GDPR**: Ogni lunedì alle 01:00 (consensi scaduti)
- **Ottimizzazione**: Ogni domenica alle 03:00
- **Analisi Anomalie**: Ogni ora

#### Comandi Privacy & GDPR specifici:
```bash
# Test manuale cleanup consensi (dry-run)
php artisan consent:cleanup --dry-run

# Cleanup forzato consensi scaduti
php artisan consent:cleanup

# Verificare stato scheduler
php artisan schedule:status

# Logs scheduler e privacy
tail -f /var/www/playsudoku/storage/logs/scheduler.log
```

#### Configurazione email alerts:
Configurare in `.env` per ricevere notifiche in caso di errori scheduler:
```env
MAIL_FROM_ADDRESS=noreply@playsudoku.example.com
MAIL_FROM_NAME="PlaySudoku System"
```

**Nota GDPR**: Il comando `consent:cleanup` è essenziale per la compliance GDPR - pulisce automaticamente i consensi scaduti e mantiene un audit trail completo.

---

### 9) Nginx + SSL
Configurazione base (`/etc/nginx/sites-available/playsudoku.conf`):
```nginx
server {
    listen 80;
    server_name playsudoku.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name playsudoku.example.com;

    root /var/www/playsudoku/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/playsudoku.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/playsudoku.example.com/privkey.pem;

    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;

    location /build/ {
        try_files $uri $uri/ =404;
    }

    location / {
        try_files $uri $uri/ @octane;
    }

    location @octane {
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass http://127.0.0.1:8000;
    }

    location ~ \.php$ {
        # Solo se si usa PHP-FPM invece di Octane
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.ht { deny all; }
}
```
Abilitazione e certificati:
```bash
sudo ln -s /etc/nginx/sites-available/playsudoku.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d playsudoku.example.com
```

---

### 10) Processo di deploy (rolling)
1. Pull ultime modifiche: `git pull --rebase`
2. `composer install --no-dev --prefer-dist --optimize-autoloader`
3. `npm ci && npm run build`
4. `php artisan migrate --force`
5. Cache: `php artisan config:cache route:cache view:cache event:cache`
6. Reload Octane: `php artisan octane:reload`

Per zero-downtime avanzato, usare strategia a release con symlink (`current` → `releases/2024xxxx`).

---

### 11) Osservabilità e sicurezza
- Log Laravel → `storage/logs`
- Error tracking: Sentry (prod), Telescope (staging)
- Backup DB: pg_dump + retention; snapshot storage (S3 consigliato)
- UFW: aprire 80/443; chiudere 8000 (solo localhost per Octane)

#### 🔒 GDPR & Privacy Compliance:
```bash
# Verificare tabelle privacy create
php artisan migrate:status | grep consent

# Controllare admin privacy dashboard
https://playsudoku.example.com/admin/consents

# Test export GDPR per utente
curl -X POST "https://playsudoku.example.com/admin/consents/export" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1}'

# Monitoring consensi attivi
php artisan tinker --execute="echo 'Consensi attivi: ' . \App\Models\UserConsent::active()->count();"
```

#### Configurazioni privacy obbligatorie in `.env`:
```env
# Admin email per notifiche privacy
MAIL_ADMIN_ADDRESS=admin@playsudoku.example.com

# Google Analytics (se utilizzato)
ANALYTICS_GA_MEASUREMENT_ID=G-446PVFY6BW
ANALYTICS_AUTO_ENABLE_ENVIRONMENTS=production

# Privacy compliance
APP_PRIVACY_POLICY_VERSION=1.0
```

#### Backup e retention dati:
- **Consensi utente**: Backup quotidiano con retention 7 anni (requisito GDPR)
- **Audit logs**: Retention 6 anni per compliance  
- **User data**: Right to erasure implementato via admin interface

---

### 12) Troubleshooting
- 502 Bad Gateway: controllare Octane/supervisor attivo; `supervisorctl status`
- 500 errori: verificare `.env` e cache config (`php artisan config:clear`)
- Asset non serviti: ricostruire `npm run build`, verificare `public/build` e `location /build/`

#### Troubleshooting Privacy & GDPR:
```bash
# Cookie banner non appare
# → Verificare inclusion componente e cache views
php artisan view:clear
curl -I https://playsudoku.example.com/ | grep -i cookie

# Consensi non salvati
# → Controllare tabella user_consents e log errori
php artisan tinker --execute="echo 'Tabella exists: ' . Schema::hasTable('user_consents');"
tail -f storage/logs/laravel.log | grep -i consent

# Scheduler consensi non funziona  
# → Verificare cron attivo e log scheduler
crontab -l | grep artisan
php artisan schedule:list | grep consent
tail -f storage/logs/scheduler.log

# Admin dashboard consensi errore 403
# → Verificare ruolo admin utente
php artisan tinker --execute="User::find(1)->isAdmin();"

# Export GDPR fallisce
# → Controllare permessi storage e config mail
ls -la storage/app/
php artisan config:show mail
```

#### Log files importanti:
- `storage/logs/laravel.log` - Errori applicazione
- `storage/logs/scheduler.log` - Comandi schedulati  
- `/var/log/supervisor/` - Supervisor queue/octane
- `/var/log/nginx/` - Access/error logs web server
