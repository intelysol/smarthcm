# Job Profiles & Specifications

## Structure of a Job Profile
A **Job Profile** defines the complete standardized qualifications and responsibilities for a role:

* **Identification**: Unique Code (`code`), Title (`title`), Job Family & Sub-Family.
* **Level Mapping**: Career Track, Career Level, Job Level, non-binding suggested Job Grade.
* **Duties**: Responsibilities and Core Deliverables (JSON array).
* **Prerequisites**: Minimum experience years, education requirements, certifications, language requirements.
* **Working Conditions**: Travel percentage, remote work eligibility (`onsite`, `hybrid`, `remote`).
* **Skill Requirements**: Mapped directly to authoritative `CareerSkill` records with target proficiencies (`beginner`, `intermediate`, `advanced`, `expert`).
* **Competency Expectations**: Mapped directly to authoritative `Competency` records with criticality ratings (`low`, `medium`, `high`, `critical`).

---

## Immutable Versioning Lifecycle
Job profiles follow a strict governance lifecycle:
```text
draft → review → approved → published → retired
```

When an active profile is modified:
1. A new version snapshot is automatically saved in `job_profile_versions`.
2. Historical positions and past employees retain references to the version effective during their tenure.
3. Pre-activation impact analysis verifies affected positions, open requisitions, and active employees.
