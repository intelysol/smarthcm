# Enterprise Platform — Operational Troubleshooting Guide

## 1. Common Operational Faults & Resolutions

### Fault 1: `/health/ready` Returns HTTP 503 (Service Unavailable)
- **Symptoms:** Load balancer drops compute node from rotation.
- **Diagnostic Steps:**
  1. Inspect response JSON payload: `curl -s https://app/health/ready | jq .`
  2. Check if `checks.database.status === 'error'`:
     - Test MySQL connection manually: `mysql -h $DB_HOST -u $DB_USERNAME -p`
     - Check MySQL processlist: `SHOW PROCESSLIST;` for locking queries.
  3. Check if `checks.storage.status === 'error'`:
     - Test filesystem write permissions on `storage/` or S3 bucket IAM permissions.
- **Resolution:** Resolve underlying database connection saturation or fix IAM storage policies; `/health/ready` recovers automatically within 5 seconds of restoration.

### Fault 2: Background Queues Stalling / High Failed Job Count
- **Symptoms:** Employees do not receive notifications; integration sync jobs delayed; failed job count rising on System Health dashboard.
- **Diagnostic Steps:**
  1. Check worker status: `php artisan horizon:status` or `ps aux | grep queue:work`
  2. Inspect failed jobs table: `php artisan queue:failed`
  3. View error stack trace with correlation ID:
     `SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 5\G`
- **Resolution:**
  - Restart worker pool: `php artisan queue:restart`
  - Retry failed jobs: `php artisan queue:retry all`
  - If third-party API is down, pause specific integration connector from Integration Command Center.

### Fault 3: Users Encounter HTTP 419 (Session Expired / CSRF Token Mismatch)
- **Symptoms:** Users submitting long forms after idle periods receive session expiration prompt.
- **Diagnostic Steps:**
  1. Verify Redis session store connectivity: `redis-cli ping`
  2. Check `SESSION_LIFETIME` setting in `.env` (default is 120 minutes).
  3. Check cookie domain configuration: `SESSION_DOMAIN`.
- **Resolution:** User is presented with friendly sign-in prompt; verify browser is accepting `SameSite=Lax` secure session cookies.

### Fault 4: Cross-Tenant 403 Forbidden Errors
- **Symptoms:** Valid authenticated user receives 403 Forbidden when accessing tenant resources.
- **Diagnostic Steps:**
  1. Check `X-Tenant` header or session `tenant_uuid`.
  2. Query `tenant_user` table:
     `SELECT * FROM tenant_user WHERE user_id = ? AND tenant_id = ?;`
  3. Verify tenant lifecycle state: `SELECT status, is_active FROM tenants WHERE id = ?;`
- **Resolution:** Ensure user is actively attached to tenant in `tenant_user` with status `active`.

---

## 2. Diagnostic Commands Quick Reference
```bash
# Check platform version and runtime status
php artisan --version
php artisan about

# Verify strict database schema integrity
php artisan hcm:schema:verify --strict

# Inspect scheduled jobs and next execution times
php artisan schedule:list

# Clear application caches in emergency
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```
