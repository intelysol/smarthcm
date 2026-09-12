# Leave Management Integration

## Non-Duplication Architecture
Epic 2.48 **does not** duplicate leave entitlement calculations, accrual rules, or balance ledgers. Those remain strictly within the core Leave domain (`leave_applications`).

## Event Synchronization
1. When an employee's leave application is approved, an event or webhook signals `AbsenceOrchestrationService::recordEvent()`.
2. The service creates a verified, planned `HcmAbsenceEvent` with `leave_application_id` foreign key reference.
3. Automatically evaluates operational schedule impacts on the affected shifts.
4. If leave is modified or canceled, the corresponding absence events and capacity impacts are updated accordingly.