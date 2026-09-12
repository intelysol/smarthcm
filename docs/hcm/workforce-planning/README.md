# Flow HCM — Strategic Workforce Planning & Organizational Modeling (Epic 2.24)

## Executive Overview
The **Flow HCM Strategic Workforce Planning Module** provides an enterprise-grade platform for organizational planning, budgeted positions, future workforce demand/supply alignment, scenario modeling, and labor cost forecasting.

### Core Architectural Separation
Workforce planning strictly distinguishes between 5 distinct data realities:
```text
ACTUAL
vs
PLAN
vs
BUDGET
vs
FORECAST
vs
SCENARIO
```

> **Fundamental Principle:** Workforce Planning describes what the organization **intends** to happen. Core HR, Recruitment, Payroll, Finance, and other HCM operational domains remain authoritative for what **actually** happens. Planning data never directly overwrites operational records.

---

## Key Capabilities

1. **Planning Cycles & Versioning**:
   - Multi-year, annual, quarterly, and project-based planning cycles (`FY2027`, `Q1-2027`).
   - Lifecycle: `draft` $\to$ `open` $\to$ `under_review` $\to$ `approved` $\to$ `locked`.
   - Once locked, plans are frozen as immutable snapshots. Revisions require creating a new tracked version (`v1` $\to$ `v2`).
2. **Workforce Demand & Supply Modeling**:
   - Driver-based headcount demand (revenue, workload, customer-to-staff ratios, project expansion).
   - Expected internal supply calculations (retirements, attrition assumptions, internal transfers, mobility).
   - Quantitative Workforce Gap analysis ($\text{Demand} - \text{Supply} = \text{Gap}$).
3. **Position Planning & Position Budgets**:
   - Formal position planning model separate from employees.
   - Statuses: `planned`, `budgeted`, `open`, `occupied`, `frozen`, `cancelled`, `eliminated`.
   - Comprehensive position budget components (base salary, bonus, benefits, statutory taxes, recruitment, equipment).
   - Deterministic Total Employment Cost using exact decimal/Money arithmetic.
4. **Hiring Plans & Recruitment Handoff**:
   - Position-linked hiring requirements with prioritization and replacement types (`immediate`, `delayed`, `internal`, `external`, `none`).
   - Seamless integration payload to Recruitment without creating candidate records directly.
5. **Workforce Scenario Simulation**:
   - What-if models: Base Plan, Aggressive Growth, Cost Reduction, Hiring Freeze, Restructuring.
   - Side-by-side comparison matrix with absolute and percentage variances for headcount, hires, exits, and labor costs.
6. **Actual vs Plan Analytics**:
   - Automated variance analysis comparing Core HR actuals with planned, budgeted, and forecast metrics.
7. **Strict AI Advisory Guardrails**:
   - Non-autonomous AI assistant that explains variances, summarizes plans, and suggests aggregate reskilling/hiring strategies.
   - Strictly prohibited from individual employee profiling, termination selection, or autonomous plan modification.
