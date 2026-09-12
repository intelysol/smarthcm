# EPIC 2.49 Architecture Assessment
## HCM Workforce Cost, Labor Cost Allocation, Workforce Economics & Labor Cost Intelligence

**Date:** September 2026  
**Status:** Approved & Ready for Implementation  
**Module:** `app/Domains/WorkforceCost/`  

---

## 1. Executive Summary & Objective

Epic 2.49 establishes the unified **Workforce Economics and Cost Intelligence** layer for the SmartHCM enterprise platform.

It provides senior leadership, HR business partners, finance analysts, and operational managers with comprehensive visibility into:
1. **Total Workforce Cost (Actual, Planned, Forecast, Estimated, Allocated, Scenario)**
2. **Cost Breakdown by Multi-Dimensional Facets** (Legal entity, Department, Cost Center, Position, Job Family, Project, Worker Type)
3. **Labor Cost Allocation** (Direct, Percentage, Hours-based, FTE-based, Cost-driver, Activity-based, Schedule-based)
4. **Workforce Economics Metrics** (Cost/FTE, Cost/Employee, Cost/Hour, Cost/Productive Hour, Overtime Ratio, Contractor Ratio)
5. **Absence, Vacancy & Overtime Economics** (Economic impact of unplanned absence, open position vacancy drag, multi-tier overtime cost)
6. **Scenario Modeling** (Economic evaluation of hiring, freezes, contractor substitution, outsourcing, automation, reskilling, relocation)
7. **Multi-Way Closed-Loop Reconciliation** (Reconciles Workforce Cost analytics against authoritative Payroll run totals and Finance GL records)
8. **Advisory AI Intelligence** (Cost driver explanations, anomaly detection, scenario comparison, and optimization recommendations with zero autonomous decision-making)

---

## 2. Critical Ownership Boundaries (Zero Duplicate Engines)

The architecture strictly respects the bounded contexts of existing platform domains:

| Domain | Authoritative Ownership | Workforce Cost Interaction |
| :--- | :--- | :--- |
| **Payroll** (`app/Domains/Payroll/`) | Owns calculation, periods, earnings, deductions, net pay, statutory deductions, pay runs (`payroll_runs`, `payroll_calculation_snapshots`, `payroll_payslips`). | **Consumes** payroll run and line totals via approved immutable snapshots. **Never** modifies payroll calculation data. |
| **Finance** (`app/Domains/Finance/`) | Owns Chart of Accounts, General Ledger, accounting periods, journal entries, posting, financial actuals. | **Provides** analytical labor cost allocation proposals. Finance remains authoritative for accounting. |
| **Compensation** (`app/Domains/Compensation/`) | Owns salary structures, grades, bands, merit increases, bonus planning, cycles. | **Consumes** base pay plans, merit assumptions, and compensation baselines. |
| **Benefits** (`app/Domains/Benefits/`) | Owns benefit plans, enrollment, employer & employee contributions. | **Consumes** employer benefit costs, health premiums, and retirement contributions. |
| **Expenses** (`app/Domains/Expenses/`) | Owns claims, per diem, travel expenses, reimbursements, policy validations. | **Consumes** approved workforce-related travel and operational reimbursements. |
| **Time & Attendance** (`app/Domains/Attendance/`) | Owns actual attendance, raw punches, worked hours, multi-tier overtime (`hcm_overtime_tier_records`), timesheets, chargeable vs payable allocations (`hcm_time_allocations`). | **Consumes** actual worked hours, overtime hours, and project timesheet distributions. |
| **Workforce Scheduling** (`app/Domains/Scheduling/`) | Owns shift definitions, rosters, planned hours, open shift bids, coverage requirements. | **Consumes** planned labor utilization and shift premium expectations. |
| **Workforce Planning** (`app/Domains/WorkforcePlanning/`) | Owns workforce plans, budgeted positions, headcount targets, scenarios (`hcm_workforce_scenarios`). | **Provides** economic analysis of headcount and position plans; **consumes** planning assumptions. |
| **Workforce Capacity** (`app/Domains/Capacity/` & Epic 2.45) | Owns workload models, required vs available capacity, productivity benchmarks. | **Consumes** capacity metrics to compute cost-per-capacity and productive hour rates. |
| **Absence Management** (`app/Domains/Absence/` & Epic 2.48) | Owns absence events, continuous periods, RTW phased capacity, operational impact. | **Consumes** absence lost hours to quantify direct paid absence and replacement drag. |

---

## 3. Cost Classification & Dimension Matrix

### 3.1 Cost Classification
Every line in the Workforce Cost domain is categorized into:
1. **Direct Labor**: Regular salary, worked hours, overtime, shift premiums, project labor.
2. **Indirect Labor**: Management, administrative overhead, support functions, internal non-billable time.
3. **Workforce Burden**: Employer payroll taxes, social security, health/life insurance, retirement contributions.
4. **Workforce Acquisition**: Agency fees, recruitment advertising, background checks, onboarding expenses.
5. **Workforce Development**: Training programs, certifications, tuition reimbursements.
6. **Workforce Mobility**: Relocation allowances, expat packages, temporary assignments.
7. **Contractor / Contingent**: External agency workers, freelance invoices, contractor hourly rates.
8. **Workforce Opportunity Cost**: Vacancy cost (unfilled capacity), absence loss, premium replacement costs.

