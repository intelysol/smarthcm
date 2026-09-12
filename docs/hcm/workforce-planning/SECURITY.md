# Workforce Planning Security & Permissions

## Permissions Defined

- `hcm.workforce_planning.view`: View high-level workforce plans and dashboards.
- `hcm.workforce_planning.manage`: Global workforce planning administration.
- `hcm.workforce_plan.create`: Author new planning cycles.
- `hcm.workforce_plan.edit`: Modify open plans.
- `hcm.workforce_plan.submit`: Advance plans into review workflow.
- `hcm.workforce_plan.approve`: Approve plans.
- `hcm.workforce_plan.lock`: Freeze plans and generate immutable snapshots.
- `hcm.position_plan.view`: View positions and occupancy statuses.
- `hcm.position_plan.manage`: Add, freeze, or eliminate positions.
- `hcm.hiring_plan.view`: View hiring requirements.
- `hcm.hiring_plan.manage`: Create and approve hiring requirements.
- `hcm.workforce_scenario.view`: View scenario models.
- `hcm.workforce_scenario.manage`: Create and simulate what-if scenarios.
- `hcm.workforce_cost.view`: Access confidential labor cost and budget totals.
- `hcm.workforce_cost.manage`: Adjust labor cost budget lines.
- `hcm.workforce_sensitive.view`: Elevated permission to view confidential compensation structures.

## Row-Level Scope Rules
- **Multi-Tenant Isolation**: Cross-tenant plan access is strictly prevented.
- **Department Scope**: Managers can only view workforce plans and positions assigned to their operational department.
