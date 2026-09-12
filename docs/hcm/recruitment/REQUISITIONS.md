# Job Requisitions & Position Validation

## Requisition Lifecycle
Requisitions follow a strict lifecycle managed by `JobRequisitionService`:
```text
Draft -> Submitted -> Under Review -> Approved -> Open -> Hiring -> Filled / Closed
```

## Workforce Planning Integration
Requisitions maintain foreign keys to strategic workforce planning entities created in Epic 2.24:
- `workforce_plan_id`
- `position_plan_id`
- `hiring_plan_id`

## Position Validation Rules
1. If linked to a Core HR Position, the position must exist and must NOT have `status == 'frozen'`.
2. If linked to a Workforce Planning Position Plan, it must NOT have `status == 'frozen'`.
3. If frozen, creating the requisition throws a `ValidationException` prohibiting requisition opening without authorized executive override.
