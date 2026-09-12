# HR Bulk Operations Engine

## 1. Overview
The Bulk Operations Engine allows authorized HR administrators to execute mass workforce changes in a safe, multi-stage, auditable workflow.

## 2. Multi-Stage Governed Lifecycle
```
Draft ──> Validating ──> Dry-Run Ready ──> Approved ──> Executing ──> Completed / Failed
```

### Stage 1: Draft
- Creation of `OpsBulkOperation` and target `OpsBulkOperationItem` records with snapshot of current vs target values.

### Stage 2: Dry-Run Validation
- Proactively checks each employee reference against business rules without modifying domain data.
- Generates `valid_count`, `warning_count`, `error_count`, and identifies downstream `impacted_domains` (e.g. Payroll cost centers, Benefits eligibility).

### Stage 3: Approval Gate
- Enforces Separation of Duties (SoD) where required. The operation must be signed off before execution is unlocked.

### Stage 4: Idempotent Queued Execution
- Asynchronously executed via `ExecuteBulkOperationJob`.
- Records execution errors on item level and writes an immutable audit record to `AuditService`.
