# 🔒 Strategia Backup e Restore - PlaySudoku

Documentazione completa per backup, restore e disaster recovery della piattaforma PlaySudoku.

## 🎯 Overview

La strategia di backup di PlaySudoku garantisce:
- **RTO (Recovery Time Objective)**: < 4 ore
- **RPO (Recovery Point Objective)**: < 1 ora  
- **Retention**: 30 giorni backup giornalieri, 12 mesi backup mensili
- **Testing**: Restore test mensili automatizzati

## 📊 Dati da Backup

### Dati Critici (Backup Continuo)
- **Database PostgreSQL**: Tutti i dati applicativi
- **File di configurazione**: `.env`, configurazioni server
- **Log audit**: Tracciabilità azioni admin e sicurezza
- **Chiavi crittografiche**: SSL/TLS certificates, APP_KEY

### Dati Importanti (Backup Giornaliero)  
- **File upload utenti**: Immagini profilo, avatar
- **Cache Redis**: Per performance (opzionale, ricostruibile)
- **Log applicativi**: Laravel logs, scheduler logs

### Dati Ricostruibili (No Backup)
- **File temporanei**: Cache compiled views, routes
- **File di build**: CSS/JS compilati (ricostruibili con `npm run build`)

## 🏗️ Architettura Backup

### Backup Automatici

```bash
# Crontab di sistema per backup automatici
# Backup database ogni ora
0 * * * * /opt/playsudoku/scripts/backup-database.sh hourly

# Backup completo ogni giorno alle 2:00
0 2 * * * /opt/playsudoku/scripts/backup-full.sh daily

# Backup mensile il primo del mese alle 1:00
0 1 1 * * /opt/playsudoku/scripts/backup-full.sh monthly

# Cleanup backup vecchi ogni settimana
0 3 * * 0 /opt/playsudoku/scripts/cleanup-backups.sh
```

### Script Backup Database

```bash
#!/bin/bash
# /opt/playsudoku/scripts/backup-database.sh

set -e

BACKUP_DIR="/opt/playsudoku/backups"
DATE=$(date +%Y%m%d_%H%M%S)
TYPE=${1:-manual}
DB_NAME=${DB_DATABASE:-playsudoku}
DB_USER=${DB_USERNAME:-playsudoku}

# Crea directory se non esiste
mkdir -p $BACKUP_DIR/$TYPE

# Backup database con compressione
PGPASSWORD=$DB_PASSWORD pg_dump \
  --host=$DB_HOST \
  --port=$DB_PORT \
  --username=$DB_USER \
  --format=custom \
  --compress=9 \
  --verbose \
  --file=$BACKUP_DIR/$TYPE/database_${DATE}.dump \
  $DB_NAME

# Verifica integrità backup
if pg_restore --list $BACKUP_DIR/$TYPE/database_${DATE}.dump > /dev/null; then
  echo "✅ Backup database completato: database_${DATE}.dump"
  
  # Log audit del backup
  php /opt/playsudoku/artisan audit:log-backup \
    --type=database \
    --file=database_${DATE}.dump \
    --size=$(stat -c%s $BACKUP_DIR/$TYPE/database_${DATE}.dump)
else
  echo "❌ Backup database fallito - verifica integrità non superata"
  exit 1
fi
```

### Script Backup Completo

