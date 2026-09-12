# Architecture & Domain Boundaries

## 1. Domain Ownership
- **Time & Attendance (Epic 2.47)**: Clock events, raw event audit, normalized attendance, sessions, timesheets, project time allocation, multi-tier overtime, compliance checks, payroll export, reconciliation.
- **Scheduling (Epic 2.46)**: Planned shifts, rosters, pattern definitions, coverage requirements.
- **Capacity & Workload (Epic 2.45)**: Capacity planning, productive hours, utilization metrics.
- **Leave Domain**: Leave types, balances, applications, approved leave transactions.
- **Payroll Domain**: Monetary pay calculation, pay rules, taxation, net disbursement.
- **Finance Domain**: GL accounts, project accounting, cost center ledgers.

## 2. Zero Duplicate Engines Rule
Under platform architectural rules, Epic 2.47 does NOT duplicate:
- Scheduling engine (consumes Epic 2.46 rosters)
- Leave engine (queries leave applications)
- Payroll engine (exports payable time payloads)
- Rules engine (uses platform rule evaluation)
- Notification platform (dispatches alerts via shared notification hub)