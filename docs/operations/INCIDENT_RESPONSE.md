# Enterprise Platform — Incident Response & Triage Protocol

## 1. Severity Classification Matrix

| Severity | Definition & Impact | Response Target | Triage Team | Example Scenarios |
|---|---|---|---|---|
| **SEV-1 (Critical)** | Catastrophic platform downtime, active data breach, total database failure, or widespread cross-tenant leakage. | **< 15 min** acknowledgment<br>**< 60 min** resolution/mitigation | VP Engineering, Lead SRE, Security Lead, DBA | Primary DB down, `/health/ready` returns 503 across all nodes, payroll payment duplicate explosion. |
| **SEV-2 (Major)** | Major business module failure affecting multiple tenants (e.g. all attendance biometric ingestion failing, background queue stalled). Core platform accessible. | **< 30 min** acknowledgment<br>**< 4 hours** resolution | Staff Engineer, Module Lead, SRE | Horizon worker failure, Redis OOM eviction loop, dead-letter spike $> 100$ items. |
| **SEV-3 (Moderate)** | Non-critical feature degradation, single tenant isolated issue, intermittent 500 error on specific reports. Workarounds available. | **< 2 hours** acknowledgment<br>**< 24 hours** resolution | On-call Engineer, QA Engineer | Slow export query, UI rendering glitch on legacy browser, localized rate-limiting misconfiguration. |
| **SEV-4 (Minor)** | Cosmetic defect, minor documentation inaccuracy, non-blocking administrative warning. | **< 24 hours** acknowledgment<br>Next sprint release | Support Engineer, Product Owner | Typo on dashboard stat card, non-critical log warning message. |

---

## 2. Incident Handling Workflow

```text
1. Detection (Synthetic monitor, /health probe, customer report, operational alert)
   ↓
2. Declaration & Bridge (Slack #incident-war-room, conference bridge, ticket created)
   ↓
3. Containment (Drain traffic, rate-limit offending IP, freeze affected queue, feature flag toggle)
   ↓
4. Root-Cause Diagnosis (Correlate request_id, examine api_request_logs, check system health UI)
   ↓
5. Mitigation / Fix (Rollback, hotfix deploy, restart workers, database replica failover)
   ↓
6. Verification (Confirm /health/ready returns 200, test critical HCM journeys)
   ↓
7. Retrospective & Post-Mortem (Published within 48 hours with blameless 5-Whys)
```

---

## 3. Communication & Escalation Paths
- **Internal Stakeholders:** Executive briefing within 30 minutes of SEV-1 declaration.
- **Customer Status Page:** Updated every 20 minutes during active outages with objective, transparent status indicators.
- **Support Escalation:** Direct escalation to engineering via PagerDuty / Opsgenie on-call schedules.
