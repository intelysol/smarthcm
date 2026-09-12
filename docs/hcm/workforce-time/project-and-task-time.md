# Project & Task Time Allocation

## 1. Granular Time Allocations
Under `hcm_time_allocations`, employees allocate daily worked time across:
- `project_id` & `project_code`
- `task_id` & `task_name`
- `cost_center_id`
- `client_id`
- `work_type` (`regular`, `overtime`, `training`, `travel`, `meeting`, `project`, `on_call`, `standby`)
- `is_billable` flag

## 2. Reconciliation & Validation
Allocated minutes cannot exceed the entry's worked minutes, guaranteeing financial consistency between attendance payroll and project billable hours.