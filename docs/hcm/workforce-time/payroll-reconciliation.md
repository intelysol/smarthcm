# Payroll Reconciliation Engine

## 1. Discrepancy Detection
`PayrollTimeReconciliationService` compares exported payable hours against payroll processed pay records:
- Missing employees in payroll run
- Unexpected employees in payroll without attendance export
- Regular hours mismatches (`> 0.05h variance`)
- Overtime hours mismatches (`> 0.05h variance`)

## 2. Audit & Resolution
Reconciliation records are stored in `hcm_payroll_time_reconciliations` with status `balanced` or `discrepancy_detected` and granular discrepancy JSON diffs.