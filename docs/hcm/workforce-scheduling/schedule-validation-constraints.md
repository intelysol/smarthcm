# Schedule Validation & Constraints

## Hard vs Soft Constraints

The validation engine distinguishes between constraints that must never be broken without emergency override and advisory warnings:

### Hard Constraints (Blocking Violations)
- **Approved Leave**: Employee is on approved annual, sick, or emergency leave (`leave_applications`).
- **Inactive Employment**: Employee is terminated, resigned, or suspended.
- **Missing Required Skill / Proficiency**: Worker fails required qualification bar.
- **Expired Mandatory License**: Mandatory compliance certification has expired.
- **Overlapping Assignments**: Worker already assigned to another shift on the same date.
- **Insufficient Rest**: Less than 11 hours rest between consecutive shifts.

### Soft Constraints (Advisory Warnings)
- **Overtime Risk**: Projected weekly hours exceed 40h or standard 48h limit.
- **Preference Violation**: Shift matches worker's `avoid` preference.
- **Position Mismatch**: Worker primary position does not match preferred role.

---

## Schedule Quality Score

The `schedule_quality_score` (0.00%–100.00%) evaluates the technical quality of the schedule:
$$\text{Quality Score} = (\text{Coverage Score} \times 0.7) + (\max(0, 100 - \text{Penalties}) \times 0.3)$$
Where:
- Critical Hard Violation: -25 points
- Soft Warning: -5 points

> **CRITICAL RULE**: The Schedule Quality Score represents the technical completeness and compliance of the schedule itself. It must NEVER be interpreted, reported, or used as an employee performance score.