```bash
#!/bin/bash
# /opt/playsudoku/scripts/backup-full.sh

set -e

BACKUP_DIR="/opt/playsudoku/backups"
DATE=$(date +%Y%m%d_%H%M%S)
TYPE=${1:-manual}
PROJECT_DIR="/var/www/playsudoku"

# Crea directory backup
mkdir -p $BACKUP_DIR/$TYPE

echo "🚀 Avvio backup completo PlaySudoku - $TYPE"

# 1. Backup database
echo "📊 Backup database..."
./backup-database.sh $TYPE

# 2. Backup file applicazione  
echo "📁 Backup file applicazione..."
tar -czf $BACKUP_DIR/$TYPE/app_files_${DATE}.tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='.git' \
  -C $PROJECT_DIR .

# 3. Backup configurazioni di sistema
echo "⚙️ Backup configurazioni..."
tar -czf $BACKUP_DIR/$TYPE/configs_${DATE}.tar.gz \
  /etc/nginx/sites-available/playsudoku \
  /etc/supervisor/conf.d/playsudoku-worker.conf \
  /etc/ssl/certs/playsudoku.crt \
  /etc/ssl/private/playsudoku.key \
  2>/dev/null || true

# 4. Backup storage e upload
echo "💾 Backup storage..."
if [ -d "$PROJECT_DIR/storage/app/public" ]; then
  tar -czf $BACKUP_DIR/$TYPE/storage_${DATE}.tar.gz \
    -C $PROJECT_DIR/storage/app/public .
fi

# 5. Crea manifest backup
cat > $BACKUP_DIR/$TYPE/manifest_${DATE}.json << EOF
{
  "backup_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "backup_type": "$TYPE",
  "version": "$(cd $PROJECT_DIR && git rev-parse HEAD 2>/dev/null || echo 'unknown')",
  "files": {
    "database": "database_${DATE}.dump",
    "app_files": "app_files_${DATE}.tar.gz", 
    "configs": "configs_${DATE}.tar.gz",
    "storage": "storage_${DATE}.tar.gz"
  },
  "sizes": {
    "database": $(stat -c%s $BACKUP_DIR/$TYPE/database_${DATE}.dump),
    "app_files": $(stat -c%s $BACKUP_DIR/$TYPE/app_files_${DATE}.tar.gz),
    "storage": $(stat -c%s $BACKUP_DIR/$TYPE/storage_${DATE}.tar.gz 2>/dev/null || echo 0)
  }
}
EOF

echo "✅ Backup completo $TYPE completato: $DATE"

# Log audit
php $PROJECT_DIR/artisan audit:log-backup \
  --type=full \
  --manifest=$BACKUP_DIR/$TYPE/manifest_${DATE}.json
```

## 🔄 Procedure di Restore

### Restore Database

```bash
#!/bin/bash
# Restore database da backup

BACKUP_FILE=$1
DB_NAME=${2:-playsudoku_restored}

if [ -z "$BACKUP_FILE" ]; then
  echo "Uso: restore-database.sh <backup_file> [db_name]"
  exit 1
fi

echo "🔄 Restore database da $BACKUP_FILE a $DB_NAME"

# 1. Crea database di restore
createdb -h $DB_HOST -U $DB_USER $DB_NAME

# 2. Restore dati
PGPASSWORD=$DB_PASSWORD pg_restore \
  --host=$DB_HOST \
  --port=$DB_PORT \
  --username=$DB_USER \
  --dbname=$DB_NAME \
  --verbose \
  --clean \
  --if-exists \
  $BACKUP_FILE

echo "✅ Restore completato: $DB_NAME"
```

### Restore Completo

```bash
#!/bin/bash
# Restore completo da backup

BACKUP_DIR=$1
RESTORE_DIR=${2:-/var/www/playsudoku_restore}

if [ -z "$BACKUP_DIR" ]; then
  echo "Uso: restore-full.sh <backup_directory> [restore_directory]"
  exit 1
fi

echo "🔄 Restore completo da $BACKUP_DIR a $RESTORE_DIR"

# 1. Crea directory restore
mkdir -p $RESTORE_DIR

# 2. Restore file applicazione
echo "📁 Restore file applicazione..."
tar -xzf $BACKUP_DIR/app_files_*.tar.gz -C $RESTORE_DIR

# 3. Restore storage
echo "💾 Restore storage..."
mkdir -p $RESTORE_DIR/storage/app/public
tar -xzf $BACKUP_DIR/storage_*.tar.gz -C $RESTORE_DIR/storage/app/public

# 4. Restore database (a database separato)
echo "📊 Restore database..."
DB_RESTORE_NAME="playsudoku_restore_$(date +%Y%m%d)"
./restore-database.sh $BACKUP_DIR/database_*.dump $DB_RESTORE_NAME

# 5. Aggiorna configurazione
echo "⚙️ Aggiornamento configurazione..."
cp $RESTORE_DIR/.env.example $RESTORE_DIR/.env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_RESTORE_NAME/" $RESTORE_DIR/.env

# 6. Installa dipendenze e ottimizza
cd $RESTORE_DIR
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Restore completo in: $RESTORE_DIR"
echo "📊 Database: $DB_RESTORE_NAME"
```

## 🧪 Testing e Validazione

### Test Automatico Backup

