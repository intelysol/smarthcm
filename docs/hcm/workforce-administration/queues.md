# HR Operational Queues

## 1. Concept & Scope
Operational queues organize and route cross-domain task items to specialized HR administrators, shared services, and operational teams without replicating domain entity records.

## 2. Standard Configurable Queues
- **New Hire Queue**: Verification of identity, contracts, and initial setup.
- **Employee Change Queue**: Department, location, manager, and designation changes.
- **Transfer Queue**: Inter-departmental and cross-branch relocation actions.
- **Promotion Queue**: Grade changes, compensation band updates, and title promotions.
- **Offboarding Queue**: Exit clearances, equipment return, and final settlement signoffs.
- **Compliance Queue**: Visa renewals, work permit audits, and mandatory certifications.
- **Document Verification Queue**: Verification of passport copies, degrees, and licenses.
- **Payroll Change Queue**: Bank detail changes, tax exemption declarations, and allowances.
- **Benefits Queue**: Enrollment reviews, dependent validations, and waiver approvals.
- **Data Quality Queue**: Incomplete profiles, invalid identifiers, and unlinked managers.
- **Integration Failure Queue**: Dead-lettered and retryable transaction queues.

## 3. Queue Lifecycle
```
Enqueued (Pending) ──> Assigned (In Progress) ──> Completed / Escalated / Cancelled
```
- Tracks SLA targets (`target_sla_hours`), due dates, and first response timestamps.
