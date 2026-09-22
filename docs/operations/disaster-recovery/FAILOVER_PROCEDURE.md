# High Availability Failover & Failback Procedures

## 1. High Availability Architecture Overview
The platform provides resilience against single-point-of-failure (SPOF) scenarios using multi-zone and multi-node clusters:
- **Application Nodes:** Stateless PHP-FPM / Nginx nodes distributed behind an Application Load Balancer (ALB).
- **Database Cluster:** MySQL 8.0 Primary with synchronous multi-AZ standby and asynchronous cross-region read replica.
- **Cache & Session:** Redis Sentinel cluster with automatic master failover.
- **Workers:** Scaled Laravel Horizon worker processes distributed across distinct virtual compute instances.

---

## 2. Database Failover Sequence
When the primary MySQL database fails health probes:
1. **Detection:** AWS RDS / ProxySQL detects loss of heartbeat (> 15s).
2. **Promotion:** Multi-AZ synchronous standby replica is automatically promoted to Primary read/write master.
3. **Endpoint CNAME Update:** Database DNS endpoint (`db.internal.smarthcm.com`) points to the new master within 30 seconds.
4. **Connection Pool Reset:** PHP-FPM workers automatically drop dead connections and establish fresh pools against the promoted master.

---

## 3. Controlled Failback Sequence
Failback to the original primary infrastructure must NEVER be executed automatically. It requires human validation to prevent split-brain scenarios:
1. **Step 1:** Verify the original primary host is fully healthy and isolated from write traffic.
2. **Step 2:** Configure the original host as a secondary replica replicating from the currently active promoted master.
3. **Step 3:** Wait until replication lag reaches 0 seconds (`Seconds_Behind_Master = 0`).
4. **Step 4:** During a scheduled 2-minute maintenance window, momentarily place the application in read-only mode.
5. **Step 5:** Reverse replication, promote the primary back to master, update DNS, and verify `/health/dependencies`.
6. **Step 6:** Restore read/write access and confirm zero transaction loss.