```bash
#!/bin/bash
# /opt/playsudoku/scripts/test-backup.sh

set -e

BACKUP_DIR="/opt/playsudoku/backups/daily"
LATEST_BACKUP=$(ls -t $BACKUP_DIR/database_*.dump | head -1)

echo "🧪 Test restore backup: $LATEST_BACKUP"

# 1. Crea database di test
TEST_DB="playsudoku_test_$(date +%Y%m%d_%H%M%S)"
createdb -h $DB_HOST -U $DB_USER $TEST_DB

# 2. Restore su database di test
PGPASSWORD=$DB_PASSWORD pg_restore \
  --host=$DB_HOST \
  --username=$DB_USER \
  --dbname=$TEST_DB \
  --verbose \
  $LATEST_BACKUP

# 3. Verifica integrità dati
TABLES_COUNT=$(PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USER -d $TEST_DB -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';")

if [ $TABLES_COUNT -gt 0 ]; then
  echo "✅ Test backup superato: $TABLES_COUNT tabelle ripristinate"
else
  echo "❌ Test backup fallito: nessuna tabella trovata"
  exit 1
fi

# 4. Cleanup database di test
dropdb -h $DB_HOST -U $DB_USER $TEST_DB

# 5. Log risultato test
php /opt/playsudoku/artisan audit:log-backup-test \
  --backup=$LATEST_BACKUP \
  --result=success \
  --tables=$TABLES_COUNT
```

### Monitoraggio Backup

```bash
# Script di monitoraggio backup
#!/bin/bash

BACKUP_DIR="/opt/playsudoku/backups"
ALERT_EMAIL="admin@playsudoku.com"

# Verifica backup recenti
LAST_BACKUP=$(find $BACKUP_DIR -name "database_*.dump" -mtime -1 | wc -l)

if [ $LAST_BACKUP -eq 0 ]; then
  echo "⚠️ ALERT: Nessun backup database nelle ultime 24 ore" | \
  mail -s "PlaySudoku Backup Alert" $ALERT_EMAIL
fi

# Verifica spazio disco
DISK_USAGE=$(df $BACKUP_DIR | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 85 ]; then
  echo "⚠️ ALERT: Spazio backup al ${DISK_USAGE}%" | \
  mail -s "PlaySudoku Disk Space Alert" $ALERT_EMAIL
fi
```

## 📋 Checklist Disaster Recovery

### Scenario: Perdita Database

1. **Identificazione** (Target: 15 minuti)
   - [ ] Conferma perdita dati database
   - [ ] Identifica ultimo backup valido
   - [ ] Stima data/ora dell'incident

2. **Preparazione** (Target: 30 minuti)  
   - [ ] Prepara ambiente di restore
   - [ ] Verifica integrità backup
   - [ ] Notifica stakeholder

3. **Restore** (Target: 2 ore)
   - [ ] Restore database da backup
   - [ ] Verifica consistenza dati
   - [ ] Test funzionalità critiche

4. **Validazione** (Target: 1 ora)
   - [ ] Test login utenti
   - [ ] Verifica sfide attive
   - [ ] Controllo leaderboard
   - [ ] Test submit risultati

### Scenario: Perdita Server Completa

1. **Setup Nuovo Server** (Target: 4 ore)
   - [ ] Provisioning nuova infrastruttura
   - [ ] Installazione software stack
   - [ ] Configurazione networking/SSL

2. **Restore Applicazione** (Target: 2 ore)
   - [ ] Restore file applicazione
   - [ ] Restore database
   - [ ] Restore configurazioni
   - [ ] Update DNS se necessario

3. **Verifica Completa** (Target: 2 ore)
   - [ ] Test tutti i flussi utente
   - [ ] Verifica scheduler attivo
   - [ ] Test notifiche email
   - [ ] Controllo performance

## 🔧 Comandi Artisan per Backup

```php
// Comando per log backup audit
php artisan make:command LogBackupCommand --command=audit:log-backup

// Comando per test backup
php artisan make:command TestBackupCommand --command=backup:test

// Comando per cleanup backup automatico  
php artisan make:command CleanupBackupsCommand --command=backup:cleanup
```

## 📊 Metriche e Monitoring

### KPI Backup
- **Backup Success Rate**: > 99%
- **Backup Duration**: < 30 minuti
- **Restore Test Success**: 100%
- **Storage Growth**: Monitoraggio trend

### Alert Configurati
- Backup fallito > 1 volta
- Spazio disco backup > 85%
- Test restore fallito
- Backup non eseguito > 25 ore

## 📞 Contatti Emergency

| Ruolo | Nome | Telefono | Email |
|-------|------|----------|-------|
| Tech Lead | TBD | +39 xxx xxxx | tech@playsudoku.com |
| DevOps | TBD | +39 xxx xxxx | ops@playsudoku.com |
| Hosting Provider | TBD | Support 24/7 | support@provider.com |

---

## 📝 Log Modifiche

| Data | Versione | Modifiche |
|------|----------|-----------|
| 2025-08-14 | 1.0 | Versione iniziale strategia backup |

> **Nota**: Questa documentazione deve essere testata regolarmente e aggiornata in base all'evoluzione dell'infrastruttura.
