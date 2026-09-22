# Blameless Post-Mortem Template

## 1. Incident Metadata
- **Incident ID:** `INC-YYYYMMDD-XXXX`
- **Severity:** [SEV-0 / SEV-1 / SEV-2 / SEV-3]
- **Incident Commander:** [Name / Role]
- **Lead Investigator:** [Name / Role]
- **Services Affected:** [e.g. core-database, payroll-engine]
- **Tenants Affected:** [All / Specific Tenant UUIDs]
- **Start Time (UTC):** `YYYY-MM-DD HH:MM:SS`
- **Detection Time (UTC):** `YYYY-MM-DD HH:MM:SS`
- **Mitigation Time (UTC):** `YYYY-MM-DD HH:MM:SS`
- **Resolution Time (UTC):** `YYYY-MM-DD HH:MM:SS`
- **Time to Detect (MTTD):** [XX minutes]
- **Time to Mitigate (MTTM):** [XX minutes]
- **Time to Resolve (MTTR):** [XX minutes]

---

## 2. Executive Summary
*Brief, non-technical explanation of what occurred, why it occurred, customer/business impact, and resolution.*

---

## 3. Customer & Business Impact
- **Total Affected Requests / Transactions:**
- **Error Percentage / SLA Impact:**
- **Financial / Operational Impact:**
- **Customer Support Tickets Logged:**

---

## 4. Incident Timeline (UTC)
| Timestamp (UTC) | Source / Actor | Event Description |
|---|---|---|
| `HH:MM:SS` | System Alert | Alert `alert-db-latency-high` triggered via PagerDuty |
| `HH:MM:SS` | SRE On-Call | Incident bridge opened, status declared SEV-1 |
| `HH:MM:SS` | Engineer | Long-running unindexed query identified on table `attendance_punches` |
| `HH:MM:SS` | SRE | Rogue query thread killed, connection pool normalized |
| `HH:MM:SS` | SRE | Health probes returned green, error budget burn ceased |
| `HH:MM:SS` | SRE | Incident declared resolved, post-mortem scheduled |

---

## 5. Root Cause Analysis (5 Whys)
1. **Why did the application return 504 Gateway Timeouts?**
   *Because the primary database connection pool was exhausted.*
2. **Why was the connection pool exhausted?**
   *Because 50 concurrent worker threads were blocked waiting on a table lock.*
3. **Why was there a table lock?**
   *Because a mass attendance reconciliation job executed a sequential scan without the composite tenant index.*
4. **Why did the job lack the composite index?**
   *Because the migration added the column in a recent release but omitted the index declaration.*
5. **Why was the missing index not caught in staging?**
   *Because test dataset volume in staging was too small to trigger query planner full-table scans.*

---

## 6. What Went Well vs. What Went Wrong
### What Went Well:
- Automated alerts triggered within 2 minutes of latency spike.
- Incident commander established the war room promptly.
- Runbook `database-failure.md` provided immediate diagnostic queries.

### What Went Wrong:
- Initial alert did not pinpoint the exact slow query thread ID.
- Staging load tests did not simulate 500k+ punch records.

---

## 7. Corrective & Preventative Action Items (CAPA)
| Action Item | Owner | Priority | Target Completion Date | Ticket / PR |
|---|---|---|---|---|
| Add missing composite index on `(tenant_id, punch_time)` | DB Team | P0 | `YYYY-MM-DD` | PR #1234 |
| Add staging data synthesizer for high-volume attendance tests | QA / SRE | P1 | `YYYY-MM-DD` | ISSUE #567 |
| Update `database-failure.md` runbook with automated kill command | SRE | P2 | `YYYY-MM-DD` | PR #1235 |
