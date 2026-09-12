# Coverage & Replacement Planning

## Replacement Candidate Ranking
When operational impact indicates an understaffing risk, `AbsenceOperationalImpactService::suggestReplacements()` evaluates eligible replacement candidates based on:
1. **Skill Match**: Candidates must possess required skills or certifications.
2. **Availability**: Candidates must not have conflicting shift allocations or approved leave.
3. **Fatigue & Overtime Rules**: Compliance with rest period requirements and weekly overtime ceilings.

## Candidate Selection & Assignment
Supervisors can assign the selected candidate directly through the API:
```json
POST /api/v1/hcm/absence/impacts/{id}/assign-replacement
{
  "replacement_employee_id": 108,
  "coverage_status": "assigned"
}
```