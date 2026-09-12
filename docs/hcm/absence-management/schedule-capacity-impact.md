# Schedule & Operational Capacity Impact

## Impact Evaluation (`HcmAbsenceOperationalImpact`)
When an absence occurs, the system evaluates the affected shift roster:
- Determines `shift_id`, `department_id`, and `required_skill`.
- Flags whether minimum operational staffing levels are breached (`understaffed_risk = true`).
- Records the precise hours deficit (`capacity_loss_hours`).

## Integration with Scheduling (Epic 2.46)
- Automatically removes or flags the absent worker from active roster allocations.
- Emits capacity shortfall signals to Epic 2.45 for site-level workload balancing.