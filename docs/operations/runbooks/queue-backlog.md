# Operational Runbook: Queue Backlog & Worker Saturation

## 1. Overview
- **ID:** `rb-queue-backlog`
- **Severity:** SEV-2 (Significant)
- **Component:** Queues / Horizon / Background Workers
- **Trigger:** `queue_pending_jobs > 1000` or queue ingestion wait time > 10s.

---

## 2. Immediate Diagnostic Steps
1. **Check Horizon Cluster & Queue Depths:**
   ```bash
   php artisan horizon:status
   # Inspect pending jobs per queue
   php artisan queue:monitor default,high,payroll,notifications,exports
   ```
2. **Check Current Throughput in Operations Console:**
   - Navigate to `/operations/queues`.
   - Identify which queue is accumulating backlog.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Temporary Spikes (e.g. Mass Payroll Calculation or Bulk Document Generation)**
  - Scale up queue worker processes:
    - Edit `config/horizon.php` or increase Supervisor worker count:
      ```bash
      # Scale horizon supervisor workers
      php artisan horizon:scale supervisor-1 --processes=16
      ```
  - Separate high-priority queues from batch exports:
    - Route transactional notifications to high-priority workers.
- **Scenario B: Workers Stuck on Long-Running Job**
  - Check Horizon running jobs:
    ```bash
    # Terminate workers exceeding timeout
    php artisan horizon:terminate
    ```

---

## 4. Verification & Post-Resolution
- Monitor queue depth until pending jobs count is under 100.
- Verify job throughput meets SLO target (< 5s queue wait).
