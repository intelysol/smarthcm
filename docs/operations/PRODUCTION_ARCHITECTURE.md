# Enterprise Platform — Production Architecture Specification

## 1. Architectural Topology Overview
The Enterprise Application Platform is engineered as a high-throughput, cloud-native **Modular Monolith** with strict Domain-Driven Design (DDD) boundaries. The runtime environment isolates stateless compute nodes from persistent data stores to enable horizontal scaling, zero-downtime rolling upgrades, and rapid disaster recovery.

```
                                  [ Internet / Corporate WAN ]
                                                │
                                                ▼
                                   [ CloudFlare / WAF / DDoS ]
                                                │ (HTTPS 443 / TLS 1.3)
                                                ▼
                                    [ Application Load Balancer ]
                                 (AWS ALB / Nginx / HAProxy / Traefik)
                                                │
                        ┌───────────────────────┴───────────────────────┐
                        │                                               │
                        ▼                                               ▼
             [ Web / API Worker Node 1 ]                    [ Web / API Worker Node 2 ]
             - Nginx Reverse Proxy                          - Nginx Reverse Proxy
             - PHP 8.4-FPM Process Pool                     - PHP 8.4-FPM Process Pool
             - Laravel 13 Core Runtime                      - Laravel 13 Core Runtime
             - Correlation & Ingress Filters                - Correlation & Ingress Filters
                        │                                               │
                        └───────────────────────┬───────────────────────┘
                                                │
       ┌────────────────────────┬───────────────┴───────────────┬────────────────────────┐
       ▼                        ▼                               ▼                        ▼
[ Primary Database ]     [ Redis Cluster ]              [ Queue Workers & ]       [ Shared Storage ]
- MySQL 8.4 InnoDB       - Sentinel / Cluster           [ Horizon Process ]       - S3 / MinIO / NFS
- 985 Normalized Tables  - DB 0: Sessions               - Queue: Inbound          - Encrypted Docs
- 2,180 Foreign Keys     - DB 1: Cache (Tenant Namesp.) - Queue: Outbound         - Resumes / Receipts
- Connection Pooling     - DB 2: Queue / Mutexes        - Queue: Webhooks         - Randomized Keys
- Async Read Replica     - DB 3: Reverb WebSockets      - Queue: Default / High   - Presigned URLs
```

---

## 2. Infrastructure Components

### 2.1 Web & Application Server Tier
- **Reverse Proxy:** Nginx 1.26+ configured with HTTP/2, TLS 1.3, strict security headers (HSTS, CSP, X-Frame-Options: SAMEORIGIN, X-Content-Type-Options: nosniff).
- **PHP FastCGI Process Manager (PHP-FPM):**
  - PHP 8.3 / 8.4 runtime with OPcache enabled (`opcache.enable=1`, `opcache.validate_timestamps=0` in production, `opcache.max_accelerated_files=30000`, `opcache.memory_consumption=256`).
  - Process Manager: `pm = dynamic`, `pm.max_children = 120`, `pm.start_servers = 30`, `pm.min_spare_servers = 20`, `pm.max_spare_servers = 40`.
- **Stateless Compute Mandate:** Application instances write no volatile state to local disk. All sessions reside in Redis, all uploaded assets stream directly to S3/MinIO, and logs output to stdout/daily syslog collectors.

### 2.2 Database Tier
- **Engine:** MySQL 8.4 / MariaDB 11.4 Enterprise.
- **Topology:** Single primary writer with asynchronous/semi-synchronous read replicas.
- **Connection Configuration:**
  - Connection pooling managed via Laravel database connection pool / ProxySQL.
  - InnoDB buffer pool configured to 70-80% of dedicated database RAM.
  - Strict SQL mode: `STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION,ERROR_FOR_DIVISION_BY_ZERO`.
- **Authoritative Baseline:** Exactly 985 tables, 2,180 foreign keys, 4,451 indexes.

### 2.3 Caching & In-Memory Tier (Redis)
- **Engine:** Redis 7.2+ with Redis Sentinel or AWS ElastiCache / Azure Cache for Redis.
- **Partitioning:**
  - Database 0: Application Sessions (`SESSION_DRIVER=redis`).
  - Database 1: Application Cache (`CACHE_STORE=redis`) with strict tenant namespace prefixes (`tenant:{tenant_id}:{key}`).
  - Database 2: Queue Broker (`QUEUE_CONNECTION=redis`).
  - Database 3: Real-Time WebSockets (Laravel Reverb).

### 2.4 Queue & Asynchronous Processing (Horizon)
- **Queue Supervisor:** Laravel Horizon running under systemd or Kubernetes daemonset.
- **Partitioned Queues:**
  - `high`: Critical interactive jobs (password resets, 2FA codes, MFA notifications).
  - `webhooks`: Inbound and outbound webhook dispatches.
  - `inbound`: Asynchronous integration entity ingestion.
  - `outbound`: External ERP/Payroll/Banking push dispatches.
  - `default`: General domain events, reporting exports, document conversions.
  - `low`: Analytics snapshots, log archiving, housekeeping.
- **Scaling:** Dynamic process scaling based on queue depth and latency thresholds.

### 2.5 Real-Time WebSockets (Laravel Reverb)
- **Server:** Laravel Reverb running as an independent systemd service.
- **Load Balancing:** Terminated behind TLS reverse proxy; sticky connections routed via Redis pub/sub.

### 2.6 Distributed Scheduler
- **Execution:** `php artisan schedule:run` triggered every minute via cron.
- **Concurrency Protection:** All scheduled tasks implement `withoutOverlapping()` using Redis mutex locks to guarantee single-execution across horizontal application nodes.

---

## 3. Scaling Topologies

### 3.1 Single-Server Small Enterprise Deployment
- Recommended for implementations under 2,500 active employees.
- Single physical or virtual instance (16 vCPU, 64 GB RAM, NVMe storage).
- Nginx, PHP-FPM, MySQL 8.4, Redis, Horizon, and Reverb co-located with dedicated resource cgroups.
- Local NVMe backup snapshotting with offsite object storage replication.

### 3.2 Multi-Server High-Availability Cluster
- Recommended for implementations over 2,500 employees, multi-tenant SaaS, or high-concurrency payroll cycles.
- Dual or multi-zone AWS / Azure / Private Cloud VPC:
  - 2+ Web/API Compute Nodes behind ALB.
  - 2+ Dedicated Queue Worker / Horizon Nodes.
  - 1 Primary + 1 Replica MySQL Cluster with automated failover (AWS RDS Multi-AZ).
  - 3-Node Redis Sentinel Cluster.
  - S3/MinIO Distributed Object Storage.

---

## 4. Disaster Recovery & Availability Targets
- **Recovery Point Objective (RPO):** $< 15\text{ minutes}$ (continuous binary log shipping).
- **Recovery Time Objective (RTO):** $< 60\text{ minutes}$ (automated infrastructure reprovisioning).
- **Availability Target:** 99.95% uptime excluding scheduled maintenance windows.
