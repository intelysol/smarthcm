# Recovery Point Objective (RPO) & Recovery Time Objective (RTO) Standard

## 1. Core Principles
- **Recovery Point Objective (RPO):** The maximum acceptable data loss measured in time between the failure event and the last valid restorable transaction.
- **Recovery Time Objective (RTO):** The maximum permissible duration from incident declaration to full restoration of operational service.

---

## 2. Service Tier Classifications & Targets

| Tier | Classification | Definition | Target RPO | Target RTO | Maximum Tolerable Downtime (MTD) |
|---|---|---|:---:|:---:|:---:|
| **Tier 0** | Platform Critical | Foundational services without which no tenant can authenticate or operate (Core DB, Identity, Storage, Cache, Queue). | $< 15\text{ min}$ | $< 60\text{ min}$ | $2\text{ hours}$ |
| **Tier 1** | Business Critical | Core business transactional engines (HCM Lifecycle, Payroll, Time & Attendance, Recruitment). | $< 15\text{ min}$ | $< 120\text{ min}$ | $4\text{ hours}$ |
| **Tier 2** | Important Workflows | Secondary processes and integrations (Workflows, Analytics, Notification Hub, Webhooks). | $< 60\text{ min}$ | $< 240\text{ min}$ | $8\text{ hours}$ |
| **Tier 3** | Non-Critical | Auxiliary value-add components (AI Concierge, Platform Commercial Billing). | $< 24\text{ hours}$ | $< 24\text{ hours}$ | $48\text{ hours}$ |

---

## 3. Business Rationale & Justifications

### Primary Database (Tier 0)
- **RPO = 15 Minutes:** Maintained via continuous binary log shipping and 24-hour full consistent snapshots. Under statutory payroll regulations, unrecorded clock-ins or payroll approvals exceeding 15 minutes would require manual workforce reconciliation.
- **RTO = 60 Minutes:** Restoring a 50GB database from S3 Glacier and replaying 15 minutes of binlogs takes approximately 35 minutes in staging drills, providing a 25-minute buffer within the 60-minute window.

### Payroll Engine (Tier 1)
- **RPO = 0 Minutes for Finalized Runs:** Finalized payroll batches write immutable ledger entries and generate signed PDF payslips immediately to S3 Object Lock storage.
- **RTO = 120 Minutes:** In the event of calculation interruption, the idempotent payroll calculation engine can re-process gross-to-net calculations without producing duplicate payments or corrupting bank export files.

### Redis Cache (Tier 0)
- **RPO = 60 Minutes:** Redis holds temporary sessions, query cache buffers, and rate-limiting buckets. In the event of complete cache loss, the application boots cleanly from the database with zero business data loss.
- **RTO = 15 Minutes:** Redis restarts or spin-up of a new cluster node completes in under 3 minutes.
