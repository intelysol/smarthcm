# Enterprise Platform — Production Deployment & Rollback Runbook

## 1. Zero-Downtime Deployment Lifecycle

```text
[Pre-Deployment Gates]
        ↓
[Database Backup Verification]
        ↓
[Artifact Packaging & Release Stage]
        ↓
[Atomic Symlink Swap / Blue-Green Switch]
        ↓
[Optimized Cache Warming (config, route, view)]
        ↓
[Horizon & Queue Worker Graceful Restart]
        ↓
[Live Health Check & Smoke Test Gate]
        ↓
[Release Confirmation / Instant Rollback Trigger]
```

---

## 2. Step-by-Step Production Deployment Procedure

### Step 1: Pre-Deployment Gates
- Ensure CI/CD status on release commit is 100% green (unit, feature, security, schema verification).
- Confirm maintenance window or zero-downtime rolling strategy.

### Step 2: Immediate Database Snapshot
Before touching production code or executing migrations, create a tagged pre-deployment snapshot:
```bash
# Linux
./database/scripts/backup.sh smarthcm 127.0.0.1 3306 root storage/backups

# Windows PowerShell
pwsh database/scripts/backup.ps1 -DbName smarthcm -OutputDir storage/backups
```

### Step 3: Deploy New Release Artifact
Deploy new code into a timestamped release directory (`/var/www/smarthcm/releases/20260914_XXXXXX`).
```bash
git clone --depth 1 --branch release/2.65.0 <repo-url> /var/www/smarthcm/releases/2.65.0
cd /var/www/smarthcm/releases/2.65.0
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
```

### Step 4: Run Safe Migrations
Execute any non-breaking forward migrations:
```bash
php artisan migrate --force
php artisan hcm:schema:verify --strict
```
*(Never run `migrate:fresh` against production).*

### Step 5: Cache Compilation & Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 6: Atomic Symlink Switch
Switch current release symlink pointing to new build:
```bash
ln -nfs /var/www/smarthcm/releases/2.65.0 /var/www/smarthcm/current
```

### Step 7: Graceful Queue Worker & Service Restart
Restart background worker pools without terminating in-flight jobs:
```bash
php artisan queue:restart
php artisan horizon:terminate
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

### Step 8: Automated Post-Deployment Verification Gate
Execute liveness, readiness, and production smoke tests:
```bash
curl -f https://hcm.enterprise.internal/health/live
curl -f https://hcm.enterprise.internal/health/ready
php artisan test --filter=ProductionSmokeTest
```

---

## 3. Emergency Rollback Procedure
If the post-deployment verification gate fails or error rates exceed 0.5% within 15 minutes:

1. **Revert Symlink Instantly:**
   ```bash
   ln -nfs /var/www/smarthcm/releases/previous_stable /var/www/smarthcm/current
   ```
2. **Rebuild Caches for Previous Release:**
   ```bash
   cd /var/www/smarthcm/current
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. **Restart Workers & PHP-FPM:**
   ```bash
   php artisan queue:restart
   sudo systemctl reload php8.4-fpm
   ```
4. **Database Rollback (If Destructive Schema Occurred):**
   ```bash
   pwsh database/scripts/restore.ps1 -BackupFile storage/backups/pre_deploy_backup.sql -TargetDb smarthcm -VerifyChecksum
   ```
5. **Verify Health:**
   ```bash
   curl -f https://hcm.enterprise.internal/health/ready
   ```
