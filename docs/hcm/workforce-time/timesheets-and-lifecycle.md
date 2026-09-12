# Timesheets & Lifecycle Management

## 1. Timesheet Aggregation
Timesheets aggregate daily session entries for a cutoff period (weekly, bi-weekly, monthly):
- Total scheduled minutes
- Total worked minutes
- Regular minutes
- Overtime minutes
- Paid leave minutes
- Holiday minutes

## 2. State Machine
```text
Draft → Submitted → Manager Approved → HR Approved → Locked → Exported → Reconciled
```
Locked timesheets cannot be modified without formal reopening authorization.