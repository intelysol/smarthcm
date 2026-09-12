# HCM Analytics Architecture & Pipeline Design

## Architectural Flow
```text
HCM TRANSACTIONAL DOMAINS
(Core HR, Recruitment, Attendance, Leave, Payroll, Benefits, Expenses, Performance, ER, Helpdesk)
       │
       ▼
DOMAIN EVENTS / PERIODIC SNAPSHOTS
       │
       ▼
HCM ANALYTICS ADAPTER & METRIC REGISTRY
(Dimensions, Facts, Daily/Monthly Snapshots, Formula Engine)
       │
       ▼
ANALYTICS & REPORTING SERVICES
(Standard Dashboards, Custom Report Builder, Alerts, Data Quality Monitor)
       │
       ▼
CONSUMER INTERFACES
(CHRO Executive Hub, Manager MSS, REST APIs, CSV Exports, AI Analytics Assistant)
```

## Guiding Principles
1. **Separation of Concerns**: Analytics reads and computes metrics; it never mutates transactional records.
2. **Immutable Point-in-Time Snapshots**: Workforce snapshots capture historical organizational states for reproducible reporting.
3. **Safe Evaluation**: Metric calculations are metadata-driven and never execute arbitrary dynamic PHP `eval()`.
4. **Controlled AI Integration**: AI inquiries generate validated analytical query specifications and never execute arbitrary SQL.