### 3.2 Strict Cost Nature Distinction
Costs MUST never silently mix accounting actuals with hypothetical numbers. Every line records its exact nature:
- `ACTUAL`: Backed by processed payroll, paid invoices, or recorded punches.
- `PLANNED`: Budgeted or approved in workforce planning.
- `FORECAST`: Statistically projected for future periods.
- `ESTIMATED`: Calculated using standard cost models or hourly proxies.
- `ALLOCATED`: Distributed across projects/cost centers via allocation rules.
- `SCENARIO`: Modeled in what-if simulations.

---

## 4. Domain Architecture (`app/Domains/WorkforceCost/`)

```
WorkforceCost/
├── Contracts/
│   ├── CostAggregationInterface.php
│   └── CostAllocationRuleInterface.php
├── DTOs/
│   ├── CostSnapshotData.php
│   ├── AllocationResultData.php
│   └── ForecastProjectionData.php
├── Enums/
│   ├── CostCategory.php
│   ├── CostNature.php
│   ├── CostComponentType.php
│   ├── AllocationMethod.php
│   └── ReconciliationStatus.php
├── Models/
│   ├── HcmWorkforceCostModel.php
│   ├── HcmWorkforceCostSnapshot.php
│   ├── HcmWorkforceCostLine.php
│   ├── HcmWorkforceCostAllocationRule.php
│   ├── HcmWorkforceCostAllocation.php
│   ├── HcmWorkforceCostForecast.php
│   ├── HcmWorkforceCostVariance.php
│   ├── HcmWorkforceCostEconomics.php
│   ├── HcmWorkforceCostScenario.php
│   ├── HcmWorkforceCostReconciliation.php
│   └── HcmWorkforceCostAudit.php
├── Services/
│   ├── LaborCostAggregationService.php
│   ├── LaborCostAllocationService.php
│   ├── WorkforceCostForecastService.php
│   ├── WorkforceCostVarianceService.php
│   ├── WorkforceEconomicsService.php
│   ├── WorkforceCostScenarioService.php
│   ├── WorkforceCostReconciliationService.php
│   └── AdvisoryWorkforceCostAiService.php
├── Jobs/
│   ├── BuildWorkforceCostSnapshotJob.php
│   ├── ExecuteLaborCostAllocationJob.php
│   ├── GenerateWorkforceCostForecastJob.php
│   └── ReconcileWorkforceCostJob.php
├── Http/Controllers/
│   ├── WorkforceCostSnapshotController.php
│   ├── LaborCostAllocationController.php
│   ├── WorkforceCostForecastController.php
│   ├── WorkforceCostVarianceController.php
│   ├── WorkforceEconomicsController.php
│   ├── WorkforceCostScenarioController.php
│   ├── WorkforceCostReconciliationController.php
│   └── AdvisoryWorkforceCostAiController.php
└── Routes/
    └── api.php
```

---

## 5. Security, Multi-Tenancy & Medical/Salary Privacy

1. **Multi-Tenant Isolation**: Every database table includes `tenant_id` foreign-keyed to `tenants` with automatic Eloquent scoping.
2. **Access Control (RBAC)**:
   - *Line Managers*: Only view aggregated costs and allocated hours for their specific organizational unit/project; individual salaries are hidden.
   - *Finance / HR Analysts*: Access to departmental allocations, variance analysis, and reconciliation.
   - *Payroll Administrators*: Authoritative payroll snapshot linkage and reconciliation.
   - *Executives*: Enterprise-wide macro economics (cost per FTE, overtime ratios).
3. **Differential Privacy & Small Group Suppression**: If a reporting group has fewer than 3 employees, individual compensation cannot be deduced through drill-downs.

---

## 6. AI Governance & Ethical Boundaries

The `AdvisoryWorkforceCostAiService` operates under strict enterprise guardrails:
- `is_advisory_only = true`
- `autonomous_actions_permitted = false`
- **Prohibited Actions**: No autonomous termination recommendations, no discriminatory profiling, no automatic payroll alterations, and no direct posting to General Ledger without human review.

---

## 7. Migration & Database Schema Design

Table prefix: `hcm_workforce_cost_*`
1. `hcm_workforce_cost_models`
2. `hcm_workforce_cost_snapshots`
3. `hcm_workforce_cost_lines`
4. `hcm_workforce_cost_allocation_rules`
5. `hcm_workforce_cost_allocations`
6. `hcm_workforce_cost_forecasts`
7. `hcm_workforce_cost_variances`
8. `hcm_workforce_cost_economics`
9. `hcm_workforce_cost_scenarios`
10. `hcm_workforce_cost_reconciliations`
11. `hcm_workforce_cost_audits`

All primary keys use UUIDs (`Str::uuid()`), monetary figures use `decimal(14, 4)` for precision, and indexes are placed on all tenant, employee, department, cost center, and period keys.