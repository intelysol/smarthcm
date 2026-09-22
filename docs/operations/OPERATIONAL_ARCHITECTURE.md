# Operational Architecture & Site Reliability Engineering (SRE) Guide

## 1. Executive Summary
The Enterprise Application Platform's operational architecture is built upon zero-trust observability, continuous health monitoring, automated alert evaluation, and structured incident response. Every request, background job, integration event, and analytical calculation generates correlated telemetry that enables site reliability engineers to diagnose and remediate issues in real time.

---

## 2. Observability & Telemetry Pipeline

```text
Incoming Request / External Event
      │
      ├── [CaptureCorrelationId Middleware]
      │       ├── Generates / captures X-Request-ID & X-Correlation-ID
      │       ├── Enriches Log::shareContext and Context::add
      │       └── Injects correlation headers into outgoing response
      │
      ├── [Domain & Service Layer]
      │       ├── Health Signals: /health, /health/live, /health/ready,
      │       │                   /health/dependencies, /health/services
      │       ├── OperationalTelemetryService records OpsMetric (latencies, errors, throughput)
      │       └── IncidentManagementService governs OpsIncident lifecycle
      │
      ├── [Alert Evaluation Engine]
      │       ├── Evaluates OpsAlertRule thresholds (>, <, =, >=, <=)
      │       ├── Raises OpsAlert events with payload context
      │       └── Triggers runbook escalation paths
      │
      └── [Operations Workspaces]
              ├── Platform Operations Console (/operations) -> Cluster-wide SRE
              └── Tenant Operations Console (/tenant-admin/operations) -> Tenant-scoped
```

---

## 3. Telemetry Tiers & Service Classification
The platform establishes four discrete service tiers (`docs/operations/service-catalog.yaml`):
- **Tier 0 (Mission-Critical):** Core Database, Identity/Auth, Multi-Tenancy Engine, Redis Cache, Queue Broker, and Storage. Max RTO: 15 min | RPO: 1 min | Availability: 99.99%.
- **Tier 1 (Core Business Operations):** HCM Core, Payroll Engine, Time & Attendance, Performance, and Recruitment ATS. Max RTO: 1 hour | RPO: 5 min | Availability: 99.95%.
- **Tier 2 (Secondary Business Workflows):** Multi-Step Workflow Engine, Analytics Engine, Notification Hub, Reverb WebSockets, Integration Hub. Max RTO: 4 hours | RPO: 1 hour | Availability: 99.9%.
- **Tier 3 (Auxiliary Services):** HCM AI Concierge, Commercial Billing. Max RTO: 24 hours | Availability: 99.0%.

---

## 4. Health Probing Model

| Endpoint | Probe Type | Purpose | HTTP 200 Condition | HTTP 503 Condition |
|---|---|---|---|---|
| `/health/live` | Liveness | Orchestrator container ping | Web server process responsive | Process down |
| `/health/ready` | Readiness | Traffic routing readiness | Primary DB & storage accessible | DB or storage unreachable |
| `/health/dependencies`| Dependencies | Backing infrastructure status | All Tier-0 infrastructure online | Any Tier-0 dependency failure |
| `/health/services` | Domain Services | Micro-module health check | Core domain services initialized | Domain failure |
| `/health` | Detailed Health | Full cluster health status | All checks return 'ok' | Any failure |

---

## 5. Correlated Logging Standard
Every log entry adheres to the JSON structured schema:
```json
{
  "timestamp": "2026-09-22T12:00:00.000000Z",
  "level": "ERROR",
  "message": "Database query timed out during gross-to-net payroll execution",
  "context": {
    "request_id": "req_a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "correlation_id": "corr_98765432-10fe-dcba-0987-654321fedcba",
    "tenant_id": "018f8e02-9999-7111-8222-123456789abc",
    "user_id": 42,
    "ip": "192.168.1.100",
    "uri": "/api/payroll/run",
    "method": "POST",
    "service": "payroll-engine",
    "duration_ms": 3542.12
  }
}
```

---

## 6. Dead-Letter Queue & Horizon SRE Operations
- **Queue Drivers:** Redis backed with persistent Redis AOF logging.
- **Dead-Letter Queue:** Failed background jobs automatically route to the `failed_jobs` table with serialised payload, stack trace, and correlation context.
- **Triage & Retry:** Platform operators can inspect, retry individual jobs, retry all jobs per queue, or purge poisoned jobs via Artisan CLI or Operations Console.
