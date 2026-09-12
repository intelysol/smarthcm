# Payroll Integration & Batched Export

## 1. Integration Hub Export Payload
Approved and locked timesheets are aggregated into `hcm_payroll_time_exports` batches:
- `export_reference`: Unique batch identifier (e.g. `PAY-EXP-20261001-XXXXXX`)
- Per-employee totals: Regular hours, Overtime Tier 1, Tier 2, Tier 3 hours, Leave hours
- Batch totals: Total employees, total regular hours, total overtime hours

## 2. Non-Invasive Architecture
Time & Attendance exports payload to Integration Hub without directly modifying payroll ledger tables, adhering to domain boundaries.