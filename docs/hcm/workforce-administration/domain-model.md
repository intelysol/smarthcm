# Domain Model & Entities

## 1. Entity Relationship Overview
The Workforce Administration module owns operational and governance control metadata while referencing authoritative entities across HCM domains.

```
  ┌────────────────────────────────────────────────────────┐
  │                    HCM Operations                      │
  │                                                        │
  │  OpsQueue ────< OpsQueueItem (references Entity/Emp)   │
  │                                                        │
  │  OpsException ──┬──< OpsExceptionAssignment            │
  │                 └──< OpsExceptionEvent                 │
  │                                                        │
  │  OpsSlaPolicy ────< OpsSlaInstance                     │
  │                                                        │
  │  OpsGovernancePolicy ──< OpsGovernanceRule             │
  │                                                        │
  │  OpsBulkOperation ──┬──< OpsBulkOperationItem          │
  │                     ├──< OpsBulkOperationValidation    │
  │                     └──< OpsBulkOperationError         │
  │                                                        │
  │  OpsDataQualityRun ───< OpsDataQualityResult           │
  │  OpsDataQualityRule                                    │
  │                                                        │
  │  OpsReconciliationRule ──< OpsReconciliationResult     │
  │                                                        │
  │  OpsChecklistTemplate ──< OpsChecklistInstance ──< Item│
  │                                                        │
  │  OpsCalendarEvent / OpsDeadline                        │
  │  OpsConfigurationHealthCheck                           │
  └────────────────────────────────────────────────────────┘
```

## 2. Core Entities & Schemas

### `OpsQueue` & `OpsQueueItem`
Configurable operational task queues that aggregate pointers to domain records without copying full records.
- Fields: `id`, `tenant_id`, `code`, `name`, `category`, `default_priority`, `target_sla_hours`, `is_active`.
- Items: `id`, `tenant_id`, `queue_id`, `item_number`, `title`, `entity_type`, `entity_id`, `employee_id`, `priority`, `status`, `assigned_to`, `assigned_team`, `due_at`, `first_responded_at`, `resolved_at`, `is_sla_breached`, `payload`.

### `OpsException`, `OpsExceptionAssignment`, `OpsExceptionEvent`
Generic HCM operational exception tracking.
- Severity levels: `critical`, `high`, `medium`, `low`, `informational`.
- Lifecycle status: `detected` $\to$ `assigned` $\to$ `investigating` $\to$ `action_required` $\to$ `resolved` $\to$ `verified` $\to$ `closed`.

### `OpsBulkOperation` & Child Entities
Governed multi-stage bulk operations engine with audit trails and validation.
- Status: `draft` $\to$ `validating` $\to$ `dry_run_ready` $\to$ `approved` $\to$ `executing` $\to$ `completed` / `failed`.
- Validation summary captures `valid_count`, `warning_count`, `error_count`, and `impacted_domains`.
