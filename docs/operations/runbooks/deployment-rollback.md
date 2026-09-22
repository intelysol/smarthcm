# Operational Runbook: Deployment Rollback & Canary Reversion

## 1. Overview
- **ID:** `rb-deployment-rollback`
- **Severity:** SEV-1 (Major)
- **Component:** Deployment / CI/CD Release / Zero-Downtime Rollback
- **Trigger:** Immediate post-deployment error spike, fatal exception during canary verification, or broken critical migration.

---

## 2. Immediate Diagnostic Steps
1. **Compare Current Release with Previous Release:**
   ```bash
   git status
   git log -n 5 --oneline
   ```
2. **Review Deployment Logs:**
   - Inspect `/health/services` and `/health/dependencies`.
   - Inspect recent fatal logs in `storage/logs/laravel.log`.

---

## 3. Mitigation & Rollback Procedures
- **Step 1: Point Symlink to Previous Release (Atomic Reversion)**
  - If using Envoyer / deployer symlink structure:
    ```bash
    # Switch active current symlink to previous release folder
    ln -nfs /var/www/releases/<previous_release_timestamp> /var/www/current
    ```
  - If single-directory git checkout:
    ```bash
    git checkout <previous_stable_commit_hash>
    ```
- **Step 2: Revert Database Migrations (If non-breaking rollback possible)**
  - Check last migration batch:
    ```bash
    php artisan migrate:status
    ```
  - Revert batch only if data loss will not occur:
    ```bash
    php artisan migrate:rollback --step=1
    ```
- **Step 3: Rebuild Caches & Restart Workers**
  ```bash
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan horizon:terminate
  systemctl reload php8.3-fpm
  ```

---

## 4. Verification & Post-Resolution
- Verify `/health/live` and `/health/ready` return HTTP 200 OK.
- Run automated sanity tests:
  ```bash
  php artisan test --filter=ObservabilityAndReliabilityTest
  ```
- Inform stakeholders and log incident in `/operations/incidents`.
