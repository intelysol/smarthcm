# Planned vs. Unplanned Absence Lifecycle

## Categorization Framework
Absence events are strictly partitioned into two operational categories:

### 1. Planned Absence
- Originated from approved leave applications (`leave_applications`), scheduled training, or sanctioned leaves of absence.
- Has advance notice (> 24 hours).
- Triggers advance schedule adjustments, shift deallocations, and planned replacement sourcing.

### 2. Unplanned Absence
- Originated from sudden illness, emergency call-offs, or automated shift no-show detections.
- Zero or negligible advance notice.
- Triggers real-time operational capacity deficit alerts, urgent coverage candidate notifications, and immediate supervisory visibility.

## Lifecycle State Machine
```text
[Reported / Detected] 
         │
         ▼
    [Verified] ──(Disputed)──> [Cancelled]
         │
         ▼
     [Active]
         │
         ▼
    [Completed] ──(Requires Support)──> [Return To Work Plan]
```