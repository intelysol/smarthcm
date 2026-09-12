# Payroll & Finance Closed-Loop Reconciliation

## Discrepancy Auditing
`WorkforceCostReconciliationService` continuously validates analytical numbers against authoritative accounting records:
- **Payroll Reconciliation**: Matches `PayrollRun` gross + employer cost totals with Workforce Cost lines.
- **Finance Reconciliation**: Compares General Ledger account totals against Workforce Cost snapshot aggregates.
- **Status Classification**: `MATCHED`, `MINOR_VARIANCE`, `MAJOR_VARIANCE`, or `MISSING_SOURCE`.