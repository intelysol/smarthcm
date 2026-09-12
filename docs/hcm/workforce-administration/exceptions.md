# Operational Exceptions & Resolution Lifecycle

## 1. Concept
HCM operational exceptions capture cross-domain anomalies, data synchronization failures, missing compliance proofs, and workflow blocks.

## 2. Standard Exception Model
- **Fields**: `exception_number`, `exception_type`, `severity`, `domain`, `entity_type`, `entity_id`, `employee_id`, `description`, `owner_id`, `assigned_team`, `detected_at`, `resolution_guidance`, `resolution_notes`, `resolved_at`, `resolved_by`.
- **Severity Levels**:
  - `Critical` (SLA: 4 hours)
  - `High` (SLA: 1 business day)
  - `Medium` (SLA: 3 business days)
  - `Low` (SLA: 5 business days)
  - `Informational`

## 3. Exception Lifecycle
```
Detected ──> Assigned ──> Investigating ──> Action Required ──> Resolved ──> Verified ──> Closed
```
Each state transition emits `HrOperationalException*` events, records immutable audit trails in `hcm_ops_exception_events`, and updates the corresponding SLA instance.
