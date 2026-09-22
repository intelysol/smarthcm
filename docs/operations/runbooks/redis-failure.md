# Operational Runbook: Redis In-Memory Store & Cache Failure

## 1. Overview
- **ID:** `rb-redis-failure`
- **Severity:** SEV-1 (Major)
- **Component:** Cache / Redis 7.0+ / Horizon Queue Driver
- **Trigger:** `redis_status == 0` or cache read/write timeouts.

---

## 2. Immediate Diagnostic Steps
1. **Probe Redis Connectivity & Latency:**
   ```bash
   redis-cli -h 127.0.0.1 -p 6379 ping
   # Check Redis memory and client connections
   redis-cli info memory
   redis-cli info clients
   ```
2. **Check Redis Log Files:**
   ```bash
   tail -n 100 /var/log/redis/redis-server.log
   ```
3. **Verify App Redis Fallback Configuration:**
   - Review `/health/dependencies` checks for `redis` and `cache`.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Max Memory Exhaustion (OOM)**
  - If Redis returns `OOM command not allowed when used memory > 'maxmemory'`:
    1. Check maxmemory policy: `redis-cli config get maxmemory-policy`
    2. Set policy to volatile-lru or allkeys-lru:
       ```bash
       redis-cli config set maxmemory-policy allkeys-lru
       ```
    3. Clear non-critical cache tags if necessary:
       ```bash
       php artisan cache:clear
       ```
- **Scenario B: Redis Server Dead or Hung**
  - Restart service:
    ```bash
    systemctl restart redis-server
    ```
  - Restart Laravel Horizon to establish fresh Redis connection pools:
    ```bash
    php artisan horizon:terminate
    ```

---

## 4. Verification & Post-Resolution
- Verify `redis-cli ping` returns `PONG`.
- Verify Horizon is active: `php artisan horizon:status`.
- Confirm `/health/dependencies` reports cache and redis status `ok`.
