# Enterprise Report Governance Framework

This document outlines the governance principles, ownership model, and operational rules governing report creation, execution, and lifecycle management in the Enterprise Application Platform.

---

## 1. Single Business Ownership Model

Every report in the Enterprise Application Platform requires exactly one designated Business Owner and one Data Owner:
- **Business Owner:** Accountable for business requirements, field definitions, interpretation, and audience scope.
- **Data Owner:** Accountable for the underlying SSoR transactional integrity, security classifications, and performance.
- **Reporting / Analytics Team:** Owns the presentation infrastructure, query builder framework, and export delivery engines.

| Report Category | Example Report | Business Owner | Data Owner | Primary Purpose |
|---|---|---|---|---|
| **Operational** | Employee Directory | Core HR Lead | Core HR Domain | Daily operational roster checks |
| **Operational** | Attendance Exceptions | Attendance Manager | Time & Attendance | Daily payroll & shift exception resolution |
| **Operational** | Leave Requests Queue | Absence Specialist | Absence Domain | Approval turnaround tracking |
| **Management** | Headcount & FTE Distribution | HR Director | Core HR & Job Arch | Workforce capacity and department planning |
| **Management** | Turnover & Retention Analysis | Talent Director | Offboarding & Core HR | Flight risk and attrition root cause analysis |
| **Management** | Payroll Cost Register | Compensation Director | Payroll Processing | Departmental salary expenditure tracking |
| **Executive** | Executive Intelligence Brief | CHRO | Analytics & BI | Board-level monthly strategic workforce briefing |
| **Executive** | Labor Cost & Headcount Forecast | CFO | Finance & Workforce Cost | Fiscal budget variance and labor runrate |
| **Regulatory** | Statutory Compliance & Training | Compliance Officer | Workforce Compliance | Statutory OSHA/Infosec audit defensibility |
| **Regulatory** | Platform Security & Audit Trail | CISO | Security & Identity | Access logs, data access tracking, forensics |

---

## 2. Safe Query Building & Metadata Execution

To ensure database stability and prevent data leaks, the Report Builder adheres to strict architectural constraints:
1. **Zero Arbitrary SQL Execution:** Normal users, managers, and administrators cannot execute raw SQL queries.
2. **Metadata-Driven Data Products:** All reports query declared datasets (`workforce`, `attendance`, `leave`, `payroll`, `compliance`) with pre-approved join graphs and field catalogs.
3. **Automatic Tenant Scoping:** Every generated SQL query automatically binds `tenant_id = current_tenant()` at the root table and all join branches.
4. **Execution Mode Gating:**
   - **Synchronous Execution:** On-demand reports with estimated row counts $< 5,000$ and simple aggregations (timeout: $10$ seconds).
   - **Asynchronous Background Execution:** Large datasets or detailed transaction registers ($> 5,000$ rows) dispatch to `GenerateScheduledHcmReportJob` on the background queue, saving artifacts to secure storage and notifying the requester via in-app alert.

---

## 3. Data Freshness Display Standards

Every dashboard card, report view, and export must explicitly declare its data freshness:
- **"Real-Time" / "Live":** Permitted only when the report directly reads from operational tables within the past 60 seconds (e.g. Current Clock-Ins, Pending Requests).
- **"Updated X minutes ago":** Used for cached operational aggregations.
- **"Batch Snapshot as of YYYY-MM-DD HH:MM":** Used for closed period payroll runs, daily midnight headcount snapshots, or quarterly talent matrices.
- Under no circumstances will a cached or batch report be labeled as "Live".

---

## 4. Report Lifecycle & Deprecation

```text
DRAFT ──> REVIEW ──> CERTIFIED ──> DEPRECATED ──> ARCHIVED
```
- **Draft:** Author designing report layout and selecting fields.
- **Review:** Evaluated by Governance Lead for security, index efficiency, and SSoR alignment.
- **Certified:** Production-ready report added to the tenant Report Catalog.
- **Deprecated:** Maintained for 12 months for scheduled report compatibility, but hidden from the "Create New Report" selection.

---
*Certified Enterprise Report Governance Framework — SmartHCM Enterprise Platform*
