# Business Continuity Plan (BCP) & Degraded-Mode Operations

## 1. Plan Purpose
This Business Continuity Plan defines how core enterprise workforce operations continue even when major platform components, cloud regions, or external integration partners experience prolonged downtime.

---

## 2. Degraded-Mode Operations by Component

### A. External AI Provider Outage
- **Degraded Behavior:** HCM AI Concierge trips its circuit breaker.
- **Business Continuity:** Standard employee self-service, leave requests, timesheets, and policy lookups remain 100% operational in deterministic mode. Zero HCM business transactions are blocked.

### B. Third-Party Integration Partner Outage (SAP / NetSuite / Workday)
- **Degraded Behavior:** Outbound sync batches enter exponential backoff and queue checkpointing.
- **Business Continuity:** Employees can continue submitting expenses and time records. Internal records are saved authoritatively; sync transactions are buffered in the queue dead-letter table and automatically replayed upon partner restoration.

### C. Commercial Payment Gateway (Stripe) Outage
- **Degraded Behavior:** Subscription card updates and real-time invoice charges pause.
- **Business Continuity:** An automatic 7-day grace period is enforced across all tenant subscriptions. Zero customer tenant access is revoked due to external gateway payment processing errors.

### D. Biometric Attendance Device Network Severance
- **Degraded Behavior:** Biometric device cannot reach server API.
- **Business Continuity:** Biometric hardware stores up to 50,000 punches in local encrypted NVRAM buffer. Upon network reconnection, devices flush buffered logs sequentially.

---

## 3. Communication Cadence & Stakeholder Management
- **Internal Stakeholders (Execs/Legal):** Notified within 15 minutes of SEV-0 declaration; briefed hourly.
- **Tenant Administrators:** In-app banner and email notification dispatched within 30 minutes explaining degraded features and estimated time to resolution.
- **Public Status Page:** Updated every 30 minutes at `https://status.smarthcm.com`.
