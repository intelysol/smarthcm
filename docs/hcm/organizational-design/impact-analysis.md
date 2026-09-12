# Architecture Impact Analysis

## Purpose & Scope
Before retiring, merging, or significantly altering a Job Profile or Organizational Unit, the `ArchitectureImpactAnalysisService` computes pre-activation blast radius metrics across all connected HCM modules.

---

## Assessed Dimensions

```mermaid
graph TD
    Change[Job Profile / Architecture Change] --> Impact[Impact Analysis Engine]
    
    Impact --> P[Positions: Count & List of affected seat IDs]
    Impact --> E[Employees: Count & List of active workers in affected positions]
    Impact --> R[Recruitment: Active job requisitions linked to the role]
    Impact --> C[Compensation: Associated salary grades & compensation bands]
    Impact --> L[Learning: Assigned mandatory learning requirements]
```

* **Positions**: Direct query on `Position` records referencing `job_id`.
* **Employees**: Authoritative `Employee` records assigned to affected positions.
* **Recruitment**: Active requisitions in `hcm_recruitment_requisitions`.
* **Compensation**: Compensation bands linked via `job_grade_id`.
* **Learning**: Required courses mapped via `LearningRequirement`.
