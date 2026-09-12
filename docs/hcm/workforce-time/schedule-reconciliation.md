# Schedule Reconciliation

## 1. Scheduled vs Actual Work
The system continuously reconciles planned shifts from Epic 2.46 against actual clock sessions:
- **Scheduled Time**: Planned start and end per roster assignment
- **Actual Time**: Verified clock-in and clock-out timestamps
- **Approved Time**: Verified payable time approved by manager
- **Chargeable Time**: Project/task time allocated to client/cost center

## 2. Adherence Metrics
- Schedule adherence rate: `(Actual Hours / Scheduled Hours) * 100`
- Variance tracking: Late minutes, early departure minutes, undertime, unscheduled shifts