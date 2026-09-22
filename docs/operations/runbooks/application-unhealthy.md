# Operational Runbook: Application Unhealthy / 5xx Error Spikes

## 1. Overview
- **ID:** `rb-app-unhealthy`
- **Severity:** SEV-1 (Major)
- **Component:** Application / Web / PHP-FPM / Nginx
- **Trigger:** HTTP 5xx rate > 2% over 5 minutes or `/health` returning `status: degraded` / `503 Service Unavailable`.

---

## 2. Immediate Diagnostic Steps
1. **Check Live Health Probes:**
   ```bash
   curl -I https://app.smarthcm.com/health/live
   curl -s https://app.smarthcm.com/health | jq .
   curl -s https://app.smarthcm.com/health/dependencies | jq .
   ```
2. **Inspect Correlated Logs by Request ID:**
   ```bash
   # Find top failing endpoints and associated correlation IDs
   tail -n 500 storage/logs/laravel.log | grep -E "ERROR|CRITICAL" | tail -n 20
   ```
3. **Check Web Server & PHP-FPM Process Health:**
   ```bash
   # Check PHP-FPM pool status and active workers
   systemctl status php8.3-fpm
   # Check Nginx error logs
   tail -n 100 /var/log/nginx/error.log
   ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: PHP-FPM Worker Pool Exhaustion**
  - If `server reached pm.max_children setting` appears in PHP-FPM logs:
    1. Temporarily increase worker limit in `/etc/php/8.3/fpm/pool.d/www.conf`:
       `pm.max_children = 100` (adjust based on RAM).
    2. Gracefully reload PHP-FPM:
       `systemctl reload php8.3-fpm`
- **Scenario B: Upstream Timeout (504 Gateway Timeout)**
  - Check slow database queries: inspect `checkDatabase()` output in `/health/dependencies`.
  - Check if long-running synchronous requests should be offloaded to queue workers.
- **Scenario C: Fatal Code Error Post-Deployment**
  - Inspect latest git commit: `git log -1`
  - If broken release, trigger immediate rollback using `docs/operations/runbooks/deployment-rollback.md`.

---

## 4. Verification & Post-Resolution
- Verify `/health` returns `HTTP 200` with `status: ok`.
- Confirm HTTP 5xx rate drops below 0.1% for 15 consecutive minutes.
- Resolve alert in Operations Console (`/operations/alerts`).
