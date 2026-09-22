# Enterprise Platform — Health Check Framework & Probe Specification

## 1. Executive Summary
The Health Check Framework provides standardized, high-performance HTTP probes for automated infrastructure orchestrators (Kubernetes kubelet, AWS Application Load Balancers, Azure App Gateway, Docker healthcheck, and external uptime monitors).

---

## 2. Probe Endpoints

### 2.1 Process Liveness Probe: `GET /health/live`
- **Purpose:** Verifies that the PHP-FPM / Laravel runtime process is responsive and capable of handling incoming TCP requests.
- **Dependency Isolation:** Executes **zero database or network calls**. Does not fail if downstream databases or caches are temporarily unreachable, preventing cascading pod restarts.
- **HTTP Response:** `200 OK`
```json
{
  "status": "ok",
  "application": "Enterprise Platform",
  "timestamp": "2026-09-14T11:47:00+00:00",
  "uptime_seconds": 128
}
```

### 2.2 Deep Readiness Probe: `GET /health/ready`
- **Purpose:** Verifies whether the compute node is ready to accept production traffic. Validates connectivity to the primary database (`SELECT 1`) and storage persistence.
- **Orchestration Action:** If this probe returns `503`, the load balancer takes this specific node out of service rotation until connectivity resumes.
- **HTTP Response:** `200 OK` (Ready) or `503 Service Unavailable` (Not Ready)
```json
{
  "status": "ok",
  "ready": true,
  "timestamp": "2026-09-14T11:47:00+00:00",
  "checks": {
    "database": {
      "status": "ok",
      "latency_ms": 1.42,
      "connection": "mysql"
    },
    "storage": {
      "status": "ok",
      "latency_ms": 2.15,
      "disk": "local"
    }
  }
}
```

### 2.3 Detailed Health Inspection: `GET /health`
- **Purpose:** Comprehensive telemetry for monitoring agents and operations dashboards. Inspects database, cache, queue, storage, and Redis connectivity, capturing execution latency in milliseconds.
- **Information Masking:** Never exposes raw database credentials, connection passwords, IP topology, or internal exception stack traces.
- **HTTP Response:** `200 OK` (Healthy) or `503 Service Unavailable` (Degraded)

---

## 3. Kubernetes Ingress Configuration Example
```yaml
livenessProbe:
  httpGet:
    path: /health/live
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 10
  timeoutSeconds: 3
  failureThreshold: 3

readinessProbe:
  httpGet:
    path: /health/ready
    port: 80
  initialDelaySeconds: 15
  periodSeconds: 10
  timeoutSeconds: 5
  failureThreshold: 2
```

---

## 4. Administrative Health Dashboard: `GET /operations/system-health`
- Web-based single pane of glass for platform administrators.
- Live telemetry cards for Database, Cache, Queue Workers, Memory/Runtime, and Schema baseline.
- Audit table for recent API gateway calls and dead-letter/failed background jobs.
