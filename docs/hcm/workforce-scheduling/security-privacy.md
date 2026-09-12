# Security, RBAC & Privacy

## Multi-Tenant Isolation
- Strict database indexing and scoping by `tenant_id` on all tables (`hcm_schedule_coverage_requirements`, `hcm_employee_availabilities`, `hcm_shift_swap_requests`, `hcm_open_shifts`, etc.).
- Cross-tenant swaps and queries throw immediate validation exceptions.

## Granular RBAC Permissions
- `scheduling.view`: View schedules and coverage heatmaps.
- `scheduling.create`: Create periods, open shifts, requirements.
- `scheduling.validate`: Run schedule validation.
- `scheduling.publish`: Authorize and publish schedules.
- `scheduling.lock`: Lock finalized periods.
- `scheduling.swap`: Submit and respond to peer swaps.
- `scheduling.optimize`: Trigger optimization engine runs.
- `scheduling.view_cost`: View estimated labor costs and wage projections.

## Employee Privacy Protection
- **Medical & Absence Confidentiality**: Reasons for approved leave and private availability blocks are strictly masked on peer team schedules.
- **Coworker Views**: Employees can view who is working the same shift, but cannot see peers' compensation, overtime status, or private preference history.
