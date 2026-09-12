# Security, Multi-Tenancy & Access Control

## Multi-Tenant Isolation
All tables in Organizational Design (`job_families`, `job_sub_families`, `career_tracks`, `career_levels`, `job_levels`, `job_profiles`, `job_profile_versions`, `job_evaluations`, `org_design_scenarios`, `org_design_scenario_nodes`, `org_design_health_issues`) include mandatory `tenant_id` foreign keys and Eloquent tenant scoping.

---

## Role-Based Access Control (RBAC)
* **Organization Designer / HR Architect (`hcm.org_design.manage`)**:
  * Create/update Job Families, Levels, Profiles, and Scenarios.
  * Execute impact analyses and evaluation scoring.
* **HR Business Partner / Specialist (`hcm.org_design.view`)**:
  * View-only access to published Job Profiles and Career Frameworks.
* **Compensation Administrator (`hcm.compensation.manage`)**:
  * Authoritative for salary grades and compensation bands; evaluates suggested grades from Job Evaluations.
* **Core HR Administrator (`hcm.core_hr.manage`)**:
  * Authoritative for executing actual organizational change personnel actions.

---

## Non-Destructive Scenario Isolation
Scenario nodes and reorganization plans remain completely isolated from Core HR. Operational queries (employee directory, organizational charts, payroll routing) exclusively read live Core HR entities.
