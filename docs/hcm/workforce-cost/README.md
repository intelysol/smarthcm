# HCM Workforce Cost, Labor Cost Allocation, Workforce Economics & Labor Cost Intelligence (Epic 2.49)

## 1. Overview
Epic 2.49 establishes the enterprise-grade Workforce Economics and Cost Intelligence layer for the SmartHCM platform.

It answers the essential financial and operational questions:
- *How much does our workforce actually cost?*
- *What is the planned and forecasted workforce cost?*
- *Where is workforce money being spent across departments, projects, positions, and cost centers?*
- *What are our workforce economics metrics (cost per FTE, employee, labor hour, and productive hour)?*
- *What is the financial cost of overtime, unplanned absence, and unfilled vacancies?*
- *What is the cost difference between employees, contractors, and agency personnel?*
- *What are the economic impacts of workforce decisions (hiring, freeze, outsourcing, automation, relocation)?*

## 2. Guiding Principles & Architectural Boundaries
1. **Zero Duplicate Engines**: Workforce Cost is an **analytical economics layer**. It does not duplicate Payroll, Finance/GL, Compensation, Benefits, Expenses, Time & Attendance, Scheduling, Absence, Capacity, or Workforce Planning.
2. **Strict Cost Nature**: Fully partitions `ACTUAL`, `PLANNED`, `FORECAST`, `ESTIMATED`, `ALLOCATED`, and `SCENARIO` costs to avoid mixing accounting actuals with hypothetical projections.
3. **Immutable Versioned Snapshots**: Historical workforce cost calculations are locked and preserved without overwriting history.
4. **Advisory AI Only**: AI capabilities provide explanations and recommendations under strict human oversight (`is_advisory_only: true`, `autonomous_actions_permitted: false`).

## 3. Documentation Index
- [1. Architecture & Domain Boundaries](architecture.md)
- [2. Cost Classification & Cost Nature Framework](cost-classification-and-nature.md)
- [3. Normalized Cost Component Model](cost-component-model.md)
- [4. Payroll Ingestion & Consumption Pipeline](payroll-ingestion-integration.md)
- [5. Time & Attendance and Scheduling Integration](time-attendance-scheduling-integration.md)
- [6. Benefits, Expenses & Compensation Integration](benefits-expenses-compensation-integration.md)
- [7. Cost Snapshots, Versioning & Immutability](cost-snapshots-and-immutability.md)
- [8. Labor Cost Allocation Engine](labor-cost-allocation-engine.md)
- [9. Allocation Rules, Drivers & Validation](allocation-rules-and-validation.md)
- [10. Workforce Cost Forecasting](workforce-cost-forecasting.md)
- [11. Variance Analysis & Cost Drivers](variance-analysis-cost-drivers.md)
- [12. Workforce Economics & Productivity Metrics](workforce-economics-metrics.md)
- [13. Overtime, Absence & Vacancy Economics](overtime-absence-vacancy-economics.md)
- [14. Employee vs. Contractor Economics](employee-vs-contractor-economics.md)
- [15. Scenario Economic Modeling (What-Ifs)](scenario-economic-modeling.md)
- [16. Payroll & Finance Closed-Loop Reconciliation](payroll-finance-reconciliation.md)
- [17. AI Governance, Security, Privacy & Audit](ai-governance-security-audit.md)