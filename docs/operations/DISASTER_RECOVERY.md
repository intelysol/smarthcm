# Enterprise Platform — Disaster Recovery & Business Continuity Plan

## 1. Objectives & Metrics
- **Recovery Point Objective (RPO):** $< 15\text{ minutes}$ (maximum tolerable data loss window).
- **Recovery Time Objective (RTO):** $< 60\text{ minutes}$ (maximum duration to restore platform availability).
- **Availability Target:** $99.95\%$ uptime excluding announced maintenance windows.

---

## 2. Disaster Scenarios & Recovery Sequences

### Scenario A: Complete Primary Database Loss / Corruption
1. **Declare Incident:** Incident Commander convenes bridge, sets SEV-1.
2. **Retrieve Latest Cold Backup:** Download most recent valid dump from secondary offsite storage.
3. **Verify Integrity:** Validate SHA-256 checksum against signed digest.
4. **Provision Primary Database:** Launch new MySQL 8.4 instance.
5. **Execute Restore Sequence:**
   ```bash
   pwsh database/scripts/restore.ps1 -BackupFile storage/backups/latest.sql -TargetDb smarthcm -VerifyChecksum
   ```
6. **Replay Binary Logs:** Apply incremental MySQL binary logs from backup point-in-time up to the corruption boundary.
7. **Schema & Application Verification:**
   ```bash
   php artisan hcm:schema:verify --strict
   php artisan test --filter=HealthCheckTest
   ```
8. **Re-route Application Traffic:** Update database endpoint connection pool.

### Scenario B: Regional Cloud Outage (Multi-Zone / Multi-Region Failover)
1. **Redirect DNS:** Update Cloudflare / Route53 apex records to secondary region load balancer.
2. **Promote Read Replica:** Promote secondary region MySQL read replica to primary read/write.
3. **Scale Compute Nodes:** Auto-scale PHP-FPM web workers and Horizon queue consumers in secondary VPC.
4. **Warm Caches & Reverb:** Re-initialize Redis cache namespaces.
5. **Execute Smoke Tests:** Validate `/health/ready` and critical employee login journey.

---

## 3. Disaster Recovery Runbook Checklist

| Step | Action Item | Responsible Party | Estimated Duration | Verification Gate |
|---|---|---|---|---|
| **1** | Incident Declaration & Bridge Setup | Lead SRE | 5 min | Bridge active, ticket created |
| **2** | Isolate Corrupted Instances | Security / SRE | 5 min | Traffic drained to maintenance page |
| **3** | Fetch & Verify Backup Archive | Database Administrator | 10 min | SHA-256 checksum matches |
| **4** | Restore Database & Apply Replays | Database Administrator | 20 min | 985 tables, 2,180 FKs confirmed |
| **5** | Deploy & Optimize Application | DevOps Engineer | 10 min | `config:cache`, `route:cache` |
| **6** | Run Health & Smoke Test Suite | QA Lead / SRE | 5 min | `/health/ready` = 200 OK |
| **7** | Re-enable Ingress Traffic | Traffic Manager | 5 min | Live traffic serving normal latencies |
| **Total** | **Full Recovery** | | **55 min** | **Target RTO Met (< 60 min)** |
