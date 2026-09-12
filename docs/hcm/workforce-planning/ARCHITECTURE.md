# Workforce Planning Architecture & System Boundaries

## Architectural Topology

```text
CORE HR / ACTUAL WORKFORCE
    │
    ├── Employees (Active / Terminated)
    ├── Actual Positions
    ├── Core Organization (Company, Dept, Grade, Cost Center)
    └── Payroll / Actual Compensation
          │
          ▼
     ANALYTICS READ-MODEL
          │
          ├──────────────────────────┐
          │                          │
          ▼                          ▼
   REPORTING / DASHBOARDS      WORKFORCE PLANNING
                                     │
                    ┌────────────────┼────────────────┐
                    │                │                │
                    ▼                ▼                ▼
                 Demand           Supply           Cost
                    │                │                │
                    └────────────────┼────────────────┘
                                     ▼
                             SCENARIO ENGINE
                                     │
                                     ▼
                            WORKFORCE PLAN
                                     │
                    ┌────────────────┼────────────────┐
                    ▼                ▼                ▼
                 Hiring          Budget          Positions
                    │                │                │
                    └────────────────┼────────────────┘
                                     ▼
                             APPROVAL / REVIEW
                                     │
                                     ▼
                         Locked Workforce Plan
                                     │
                                     ▼
                    Recruitment Requisition Feeds
```

## System Invariants

1. **Read-Only Ingestion**: Workforce planning queries Core HR and Payroll as immutable read models to establish baselines. It never writes back to `employees`, `payroll_runs`, or `departments`.
2. **Deterministic Financials**: Labor cost calculations strictly use decimal data types. Floating-point types (`float`, `double`) are prohibited in financial columns.
3. **Headcount Conservation Formula**: Monthly balance is calculated deterministically:
   $$\text{Closing Headcount} = \text{Opening Headcount} + \text{Planned Hires} + \text{Transfers In} - \text{Planned Exits} - \text{Transfers Out}$$
4. **Immutability of Locked Plans**: Once approved and locked, plan records are read-only. Adjustments require generating an explicit child version.
5. **Separation of Position and Employee**: A planned position can exist without an employee, and an employee can occupy a position. Creating a planned position never creates an employee record.
