# Workforce Planning Data Model

The module introduces 20 normalized database tables:

| Table Name | Description | Key Relationships |
|---|---|---|
| `hcm_workforce_plans` | Central plan header & cycle definition | Tenant, Company, Department, Owner |
| `hcm_workforce_plan_versions` | Version audit history & change rationale | Plan |
| `hcm_workforce_plan_periods` | Monthly/quarterly planning intervals | Plan |
| `hcm_workforce_plan_assumptions` | Configurable assumptions (attrition, inflation, taxes) | Plan |
| `hcm_workforce_demand_plans` | Driver-based required FTE demand | Plan, Department, Grade |
| `hcm_workforce_supply_plans` | Projected organic workforce supply & exits | Plan, Department |
| `hcm_workforce_headcount_plans` | Deterministic monthly balance records | Plan, Period, Department |
| `hcm_workforce_position_plans` | Planned, budgeted, open, and frozen positions | Plan, Department, Grade |
| `hcm_workforce_position_budgets` | Position financial allocations & total employment cost | Position Plan |
| `hcm_workforce_hiring_plans` | Hiring requirements & replacement definitions | Plan, Position Plan, User |
| `hcm_workforce_cost_plans` | Labor cost categories (actual, budget, forecast) | Plan, Department |
| `hcm_workforce_skill_demand_plans` | Skill gap tracking & upskilling plans | Plan |
| `hcm_workforce_capacity_plans` | Operational workload capacity models | Plan |
| `hcm_workforce_scenarios` | Hypothetical what-if models | Base Plan, User |
| `hcm_workforce_scenario_assumptions` | Scenario-specific assumption overrides | Scenario |
| `hcm_workforce_scenario_positions` | Scenario position actions (freeze, add, eliminate) | Scenario, Position Plan |
| `hcm_workforce_scenario_headcount` | Simulated scenario monthly headcount | Scenario |
| `hcm_workforce_scenario_costs` | Simulated scenario cost impacts | Scenario |
| `hcm_workforce_plan_approvals` | Workflow approval audit steps | Plan, Approver |
| `hcm_workforce_plan_snapshots` | Immutable JSON state frozen on plan lock | Plan, User |
