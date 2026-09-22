# Operational Runbook: Database Connection Failure & High Latency

## 1. Overview
- **ID:** `rb-db-failure`
- **Severity:** SEV-0 (Critical) / SEV-1 (Major)
- **Component:** Database / MySQL 8.0+ / Connection Pool
- **Trigger:** `db_connection_status == 0` or query latency > 150ms over 5m.

---

## 2. Immediate Diagnostic Steps
1. **Check Database Health Signal:**
   ```bash
   curl -s https://app.smarthcm.com/health/dependencies | jq .dependencies.database
   ```
2. **Inspect Active Database Connections & Thread Pool:**
   ```sql
   SHOW PROCESSLIST;
   SHOW STATUS LIKE 'Threads_connected';
   SHOW STATUS LIKE 'Max_used_connections';
   SHOW VARIABLES LIKE 'max_connections';
   ```
3. **Inspect Long-Running & Locking Queries:**
   ```sql
   SELECT id, user, host, db, command, time, state, info 
   FROM information_schema.processlist 
   WHERE command != 'Sleep' AND time > 10 
   ORDER BY time DESC LIMIT 20;

   SELECT * FROM performance_schema.data_locks;
   ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Max Connections Reached**
  - If `Too many connections` error is present:
    ```sql
    SET GLOBAL max_connections = 500;
    ```
  - Verify application connection pooling or persistent connections (`PDO::ATTR_PERSISTENT => false`).
- **Scenario B: Rogue Query Locking Tables**
  - Identify the thread ID holding exclusive lock:
    ```sql
    KILL <thread_id>;
    ```
- **Scenario C: Replica Lag or Failover**
  - If Primary DB is unresponsive, execute automated failover to read-replica via AWS RDS / Aurora / ProxySQL switchover.
  - Update `.env` `DB_HOST` if not using DNS cluster endpoint, and restart PHP-FPM:
    ```bash
    php artisan config:cache
    systemctl reload php8.3-fpm
    ```

---

## 4. Verification & Post-Resolution
- Verify `php artisan db:monitor` returns healthy thread counts.
- Confirm `/health/dependencies` database latency returns under 20ms.
