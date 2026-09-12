# Recruitment Architecture & Domain Boundaries

## Bounded Context Ownership
The Recruitment module enforces strict domain separation:

```text
DOMAIN                  AUTHORITATIVE RESPONSIBILITY
────────────────────────────────────────────────────────────────────────
Recruitment             Candidates, Applications, Requisitions,
                        Pipelines, Interviews, Assessments, Offers,
                        Sources, Recruitment Activities.
Core HR                 Employee Master, Employment, Organization,
                        Position Occupancy, Reporting Hierarchies.
Workforce Planning      Workforce Demand, Budgeted Positions,
                        Hiring Requirements, Scenario Plans.
Onboarding              Pre-joining checklists, tasks, equipment,
                        and orientation milestones.
```

### Invariant Rules
1. **Recruitment is NOT the employee master**: Candidates and applicants exist independently of employee records. Even internal employees applying for jobs reference their employee profile without creating duplicate master identities.
2. **Deterministic Position Validation**: A requisition cannot be activated if the targeted Core HR position or Workforce Planning position is frozen or cancelled.
3. **Core HR Employee Provisioning**: When an offer is accepted and pre-employment checks are verified, Recruitment hands off the sanitized payload to Core HR (`EmployeeService::create`). Core HR provisions the employee ID and employment relation.
