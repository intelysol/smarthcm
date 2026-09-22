# Enterprise Analytical Reconciliation Framework

This document defines the automated analytical reconciliation checks connecting the transactional layer (SSoR) to the analytical and reporting layer.

---

## 1. Reconciliation Principles

1. **Zero Discrepancy Baseline:**
   Analytical reports must perfectly reconcile with underlying transactional balances.
2. **Deterministic Root Cause Identification:**
   When variances occur (due to in-flight transactions, retro-adjustments, or data quality anomalies), reconciliation tools must isolate the exact delta records.
3. **Automated Continuous Verification:**
   Scheduled reconciliation jobs execute nightly or post-batch to detect drift between operational SSoR databases and analytical marts or summaries.

---

## 2. Core Reconciliation Checks

### 2.1 Check REC-01: Core HR Headcount vs Analytical Reporting
- **Transactional SSoR:** `employees` (`joining_date <= as_of_date` AND (`termination_date IS NULL` OR `termination_date > as_of_date`))
- **Analytical Presentation:** `HcmWorkforceAnalyticsService::getHeadcountSummary` & `HcmReportBuilderService` (`dataset = 'workforce'`)
- **Validation Formula:**
  $$\Delta = \text{Count}(\text{Active Employees in DB}) - \text{Reported Headcount}$$
- **Tolerance:** Exactly $0.00$
- **Failure Action:** If $\Delta \neq 0$, identify non-matching employee IDs; audit for soft-delete status or null joining dates.

---

### 2.2 Check REC-02: Payroll Register vs Disbursed Bank Actuals
- **Transactional SSoR:** `payroll_runs` and `payroll_entries` (`status = 'completed'`, `payment_date = cycle_date`)
- **Analytical Presentation:** `HcmCompensationAndPayrollAnalyticsService::getPayrollSummary` & `payroll_register` report
- **Validation Formula:**
  $$\Delta = \sum \text{gross\_salary}_{\text{entries}} - \text{Total Gross Pay}_{\text{run\_header}}$$
- **Tolerance:** $\$0.00$ (Cent-exact precision across all currencies).
- **Failure Action:** Block report publishing and alert Payroll Administrator for calculation line re-audit.

---

### 2.3 Check REC-03: Attendance Timesheets vs Overtime Payroll Inputs
- **Transactional SSoR:** `timesheets` (`status = 'approved'`, `total_approved_overtime_minutes`)
- **Downstream Consumer:** `payroll_inputs` (`source_module = 'attendance'`, `input_type = 'overtime_hours'`)
- **Validation Formula:**
  $$\Delta = \frac{\sum \text{approved\_ot\_minutes}}{60} - \sum \text{quantity}_{\text{payroll\_input\_lines}}$$
- **Tolerance:** Exactly $0.00$ hours.
- **Failure Action:** Flag un-exported approved overtime or duplicate payroll input lines.

---

### 2.4 Check REC-04: Leave Balance Ledger vs Approved Leave Requests
- **Transactional SSoR:** `leave_balances` (`used`, `pending`)
- **Downstream Consumer:** `leave_applications` (`status = 'approved'`, `duration`)
- **Validation Formula:**
  $$\Delta = \text{leave\_balances.used} - \sum \text{duration}_{\text{approved\_leaves\_in\_year}}$$
- **Tolerance:** Exactly $0.00$ days.
- **Failure Action:** Flag balance ledger desynchronization and log `DATA_QUALITY_LEAVE_OVERDRAFT` alert.

---

### 2.5 Check REC-05: Job Architecture Positions vs Occupied Headcount
- **Transactional SSoR:** `positions.filled_headcount`
- **Downstream Consumer:** `position_occupancies` (`starts_on <= today` AND (`ends_on IS NULL` OR `ends_on > today`))
- **Validation Formula:**
  $$\Delta = \text{positions.filled\_headcount} - \text{Count}(\text{Active Position Occupancies})$$
- **Tolerance:** Exactly $0$.
- **Failure Action:** Re-align position filled count; flag multi-occupancy violations exceeding authorized quotas.

---

## 3. Discrepancy Resolution Protocol

| Severity | Condition | Impact | Escalation Target | SLA |
|---|---|---|---|---|
| **CRITICAL (P0)** | Payroll gross or net discrepancy > $0.00 | Compliance & audit blocker | VP of Finance & Lead Payroll Admin | 2 hours |
| **HIGH (P1)** | Active employee missing from headcount report | Misleading board metric | Core HR Director | 4 hours |
| **MEDIUM (P2)** | Timesheet overtime delta vs payroll inputs | Potential under/over-payment | Workforce Time Administrator | 24 hours |
| **LOW (P3)** | Leave balance rounding discrepancy (< 0.1 day) | ESS display variance | Absence Administrator | 48 hours |

---
*Certified Enterprise Analytical Reconciliation Framework — SmartHCM Enterprise Platform*
